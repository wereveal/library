<?php

namespace Wer\GuideBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Wer\GuideBundle\Model\Field;

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
        return $this->render('WerGuideBundle:Manager:index.html.twig', $a_twig_values);
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
        return $this->render('WerGuideBundle:Manager:field.html.twig', $a_twig_values);
    }


}
