<?php
/**
 * Class Tail
 * @package Ritc_Library
 */
namespace Ritc\Library\Services;

use Ritc\Library\Traits\ViewTraits;

/**
 * Similar to the Unix tail command e.g. tail -n 40 file.php.
 * When on a web page, use the meta refresh to keep tailing a file.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version 3.0.0
 * @date    2021-12-01 14:03:35
 * @change_log
 * - 3.0.0  - updated to php 8 standards                                - 2021-12-01 wer
 * - 2.2.0  - Changed method names to reflect current coding standards  - 2016-03-11 wer
 * - 2.1.1  - Moved to Services namespace                               - 11/15/2014 wer
 * - 2.1.0  - Changed to work in the ritc_library                       - 04/22/2013 wer
 */
class Tail
{
    use ViewTraits;

    /** @var int $timestamp */
    private int $timestamp = 0;
    /** @var int $file_size */
    private int $file_size = 0;
    /** @var string $file_name */
    private mixed $file_name = ''; // requires full path
    /** @var array $a_lines */
    private array $a_lines = array();
    /** @var int $lines */
    private int $lines = 0;
    /** @var int $show_lines */
    private mixed $show_lines = 10;
    /** @var string $search_string */
    private string $search_string = '';
    /** @var string $search_string_regex */
    private string $search_string_regex = '';
    /** @var bool $changed */
    private bool $changed = false;
    /** @var string $pre_highlight */
    private string $pre_highlight = '<span style="color: red; font-weight: 900;">';
    /** @var string $post_highlight */
    private string $post_highlight = '</span>';
    /** @var bool $newest_first */
    private mixed $newest_first = true;
    /** @var string $output_format */
    private string $output_format = 'BR';
    /** @var string $pre_output */
    private string $pre_output = '';
    /** @var string $post_output */
    private string $post_output = '';
    /** @var string $tpl */
    private mixed $tpl = '';

    /**
     * Tail constructor.
     * @param Di $o_di
     */
    public function __construct(Di $o_di)
    {
        $this->setupView($o_di);
        $file_name = $o_di->getVar('file_name');
        if (file_exists($file_name)) {
            $this->show_lines   = $o_di->getVar('show_lines');
            $this->newest_first = $o_di->getVar('newest_first');
            $this->tpl          = $o_di->getVar('tpl');
            $this->file_name    = $file_name;
        }
        else {
            die('elog file does not exist.');
        }
    }

    ### Public Methods ###
    /**
     * @return string
     * @noinspection PhpUndefinedConstantInspection
     */
    public function output(): string
    {
        $this->updateStats();
        $a_twig_values = [
            'content'        => '',
            'lang'           => 'en',
            'charset'        => 'utf8',
            'title'          => 'Tail Log File',
            'description'    => 'Tails a log file.',
            'a_message'      => [],
            'tolken'         => '',
            'form_ts'        => '',
            'hobbit'         => '',
            'a_menus'        => [],
            'adm_lvl'        => 10,
            'site_prefix'    => SITE_TWIG_PREFIX,
            'lib_prefix'     => LIB_TWIG_PREFIX,
            'public_dir'     => PUBLIC_DIR,
            'site_url'       => SITE_URL,
            'rights_holder'  => RIGHTS_HOLDER,
            'copyright_date' => COPYRIGHT_DATE
        ];
        if ($this->changed) {
            $pre_output = '';
            $post_output = '';
            $pre_line = '';
            $post_line = "\n";
            switch ($this->output_format) {
                case 'BR':
                    $pre_line = '';
                    $post_line = '<br />';
                    break;
                case 'P':
                    $pre_line = '<p>';
                    $post_line = '</p>';
                    break;
                case 'UL':
                    $pre_output = $this->pre_output === '' ? '<ul>' : $this->pre_output ;
                    $post_output = $this->post_output === '' ? '</ul>' : $this->post_output;
                    $pre_line = '<li>';
                    $post_line = '</li>';
                    break;
                case 'OL':
                    $pre_output = $this->pre_output === '' ? '<ol>' : $this->pre_output ;
                    $post_output = $this->post_output === '' ? '</ol>' : $this->post_output;
                    $pre_line = '<li>';
                    $post_line = '</li>';
                    break;
                case 'XML':
                    $pre_output = $this->pre_output === '' ? "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\n<list>" : $this->pre_output ;
                    $post_output = $this->post_output === '' ? '</list>' : $this->post_output;
                    $pre_line = '<line>';
                    $post_line = "</line>\n";
                    break;
                default:
                    break;
            }
            $output = $pre_output;
            $a_lines = $this->a_lines;
            if ($this->newest_first === TRUE) {
                $a_lines = array_reverse($a_lines);
            }
            foreach($a_lines as $line) {
                $output .= $pre_line;
                if (($this->search_string === '') && ($this->search_string_regex === '')) {
                    $output .= $line;
                }
                elseif ($this->search_string_regex !== '') {
                    $replacement = '$1' . $this->pre_highlight . '$2' . $this->post_highlight . '$3';
                    $output .= preg_replace($this->search_string_regex, $replacement, $line);
                }
                elseif ($this->search_string !== '') {
                    $output .= str_replace($this->search_string, $this->pre_highlight . $this->search_string . $this->post_highlight, $line);
                }
                else {
                    $output .= $line;
                }
                $output .= $post_line;
            }
            $output .= $post_output;
            $a_twig_values['content'] = $output;
            return $this->renderIt($this->tpl, $a_twig_values);
        }

        $a_twig_values['content'] = 'No changes.<br>';
        return $this->renderIt($this->tpl, $a_twig_values);
    }

