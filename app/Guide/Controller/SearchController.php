<?php
/**
 *  Search Controller for the Guide.
 *  @file SearchController.php
 *  @class SearchController
 *  @author William Reveal  <bill@revealitconsulting.com>
 *  @version 0.1
 *  @par Change Log
 *      v0.1 - Initial version 2012-05-28
 *  @par Wer Guide version 1.0
 *  @date 2013-05-28 17:45:30
 *  @ingroup guide
**/

namespace Wer\Guide\Controller;

use Wer\Guide\Model\Category;
use Wer\Guide\Model\Item;
use Wer\Guide\Model\Section;
use Wer\Framework\Library\Arrays;
use Wer\Framework\Library\Elog;
use Wer\Framework\Library\Strings;

class SearchController extends CommonController
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
        if (defined(DISPLAY_DATE_FORMAT)) {
            $this->date_format = DISPLAY_DATE_FORMAT;
        }
        if (defined(DISPLAY_PHONE_FORMAT)) {
            $this->phone_format = DISPLAY_PHONE_FORMAT;
        }
    }

    ### Main Actions called from routing ###
    /**
     *  Displays the result of a simple search (from the quick search form).
     *  @param none
     *  @return str the html to display
    **/
    public function defaultAction()
    {
        $a_quick_form    = $this->formQuickSearch();
        $a_alpha_list    = $this->alphaList();
        $a_section_list  = $this->sectionList($this->default_section);
        $a_category_list = $this->categoryList($this->default_section);

        /* HMM WHAT TO DO
            Generically, need to do a search based on the key terms from the post
            and send those records to the template.

            More specifically, I need to figure out how many records are in the
            results. If greater than default number of records to display then
            we need to initially show just the first x records and give the user
            the ability to then look at the remaning records.

            This could theoretically be done in one step, get all the records,
            send them all to the browser but have it only display the first 10
            hidding the rest. As they click on the previous next buttons it hides
            the visible records and unhides the hidden ones. This makes certain aspects
            very easy as you don't have to remember the search terms to go to prev next.

            Maybe just put the values in JSON format and jump around that way?

            The other way is to have the prev next links actually post all that information
            and get back the next/prev/exact records. That makes the links basically forms, a lot
            of junk to deal with.
        */

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
        return $this->render('WerGuide:Pages:search.html.twig', $a_twig_values);
    }
    /**
     *  Displays the advanced search form.
     *  @param none
     *  @return str the html to display
    **/
    public function advancedAction()
    {
        return '';
    }
    /**
     *  Displays the results of an advanced search.
     *  @param none
     *  @return str the html to display
    **/
    public function advancedSearchAction()
    {
        return '';
    }
    /**
     *  Displays the result of an alpha search.
     *  @param str $the_letter
     *  @param int $start the record number to start with
     *  @param int $number the number of records to return optional, defaults to ''
     *      but will get set to the class parameter $num_to_display
     *  @return str the html to display
    **/
    public function byAlphaAction($the_letter = 'A', $start = 0, $number = '')
    {
        if ($number == '') {
            $number = $this->num_to_display;
        }
        $a_quick_form    = $this->formQuickSearch();
        $a_alpha_list    = $this->alphaList();
        $a_section_list  = $this->sectionList($this->default_section);
        $a_category_list = $this->categoryList($this->default_section);
        $a_item_cards    = $this->alphaItemCards($the_letter, $start, $number);
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
        return $this->render('WerGuide:Pages:search.html.twig', $a_twig_values);
    }
    /**
     *  Displays the records from a category search.
     *  Results will normally by either featured or random.
     *  @param none
     *  @return str the html to display
    **/
    public function byCategoryAction()
    {
        return '';
    }
    /**
     *  Displays the records from a section search.
     *  Results will normally by either featured or random.
     *  @param none
     *  @return str the html to display
    **/
    public function bySectionAction()
    {
        return '';
    }
    /**
     *  Displays the quick search form.
     *  @param none
     *  @return str the html to display
    **/
    public function quickFormAction()
    {
        return '';
    }

    ### Utilities ####
    /**
     *  creates the values to be used for the item cards
     *  @param str $letter_to_find defaults to A
     *  @param int $num_to_display defaults to 10
     *  @return array $a_values
    **/
    public function alphaItemCards($letter_to_find = 'A', $start = 0, $num_to_display = '')
    {
        if ($num_to_display == '') {
            $num_to_display == $this->num_to_display;
        }
        $a_items = $this->o_item->readItemByNameFirstLetter($letter_to_find, $start, $num_to_display);
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
        $a_items = $this->addDataToItems($a_items, $a_search_for_fields, $a_search_parameters);
        foreach ($a_items as $key=>$a_item) {
            if (strlen($a_items[$key]['about']) > 0) {
                $a_items[$key]['about'] = $this->o_str->makeShortString($a_items[$key]['about'], 12)
                    . '... <a href="/item/'
                    . $a_items[$key]['item_id']
                    . '/">More</a>';
            }
        }
        $this->o_elog->write('a_items: ' . var_export($a_items, true), LOG_OFF, __METHOD__ . '.' . __LINE__);
        $a_search_pairs = array('item_name' => $letter_to_find . '%');
        $a_search_params = array('comparison_type' => 'LIKE');
        $a_params = array(
            'a_search_pairs' => $a_search_pairs,
            'a_search_params' => $a_search_params,
            'start' => $start,
            'num_to_display' => $num_to_display,
            'url' => "/search/by_alpha/{$letter_to_find}"
        );
        $a_prevnext = $this->makePreviousNext($a_params);
        $this->o_elog->write('' . var_export($a_prevnext, TRUE), LOG_OFF, __METHOD__ . '.' . __LINE__);
        $a_item_cards = array('items' => $a_items, 'prev_next' => $a_prevnext);
        return $a_item_cards;
    }

    ### Utilities ###

    /**
     *  Returns an array of arrays to create the previous next navigation.
     *  @param array $a_parameters
     *  @return array $a_values
    **/
    public function makePreviousNext($a_parameters = '')
    {
        $a_search_pairs  = isset($a_parameters['a_search_pairs'])
            ? $a_parameters['a_search_pairs']
            : array();
        $a_search_params = isset($a_parameters['a_search_params'])
            ? $a_parameters['a_search_params']
            : array();
        $start = isset($a_parameters['start'])
            ? $a_parameters['start']
            : 0;
        $num_to_display = isset($a_parameters['num_to_display'])
            ? $a_parameters['num_to_display']
            : 10;
        if ($a_parameters['url'] == '') {
            return array();
        }
        if ($a_search_pairs == array()) {
            return array();
        }
        $total_records = $this->o_item->readItemCount($a_search_pairs, $a_search_params);
        $number_of_links = (int) ($total_records / $num_to_display);
        if ($total_records % $num_to_display > 0) {
            $number_of_links++;
        }
        $x = 0;
        for ($i = 0; $i < $number_of_links; $i++) {
            if ($i == 0 && $start != 0 && $start - $num_to_display >= 0) {
                // make a previous button first
                $start_here = $start - $num_to_display;
                $url = '<a href="' . $a_parameters['url'] . "/$start_here/" . $num_to_display . '/">';
                $a_return_this[] = array('address' => $url, 'text' => 'Previous', 'endaddress' => '</a>');
            }
            if ($x == $start) {
                $a_return_this[] = array('address' => '', 'text' => $i, 'endaddress' => '');
            } else {
                $url = '<a href="' . $a_parameters['url'] . '/' . $x . '/' . $num_to_display . '/">';
                $a_return_this[] = array('address' => $url, 'text' => $i, 'endaddress' => '</a>');
            }
            if ($i == ($number_of_links - 1) && $start < $x) {
                // make a next button last but only if $start < than the $x of the last link
                $url = '<a href="' . $a_parameters['url'] . '/' . ($start + $num_to_display) . '/' . $num_to_display . '/">';
                $a_return_this[] = array('address' => $url, 'text' => 'Next', 'endaddress' => '</a>');
            }
            $x += $num_to_display;
        }
        return $a_return_this;
    }

    ### SETTERS ###
    /*
     *  See BaseController for
     *      setDateFormat($value = '')
     *      setDefaultSection($section_id = '')
     *      setNumToDisplay($value = 10)
     *      setPhoneFormat($value = '')
    */
    ### GETTERS ###
    /*
     *  See BaseController for
     *      getDateFormat()
     *      getDefaultSection()
     *      getNumToDisplay()
     *      getPhoneFormat()
    */
}
