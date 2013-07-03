<?php
/**
 *  Controller for the home page of the Guide.
 *  @file HomeController.php
 *  @class HomeController
 *  @author William Reveal  <bill@revealitconsulting.com>
 *  @version 0.2
 *  @par Change Log
 *      v0.2 - New repository and name change 2013-03-26
 *      v0.1 - Initial version 2012-06-04
 *  @par Wer Guide version 1.0
 *  @date 2013-03-26 15:49:24
 *  @ingroup guide controller
**/
namespace Wer\Guide\Controller;

use Wer\Guide\Model\Category;
use Wer\Guide\Model\Item;
use Wer\Guide\Model\Section;
use Wer\Framework\Library\Arrays;
use Wer\Framework\Library\Elog;
use Wer\Framework\Library\Strings;
use Wer\Guide\Forms\SearchForms;
use Wer\Guide\Forms\Entity\QuickSearch;
use Twig\Loader\Twig_Loader_Filesystem;
use Twit\Twig_Environment;

class HomeController extends CommonController
{
    protected $default_section = 1;
    protected $num_to_display  = 10;
    protected $phone_format    = "(XXX) XXX-XXXX";
    protected $date_format     = "m/d/Y";
    protected $o_arr;
    protected $o_cat;
    protected $o_elog;
    protected $o_item;
    protected $o_sec;
    protected $o_str;
    protected $o_twig;
    protected $o_view;

    public function __construct()
    {
        $this->o_arr  = new Arrays;
        $this->o_cat  = new Category;
        $this->o_elog = Elog::start();
        $this->o_item = new Item;
        $this->o_sec  = new Section;
        $this->o_str  = new Strings;
        $loader       = $this->twigLoader();
        $this->o_twig = new Twig_Environment(
            $loader,
            array(
                'cache'       => APP_PATH . '/twig_cache',
                'debug'       => false,
                'auto_reload' => true,
                'autoescape'  => true
            )
        );
        if (defined(DISPLAY_DATE_FORMAT)) {
            $this->date_format = DISPLAY_DATE_FORMAT;
        }
        if (defined(DISPLAY_PHONE_FORMAT)) {
            $this->phone_format = DISPLAY_PHONE_FORMAT;
        }
    }

    ### Main Actions called by routing parameters ###
    public function defaultAction()
    {
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
        return $this->render('@pages/index.html.twig', $a_twig_values);
    }
    public function formTestAction(Request $request)
    {
        $o_forms = new SearchForms;
        $a_twig_values = array(
            'title'         => 'Guide',
            'description'   => 'This is a description',
            'site_url'      => SITE_URL,
            'rights_holder' => 'William E. Reveal',
            'form'          => $this->quickSearch($request),
        );

        return $this->o_twig->render('@pages/testSearch.html.twig', $a_twig_values);
    }
    ### Methods Used ###
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
        return $a_items;
    }
    /**
     *  Creates a Twig Loader object.
     *  @param none
     *  @return object
    **/
    public function twigLoader()
    {
        $guide_path = APP_PATH . '/Guide/resources/templates/twig';
        $frame_path = APP_PATH . '/Framework/resources/templates/twig';
        $a_template_paths = array(
            $guide_path . '/default'  => 'default',
            $guide_path . '/pages'    => 'pages',
            $guide_path . '/elements' => 'elements',
            $guide_path . '/snippets' => 'snippets',
            $guide_path . '/manager'  => 'manager',
            $frame_path . '/default'  => 'fwmain',
            $frame_path . '/tests'    => 'fwtests'
        );
        $loader = new Twig_Loader_Filesystem($guide_path);
        foreach ($a_template_paths as $path => $namespace ) {
            $loader->prependPath($path, $namespace);
        }
        return $loader;
    }
    /*
     *  See CommonController for the following methods
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
