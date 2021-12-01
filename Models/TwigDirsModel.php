<?php
/**
 * Class TwigDirsModel
 * @package Ritc_Library
 */
namespace Ritc\Library\Models;

use Ritc\Library\Abstracts\ModelAbstract;
use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Services\DbModel;

/**
 * Does database operations on the twig_prefix table.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v2.0.0
 * @date    2021-11-30 15:15:30
 * @change_log
 * - v2.0.0         - updated for php8 only         - 2021-11-30 wer
 * - v1.0.1         - fixed default dir names       - 2018-05-28 wer
 * - v1.0.0         - Initial Production version    - 2017-12-15 wer
 * - v1.0.0-alpha.0 - Initial version               - 2017-05-13 wer
 */
class TwigDirsModel extends ModelAbstract
{
    /**
     * TwigDirsModel constructor.
     *
     * @param DbModel $o_db
     */
    public function __construct(DbModel $o_db)
    {
        $this->setupProperties($o_db, 'twig_dirs');
        $this->setRequiredKeys(['tp_id', 'td_name']);
    }

    ### Abstract Methods ###
    # create(array $a_values = [])
    # read(array $a_search_for = [], array $a_search_params = [])
    # update(array $a_values = [], array $a_do_not_change = [])
    # delete($id = -1)
    ###

    ### Specific Methods ###
    /**
     * Creates the records for default directories for the prefix id.
     *
     * @param int $prefix_id Required.
     * @return array
     * @throws ModelException
     * @noinspection PhpUndefinedConstantInspection
     */
    public function createDefaultDirs(int $prefix_id = -1):array
    {
        $meth = ' — ' . __METHOD__;
        if ($prefix_id < 1) {
            throw new ModelException('Prefix record id not provided' . $meth, 20);
        }
        if (file_exists(SRC_CONFIG_PATH . '/install_files/default_data.php')) {
            $a_dd = include SRC_CONFIG_PATH . '/install_files/default_data.php';
            $a_default_dirs = $a_dd['twig_default_dirs'];
        }
        else {
            $a_default_dirs = [
                'themes',
                'elements',
                'forms',
                'pages',
                'snippets',
                'tests'
            ];
        }
        $a_dirs = [];
        foreach ($a_default_dirs as $dir) {
            $a_dirs[] = ['tp_id' => $prefix_id, 'td_name' => $dir];

        }
        $a_new_ids = [];
        foreach ($a_dirs as $a_dir) {
            try {
                $a_results = $this->read($a_dir, ['search_type' => 'AND']);
                if (empty($a_results)) {
                    try {
                        $results = $this->create($a_dir);
                        if (empty($results)) {
                            $message = "Unable to create the default dir {$a_dir['td_name']}";
                            throw new ModelException($message . $meth, 110);
                        }
                        $a_new_ids[] = $results[0];
                    }
                    catch (ModelException $e) {
                        $message = "Unable to create the default dirs for {$prefix_id}. ";
                        $message .= DEVELOPER_MODE
                            ? $e->errorMessage()
                            : $e->getMessage();
                        throw new ModelException($message . $meth, $e->getCode(), $e);
                    }
                }
            }
            catch (ModelException $e) {
                $message = 'Unable to determine if the default dir exists. ';
                $message .= DEVELOPER_MODE
                    ? $e->errorMessage()
                    : $e->getMessage();
                throw new ModelException($message . $meth, 110);
            }
        }
        return $a_new_ids;
    }
}
