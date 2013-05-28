<?php
namespace Wer\GuideBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Wer\GuideBundle\Model\Category;
use Wer\GuideBundle\Model\Item;
use Wer\GuideBundle\Model\Section;
use Wer\FrameworkBundle\Library\Arrays;
use Wer\FrameworkBundle\Library\Elog;
use Wer\FrameworkBundle\Library\Strings;

class SearchController extends Controller
{
    protected $default_section = 1;
    protected $num_to_display  = 10;
    protected $phone_format    = "AAA-BBB-CCCC";
    protected $date_format     = "mm/dd/YYYY";
    protected $o_item;
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

    ### Main Actions called from routing ###
    /**
     *  Displays the result of a simple search (from the quick search form).
     *  @param none
     *  @return str the html to display
    **/
    public function indexAction()
    {
        return ''
    }
    /**
     *  Displays the result of an advanced search.
     *  @param none
     *  @return str the html to display
    **/
    public function advancedAction()
    {
        return '';
    }
    /**
     *  Displays the result of an alpha search.
     *  @param none
     *  @return str the html to display
    **/
    public function byAlphaAction()
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
}
