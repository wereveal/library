<?php
/**
 * Class SitemapView.
 * @package Ritc_Library
 */
namespace Ritc\Library\Views;

use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Helper\AuthHelper;
use Ritc\Library\Helper\ViewHelper;
use Ritc\Library\Interfaces\ViewInterface;
use Ritc\Library\Models\NavComplexModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\ConfigViewTraits;
use Ritc\Library\Traits\LogitTraits;

/**
 * Various sitemap views.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version 1.0.0-alpha.0
 * @date    2018-05-26 12:50:04
 * @change_log
 * - v1.0.0-alpha.0 - Initial version        - 2018-05-26 wer
 * @todo SitemapView.php - Everything
 */
class SitemapView implements ViewInterface
{
    use LogitTraits, ConfigViewTraits;

    /**
     * Sitemap constructor.
     * @param \Ritc\Library\Services\Di $o_di
     */
    public function __construct(Di $o_di)
    {
        $this->setupElog($o_di);
        $this->setupView($o_di);
    }

    /**
     * Main method to render the sitemap page.
     * @return string
     */
    public function render()
    {
        $meth = __METHOD__ . '.';
        $a_sitemap = [];
        $a_message = [];
        if ($this->use_cache) {
            $date_key = 'sitemap.html.date';
            $value_key = 'sitemap.html.value';
            $date = $this->o_cache->get($date_key);
            if ($date == date('Ymd')) {
                $a_values = $this->o_cache->get($value_key);
                if (!empty($a_values)) {
                    $a_sitemap = $a_values;
                }
            }
        }
        if (empty($a_sitemap)) {
            $o_nav = new NavComplexModel($this->o_di);
            $o_auth = new AuthHelper($this->o_di);
            $a_navgroups = ['Sitemap'];
            if ($o_auth->isLoggedIn()) {
                $auth_level = $this->o_session->getVar('adm_lvl');
                if ($auth_level >= 9) {
                    $a_navgroups[] = 'ConfigLinks';
                    $a_navgroups[] = 'ManagerLinks';
                    $a_navgroups[] = 'EditorLinks';
                }
                elseif ($auth_level >= 6) {
                    $a_navgroups[] = 'ManagerLinks';
                    $a_navgroups[] = 'EditorLinks';
                }
                elseif ($auth_level >= 4) {
                    $a_navgroups[] = 'EditorLinks';
                }
            }
            else {
                $auth_level = 0;
            }
            try {
                $a_sitemap = $o_nav->getSitemap($a_navgroups, $auth_level);
            }
            catch (ModelException $e) {
                $a_message = ViewHelper::errorMessage('Unable to retrieve the sitemap.');
            }
        }
        $log_message = 'sitemap ' . var_export($a_sitemap, true);
        $this->logIt($log_message, LOG_ON, $meth . __LINE__);

        $a_twig_values = $this->createDefaultTwigValues($a_message);
        $a_twig_values['a_sitemap'] = $a_sitemap;
        $tpl = $this->createTplString($a_twig_values);
        return $this->renderIt($tpl, $a_twig_values);
    }
}
