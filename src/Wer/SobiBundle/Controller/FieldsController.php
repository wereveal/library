<?php
namespace Wer\SobiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class FieldsController extends Controller
{
    /**
     * List the Fields in Sobi
     * @return Render object
    **/
    public function indexAction()
    {
        $a_twig = array(
            'title'=>'Hello Fields',
            'stylesheets'=>'',
            'body'=>'Hello Fields',
            'javascripts'=>''
        );
        return $this->render('WerSobiBundle:Default:fields.html.twig', $a_twig);
    }
    /**
     * Import the Fields from Sobi tables into Guide tables
     * @return Render object
    **/
    public function importAction()
    {
        $a_twig = array(
            'title'=>'Import Fields',
            'stylesheets'=>'',
            'body'=>'Import Fields',
            'javascripts'=>''
        );
        return $this->render('WerSobiBundle:Default:fields.html.twig', $a_twig);
    }
    /**
     * List the Fields imported into the Guide tables
     * @return Render object
    **/
    public function listAction()
    {
        $a_twig = array(
            'title'=>'List Import Results',
            'stylesheets'=>'',
            'body'=>'List Import Results',
            'javascripts'=>''
        );
        return $this->render('WerSobiBundle:Default:fields.html.twig', $a_twig);
    }
}
