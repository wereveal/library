<?php
/**
 * @brief     View for the Tests page.
 * @ingroup   lib_views
 * @file      LibraryView.php
 * @namespace Ritc\Library\Views
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   2.0.0
 * @date      2017-05-14 16:47:54
 * @note <b>Change Log</b>
 * - v2.0.0   - Name refactoring                             - 2017-05-14 wer
 * - v1.1.0   - removed abstract class Base, use LogitTraits - 09/01/2015 wer
 * - v1.0.0   - First stable version                         - 01/16/2015 wer
 * - v1.0.0β2 - changed to match DI/IOC                      - 11/15/2014 wer
 * - v1.0.0β1 - Initial version                              - 11/08/2014 wer
 */
namespace Ritc\Library\Views;

use Ritc\Library\Helper\ViewHelper;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\LogitTraits;
use Ritc\Library\Traits\ViewTraits;

/**
 * Class TestsView
 * @class   LibraryView
 * @package Ritc\Library\Views
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
        $a_test = [
            'PeopleModel' => 'People Model Test',
            'PageModel'   => 'Page Model Test',
            'UrlsModel'   => 'Urls Model Test'
        ];
        $a_buttons = [];
        foreach ($a_test as $key => $value) {
            $a_buttons[] = [
                'value' => $key,
                'label' => $value,
                'test'  => $key
            ];
        }
        $a_twig_values['menus'] = $this->o_nav;
        $a_twig_values['a_buttons'] = $a_buttons;
        $tpl = $this->createTplString($a_twig_values);
        return $this->o_twig->render($tpl, $a_twig_values);
    }

    /**
     * @param array $a_result_values
     * @return string
     */
    public function renderResults(array $a_result_values = array())
    {
        if (count($a_result_values) == 0) {
            $a_params = [
                'message' => 'No results were available.',
                'type'    => 'error'
            ];
            $a_message = ViewHelper::messageProperties($a_params);
            return $this->o_twig->render(
                '@pages/error.twig',
                [
                    'description'   => 'An error has occurred.',
                    'public_dir'    => '',
                    'a_message'     => $a_message,
                    'site_url'      => SITE_URL,
                    'rights_holder' => RIGHTS_HOLDER
                ]
            );
        }
        return $this->o_twig->render('@tests/results.twig', $a_result_values);
    }
}
