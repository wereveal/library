<?php
/**
 *  Controller for Item.
 *  @file ItemController.php
 *  @class ItemController
 *  @author William Reveal  <bill@revealitconsulting.com>
 *  @version 0.1
 *  @par Change Log
 *      v0.1 - Initial version
 *  @par Wer Guide version 1.0
 *  @date 2013-06-03 14:38:59
 *  @ingroup guide
**/
namespace Wer\Guide\Controller;

use Symfony\\Framework\Controller\Controller;
use Wer\Guide\Model\Category;
use Wer\Guide\Model\Item;
use Wer\Guide\Model\Section;
use Wer\Framework\Library\Arrays;
use Wer\Framework\Library\Elog;
use Wer\Framework\Library\Strings;

class ItemController extends BaseController
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
     *  @param int $item_id required, redirects to main page if missing
     *  @return str the html to display
    **/
    public function indexAction($item_id = '')
    {
        if ($item_id == '') {
            header('Location: ' . SITE_URL);
        }
        $a_quick_form    = $this->formQuickSearch();
        $a_alpha_list    = $this->alphaList();
        $a_section_list  = $this->sectionList($this->default_section);
        $a_category_list = $this->categoryList($this->default_section);
        $a_item = $this->o_item->readItem(array('item_id' => $item_id));
        $a_item = $this->addDataToItem($a_item[0]);
        $this->o_elog->write('Item: ' . var_export($a_item, true), LOG_OFF, __METHOD__ . '.' . __LINE__);
        $a_twig_values = array(
            'title'         => 'Guide',
            'description'   => 'This is a description',
            'site_url'      => SITE_URL,
            'rights_holder' => 'William E. Reveal',
            'quick_form'    => $a_quick_form,
            'alpha_list'    => $a_alpha_list,
            'section_list'  => $a_section_list,
            'category_list' => $a_category_list,
            'item_data'     => $a_item[0]
        );
        return $this->render('WerGuide:Pages:item.html.twig', $a_twig_values);;
    }
    ### Other Methods ###
    /**
     *  Gets all the data for the item specified.
     *  This uses the base method addDataToItems which works for multiple items
     *  @param array $a_item required
     *  @return array $a_item
    **/
    public function addDataToItem($a_item = '')
    {
        $a_search_for_fields = array(
            'about', 'accepts_checks', 'accept_reservations', 'activities',
            'address', 'alcohol_options', 'attire', 'averageentreeprice',
            'bestof', 'capacity', 'catering', 'city', 'cost', 'country',
            'creditcards', 'delivery', 'email', 'fax', 'federal_state',
            'friday', 'latitude', 'location', 'longitude', 'monday',
            'outdoor_seating', 'phone', 'postcode', 'private_parties',
            'quick_lunch', 'reservation_required', 'saturday',
            'street', 'sunday', 'take_out', 'thursday', 'tuesday',
            'vegetarian_entrees', 'website', 'wednesday', 'wifi_available'
        );
        $a_search_parameters = array(
            'search_type' => 'AND'
        );
        $a_items = array();
        $a_items[] = $a_item;
        return $this->addDataToItems($a_items, $a_search_for_fields, $a_search_parameters);
    }
    /*
     *  See BaseController for the following methods
     *      alphaList($current_letter = '')
     *      categoryList($section_id = 1, $selected_category = '')
     *      formQuickSearch($a_search_for = 'Search For')
     *      sectionList($selected_section = '', $a_search_parameters = '')
     *      addDataToItems($a_items = '', $a_search_for_fields = '', $a_search_parameters = '')
     */

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
