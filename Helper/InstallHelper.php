<?php
/**
 * Class InstallHelper.
 *
 * @package Ritc_Library
 */
namespace Ritc\Library\Helper;

/**
 * Helper for installing a new or update an existing framework.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version 1.0.0-alpha.0
 * @date    2021-12-07 22:21:39
 * @change_log
 * - v1.0.0-alpha.0 - Initial version.                               - 2021-12-07 wer
 */
class InstallHelper
{
    protected array  $a_config;
    protected string $db_type;
    protected string $the_db_config_file;

    public function createDbConfigFiles(): void
    {
        $install_host   = $this->a_config['install_host'] ?? 'default';
        $db_file_name   = $this->a_config['db_file'] ?? 'db_config';
        $db_config_file = $db_file_name . '.php';
        $db_local_file  = $db_file_name . '_local.php';

        if (empty($this->a_config['db_port'])) {
            $this->a_config['db_port'] = match ($this->a_config['db_type']) {
                'mysql' => '3306',
                'sqlite' => '',
                default => '5432' // pgsql
            };
        }
        if (empty($this->a_config['db_ro_pass']) || empty($this->a_config['db_ro_user'])) {
            $this->a_config['db_ro_user'] = $this->a_config['db_user'];
            $this->a_config['db_ro_pass'] = $this->a_config['db_pass'];
        }
        $db_config_file_text =<<<EOT
            <?php
            return [
                'driver'     => '{$this->a_config['db_type']}',
                'host'       => '{$this->a_config['db_host']}',
                'port'       => '{$this->a_config['db_port']}',
                'name'       => '{$this->a_config['db_name']}',
                'user'       => '{$this->a_config['db_user']}',
                'password'   => '{$this->a_config['db_pass']}',
                'userro'     => '{$this->a_config['db_ro_user']}',
                'passro'     => '{$this->a_config['db_ro_pass']}',
                'persist'    => {$this->a_config['db_persist']},
                'prefix'     => '{$this->a_config['db_prefix']}',
                'errmode'    => '{$this->a_config['db_errmode']}',
                'db_prefix'  => '{$this->a_config['db_prefix']}',
                'lib_prefix' => '{$this->a_config['lib_db_prefix']}'
            ];
        EOT;
        file_put_contents(SRC_CONFIG_PATH . '/' . $db_config_file, $db_config_file_text);

        $db_local_type = empty($this->a_config['db_local_type']) ? $this->a_config['db_type'] : $this->a_config['db_local_type'];
        $db_local_host = empty($this->a_config['db_local_host']) ? 'localhost'                : $this->a_config['db_local_host'];
        $db_local_port = empty($this->a_config['db_local_port']) ? $this->a_config['db_port'] : $this->a_config['db_local_port'];
        $db_local_name = empty($this->a_config['db_local_name']) ? $this->a_config['db_name'] : $this->a_config['db_local_name'];
        $db_local_user = empty($this->a_config['db_local_user']) ? $this->a_config['db_user'] : $this->a_config['db_local_user'];
        $db_local_pass = empty($this->a_config['db_local_pass']) ? $this->a_config['db_pass'] : $this->a_config['db_local_pass'];
        $db_config_file_text =<<<EOT
            <?php
            return [
                'driver'     => '{$db_local_type}',
                'host'       => '{$db_local_host}',
                'port'       => '{$db_local_port}',
                'name'       => '{$db_local_name}',
                'user'       => '{$db_local_user}',
                'password'   => '{$db_local_pass}',
                'userro'     => '{$this->a_config['db_ro_user']}',
                'passro'     => '{$this->a_config['db_ro_pass']}',
                'persist'    => {$this->a_config['db_persist']},
                'prefix'     => '{$this->a_config['db_prefix']}',
                'errmode'    => '{$this->a_config['db_errmode']}',
                'db_prefix'  => '{$this->a_config['db_prefix']}',
                'lib_prefix' => '{$this->a_config['lib_db_prefix']}'
            ];
        EOT;
        file_put_contents(SRC_CONFIG_PATH . '/' . $db_local_file, $db_config_file_text);

        $site_host    = empty($this->a_config['server_http_host']) ? 'test' : $this->a_config['server_http_host'];
        $db_site_file = $db_file_name . '_' . $site_host . '.php';
        $db_site_type = empty($this->a_config['db_site_type']) ? $this->a_config['db_type'] : $this->a_config['db_site_type'];
        $db_site_host = empty($this->a_config['db_site_host']) ? $this->a_config['db_host'] : $this->a_config['db_site_host'];
        $db_site_port = empty($this->a_config['db_site_port']) ? $this->a_config['db_port'] : $this->a_config['db_site_port'];
        $db_site_name = empty($this->a_config['db_site_name']) ? $this->a_config['db_name'] : $this->a_config['db_site_name'];
        $db_site_user = empty($this->a_config['db_site_user']) ? $this->a_config['db_user'] : $this->a_config['db_site_user'];
        $db_site_pass = empty($this->a_config['db_site_pass']) ? $this->a_config['db_pass'] : $this->a_config['db_site_pass'];

        $db_config_file_text =<<<EOT
            <?php
            return [
                'driver'     => '{$db_site_type}',
                'host'       => '{$db_site_host}',
                'port'       => '{$db_site_port}',
                'name'       => '{$db_site_name}',
                'user'       => '{$db_site_user}',
                'password'   => '{$db_site_pass}',
                'userro'     => '{$this->a_config['db_ro_user']}',
                'passro'     => '{$this->a_config['db_ro_pass']}',
                'persist'    => {$this->a_config['db_persist']},
                'prefix'     => '{$this->a_config['db_prefix']}',
                'errmode'    => '{$this->a_config['db_errmode']}',
                'db_prefix'  => '{$this->a_config['db_prefix']}',
                'lib_prefix' => '{$this->a_config['lib_db_prefix']}'
            ];
        EOT;
        file_put_contents(SRC_CONFIG_PATH . '/' . $db_site_file, $db_config_file_text);

        if ($this->a_config['specific_host'] !== '' && $this->a_config['specific_host'] === $install_host) {
            $this->the_db_config_file = $db_local_file;
            $this->db_type = $db_local_type;
        }
        else {
            switch ($install_host) {
                case 'localhost':
                    $this->the_db_config_file = $db_local_file;
                    $this->db_type = $db_local_type;
                    break;
                case $site_host:
                    $this->the_db_config_file = $db_site_file;
                    $this->db_type = $db_site_type;
                    break;
                default:
                    $this->the_db_config_file = $db_config_file;
                    $this->db_type = $this->a_config['db_type'];
            }
        }
         
    }

    /**
     * @return string
     */
    public function getDbType(): string
    {
        return $this->db_type;
    }

    /**
     * @return string
     */
    public function getTheDbConfigFileName(): string
    {
        return $this->the_db_config_file;
    }

    /**
     * @param array $a_config
     */
    public function setConfig(array $a_config): void
    {
        $this->a_config = $a_config;
    }

}
