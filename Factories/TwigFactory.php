<?php /** @noinspection PhpMultipleClassDeclarationsInspection */
/** @noinspection PhpUndefinedConstantInspection */

/**
 * Class TwigFactory
 * @package Ritc_Library
 */
namespace Ritc\Library\Factories;

use Error;
use Exception;
use Parsedown;
use ParsedownExtra;
use Ritc\Library\Exceptions\FactoryException;
use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Helper\ExceptionHelper;
use Ritc\Library\Helper\LocateFile;
use Ritc\Library\Helper\OopHelper;
use Ritc\Library\Models\TwigComplexModel;
use Ritc\Library\Services\Di;
use Twig\Loader\FilesystemLoader as TwigLoaderFilesystem;
use Twig\Environment as TwigEnvironment;
use Twig\Error\LoaderError as TwigErrorLoader;
use Twig\Extension\DebugExtension as TwigExtensionDebug;
use Twig\TwigFilter;
use Twig\TwigTest;

/**
 * TwigFactory lets us create a twig object, specific to a configuration(s)
 * allowing multiple twig objects to render the html.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v3.4.0.beta.1
 * @date    2021-11-29 14:55:43
 * @todo    Need to test this seriously since there have been potential hidden changes
 * @change_log
 * - v3.4.0.beta.1  - updated for php8
 * - v3.3.0         - Modified to match changes in Twig Namespace                                             - 2019-03-28 wer
 * - v3.2.0         - added a twig test to the factory - inPublic which tests to see if the file exists       - 2018-05-01 wer
 * - v3.1.3         - Minor change in testing                                                                 - 2018-04-19 wer
 * - v3.1.2         - Class renamed elsewhere reflected here                                                  - 2018-04-04 wer
 * - v3.1.0         - Refactoring of TwigModel created the need for changes here.                             - 2017-06-20 wer
 * - v3.0.0         - Renamed getTwig to getTwigByFile and rewrote getTwig to use either getTwigByDb          - 2017-05-15 wer
 *                    or getTwigByFile, defaulting to getTwigByFile. getTwigDb was renamed to getTwigByDb
 *                    Theoretically backward compatible but the getTwig method was completely rewritten.
 * - v2.1.0         - Added method to create a twig object from database config.                              - 2017-05-13 wer
 * - v2.0.0         - added 2 new create methods, createMultiSource, createFromArray                          - 2017-03-13 wer
 *                    getTwig() rewritten to used the new methods but has backwards
 *                    compatibility with version 1.
 *                    Deleted getLoader as it did not provide any usefulness.
 * - v1.2.0         - changed to allow config file to include a path.                                         - 2017-02-09 wer
 * - v1.1.0         - added the ability to get the loader used to add additional twig namespaces              - 2017-02-08 wer
 * - v1.0.0         - not sure why this is beta. Removed Base abstract class                                  - 09/01/2015 wer
 * - v1.0.0ß2       - moved to the Factories namespace
 * - v0.2.0         - changed the name of the method which is used to create/return the object                - 09/25/2014 wer
 *                    and cleaned up some code.
 * - v0.1.1         - changed to implment the changes in Base class                                           - 09/23/2014 wer
 * - v0.1.0         - initial file creation                                                                   - 2013-11-11 wer
 */
class TwigFactory
{
    /** @var Di $o_di */
    protected static Di $o_di;
    /** @var TwigLoaderFilesystem $o_loader */
    private TwigLoaderFilesystem $o_loader;
    /** @var Parsedown $o_md */
    private Parsedown $o_md;
    /** @var ParsedownExtra $o_mde */
    private ParsedownExtra $o_mde;
    /** @var TwigEnvironment $o_twig */
    private TwigEnvironment $o_twig;
    /** @var array $instance */
    private static array $instance = array();

