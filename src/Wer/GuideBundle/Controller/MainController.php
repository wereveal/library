<?php

namespace Wer\GuideBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Wer\GuideBundle\Model\Category;
use Wer\GuideBundle\Model\Item;
use Wer\GuideBundle\Model\Section;
use Wer\FrameworkBundle\Library\Arrays;
use Wer\FrameworkBundle\Library\Elog;
use Wer\FrameworkBundle\Library\Strings;

class MainController extends Controller
{
    protected $default_section = 1;
    protected $num_to_display  = 10;
    protected $phone_format    = "AAA-BBB-CCCC";
    protected $date_format     = "mm/dd/YYYY";
    protected $o_arr;
    protected $o_cat;
    protected $o_elog;
    protected $o_item;
    protected $o_sec;
    protected $o_str;
    protected $o_view;

    public function __construct()
    {
        $this->o_arr  = new Arrays();
        $this->o_cat  = new Category();
        $this->o_elog = Elog::start();
        $this->o_item = new Item();
        $this->o_sec  = new Section();
        $this->o_str  = new Strings();
    }

    ### Main Actions called by routing parameters ###
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
     *  Displays an individual item in all its glory.
     *  @param none
     *  @return str the html
    **/
    public function displayItemAction()
    {
        return '';
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
                'the_letter' => $the_letter,
                'the_class'  => $the_class,
                'is_link'    => $is_link,
            );
            $a_li_values[$i] = $a_li_row;
        }
        return $a_li_values;
    }
    /**
     *  creates the values to be used in the category select
     *  @param int $section_id defaults to 1
     *  @return array $a_categories
    **/
    public function categoryList($section_id = 1, $selected_category = '')
    {
        $a_return_this = array(
            'name'        => '',
            'class'       => '',
            'other_stuph' => '',
            'options'     => '',
            'label_for'   => '',
            'label_text'  => '',
            'label_class' => ''
        );
        $a_categories = $this->o_cat->readCatBySec($section_id);
        if (count($a_categories) > 1) {
            foreach ($a_categories as $a_category) {
                if ($selected_category == '') {
                    $selected_category = $a_category['cat_id'];
                }
                $a_return_this['options'][] = array(
                    'value'       => $a_category['cat_id'],
                    'label'       => $a_category['cat_name'],
                    'other_stuph' => $selected_category == $a_category['sec_id'] ? ' selected' : ''
                );
            }
            $a_return_this['name']        = 'catSelect';
            $a_return_this['class']       = 'catSelect';
            $a_return_this['other_stuph'] = ' id="catSelect" onchange="searchByCategory(this)"';
            $a_return_this['label_for']   = 'catSelect';
            $a_return_this['label_text']  = 'Search By Category';
            $a_return_this['label_class'] = 'selectLabel';
        }
        return $a_return_this;
    }
    /**
     *
    **/
    public function formQuickSearch($a_search_for = 'Search For')
    {
        if ($a_search_for != 'Search For' && is_array($a_search_for)) {
            foreach ($a_search_for as $value) {
                if (strpos($value, ' ') !== false) {
                    $value = '"' . $value . '"';
                }
                $search_str .= $value . ' ';
            }

        }
        return array(
            'buttonColor'   => 'white',
            'buttonText'    => 'Locate',
            'searchForText' => $search_str
        );
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
        $a_items = $this->o_item->readItemFeatured($this->num_to_display);
        if ($a_items === false || count($a_items) <= 0) {
            $a_items = $this->o_item->readItemRandom($this->num_to_display);
        }
        $a_items = $this->o_arr->removeSlashes($a_items);
        $this->o_elog->write('' . var_export($a_items, TRUE), LOG_OFF, __METHOD__ . '.' . __LINE__);
        $a_search_parameters = array(
            'search_type' => 'AND'
        );
        $a_search_for_fields = array(
            'about',
            'street',
            'city',
            'federal_state',
            'postcode',
            'phone'
        );
        $a_items = $this->addDataToItem($a_items, $a_search_for_fields, $a_search_parameters);
        return $a_items;
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
        $a_return_this = array(
            'name'        => '',
            'class'       => '',
            'other_stuph' => '',
            'options'     => '',
            'label_for'   => '',
            'label_text'  => '',
            'label_class' => ''
        );
        $a_return_this = array();
        $a_sections = $this->o_sec->readSection('', $a_search_parameters);
        if (count($a_sections) > 1) {
            foreach ($a_sections as $a_section) {
                if ($selected_section == '') {
                    $selected_section = $a_section['sec_id'];
                }
                $a_return_this['options'][] = array(
                    'value'     => $a_section['sec_id'],
                    'label'     => $a_section['sec_name'],
                    'other_stuph' => $selected_section == $a_section['sec_id'] ? ' selected' : ''
                );
            }
            $a_return_this['name']        = 'sectionSelect';
            $a_return_this['class']       = 'sectionSelect';
            $a_return_this['other_stuph'] = ' id="sectionSelect" onchange="searchBySection(this)"';
            $a_return_this['label_for']   = 'sectionSelect';
            $a_return_this['label_text']  = 'Search By Section';
            $a_return_this['label_class'] = 'selectLabel';
        }
        $this->o_elog->write("select list array\n" . var_export($a_return_this, true), LOG_OFF, __METHOD__ . '.' . __LINE__);
        return $a_return_this;
    }
    ### Utilities ###

    ### SETTERS ###
    /**
     *  Sets the value of $this->date_format.
     *  Verifies the date format is valid for php before
     *  setting it. If it isn't a valie format, doesn't set.
     *  @param str $value date format desired
     *  @return null
    **/
    public function setDateFormat($value = '')
    {
        if ($value == '') { return; }
        if (Date_Time::isValidDateFormat($value)) {
            $this->date_format = $value;
        }
    }
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
    /**
     *  Sets the value of $phone_format
     *  Verifies value is valid formate else doesn't set it.
     *  @param str $value defaults to ''
     *  @return null
    **/
    public function setPhoneFormat($value = '')
    {
        switch ($value) {
            case 'XXX-XXX-XXXX':
            case '(XXX) XXX-XXXX':
            case 'XXX XXX XXXX':
            case 'XXX.XXX.XXXX':
            case 'AAA-BBB-CCCC':
            case '(AAA) BBB-CCCC':
            case 'AAA BBB CCCC':
            case 'AAA.BBB.CCCC':
                $this->phone_format = $value;
                return;
            default:
                return;
        }
    }

    ### GETTERS ###
    /**
     *  Gets the value of the date format.
     *  @param none
     *  @return str the value set
    **/
    public function getDateFormat()
    {
        return $this->date_format;
    }
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
    /**
     *  Gets the value of $phone_format.
     *  @param none
     *  @return str
    **/
    public function getPhoneFormat()
    {
        return $this->phone_format;
    }
}
