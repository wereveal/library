<?php
namespace Wer\GuideBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class AdminController extends Controller
{
    public function indexAction()
    {
        $a_twig = array(
            'title'=>'Hello Admin Controller',
            'stylesheets'=>'',
            'body'=>'Hello Admin Controller',
            'javascripts'=>''
        );
        return $this->render('WerGuideBundle:Admin:index.html.twig', $a_twig);
    }
}