    ### GETters and SETters ###
    /**
     * @return string
     */
    public function getHighlightCode():string
    {
        return htmlentities($this->pre_highlight) . ' && ' . htmlentities($this->post_highlight);
    }

    /**
     * Standard getter.
     * @return string
     */
    public function getOutputFormat():string
    {
        return $this->output_format;
    }

    /**
     * Standard getter.
     * @return string
     */
    public function getPostHighlight():string
    {
        return $this->post_highlight;
    }

    /**
     * Standard getter.
     * @return string
     */
    public function getPostOutput():string
    {
        return $this->post_output;
    }

    /**
     * Standard getter.
     * @return string
     */
    public function getPreHighlight():string
    {
        return $this->pre_highlight;
    }

    /**
     * @param string $string
     */
    public function setHighlightCode(string $string= '<b>'):void
    {
        $this->pre_highlight = $string;
        $a_string = explode(' ', $string);
        $this->post_highlight = '</' . str_replace('<', '', $a_string[0]) . '>';
    }

    /**
     * Sets the lines from the log file to be displayed.
     *
     * @param int $num_of_lines
     */
    public function setLines(int $num_of_lines = 0):void
    {
        $show_lines = $num_of_lines === 0
            ? $this->show_lines
            : $num_of_lines;
        $r_file = fopen($this->file_name, 'rb');
        $line_count = 0;
        while(($line = fgets($r_file)) !== false) {
            if (!empty(trim($line))) {
                $line_count++;
            }
        }
        $starting_line = $line_count - $show_lines;
        rewind($r_file);
        $line_count = 0;
        $a_lines = [];
        while(($line = fgets($r_file)) !== false) {
            if (!empty(trim($line))) {
                $line_count++;
                if ($line_count >= $starting_line) {
                    $a_lines[] = $line;
                }
            }
        }
        $this->a_lines = $a_lines;
        $this->lines = count($a_lines);
    }

    /**
     * @param bool $value
     */
    public function setNewestFirst(bool $value=TRUE):void
    {
        $this->newest_first = $value !== false;
    }

    /**
     * @param int $lines
     */
    public function setNumberOfLines(int $lines=20):void
    {
        $this->show_lines = $lines;
        $this->setLines();
    }

    /**
     * @param string $value
     */
    public function setOutputFormat(string $value = 'BR'):void
    {
        $this->output_format = $value;
    }

    /**
     * @param string $value
     */
    public function setPostOutput(string $value = ''):void
    {
        $this->post_output = $value;
    }

    /**
     * @param string $value
     */
    public function setPreOutput(string $value = ''):void
    {
        $this->pre_output = $value;
    }

    /**
     * @param string $search_string
     */
    public function setSearchString(string $search_string = ''):void
    {
        $this->search_string = $search_string;
    }

    /**
     * @param string $search_string
     */
    public function setSearchStringRegex(string $search_string = ''):void
    {
        /* overrides setSearchString */
        $this->search_string_regex = $search_string;
    }

    /**
     * SETs the class property tpl.
     *
     * @param string $tpl
     */
    public function setTpl(string $tpl = ''):void
    {
            $this->tpl = $tpl !== ''
                ? $tpl
                : '@' . SITE_PREFIX . 'pages/tail.tpl';
    }

    ### Private Functions ###
    /**
     * Reads a log file into an array and sets the number of lines in the file.
     * Not used anymore, legacy.
     *
    private function openFile()
    {
        $this->a_lines = file($this->file_name,FILE_SKIP_EMPTY_LINES);
        $this->lines = count($this->a_lines);
    }
     */

    /**
     * Sets a couple properties.
     */
    private function updateStats():void
    {
        $new_timestamp = filemtime($this->file_name);
        // check for change
        if ($new_timestamp > $this->timestamp){
            $this->file_size = filesize($this->file_name);
            // $this->openFile();
            $this->setLines();
            $this->timestamp = $new_timestamp;
            $this->changed = TRUE;
        }
        else {
            $this->changed = FALSE;
        }
    }
}
