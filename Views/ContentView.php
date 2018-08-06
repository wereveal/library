<?php
/**
 * Class ContentView.
 * @package Ritc_Library
 */

namespace Ritc\Library\Views;

use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Exceptions\ViewException;
use Ritc\Library\Helper\ExceptionHelper;
use Ritc\Library\Helper\ViewHelper;
use Ritc\Library\Interfaces\ViewInterface;
use Ritc\Library\Models\ContentComplexModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\LogitTraits;
use Ritc\Library\Traits\ViewTraits;

/**
 * Manager for Content View.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version 1.0.0-alpha.0
 * @date    2018-06-01 11:44:46
 * @change_log
 * - v1.0.0-alpha.0 - Initial version.                                    - 2018-06-01 wer
 * @todo    ContentView.php - Everything
 */
class ContentView implements ViewInterface
{
    use LogitTraits, ViewTraits;

    /** @var ContentComplexModel $o_model */
    private $o_model;

    /**
     * ContentView constructor.
     *
     * @param \Ritc\Library\Services\Di $o_di
     * @throws ViewException
     */
    public function __construct(Di $o_di)
    {
        $this->setupView($o_di);
        try {
            $this->o_model = new ContentComplexModel($o_di);
        }
        catch (ModelException $e) {
            $message = 'Unable to create the ContentComplexModel instance';
            $err_no = ExceptionHelper::getCodeTextView('view object');
            throw new ViewException($message, $err_no, $e);
        }
        $this->setupElog($o_di);
    }

    /**
     * Main method required by interface.
     * Returns the list of content records in a nice view.
     *
     * @param array $a_message
     * @return string
     */
    public function render(array $a_message = []):string
    {
        try {
            $a_records = $this->o_model->readCurrent();
        }
        catch (ModelException $e) {
            $a_message = ViewHelper::errorMessage('Unable to read the current content records.');
        }
        $log_message = 'Records:  ' . var_export($a_records, TRUE);
        $this->logIt($log_message, LOG_ON, __METHOD__);

        $a_twig_values = $this->createDefaultTwigValues($a_message);
        $tpl = $this->createTplString($a_twig_values);
        return $this->renderIt($tpl, $a_twig_values);
    }

    /**
     * Renders the form to add/update/delete a content record.
     *
     * @param string $action
     * @return string
     */
    public function renderForm($action = 'new'):string
    {
        return 'TODO: implement renderForm() method.';
    }
}