    /**
     * TwigFactory constructor.
     *
     * @param $a_twig_config
     * @throws FactoryException
     */
    private function __construct($a_twig_config)
    {
        $meth = __METHOD__ . '.';
        $o_md = new Parsedown();
        $this->o_md = $o_md;
        try {
            $o_mde = new ParsedownExtra();
            $this->o_mde = $o_mde;
        }
        catch (Exception) {
            // silently ignore
        }
        try {
            $o_loader = new TwigLoaderFilesystem($a_twig_config['default_path']);
            // $o_loader = new Twig_Loader_Filesystem($a_twig_config['default_path']);
            $this->o_loader = $o_loader;
            foreach ($a_twig_config['additional_paths'] as $path => $namespace ) {
                try {
                    $o_loader->prependPath($path, $namespace);
                }
                catch (TwigErrorLoader $e) {
                    /** @noinspection ForgottenDebugOutputInspection */
                    $msg = 'Unable to load paths with Twig Loader: ' . $e->getMessage() . ' -- ' . $meth;
                    throw new FactoryException($msg, $e->getCode(), $e);
                }

            }
            try {
                $this->o_twig = new TwigEnvironment($o_loader, $a_twig_config['environment_options']);
                $md_filter = new TwigFilter('md', [$this, 'mdFilter'], ['is_safe' => ['html']]);
                $this->o_twig->addFilter($md_filter);
                /** @noinspection PhpTypedPropertyMightBeUninitializedInspection */
                if ($this->o_mde instanceof ParsedownExtra) {
                    $mde_filter = new TwigFilter('mde', [$this, 'mdeFilter', ['is_safe' => ['html']]]);
                    $this->o_twig->addFilter($mde_filter);
                }
                $inPublic_test = new TwigTest('inPublic', [$this, 'inPublicTest']);
                $this->o_twig->addTest($inPublic_test);
                if (defined('DEVELOPER_MODE') && DEVELOPER_MODE) {
                    $this->o_twig->addExtension(new TwigExtensionDebug());
                }
            }
            catch (Error $e) {
                $msg = 'Twig Environment Error: ' . $e->getMessage() . ' -- ' . $meth;
                $err_code = ExceptionHelper::getCodeNumberFactory('instance');
                throw new FactoryException($msg, $err_code, $e);
            }
        }
        catch (Error $e) {
            $msg = 'Unable to create Twig Loader: ' . $meth;
            $err_code = ExceptionHelper::getCodeNumberFactory('instance');
            throw new FactoryException($msg, $err_code, $e);
        }
    }

