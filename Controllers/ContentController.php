<?php
/**
 * Class ContentController.
 * @package Ritc_Library
 */
namespace Ritc\Library\Controllers;

use Ritc\Library\Exceptions\ViewException;
use Ritc\Library\Helper\ViewHelper;
use Ritc\Library\Interfaces\ConfigControllerInterface;
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
 * @todo ContentController.php - Everything
 */
class ContentController implements ConfigControllerInterface
{
    use LogitTraits, ConfigControllerTraits;

    /** @var string $instance_failure Lets me know if the instance had a failure. */
    private $instance_failure = '';
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
        $this->setupElog($o_di);
        try {
            $this->o_view = new ContentView($o_di);
        }
        catch (ViewException $e) {
            $this->instance_failure = $e->getMessage();
        }
    }

    /**
     * Main method used to route the page to the appropriate controller/view/model.
     *
     * @return string
     */
    public function route():string
    {
        if (!empty($this->instance_failure)) {
            return $this->instance_failure;
        }
        switch ($this->form_action) {
            case 'edit':
                return $this->edit();
            case 'verify':
                return $this->verifyDelete();
            case 'update':
                return $this->update();
            case 'save_new':
                return $this->save();
            case 'delete':
                return $this->delete();
            default:
                return $this->o_view->render();
        }
    }

    /**
     * Method to delete data.
     *
     * @return string
     */
    public function delete():string
    {
        $a_message = ViewHelper::infoMessage('Needs to be written');
        return $this->o_view->render($a_message);
    }

    /**
     * Method to edit a content record.
     *
     * @return string
     */
    public function edit():string
    {
        $a_message = ViewHelper::infoMessage('Needs to be written');
        return $this->o_view->render($a_message);
    }

    /**
     * Method for saving data.
     *
     * @return string
     */
    public function save():string
    {
        $a_message = ViewHelper::infoMessage('Needs to be written');
        return $this->o_view->render($a_message);
    }

    /**
     * Method for updating data.
     *
     * @return string
     */
    public function update():string
    {
        $a_message = ViewHelper::infoMessage('Needs to be written');
        return $this->o_view->render($a_message);
    }

    /**
     * Method to display the verify delete form.
     *
     * @return string
     */
    public function verifyDelete():string
    {
        $a_message = ViewHelper::infoMessage('Needs to be written');
        return $this->o_view->render($a_message);
    }
}
