<?php

namespace Wer\HomeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render(
            'WerHomeBundle:Default:index.html.twig',
            array(
                'title'       => 'Hello!',
                'description' => '',
                'site_url'    => "http://{$_SERVER['SERVER_NAME']}",
                'body_text'   => 'Hello!'
            )
        );
    }
}
