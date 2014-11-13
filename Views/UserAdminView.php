<?php
/**
 *  @brief View for the User Admin page.
 *  @file UserAdminView.php
 *  @ingroup blog views
 *  @namespace Ritc/Library/Views
 *  @class UserAdminView
 *  @author William Reveal  <bill@revealitconsulting.com>
 *  @version 1.0.0ß
 *  @date 2014-11-13 13:42:43
 *  @note A file in Ritc Library
 *  @note <pre><b>Change Log</b>
 *      v1.0.0ß - Initial version - 11/13/2014 wer
 *  </pre>
 **/
namespace Ritc\Library\Views;

use Ritc\Library\Abstracts\Base;
use Ritc\Library\Core\DbModel;
use Ritc\Library\Core\Tpl;
use Ritc\Library\Helper\ViewHelper;
use Ritc\Library\Models\UsersModel;
use Ritc\Library\Models\GroupsModel;
use Ritc\Library\Models\RolesModel;
use Ritc\Library\Models\UserGroupMapModel;
use Ritc\Library\Models\UserRoleMapModel;

class UserAdminView extends Base
{
    private $o_user_model;
    private $o_group_model;
    private $o_role_model;
    private $o_ugm_model;
    private $o_urm_model;
    private $o_twig;
    protected $o_elog;
    protected $private_properties;

    public function __construct(DbModel $o_db)
    {
        $this->setPrivateProperties();
        $this->o_user_model  = new UsersModel($o_db);
        $this->o_group_model = new GroupsModel($o_db);
        $this->o_role_model  = new RolesModel($o_db);
        $this->o_ugm_model   = new UserGroupMapModel($o_db);
        $this->o_urm_model   = new UserRoleMapModel($o_db);
        $o_tpl               = new Tpl('twig_config.php');
        $this->o_twig        = $o_tpl->getTwig();
    }

    public function renderPage($a_values)
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