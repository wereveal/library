<?php

namespace Wer\GuideBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class MainController extends Controller
{
    public function indexAction()
    {
        $a_twig_values = array(
            'title'       => 'Guide',
            'description' => 'This is a description',
            'site_url'    => "http://{$_SERVER['SERVER_NAME']}",
            'body_text'   => 'This is a test for now'
        );
        return $this->render('WerGuideBundle:Main:index.html.twig', $a_twig_values);
    }
    /**
     * Displays the results of a search
    **/
    public function searchAction()
    {
    }
    /**
     *  Display the results from the selection of a category
    **/
    public function categoryAction()
    {
    }
    /**
     *  Displays the results from the selection of an alphanumeric
    **/
    public function alphaAction()
    {
    }
}
