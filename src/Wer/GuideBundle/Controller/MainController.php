<?php

namespace Wer\GuideBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Wer\GuideBundle\Model\Item;

class MainController extends Controller
{
    private $default_section = 1;
    private $num_to_display  = 10;
    private $o_item;
    protected $o_arr;
    protected $o_cat;
    protected $o_elog;
    protected $o_item;
    protected $o_sec;
    protected $o_shared_view;
    protected $o_view;

    public function __construct()
    {
        $this->o_arr  = new Arrays();
        $this->o_cat  = new Category();
        $this->o_elog = Elog::start();
        $this->o_item = new Item();
        $this->o_sec  = new Section();
        $this->o_shared_view = new Shared();
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
        $a_quick_form    = $this->formQuickSearch();
        $a_alpha_list    = $this->alphaList();
        $a_section_list  = $this->sectionList($this->default_section);
        $a_category_list = $this->categoryList($this->default_section);
        $a_item_cards    = $this->itemCards($this->num_to_display);
        $a_twig_values = array(
            'title'         => 'Guide',
            'description'   => 'This is a description',
            'site_url'      => SITE_URL,
            'rights_holder' => 'William E. Reveal',
            'quick_form'    => $a_quick_form,
            'alpha_list'    => $a_alpha_list,
            'section_list'  => $a_section_list,
            'category_list' => $a_category_list,
            'item_cards'    => $a_item_cards
        );
        return $this->render('WerGuideBundle:Pages:index.html.twig', $a_twig_values);
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
        $a_li_values  = array();
        $first_letter = $main_str[0];
        $last_letter  = $main_str[count($main_str) - 1];
        for ($i = 0; $i < strlen($main_str); $i++) {
            $the_letter = $main_str[$i];
            $the_class  = 'otherLetter';
            $is_link    = true;
            $results = $this->o_item->readItemByNameFirstLetter($the_letter);
            if ($results != false && count($results) > 0) {
                if ($the_letter == $first_letter) {
                    $the_class = 'firstLetter';
                } elseif ($the_letter == $last_letter) {
                    $the_class = 'lastLetter';
                }
                if ($the_letter == $current_letter) {
                    $the_class .= ' currentLetter';
                    $is_link = false;
                }
            } else {
                $the_class = 'noLink';
                $is_link   = false;
            }
            $a_li_row = array(
                'the_letter' => $the_letter
                'the_class'  => $the_class,
                'is_link'    => $is_link,
            );
            $a_li_values[$i] = $a_li_row;
        }
        return $a_li_values;
        // return $this->render('WerGuideBundle:Snippets:alphaListUl.html.twig', array('li_rows' => $a_li_values));
    }
    /**
     *  creates the values to be used in the category select
     *  @param int $section_id defaults to 1
     *  @return array $a_sections
    **/
    public function categoryList($section_id = 1)
    {
        return array();
    }
    /**
     *
    **/
    public function formQuickSearch()
    {
        return array(
            'color' => 'white',
            'buttonText' => 'Locate'
        );
        // return $this->render('WerGuideBundle:Snippets:quickSearch.html.twig', $a_values);
    }
    /**
     *  creates the values to be used for the item cards
     *  @param int $num_to_display defaults to 10
     *  @return array $a_values
    **/
    public function itemCards($num_to_display = 10)
    {
        // look for featured items first.
        // if there are some, grab the first 10 and return them
        // else grab 10 random items
        return array();
    }
    /**
     *  creates the values needed for the section list
     *  @param mixed $selected_section can be an id or a name
     *  @param array $a_search_parameters
     *      (see o_sec->readSection comments for more info)
     *  @return array $a_return_this
    **/
    public function sectionList($selected_section = '', $a_search_parameters = '')
    {
        $a_return_this = array();
        $a_sections = $this->o_sec->readSection('', $a_search_parameters);
        if (count($a_sections) > 1) {
            foreach ($a_sections as $a_section) {
                if ($selected_section == '') {
                    $selected_section = $a_section['sec_id'];
                }
                $a_return_this['options'][] = array(
                    'o_value'     => $a_section['sec_id'],
                    'o_label'     => $a_section['sec_id'],
                    'other_stuph' => $selected_section == $a_section['sec_id'] ? ' selected' : '';
                );
            }
            $a_return_this['select_values'] = array(
                'select_name' => 'sectionSelect',
                'class'       => 'sectionSelect',
                'other_stuph' => ' id="sectionSelect" onchange="searchBySection(this)"'
            );
            $a_return_this['label_values'] = array(
                'for'        => 'sectionSelect',
                'label_text' => 'Search By Section',
                'class'      => 'sectionSelectLabel'
            );
        }
        $this->o_elog->write("select list array\n" . var_export($a_return_this, true), LOG_OFF, __METHOD__ . '.' . __LINE__);
        return $a_return_this;
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
