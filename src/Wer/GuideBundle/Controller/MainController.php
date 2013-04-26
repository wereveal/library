<?php

namespace Wer\GuideBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Wer\GuideBundle\View\MainView;

class MainController extends Controller
{
    private $default_section = 1;
    private $num_to_display  = 10;

    public function __construct()
    {
        parent::__construct();
    }
    public function indexAction()
    {
        /*  What are we doing?
            display the quick search form
            display the alphanumeric list a-z0-9
            display the drop down to select sections if num of sections > 1
            Search for featured.
            if records > 0
                display the records
            else
                search for $num_to_display random records
                display the records

        */
        $quick_form    = $this->formQuickSearch();
        $alpha_list    = $this->alphaList();
        $section_list  = $this->sectionList($this->default_section);
        $category_list = $this->categoryList($this->default_section);
        $item_cards    = $this->itemCards($this->num_to_display);
        $a_twig_values = array(
            'title'         => 'Guide',
            'description'   => 'This is a description',
            'site_url'      => "http://{$_SERVER['SERVER_NAME']}",
            'rights_holder' => 'William E. Reveal',
            'quick_form'    => $quick_form,
            'alpha_list'    => $alpha_list,
            'section_list'  => $section_list,
            'category_list' => $category_list,
            'item_cards'    => $item_cards
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

    ### Common Code ###
    /**
     *  creates the values to be used in the twig tpl.
     *  @param str $current_letter optional, defaults to ''
     *  @return array $a_values
    **/
    public function alphaList($current_letter = '')
    {
        $main_str = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $a_li_values = array();
        $first_row = true;
        for ($i; $i < strlen($main_str); $i++) {
            $the_letter = $main_str[$i];
            $results = $this->o_item->readItemByNameFirstLetter($the_letter);
            $a_li_row = array(
                'is_link' => true,
                'the_class' => 'otherLetter',
                'the_letter' => $the_letter
            );
            if ($results != false && count($results) > 0) {
                if ($first_row) {
                    $a_li_row['the_class'] = 'firstLetter';
                    $a_li_row['the_letter'] = $the_letter;
                }
                if ($the_letter == $current_letter) {
                    $a_li_row['the_class'] .= ' currentLetter';
                    $a_li_row['is_link'] = false;
                }
            } else {
                $a_li_row['is_link'] = false;
            }
            $a_li_values[] = $a_li_row;
        }
    }
    /**
     *  creates the values to be used in the category select
     *  @param int $section_id defaults to 1
     *  @return array $a_sections
    **/
    public function categoryList($section_id = 1)
    {
        return '';
    }
    /**
     *  creates the values to be used for the item cards
     *  @param int $num_to_display defaults to 10
     *  @return array $a_values
    **/
    public function itemCards($num_to_display = 10)
    {
        return '';
    }
    /**
     *  creates the values needed for the section list
     *  @param int $section_id
     *  @return array $a_values
    **/
    public function sectionList($section_id = 1)
    {
        return '';
    }
    ### Utilities ###

    ### SETTERS ###
    /**
     *  Sets the value of $this->default_section.
     *  @param int $section_id
     *  @return null
    **/
    public function setDefaultSection($section_id = '')
    {
        $this->default_section = $section_id;
    }
    /**
     *  Sets the value of $num_to_display
     *  @param int $value defaults to 10
     *  @return null
    **/
    public function setNumToDisplay($value = 10)
    {
        $this->num_to_display = $value;
    }

    ### GETTERS ###
    /**
     *  Gets the value of $this->default_section.
     *  @param none
     *  @return int $section_id
    **/
    public function getDefaultSection()
    {
        return $this->default_section;
    }
    /**
     *  Gets the value of $num_to_display
     *  @param none
     *  @return int the number of records to display
    **/
    public function getNumToDisplay()
    {
        return $this->num_to_display;
    }
}
