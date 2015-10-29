<?php
/**
 *  @brief View for the Tests page.
 *  @file ManagerView.php
 *  @ingroup ritc_library views
 *  @namespace Ritc/Library/Views
 *  @class ManagerView
 *  @author William Reveal  <bill@revealitconsulting.com>
 *  @version 1.1.0
 *  @date 2015-09-01 08:05:20
 *  @note A file in Ritc Library
 *  @note <pre><b>Change Log</b>
 *      v1.1.0   - removed abstract class Base, use LogitTraits - 09/01/2015 wer
 *      v1.0.0   - First stable version                         - 01/16/2015 wer
 *      v1.0.0β2 - changed to match DI/IOC                      - 11/15/2014 wer
 *      v1.0.0β1 - Initial version                              - 11/08/2014 wer
 *  </pre>
 **/
namespace Ritc\Library\Views;

use Ritc\Library\Helper\ViewHelper;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\LogitTraits;
use Ritc\Library\Traits\ManagerViewTraits;

class TestsAdminView
{
    use LogitTraits, ManagerViewTraits;

    private $o_db;

    public function __construct(Di $o_di)
    {
        $this->o_db = $o_di->get('db');
        $this->setupView($o_di);
    }

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
