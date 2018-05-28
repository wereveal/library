<?php
/**
 * Class TestsView
 * @package Ritc_Library
 */
namespace Ritc\Library\Views;

use Ritc\Library\Helper\ViewHelper;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\LogitTraits;
use Ritc\Library\Traits\ViewTraits;

/**
 * View for the Tests page.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v2.1.0
 * @date    2017-06-10 07:25:13
 * @change_log
 * - v2.1.0   - Added additional tests, bug fixes            - 2017-06-10 wer
 * - v2.0.0   - Name refactoring                             - 2017-05-14 wer
 * - v1.1.0   - removed abstract class Base, use LogitTraits - 09/01/2015 wer
 * - v1.0.0   - First stable version                         - 01/16/2015 wer
 * - v1.0.0β2 - changed to match DI/IOC                      - 11/15/2014 wer
 * - v1.0.0β1 - Initial version                              - 11/08/2014 wer
 */
class TestsView
{
    use ViewTraits, LogitTraits;

    /**
     * TestsView constructor.
     * @param \Ritc\Library\Services\Di $o_di
     */
    public function __construct(Di $o_di)
    {
        $this->setupElog($o_di);
        $this->setupView($o_di);
    }

    /**
     * @return string
     */
    public function renderList()
    {
        $a_message = ViewHelper::infoMessage('Select which Class you wish to test.');
        $a_twig_values = $this->createDefaultTwigValues($a_message, '/manager/config/tests/');
        $a_test = $this->readNav('ConfigTestLinks');
        $a_buttons = [];
        foreach ($a_test as $value) {
            $a_buttons[] = [
                'value' => $value['text'],
                'label' => $value['description'],
                'test'  => $value['text']
            ];
        }
        $a_twig_values['a_buttons'] = $a_buttons;
        $tpl = $this->createTplString($a_twig_values);
        return $this->renderIt($tpl, $a_twig_values);
    }

    /**
     * @param array $a_result_values
     * @return string
     */
    public function renderResults(array $a_result_values = array())
    {
        if (count($a_result_values) == 0) {
            $a_message = ViewHelper::failureMessage('No results were available.');
            $a_twig_values = $this->createDefaultTwigValues($a_message, '/manager/config/tests/');
        }
        else {
            $a_message = ViewHelper::infoMessage('Results');
            $a_twig_values = $this->createDefaultTwigValues($a_message, '/manager/config/tests/results/');
        }
        $a_twig_values = array_merge($a_twig_values, $a_result_values);
        $tpl = $this->createTplString($a_twig_values);
        return $this->renderIt($tpl, $a_twig_values);
    }
}
