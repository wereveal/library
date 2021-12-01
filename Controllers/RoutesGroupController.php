<?php
/**
 * Class RoutesGroupController
 * @package Ritc_Library
 */
namespace Ritc\Library\Controllers;

use Ritc\Library\Interfaces\ManagerControllerInterface;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\ConfigControllerTraits;
use Ritc\Library\Traits\LogitTraits;
use Ritc\Library\Views\RoutesGroupView;

/**
 * Controller for the Routes to Group Admin page.
 * Allows one to map which routes have specific group access.
 * If you are not in the group, you can't go there.
 * The route has to be in the database and should not be able to be deleted.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v1.0.0
 * @date    2021-11-26 15:16:56
 * @change_log
 * - v1.0.0         - Production version finally? php8 too      - 2021-11-26 wer
 * - v1.0.0-alpha.0 - Initial version                           - 08/04/2015 wer
 * @todo Everything
 */
class RoutesGroupController implements ManagerControllerInterface
{
    use LogitTraits;
    use ConfigControllerTraits;

    /** @var RoutesGroupView  */
    private RoutesGroupView $o_view;

    /**
     * RoutesGroupController constructor.
     *
     * @param Di $o_di
     */
    public function __construct(Di $o_di)
    {
        $this->setupManagerController($o_di);
        $this->o_view = new RoutesGroupView($o_di);
        $this->a_object_names = [];
        $this->setupElog($o_di);
    }

    /**
     * @return string
     */
    public function route():string
    {
        return $this->o_view->render();
    }

    /**
     * @return string
     */
    public function save():string
    {
        return '';
    }

    /**
     * @return string
     */
    public function update():string
    {
        return '';
    }

    /**
     * @return string
     */
    public function verifyDelete():string
    {
        return '';
    }

    /**
     * @return string
     */
    public function delete():string
    {
        return '';
    }
}
