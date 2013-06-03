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
     *  @return str the html to display
    **/
    public function byAlphaAction($the_letter = 'A', $start = 0, $number = 10)
    {
        return '';
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
