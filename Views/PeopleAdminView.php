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

use Ritc\Library\Abstracts\Base;
use Ritc\Library\Helper\ViewHelper;
use Ritc\Library\Models\PeopleModel;
use Ritc\Library\Models\GroupsModel;
use Ritc\Library\Models\RolesModel;
use Ritc\Library\Models\PeopleGroupMapModel;
use Ritc\Library\Models\GroupRoleMapModel;
use Ritc\Library\Services\Di;

class PeopleAdminView extends Base
{
    private $o_user_model;
    private $o_group_model;
    private $o_role_model;
    private $o_ugm_model;
    private $o_urm_model;
    private $o_tpl;

    public function __construct(Di $o_di)
    {
        $this->setPrivateProperties();
        $o_db                = $o_di->get('db');
        $this->o_user_model  = new PeopleModel($o_db);
        $this->o_group_model = new GroupsModel($o_db);
        $this->o_role_model  = new RolesModel($o_db);
        $this->o_ugm_model   = new PeopleGroupMapModel($o_db);
        $this->o_urm_model   = new GroupRoleMapModel($o_db);
        $this->o_tpl         = $o_di->get('tpl');
    }

    public function renderList($a_values)
    {
        $this->o_tpl->render('@default/index.tpl', $a_values);
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
