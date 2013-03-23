<?php

namespace Wer\ImportBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class MainController extends Controller
{
    public function indexAction()
    {
        return $this->render(
            'WerImportBundle:Main:index.html.twig',
            array(
                'title'       => 'Import',
                'description' => '',
                'site_url'    => "http://{$_SERVER['SERVER_NAME']}",
                'body_text'   => 'Import'
            )
        );
    }
}
