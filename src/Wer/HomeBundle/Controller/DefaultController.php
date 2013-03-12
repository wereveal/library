<?php

namespace Wer\HomeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('WerHomeBundle:Default:index.html.twig', array('name' => $name));
    }
}
