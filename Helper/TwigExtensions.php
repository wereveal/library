<?php
/**
 * Class TwigExtensions.
 * @package Ritc_Library
 */
namespace Ritc\Library\Helper;

use Ritc\Library\Services\Di;

/**
 * Extends Twig functionality..
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version 1.0.0-alpha.0
 * @date    2018-05-30 10:43:01
 * @change_log
 * - v1.0.0-alpha.0 - Initial version        - 2018-05-30 wer
 * @todo TwigExtensions.php - Everything
 */
class TwigExtensions extends \Twig_Extension
{
    /** @var \Parsedown */
    private $o_md;
    /** @var \ParsedownExtra */
    private $o_mde;

    /**
     * TwigExtensions constructor.
     * @param \Ritc\Library\Services\Di $o_di
     */
    public function __construct(Di $o_di)
    {
        /** @var \Parsedown $o_md */
        $o_md = $o_di->get('mdParser');
        if ($o_md instanceof \Parsedown) {
            $this->o_md = $o_md;
        }
        /** @var \ParsedownExtra $o_mde */
        $o_mde = $o_di->get('mdeParser');
        if ($o_mde instanceof \ParsedownExtra) {
            $this->o_mde = $o_mde;
        }
    }

    /**
     * Overrides method in Twig_Extension.
     * @return array|\Twig_Filter[]
     */
    public function getFilters()
    {
        return [
            new \Twig_Filter('md', [$this, 'mdFilter']),
            new \Twig_Filter('mde', [$this, 'mdeFilter']),
        ];
    }

    /**
     * Overrides method in Twig_Extension.
     * @return array|\Twig_Test[]
     */
    public function getTests()
    {
        return [
           new \Twig_Test('ondisk', [$this, 'onDisk']),
        ];
    }

    /**
     * Converts markdown into html.
     * @param string $value
     * @return string
     */
    public function mdFilter($value = '')
    {
        if ($this->o_md instanceof \Parsedown) {
            $value = $this->o_md->text($value);
        }
        return $value;
    }

    /**
     * Converts markdown extra into html.
     * @param string $value
     * @return null|string|string[]
     */
    public function mdeFilter($value = '')
    {
        if ($this->o_mde instanceof \ParsedownExtra) {
            $value = $this->o_mde->text($value);
        }
        return $value;
    }

    /**
     * Verifies a file is on disk in the public site.
     * @param string $value
     * @return bool
     */
    public function onDisk($value = '')
    {
        $file_w_path = PUBLIC_PATH . $value;
        if (file_exists($file_w_path)) {
            return true;
        }
        else {
            return false;
        }
    }
}
