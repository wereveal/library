<?php
namespace Wer\SobiBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ItemsController extends Controller
{
    /**
     * List the Items in Sobi
     * @return Render object
    **/
    public function indexAction()
    {
        $a_twig = array(
            'title'=>'Hello Items Controller',
            'stylesheets'=>'',
            'body'=>'Hello Items Controller',
            'javascripts'=>''
        );
        return $this->render('WerSobiBundle:Default:items.html.twig', $a_twig);
    }
    /**
     * Import the Items from Sobi tables into Guide tables
     * @return Render object
    **/
    public function importAction()
    {
        $a_twig = array(
            'title'=>'Import Items',
            'stylesheets'=>'',
            'body'=>'Import Items',
            'javascripts'=>''
        );
        return $this->render('WerSobiBundle:Default:items.html.twig', $a_twig);
    }
    /**
     * List the Items imported into the Guide tables
     * @return Render object
    **/
    public function listAction() {
        $a_twig = array(
            'title'=>'List Items Results',
            'stylesheets'=>'',
            'body'=>'List Items Results',
            'javascripts'=>''
        );
        return $this->render('WerSobiBundle:Default:items.html.twig', $a_twig);
    }
}
