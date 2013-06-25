<?php
namespace Wer\Sobi\Controller;

use Symfony\\Framework\Controller\Controller as Controller;
use Symfony\Component\HttpFoundation\Request as Request;
use Wer\Sobi\Entity\WerSection;

class SectionsController extends Controller
{
    /**
     * List the Sections in Sobi
     * @return Render object
    **/
    public function indexAction()
    {
        $a_twig = array(
            'title'=>'Hello Sections Controller',
            'stylesheets'=>'',
            'o_sections'=>'Hello Sections Controller N/A',
            'javascripts'=>''
        );
        return $this->render('WerSobi:Default:sections.html.twig', $a_twig);
    }
    /**
     *  Import the Sections from Sobi tables into Guide tables
     * @return Render object
    **/
    public function importAction()
    {
        $a_twig = array(
            'title'=>'Import Sections',
            'stylesheets'=>'',
            'o_sections'=>'Import Sections N/A',
            'javascripts'=>''
        );
        return $this->render('WerSobi:Default:sections.html.twig', $a_twig);
    }
    /**
     * List the Sections imported into the Guide tables
     * @return Render object
    **/
    public function listAction()
    {
        $o_repository = $this->getDoctrine()
            ->getRepository("WerSobi:WerSection");
        $o_sections = $o_repository->findAll();
        error_log(var_export( $o_sections, TRUE ));
        $a_twig = array(
            'title'=>'List Sections Results',
            'stylesheets'=>'',
            'o_sections'=>$o_sections,
            'javascripts'=>''
        );
        return $this->render('WerSobi:Default:sections.html.twig', $a_twig);
    }
}
