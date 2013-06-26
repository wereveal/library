<?php
/**
 *  Main Manager Controller for the Guide.
 *  @file ManagerController.php
 *  @class ManagerController
 *  @author William Reveal  <bill@revealitconsulting.com>
 *  @version 0.2
 *  @par Change Log
 *      v0.2 - New repository 2013-03-26
 *      v0.1 - Initial version 2012-06-04
 *  @par Wer Guide version 1.0
 *  @date 2013-03-26 15:49:24
 *  @ingroup guide
**/
namespace Wer\Guide\Controller;

use Symfony\\Framework\Controller\Controller;
use Wer\Guide\Model\Field;

class ManagerController extends Controller
{
    private $a_base_twig;
    private $o_wer_field;
    public function __construct()
    {
        $site_url = 'http://' . $_SERVER['SERVER_NAME'];
        $this->a_base_twig = array(
            'title'       => 'Manager',
            'description' => 'This is a description',
            'site_url'    => $site_url,
            'body_text'   => 'This is a test of Manager'
        );
        $this->o_wer_field = new Field();
    }
    /**
     *  Displays a list of actions that one can do in the manager
    **/
    public function indexAction()
    {
        $a_twig_values = $this->a_base_twig;
        $a_twig_values['description'] = 'Main Manager Page';
        return $this->render('WerGuide:Manager:index.html.twig', $a_twig_values);
    }
    /**
     *  Displays a list of the fields
     *  @param none
     *  @return str html
    **/
    public function fieldAction()
    {
        $a_twig_values = $this->a_base_twig;
        $a_twig_values['description'] = 'View Fields';
        $a_fields = $this->o_wer_field->readField();
        $a_twig_values['body_text'] = '';
        $a_twig_values['fields'] = $a_fields;
        return $this->render('WerGuide:Manager:field.html.twig', $a_twig_values);
    }


}
