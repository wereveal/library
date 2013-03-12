<?php
namespace Wer\GuideBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        // $a_info = ini_get_all();
        $a_twig = array(
            'title'=>'Hello Default Controller',
            'stylesheets'=>'',
            'body'=>'Hello Default Controller',
            'javascripts'=>''
        );
        return $this->render('WerGuideBundle:Default:index.html.twig', $a_twig);
    }
}
