<?php

namespace Wer\GuideBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ManagerController extends Controller
{
    /**
     *  Displays a list of actions that one can do in the manager
    **/
    public function indexAction()
    {
        $a_twig_values = array(
            'title'       => 'Manager',
            'description' => 'This is a description',
            'site_url'    => "http://{$_SERVER['SERVER_NAME']}",
            'body_text'   => 'This is a test of Manager'
        );
        return $this->render('WerGuideBundle:Manager:index.html.twig', $a_twig_values);
    }

}
