<?php
/**
 *  @brief View for the User Admin page.
 *  @file PeopleAdminView.php
 *  @ingroup blog views
 *  @namespace Ritc/Library/Views
 *  @class PeopleAdminView
 *  @author William Reveal  <bill@revealitconsulting.com>
 *  @version 1.0.1ß
 *  @date 2014-11-15 15:20:52
 *  @note A file in Ritc Library
 *  @note <pre><b>Change Log</b>
 *      v1.0.1ß - Changed to use DI/IOC                           - 11/15/2014 wer
 *      v1.0.0ß - Initial version                                 - 11/13/2014 wer
 *  </pre>
 *  @TODO write the code to display users to allow for CRUD actions
 **/
namespace Ritc\Library\Views;

use Ritc\Library\Helper\ViewHelper;
use Ritc\Library\Models\PeopleModel;
use Ritc\Library\Models\GroupsModel;
use Ritc\Library\Models\RolesModel;
use Ritc\Library\Models\PeopleGroupMapModel;
use Ritc\Library\Models\GroupRoleMapModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\LogitTraits;

class PeopleAdminView
{
    use LogitTraits;

    private $o_people_model;
    private $o_group_model;
    private $o_role_model;
    private $o_pgm_model;
    private $o_grm_model;
    private $o_twig;

    public function __construct(Di $o_di)
    {
        $o_db                 = $o_di->get('db');
        $this->o_people_model = new PeopleModel($o_db);
        $this->o_group_model  = new GroupsModel($o_db);
        $this->o_role_model   = new RolesModel($o_db);
        $this->o_pgm_model    = new PeopleGroupMapModel($o_db);
        $this->o_grm_model    = new GroupRoleMapModel($o_db);
        $this->o_twig         = $o_di->get('twig');
        if (DEVELOPER_MODE) {
            $this->o_elog = $o_di->get('elog');
            $this->o_people_model->setElog($this->o_elog);
            $this->o_group_model->setElog($this->o_elog);
            $this->o_role_model->setElog($this->o_elog);
            $this->o_pgm_model->setElog($this->o_elog);
            $this->o_grm_model->setElog($this->o_elog);
        }
    }

    public function renderList($a_values)
    {
        $this->o_twig->render('@default/index.tpl', $a_values);
        return '';
    }
    /**
     * Something to keep phpStorm from complaining until I use the ViewHelper.
     * @return array
     */
    public function temp()
    {
        $a_stuph = ViewHelper::messageProperties(['stuff'=>'stuff']);
        return $a_stuph;
    }
}
