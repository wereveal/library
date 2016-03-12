<?php
/**
 * @brief     View for the Tests page.
 * @ingroup   ritc_library lib_views
 * @file      ManagerView.php
 * @namespace Ritc\Library\Views
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   1.1.0
 * @date      2015-09-01 08:05:20
 * @note <b>Change Log</b>
 * - v1.1.0   - removed abstract class Base, use LogitTraits - 09/01/2015 wer
 * - v1.0.0   - First stable version                         - 01/16/2015 wer
 * - v1.0.0β2 - changed to match DI/IOC                      - 11/15/2014 wer
 * - v1.0.0β1 - Initial version                              - 11/08/2014 wer
 */
namespace Ritc\Library\Views;

use Ritc\Library\Helper\ViewHelper;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\LogitTraits;
use Ritc\Library\Traits\ManagerViewTraits;

/**
 * Class TestsAdminView
 * @class   ManagerView
 * @package Ritc\Library\Views
 */
class TestsAdminView
{
    use LogitTraits, ManagerViewTraits;

    /**
     * TestsAdminView constructor.
     * @param \Ritc\Library\Services\Di $o_di
     */
    public function __construct(Di $o_di)
    {
        $this->setupView($o_di);
    }

    /**
     * @return string
     */
    public function renderList()
    {
        $values = [
            'menus' => $this->a_links,
            'links' => [
                [
                    'url'    => '/manager/tests/PeopleModel/',
                    'class'  => '',
                    'extras' => '',
                    'text'   => 'People Model Test'
                ]
        ]];
        return $this->o_twig->render('@pages/test.twig', $values);
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
