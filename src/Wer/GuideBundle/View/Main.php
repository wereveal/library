<?php
namespace Wer\Guide\Controller;

use Wer\Framework\Library\Base;
use Wer\Framework\Library\Date_Time;
use Wer\Framework\Library\Elog;
use Wer\Framework\Library\Files;
use Wer\Framework\Library\Html;
use Wer\Framework\Library\Strings;
use Wer\Guide\Model\Item;

class Main extends Base
{
    protected $action1 = '';
    protected $action2 = '';
    protected $action3 = '';
    protected $date_format = 'm/d/Y h:i A T';
    protected $o_elog;
    protected $o_files;
    protected $o_html;
    protected $o_item;
    protected $o_str;
    protected $posted_action = '';
    protected $private_properties;
    protected $theme = 'default';
    public function __construct()
    {
        $this->o_elog = Elog::start();
        $this->setPrivateProperties(__CLASS__);
        $this->o_db = Datebase::start();
        $this->o_str = new Strings;
    }

    /**
     *  Displays an alphanumeric click thingie
     *  @param none
     *  @return str $html
    **/
    public function alphaList()
    {
        $main_str = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $li_rows = '';
        for ($i; $i < strlen($main_str); $i++) {
            $this_letter = $main_str[$i];
            $results = $this->o_item->readItemByNameFirstLetter($this_letter);
            if ($results !== false && count($results) > 0) {
                if ($li_rows = '') {
                    $the_class = 'alphaListFirstLetter';
                } else {
                    $the_class = 'alphaListOtherLetter';
                }
                $a_values = array('the_class' => $the_class, 'the_letter' => $this_letter);
                $li_rows .= $this->o_html->fillTemplate($li_tpl, $a_values);
            }
        }
        $a_values = array('li_rows' => $li_rows);
        return $this->o_html->fillTemplate($ul_tpl, $a_values);
    }
    /**
     *  Renders the html for the category list selector
     *  @param int $default_section
     *  @return str $html
    **/
    public function categoryList($default_section = '')
    {
        return '';
    }
    /**
     *  Display a quick search form
     *  @param array $a_search_params optional array('search_words' => 'fred', 'barney', 'wilma flinstone')
     *  @return str $html
    **/
    public function formQuickSearch($a_search_params = '')
    {
        return '';
    }
    /**
     *  Creates the html to display short info about one or more items
     *  @param array $a_item required an array of arrays of items to display
     *  @return str html
    **/
    public function itemCards($a_items = '')
    {
        return '';
    }
    /**
     *  Creates a select to choose which section to view
     *  @param int $section_id
     *  @return str $html
    **/
    public function sectionList($section_id = '')
    {
        return '';
    }
    ### SETTERS ###
    /**
     *  Sets $action1
     *  @param str $value
     *  @return null
    **/
    public function setAction1($value = '')
    {
        $this->action1 = $value;
    }
    /**
     *  Sets $action2
     *  @param str $value
     *  @return null
    **/
    public function setAction2($value = '')
    {
        $this->action2 = $value;
    }
    /**
     *  Sets $action3
     *  @param str $value
     *  @return null
    **/
    public function setAction3($value = '')
    {
        $this->action3 = $value;
    }
    /**
     *  Sets date_format
     *  @param str $value required, in php date() format
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
     *  Sets the posted action
     *  The action specified in a form
     *  @param str $value
     *  @return null
    **/
    public function setPostedAction($value = '')
    {
        $this->posted_action = $value;
    }
    /**
     *  Sets the $theme value
     *  @param str $value optional defaults to 'default'
     *  @return null
    **/
    public function setTheme($value = 'default')
    {
        $this->theme = $value;
    }

    ### GETTERS ###
    ### returns the value of the property ###
    public function getAction1($value = '')
    {
        return $this->action1;
    }
    public function getAction2($value = '')
    {
        return $this->action2;
    }
    public function getAction3($value = '')
    {
        return $this->action3;
    }
    public function getDateFormat($value = '')
    {
        return $this->date_format;
    }
    public function getPostedAction($value = '')
    {
        return $this->posted_action;
    }
    public function getTheme($value = 'default')
    {
        return $this->theme;
    }

}
