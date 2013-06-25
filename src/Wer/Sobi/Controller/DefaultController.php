<?php

namespace Wer\Sobi\Controller;

use Symfony\\Framework\Controller\Controller;

class DefaultController extends Controller
{
    /**
     * List the different links to do stuff
     * @return Render object
    **/
    public function indexAction()
    {
        $a_twig = array(
            'title'=>'Hello Default Controller',
            'stylesheets'=>'',
            'javascripts'=>'',
            'body'=>'Hello Default Controller'
        );

        return $this->render('WerSobi:Default:index.html.twig', $a_twig);
    }
}
