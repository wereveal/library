<?php
/**
 * Class ContentModel.
 * @package Ritc_Library
 */
namespace Ritc\Library\Models;

use Ritc\Library\Abstracts\ModelAbstract;
use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Helper\ExceptionHelper;
use Ritc\Library\Services\DbModel;
use Ritc\Library\Traits\LogitTraits;

/**
 * Does all the database operations and business logic for content.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version 1.0.0-alpha.0
 * @date    2018-05-27 18:56:10
 * @change_log
 * - v1.0.0-alpha.0 - Initial version        - 2018-05-27 wer
 * @todo ContentModel.php - Everything
 */
class ContentModel extends ModelAbstract
{
    use LogitTraits;

    public function __construct(DbModel $o_db)
    {
        $this->setupProperties($o_db, 'content');
    }

    /**
     * Reads all versions of content for a particular page and block
     * @param int    $page_id Required
     * @param string $block   Optional, defaults to 'body'
     * @return array          Records from the database table.
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function readAllByPage($page_id = -1, $block = 'body') {
        if ($page_id < 1) {
            $message = 'Missing the required page id.';
            $error_code = ExceptionHelper::getCodeNumberModel('missing value');
            throw new ModelException($message, $error_code);
        }
        $a_search_for = [
            'c_page_id' => $page_id,
            'c_block'   => $block
        ];
        try {
            $a_results = $this->read($a_search_for);
        }
        catch (ModelException $e) {
            throw new ModelException($e->getMessage(), $e->getCode(), $e);
        }
        return $a_results;
    }

    /**
     * Reads the current version of content for a page.
     * @param int    $page_id
     * @param string $block
     * @return array Record - single record.
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function readCurrent($page_id = -1, $block = 'body')
    {
        if ($page_id < 1) {
            $message    = 'Missing the required page id.';
            $error_code = ExceptionHelper::getCodeNumberModel('missing value');
            throw new ModelException($message, $error_code);
        }
        $a_search_for = [
            'c_page_id' => $page_id,
            'c_block'   => $block,
            'c_current' => 'true'
        ];
        try {
            $a_results = $this->read($a_search_for);
            if (count($a_results) === 1) {
                $a_results = $a_results[0];
            }
            elseif (count($a_results) > 1) {
                $message    = 'Too many records were returned.';
                $error_code = ExceptionHelper::getCodeNumberModel('read too many records');
                throw new ModelException($message, $error_code);
            }
            else {
                $message    = '.';
                $error_code = ExceptionHelper::getCodeNumberModel('missing value');
                throw new ModelException($message, $error_code);
            }
        }
        catch (ModelException $e) {
            throw new ModelException($e->getMessage(), $e->getCode(), $e);
        }
        return $a_results;
    }
}