    /**
     * Primary method for the factory. see \ref twigfactory
     * Created to provide backwards compatibility.
     *
     * @param array|string|Di $param_one Optional sort of
     * @param bool|string     $param_two
     * @return TwigEnvironment
     * @throws FactoryException
     */
    public static function getTwig(array|string|Di $param_one = '', bool|string $param_two = ''): TwigEnvironment
    {
        if ($param_one instanceof Di) {
            self::$o_di = $param_one;
            if (!is_bool($param_two)) {
                $param_two = false;
            }
            try {
                return self::getTwigByDb($param_one, $param_two);
            }
            catch (FactoryException $e) {
                throw new FactoryException($e->errorMessage(), $e->getCode(), $e);
            }
        }

        if (is_bool($param_two)) {
            $param_two = '';
        }
        try {
            return self::getTwigByFile($param_one, $param_two);
        }
        catch (FactoryException $e) {
            throw new FactoryException($e->errorMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Returns the twig environment instance as configured from database values.
     *
     * @param Di   $o_di
     * @param bool $use_cache
     * @return TwigEnvironment
     * @throws FactoryException
     */
    public static function getTwigByDb(Di $o_di, bool $use_cache = true): TwigEnvironment
    {
        $cache = $use_cache
            ? SRC_PATH . '/twig_cache'
            : false;
        $debug = defined('DEVELOPER_MODE') ? DEVELOPER_MODE : false;
        $a_config = [
            'default_path' => SRC_PATH . '/templates',
            'additional_paths' => [],
            'environment_options' => [
                'cache'       => $cache,
                'auto_reload' => true,
                'debug'       => $debug
            ]
        ];
        $try_create = false;
        $o_tf = '';
        try {
            $o_tp = new TwigComplexModel($o_di);
            try {
                $results = $o_tp->readTwigConfig();
                if (empty($results)) {
                    $try_create = true;
                }
                else {
                    $additional_paths = [];
                    $default_path = '';
                    foreach ($results as $record) {
                        if ($default_path === '' && $record['tp_default'] === 'true') {
                            $default_path = $record['twig_path'];
                            $a_config['default_path'] = BASE_PATH . $default_path;
                        }
                        $twig_path = BASE_PATH . $record['twig_path'] . '/' . $record['twig_dir'];
                        $additional_paths[$twig_path] = $record['twig_prefix'] . $record['twig_dir'];
                    }
                    $a_config['additional_paths'] = $additional_paths;
                    try {
                        $o_tf = self::createWithArray($a_config, 'db');
                    }
                    catch (FactoryException $e) {
                        throw new FactoryException('Unable to create instance of the twig.' . $e->errorMessage());
                    }
                    catch (Error $e) {
                        throw new FactoryException('Unable to create instace of the twig: ' . $e->getMessage());
                    }
                }
            }
            catch (ModelException) {
                $try_create = true;
            }
        }
        catch (Error) {
            $try_create = true;
        }
        if ($try_create || !($o_tf instanceof self)) {
            try {
                $o_tf = self::create();
            }
            catch (FactoryException $e) {
                throw new FactoryException($e->getMessage(), $e->getCode(), $e);
            }
        }
        /** @noinspection PhpUndefinedFieldInspection */
        return $o_tf->o_twig;
    }

    /**
     * Returns the twig environment object which we use to do all the template rendering.
     *
     * @param array|string $config    Optional but highly recommended. \ref twigfactory
     * @param string       $namespace Optional, defaults to ''
     * @return TwigEnvironment
     * @throws FactoryException
     */
    public static function getTwigByFile(array|string $config = 'twig_config.php', string $namespace = ''):TwigEnvironment
    {
        if (is_array($config)) {
            if (empty($config)) {
                $o_tf = self::create('twig_config.php', $namespace);
            }
            elseif (isset($config['default_path'])) {
                $o_tf = self::createWithArray($config, 'array');
            }
            elseif (!empty($config['twig_files'])) {
                $a_config_files = $config['twig_files'];
                $name = empty($config['instance_name'])
                    ? 'main'
                    : $config['instance_name'];
                $use_main_twig = empty($config['use_default'])
                    ? true
                    : $config['use_default'];
                $o_tf = self::createMultiSource($a_config_files, $name, $use_main_twig);
            }
            else {
                $o_tf = self::create('twig_config.php', $namespace);
            }
        }
        else {
            $o_tf = self::create($config, $namespace);
        }
        return $o_tf->o_twig;
    }

    /**
     * Creates an instance of the class.
     * Note that this method returns the factory instance and not the Twig_Environment object.
     * Use self::getTwig() to get the Twig_Environment object.
     * The instance name will be derived from the config file name.
     * If the file name is the default name, the instance name will be main.
     *
     * @param string $config_file Optional, defaults to twig_config.php
     * @param string $namespace   Optional, defaults to no namespace (looks for the file in all the usual places).
     *                            If namespace is given, it looks for the file in the namespace.
     * @return TwigFactory
     * @throws FactoryException
     */
    protected static function create(string $config_file = 'twig_config.php', string $namespace = ''): TwigFactory
    {
        $org_config_file = $config_file;
        if (str_contains($config_file, '/')) {
            $a_parts = explode('/', $config_file);
            $config_file = $a_parts[count($a_parts) - 1];
        }
        [$name, $extension] = explode('.', $config_file);
        unset($extension);
        $name = str_replace('twig_config_', '', $name);
        if ($name === 'twig_config' || $name === '') {
            $name = 'main';
        }
        if (!isset(self::$instance[$name])) {
            $a_twig_config = self::retrieveTwigConfigArray($org_config_file, $namespace);
            try {
                self::$instance[$name] = new TwigFactory($a_twig_config);
            }
            catch (Error $e) {
                throw new FactoryException('Unable to create a new TwigFactory: ' . $e->getMessage(), 100, $e);
            }
        }
        return self::$instance[$name];
    }

    /**
     * Creates an instance of the class.
     * Note that this method returns the factory instance and not the Twig_Environment object.
     * Use self::getTwig() to get the Twig_Environment object.
     *
     * @param array  $a_config_files Optional but if omitted silly, because it will only return the main twig instance
     *                               [['name' => 'twig_config.php', 'namespace' => 'Ritc\MyApp']...]
     * @param string $name           Optional but recommended as it will only return the main twig environment if it has already been defined.
     * @param bool   $use_main_twig  Optional, defaults to true. If set to false, will only use the config files specified.
     * @return TwigFactory
     * @throws FactoryException
     */
    protected static function createMultiSource(array $a_config_files = [], string $name = 'main', bool $use_main_twig = true):TwigFactory
    {
        if (!isset(self::$instance[$name])) {
            # No config files specified
            if (empty($a_config_files)) {
                return self::create();
            }
            if ($use_main_twig) {
                # Use /src/config/twig_config.php to create the $a_twig_config array
                $a_twig_config = self::retrieveTwigConfigArray();
            }
            else {
                # Use the first key=>value pair in $a_config_files to create the $a_twig_config_array
                $a_twig_config = self::retrieveTwigConfigArray($a_config_files[0]['name'], $a_config_files[0]['namespace']);
                unset($a_config_files[0]);
            }

            # Go through the config files specified to create additional paths
            foreach ($a_config_files as $a_config_file) {
                $config_file = $a_config_file['name'];
                $namespace = !empty($a_config_file['namespace'])
                    ? $a_config_file['namespace']
                    : '';
                $a_twig_config_next = self::retrieveTwigConfigArray($config_file, $namespace);
                if (!empty($a_twig_config_next['additional_paths'])) {
                    /** @noinspection SlowArrayOperationsInLoopInspection */
                    $a_twig_config['additional_paths'] = array_merge(
                        $a_twig_config['additional_paths'],
                        $a_twig_config_next['additional_paths']
                    );
                }
            }
            try {
                self::$instance[$name] = new TwigFactory($a_twig_config);
            }
            catch (Error $e) {
                throw new FactoryException('Unable to create a new TwigFactory: ' . $e->getMessage(), 100, $e);
            }
        }
        return self::$instance[$name];
    }

    /**
     * Creates an instance of the class.
     * Note that this method returns the factory instance and not the Twig_Environment object.
     * Use self::getTwig() to get the Twig_Environment object.
     *
     * @param array  $a_twig_config    Required. Array must have three key pairs,
     *                                 default_path        => string
     *                                 additional_paths    => array map of path => twig_name
     *                                 environment_options => array map of twig options, have at least the
     *                                 cache, debug, and auto_reload options set.
     * @param string $name             Optional but recommended. Name to give the instance. Defaults to main.
     * @return TwigFactory
     * @throws FactoryException
     */
    protected static function createWithArray(array $a_twig_config = [], string $name = 'main'):TwigFactory
    {
        if (!isset(self::$instance[$name])) {
            if (   empty($a_twig_config)
                || empty($a_twig_config['default_path'])
                || empty($a_twig_config['additional_paths'])
                || empty($a_twig_config['environment_options'])
            ) {
                return self::create();
            }
            try {
                self::$instance[$name] = new TwigFactory($a_twig_config);
            }
            catch (FactoryException | Error $e) {
                $msg = 'Unable to create a new TwigFactory: ' . $e->getMessage();
                $err_code = ExceptionHelper::getCodeNumberFactory('instance');
                throw new FactoryException($msg, $err_code, $e);
            }
        }
        return self::$instance[$name];
    }

    /**
     * Returns the array from the config file.
     *
     * @param string $config_file Required Can be a file name or file with path.
     * @param string $namespace   Optional. If omitted, looks in the SRC_CONFIG_PATH.
     *                            Use namespace format e.g. My\Namespace.
     * @return array
     */
    private static function retrieveTwigConfigArray(string $config_file = 'twig_config.php', string $namespace = ''):array
    {
        if (str_contains($config_file, '/')) {
            $config_w_path = $config_file;
        }
        else {
            $namespace = OopHelper::namespaceExists($namespace)
                ? $namespace
                : '';
            $config_w_path = LocateFile::getConfigWithPath($config_file, $namespace);
        }
        return $config_w_path !== '' ? require $config_w_path : [];
    }

    ### Extensions to Twig ###
    /**
     * Determines if a file exists within the public directory specified.
     *
     * @param string $value Path and filename starting from the public root directory.
     * @return bool
     */
    public function inPublicTest(string $value = ''):bool
    {
        $file_w_path = PUBLIC_PATH . $value;
        return file_exists($file_w_path);
    }

    /**
     * Converts markdown into html.
     *
     * @param string $value
     * @return string
     */
    public function mdFilter(string $value = ''):string
    {
        if ($this->o_md instanceof Parsedown) {
            $value = $this->o_md->text($value);
        }
        return $value;
    }

    /**
     * Converts markdown extra into html.
     *
     * @param string $value
     * @return string
     */
    public function mdeFilter(string $value = ''): string
    {
        if ($this->o_mde instanceof ParsedownExtra) {
            /** @noinspection CallableParameterUseCaseInTypeContextInspection */
            $value = $this->o_mde->text($value);
        }
        return $value;
    }

    /**
     * @return TwigLoaderFilesystem
     */
    public function getLoader():TwigLoaderFilesystem
    {
        return $this->o_loader;
    }

    /**
     * @return Parsedown
     */
    public function getMd(): Parsedown
    {
        return $this->o_md;
    }

    /**
     * @return ParsedownExtra
     */
    public function getMde(): ParsedownExtra
    {
        return $this->o_mde;
    }

    /**
     * @return array
     */
    public static function getInstance():array
    {
        return self::$instance;
    }
}
