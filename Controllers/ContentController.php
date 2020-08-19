<?php
/**
 * Class ContentController.
 * @package Ritc_Library
 */
namespace Ritc\Library\Controllers;

use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Exceptions\ViewException;
use Ritc\Library\Helper\Arrays;
use Ritc\Library\Helper\ViewHelper;
use Ritc\Library\Interfaces\ConfigControllerInterface;
use Ritc\Library\Models\ContentComplexModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\ConfigControllerTraits;
use Ritc\Library\Traits\LogitTraits;
use Ritc\Library\Views\ContentView;

/**
 * Manages the Content.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version 1.0.0-alpha.0
 * @date    2018-05-30 16:58:04
 * @change_log
 * - v1.0.0-alpha.0 - Initial version        - 2018-05-30 wer
 */
class ContentController implements ConfigControllerInterface
{
    use LogitTraits;
    use ConfigControllerTraits;

    /** @var string $instance_failure Lets me know if the instance had a failure. */
    private $instance_failure = '';
    /** @var ContentComplexModel  */
    private $o_model;
    /** @var ContentView $o_view view for content. */
    private $o_view;

    /**
     * ContentController constructor.
     *
     * @param Di $o_di
     */
    public function __construct(Di $o_di)
    {
        $this->setupManagerController($o_di);
        try {
            $this->o_model = new ContentComplexModel($o_di);
        }
        catch (ModelException $e) {
            $this->instance_failure .= $e->getMessage();
        }
        $this->setupElog($o_di);
        try {
            $this->o_view = new ContentView($o_di);
        }
        catch (ViewException $e) {
            $this->instance_failure .= $e->getMessage();
        }
    }

    /**
     * Main method used to route the page to the appropriate controller/view/model.
     * Required by interface.
     *
     * @return string
     */
    public function route():string
    {
        if (!empty($this->instance_failure)) {
            return $this->instance_failure;
        }
        switch ($this->form_action) {
            case 'delete':
                return $this->delete();
            case 'edit_content':
                return $this->o_view->renderForm('by_c_id');
            case 'edit_content_by_pbm':
                return $this->o_view->renderForm('by_pbm_id');
            case 'new_content':
                return $this->o_view->renderForm('new');
            case 'save_new':
                return $this->save();
            case 'update':
                return $this->update();
            case 'verify':
                return $this->verifyDelete();
            case 'view_content':
                return $this->o_view->renderDetails();
            case 'view_all':
                return $this->o_view->renderAllVersions($this->a_post['content']['c_pbm_id']);
            default:
                return $this->o_view->render();
        }
    }

    /**
     * Method to delete data.
     * Required by interface.
     *
     * @return string
     */
    public function delete():string
    {
        if (empty($this->a_post['c_id'])) {
            $a_message = ViewHelper::failureMessage('Unable to delete the record: unknown reason');
        }
        else {
            try {
                $a_results = $this->o_model->readByContentId($this->a_post['c_id']);
                $pbm_id = $a_results['c_pbm_id'];
            }
            catch (ModelException $e) {
                $msg = 'Unable to delete the record: could not get all the records needed to delete.';
                $a_message = ViewHelper::errorMessage($msg);
                return $this->o_view->render($a_message);
            }
            if (CONTENT_VCS) { // don't actually delete records, make them not current.
                try {
                    $this->o_model->deactivateAll($pbm_id);
                    $a_message = ViewHelper::successMessage();
                }
                catch (ModelException $e) {
                    $msg = 'Unable to delete the record: with version control, 
                        records are set to "not current" but unable to do that.';
                    $a_message = ViewHelper::errorMessage($msg);
                }
            }
            else {
                try {
                    $this->o_model->deleteAllByPage(['pbm_id' => $pbm_id]);
                    $a_message = ViewHelper::successMessage();
                }
                catch (ModelException $e) {
                    $msg = 'Unable to delete the record: ' . $e->getMessage();
                    $a_message = ViewHelper::errorMessage($msg);
                }
            }
        }
        return $this->o_view->render($a_message);
    }

    /**
     * Method for saving new data.
     * Required by interface.
     *
     * @return string
     */
    public function save():string
    {
        $a_message = [];
        $a_content = $this->a_post['content'];
        $a_requires_values = ['c_pbm_id'];
        if (Arrays::hasBlankValues($a_content, $a_requires_values)) {
            $message = 'A page/block combo must be specified.';
            $a_message = ViewHelper::errorMessage($message);
        }
        else {
            try {
                $this->o_model->saveNew($a_content);
            }
            catch (ModelException $e) {
                $a_message = ViewHelper::errorMessage($e->errorMessage());
            }
        }
        return $this->o_view->render($a_message);
    }

    /**
     * Method for updating data.
     * Required by interface.
     *
     * @return string
     */
    public function update():string
    {
        $a_message = [];
        $a_content = $this->a_post['content'];
        try {
            $a_record = $this->o_model->readByContentId($a_content['c_id']);
        }
        catch (ModelException $e) {
            $a_message = ViewHelper::errorMessage('Could not update the record. Unknown error.');
            return $this->o_view->render($a_message);
        }
        if (Arrays::compareArrays($a_content, $a_record)) {
            $this->o_view->renderForm('by_c_id');
        }
        else {
            try {
                $this->o_model->updateContent($a_content);
                $a_message = ViewHelper::successMessage();
            }
            catch (ModelException $e) {
                $a_message = ViewHelper::errorMessage('Could not update the record. ' . $e->getMessage());
            }
        }
        return $this->o_view->render($a_message);
    }

    /**
     * Method to display the verify delete form.
     * Required by interface.
     *
     * @return string
     */
    public function verifyDelete():string
    {
        $c_id = $this->a_post['content']['c_id'];
        try {
            $a_content = $this->o_model->readByContentId($c_id);
        }
        catch (ModelException $e) {
            $a_message = ViewHelper::errorMessage('Could not determine the content to delete.');
            return $this->o_view->render($a_message);
        }
        $a_values = [
            'what'          => 'Content for page/block',
            'name'          => $a_content['page_title'] . '/' . $a_content['b_name'],
            'form_action'   => '/manager/config/content/',
            'btn_value'     => 'Content',
            'hidden_name'   => 'c_id',
            'hidden_value'  => $a_content['c_id']
        ];
        return $this->o_view->renderVerifyDelete($a_values);
    }
}
