<?php
/**
 *  @brief     Similar to the Unix tail command e.g. tail -n 40 file.php.
 *  @detail    When on a webpage, use the meta refresh to keep tailing a file.
 *  @ingroup   ritc_library lib_services
 *  @file      Tail.php
 *  @namespace Ritc\Library\Services
 *  @class     Tail
 *  @author    William E Reveal <bill@revealitconsulting.com>
 *  @version   2.1.1
 *  @date      2014-11-15 13:33:15
 *  @note <pre><b>Change Log</b>
 *      v2.1.1 - Moved to Services namespace         - 11/15/2014 wer
 *      v2.1.0 - Changed to work in the ritc_library - 04/22/2013 wer
 *  </pre>
**/
namespace Ritc\Library\Services;

/**
 * Class Tail
 * @package Ritc\Library\Services
 */
class Tail
{
    /**
     * @var int
     */
    private $timestamp           = 0;
    /**
     * @var int
     */
    private $file_size           = 0;
    /**
     * @var string
     */
    private $file_name           = ""; // requires full path
    /**
     * @var array
     */
    private $a_lines             = array();
    /**
     * @var int
     */
    private $lines               = 0;
    /**
     * @var int
     */
    private $show_lines          = 10;
    /**
     * @var string
     */
    private $search_string       = "";
    /**
     * @var string
     */
    private $search_string_regex = "";
    /**
     * @var bool
     */
    private $changed             = FALSE;
    /**
     * @var string
     */
    private $pre_highlight       = '<span style="color: red; font-weight: 900;">';
    /**
     * @var string
     */
    private $post_highlight      = "</span>";
    /**
     * @var bool
     */
    private $newest_first        = TRUE;
    /**
     * @var string
     */
    private $output_format       = "BR";
    /**
     * @var string
     */
    private $pre_output          = "";
    /**
     * @var string
     */
    private $post_output         = "";

    /**
     * Tail constructor.
     * @param $file_name
     */
    public function __construct($file_name)
    {
        if (file_exists($file_name)) {
            $this->file_name = $file_name;
            $this->update_stats();
        } else {
            return null;
        }
    }

    /**
     * @return bool|string
     */
    public function output()
    {
        if ($this->changed) {
            $pre_output = '';
            $post_output = '';
            $pre_line = "";
            $post_line = "\n";
            switch ($this->output_format) {
                case "BR":
                    $pre_line = "";
                    $post_line = "<br />";
                    break;
                case "P":
                    $pre_line = "<p>";
                    $post_line = "</p>";
                    break;
                case "UL":
                    $pre_output = $this->pre_output == "" ? "<ul>" : $this->pre_output ;
                    $post_output = $this->post_output == "" ? "</ul>" : $this->post_output;
                    $pre_line = "<li>";
                    $post_line = "</li>";
                    break;
                case "OL":
                    $pre_output = $this->pre_output == "" ? "<ol>" : $this->pre_output ;
                    $post_output = $this->post_output == "" ? "</ol>" : $this->post_output;
                    $pre_line = "<li>";
                    $post_line = "</li>";
                    break;
                case "XML":
                    $pre_output = $this->pre_output == "" ? "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\n<list>" : $this->pre_output ;
                    $post_output = $this->post_output == "" ? "</list>" : $this->post_output;
                    $pre_line = "<line>";
                    $post_line = "</line>\n";
                    break;
                default:
                    break;
            }

            $output = $pre_output;
            if ($this->newest_first === TRUE) {
                for ($i = $this->lines - 1; $i >= $this->lines - $this->show_lines; $i--) {
                    if (isset($this->a_lines[$i])) {
                        $output .= $pre_line;
                        if (($this->search_string == "") && ($this->search_string_regex == "")) {
                            $output .= $this->a_lines[$i];
                        }
                        elseif ($this->search_string_regex != "") {
                            $replacement = "$1" . $this->pre_highlight . "$2" . $this->post_highlight . "$3";
                            $output .= preg_replace($this->search_string_regex, $replacement, $this->a_lines[$i]);
                        }
                        elseif ($this->search_string != "") {
                            $output .= str_replace($this->search_string, $this->pre_highlight . $this->search_string . $this->post_highlight, $this->a_lines[$i]);
                        }
                        else {
                            $output .= $this->a_lines[$i];
                        }
                        $output .= $post_line;
                    }
                }
            }
            else {
                for ($i = $this->lines - $this->show_lines; $i<$this->lines; $i++){
                    if (isset($this->a_lines[$i])) {
                        $output .= $pre_line;
                        if (($this->search_string == "") && ($this->search_string_regex == "")) {
                            $output .= $this->a_lines[$i];
                        }
                        elseif ($this->search_string_regex != "") {
                            $replacement = "$1" . $this->pre_highlight . "$2" . $this->post_highlight . "$3";
                            $output .= preg_replace($this->search_string_regex, $replacement, $this->a_lines[$i]);
                        }
                        elseif ($this->search_string != "") {
                            $output .= str_replace($this->search_string, $this->pre_highlight . $this->search_string . $this->post_highlight, $this->a_lines[$i]);
                        }
                        else {
                            $output .= $this->a_lines[$i];
                        }
                        $output .= $post_line;
                    }
                }
            }
            $output .= $post_output;
            return $output;
        }
        else {
            return FALSE;
        }
    }

    /**
     * @param string $value
     */
    public function set_output_format($value = "BR")
    {
        $this->output_format = $value;
    }

    /**
     * @param string $value
     */
    public function set_pre_output($value = "")
    {
        $this->pre_output = $value;
    }

    /**
     * @param string $value
     */
    public function set_post_output($value = "")
    {
        $this->post_output = $value;
    }

    /**
     * @param string $search_string
     */
    public function set_search_string($search_string = "")
    {
        $this->search_string = $search_string;
    }

    /**
     * @param string $search_string
     */
    public function set_search_string_regex($search_string = "")
    {
        /* overrides set_search_string */
        $this->search_string_regex = $search_string;
    }

    /**
     * @param string $string
     */
    public function set_highlight_code($string="<b>")
    {
        $this->pre_highlight = $string;
        $a_string = explode(" ",$string);
        $this->post_highlight = "</" . str_replace("<","",$a_string[0]) . ">";
    }

    /**
     * @return string
     */
    public function get_highlight_code()
    {
        return htmlentities($this->pre_highlight) . " && " . htmlentities($this->post_highlight);
    }

    /**
     * @param int $lines
     */
    public function set_number_of_lines($lines=20)
    {
        $this->show_lines = $lines;
    }

    /**
     * @param bool $value
     */
    public function set_newest_first($value=TRUE)
    {
        $this->newest_first = $value == FALSE ? FALSE : TRUE;
    }

    /**
     *
     */
    private function open_file()
    {
        $this->a_lines = file($this->file_name,FILE_SKIP_EMPTY_LINES);
        $this->lines = count($this->a_lines);
    }

    /**
     *
     */
    private function update_stats()
    {
        $new_timestamp = filemtime($this->file_name);
        // check for change
        if ($new_timestamp > $this->timestamp){
            $this->file_size = filesize($this->file_name);
            $this->open_file();
            $this->timestamp = $new_timestamp;
            $this->changed = TRUE;
        }
        else {
            $this->changed = FALSE;
        }
    }
}
