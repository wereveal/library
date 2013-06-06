<?php
namespace Wer\GuideBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Wer\GuideBundle\Model\Category;
use Wer\GuideBundle\Model\Item;
use Wer\GuideBundle\Model\Section;
use Wer\FrameworkBundle\Library\Arrays;
use Wer\FrameworkBundle\Library\Elog;
use Wer\FrameworkBundle\Library\Strings;

class SearchController extends BaseController
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
    public function indexAction()
    {
        return '';
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
        $a_item_cards    = $this->alphpItemCards($the_letter, $start, $number);
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
        $a_item_cards = array('items' => $a_items, 'more' => $a_more);
        return $a_item_cards;
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
