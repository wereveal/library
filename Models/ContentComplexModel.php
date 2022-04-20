<?php /** @noinspection PhpUndefinedConstantInspection */

/**
 * Class ContentComplexModel.
 * @package Ritc_Library
 */
namespace Ritc\Library\Models;

use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Helper\Arrays;
use Ritc\Library\Helper\ExceptionHelper;
use Ritc\Library\Services\DbModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\DbUtilityTraits;

/**
 * Multi-table database operations and business logic.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version 2.0.0
 * @date    2021-11-30 13:32:23
 * @change_log
 * - v2.0.0 - updated for php8                                  - 2021-11-30 wer
 * - v1.0.0 - Initial version                                   - 2018-06-02 wer
 */
class ContentComplexModel
{
    use DbUtilityTraits;

    /** @var string $select_sql */
    private string $select_sql;
    /** @var BlocksModel $o_blocks */
    private BlocksModel $o_blocks;
    /** @var ContentModel $o_content */
    private ContentModel $o_content;
    /** @var PageModel $o_page */
    private PageModel $o_page;

    /**
     * ContentComplexModel constructor.
     *
     * @param Di $o_di
     * @throws ModelException
     */
    public function __construct(Di $o_di)
    {
        $o_db = $o_di->get('db');
        if (! $o_db instanceof DbModel) {
            $message = 'Unable to get instanceof DbModel';
            $error_code = ExceptionHelper::getCodeNumber('instance');
            throw new ModelException($message, $error_code);
        }
        $this->o_blocks       = new BlocksModel($o_db);
        $this->o_content      = new ContentModel($o_db);
        $this->o_page         = new PageModel($o_db);
        $this->setupProperties($o_db);
        $this->buildBasicSelectSql();
    }

    /**
     * Sets all records for a page/block to not current.
     *
     * @param int $c_pbm_id
     * @throws ModelException
     */
    public function deactivateAll(int $c_pbm_id = -1): void
    {
        try {
            $this->o_content->setCurrentFalse($c_pbm_id);
        }
        catch (ModelException $e) {
            throw new ModelException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Deletes all content records for a specified page.
     *
     * @param array $a_values Required either ['page_id'] or ['pbm_id'], optional 'block_id' and 'current'.
     * @return bool
     * @throws ModelException
     */
    public function deleteAllByPage(array $a_values = []):bool
    {
        try {
            $a_results = $this->readAllByPage($a_values);
        }
        catch (ModelException $e) {
            throw new ModelException($e->getMessage(), $e->getCode(), $e);
        }
        $a_content_ids = [];
        foreach ($a_results as $a_record) {
            $a_content_ids[] = ['c_id' => $a_record['c_id']];
        }
        if (count($a_content_ids) > 1) {
            $delete_sql = "
                DELETE FROM {$this->lib_prefix}content
                WHERE c_id = :c_id
            ";
            try {
                $this->o_db->delete($delete_sql, $a_content_ids, false);
            }
            catch (ModelException $e) {
                throw new ModelException($e->getMessage(), $e->getCode(), $e);
            }
        }
        return true;
    }

    /**
     * Deletes a content record.
     *
     * @param int $c_id
     * @return bool
     * @throws ModelException
     */
    public function deleteById(int $c_id = -1):bool
    {
        try {
            return $this->o_content->delete($c_id);
        }
        catch (ModelException $e) {
            throw new ModelException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Reads content for a particular page and optional block.
     *
     * @param array $a_params One of two values must be specified, page_id or pbm_id.
     *                        block_id and current are optional. Current defaults to showing all versions if empty.
     * @return array          Records from the database table.
     * @throws ModelException
     */
    public function readAllByPage(array $a_params = []):array
    {
        $block_extra = '';
        $page_extra  = '';
        $pbm_extra   = '';
        $where       = '';
        $order_by    = '';
        if (!empty($a_params['pbm_id'])) {
            $pbm_id       = $a_params['pbm_id'];
            $a_search_for = ['pbm_id' => $pbm_id];
            $pbm_extra    = ' AND pbm.pbm_id = :pbm_id';
        }
        elseif (!empty($a_params['page_id'])) {
            $page_id      = $a_params['page_id'];
            $a_search_for = [':page_id' => $page_id];
            $page_extra   = ' AND p.page_id = :page_id';
            if (!empty($a_params['block_id'])) {
                $block_id                = $a_params['block_id'];
                $a_search_for[':b_name'] = $block_id;
                $block_extra             = ' AND b.b_name = :b_name';
            }
        }
        else {
            $message    = 'Missing a required value.';
            $error_code = ExceptionHelper::getCodeNumberModel('missing value');
            throw new ModelException($message, $error_code);
        }
        if (!empty($a_params['current'])) {
            $a_search_for[':c_current'] = $a_params['current'];
            $where                      = '
                WHERE c.c_current = :c_current';
        }
        if (!empty($a_params['order_by'])) {
            $order_by = ' ORDER BY c_version ' . $a_params['order_by'];
        }
        $sql = str_replace(
            ['{{pbm_extra}}', '{{blocks_extra}}', '{{page_extra}}'],
            [$pbm_extra, $block_extra, $page_extra],
            $this->select_sql
        );
        $sql .= $where . $order_by;
        try {
            $a_results = $this->o_db->search($sql, $a_search_for);
        }
        catch (ModelException $e) {
            throw new ModelException($e->getMessage(), $e->getCode(), $e);
        }
        return $a_results;
    }

    /**
     * Reads the current content for a page specified by the url.
     *
     * @param int $url_id
     * @return array
     * @throws ModelException
     */
    public function readAllByUrlId(int $url_id = -1):array
    {
        try {
            $a_results = $this->o_page->readByUrlId($url_id);
            $page_id = $a_results[0]['page_id'];
            return $this->readAllByPage(['page_id' => $page_id]);
        }
        catch (ModelException $e) {
            throw new ModelException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Returns all records that current.
     *
     * @return array
     * @throws ModelException
     */
    public function readAllCurrent():array
    {
        $where = '
            WHERE c_current = :c_current
            ORDER BY p.page_title ASC, b.b_name ASC
        ';
        $sql   = str_replace(
            ['{{pbm_extra}}', '{{blocks_extra}}', '{{page_extra}}'],
            '',
            $this->select_sql
        );
        $sql   .= $where;
        $a_search_for = [':c_current' => 'true'];
        try {
            $a_results = $this->o_db->search($sql, $a_search_for);
        }
        catch (ModelException $e) {
            throw new ModelException($e->getMessage(), $e->getCode(), $e);
        }
        return $a_results;
    }

    /**
     * Returns current featured content.
     *
     * @return array
     * @throws ModelException
     */
    public function readAllFeatured():array
    {
        $where = '
            WHERE c.c_current = :c_current
            AND c.c_featured = :c_featured';
        $page_extra = '
                AND p.page_id = :page_id';
        $sql = str_replace(
            ['{{pbm_extra}}', '{{blocks_extra}}', '{{page_extra}}'],
            ['', '', $page_extra],
            $this->select_sql
        ) . $where;
        $a_search_for = [
            ':c_current'  => 'true',
            ':c_featured' => 'true',
            ':page_id'    => ''
        ];

        try {
            $a_results = $this->o_db->search($sql, $a_search_for);
        }
        catch (ModelException $e) {
            throw new ModelException($e->getMessage(), $e->getCode(), $e);
        }
        return $a_results;
    }

    /**
     * Reads the content for a page by the content id.
     *
     * @param int $id
     * @return array
     * @throws ModelException
     */
    public function readByContentId(int $id = -1):array
    {
        $pin = $this->o_content->getPrimaryIndexName();
        $sql = str_replace(
            ['{{pbm_extra}}', '{{blocks_extra}}', '{{page_extra}}'],
            '',
            $this->select_sql
        ) . ' WHERE ' . $pin . ' = :' . $pin;
        try {
            $a_results = $this->o_db->search($sql, [':' . $pin => $id]);
        }
        catch (ModelException $e) {
            throw new ModelException($e->getMessage(), $e->getCode(), $e);
        }
        return $a_results[0];
    }

    /**
     * Reads the current version of content for a page.
     *
     * @param array $a_params
     * @return array Record(s).
     * @throws ModelException
     */
    public function readCurrent(array $a_params = []):array
    {
        $a_params['current'] = 'true';
        try {
            return $this->readAllByPage($a_params);
        }
        catch (ModelException $e) {
            throw new ModelException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Returns the values of shared content.
     *
     * @return array
     * @throws ModelException
     */
    public function readCurrentShared():array
    {
        $blocks_extra = ['{{pbm_extra}}', '{{blocks_extra}}', '{{page_extra}}'];
        $where = 'WHERE c.c_current = :c_current AND b.b_type = :b_type AND b.b_active = :b_active';
        $sql = str_replace($blocks_extra, '', $this->select_sql) . $where;
        $a_search_for = [
            ':b_active'  => 'true',
            ':b_type'    => 'shared',
            ':c_current' => 'true'
        ];
        try {
            $results = $this->o_db->search($sql, $a_search_for);
        }
        catch (ModelException $e) {
            throw new ModelException($e->getMessage(), $e->getCode(), $e);
        }
        return $results;
    }

    /**
     * Saves a new content record.
     *
     * @param array $a_values
     * @return int
     * @throws ModelException
     */
    public function saveNew(array $a_values = []): int
    {
        if (empty($a_values['c_pbm_id'])) {
            $err_code = ExceptionHelper::getCodeNumberModel('create missing values');
            throw new ModelException('The page/block must be specified to save.', $err_code);
        }
        $a_values['c_current'] = 'true';
        $a_values['c_version'] = 1;
        if (empty($a_values['c_type'])) {
            $a_values['c_type'] = 'text';
        }
        try {
            $a_ids = $this->o_content->create($a_values);
            return $a_ids[0];
        }
        catch (ModelException $e) {
            throw new ModelException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Updates the content for a page/block.
     * If version control in effect, creates a new record, else, updates existing.
     *
     * @param array $a_values
     * @return bool
     * @throws ModelException
     */
    public function updateContent(array $a_values = []):bool
    {
        $a_requires_values = [
            'c_pbm_id',
            'c_id'
        ];
        $a_missing_values = Arrays::findMissingValues($a_values, $a_requires_values);
        if (!empty($a_missing_values)) {
            $err_code = ExceptionHelper::getCodeNumberModel('create missing values');
            $missing = '';
            foreach ($a_missing_values as $key_name) {
                $missing .= $missing === '' ? $key_name : ', ' . $key_name;
            }
            throw new ModelException('Missing Values: ' . $missing, $err_code);
        }
        try {
            $a_results = $this->o_content->readById($a_values['c_id']);
        }
        catch (ModelException $e) {
            throw new ModelException($e->getMessage(), $e->getCode(), $e);
        }
        $a_values['c_version'] = $a_results['c_version'] + 1;
        $a_values['c_updated'] = date('Y-m-d H:i:s');
        $a_values['c_current'] = 'true';
        if (CONTENT_VCS) {
            unset($a_values['c_id']);
            $this->o_db->startTransaction();
            try {
                $set_results = $this->o_content->setCurrentFalse($a_values['c_pbm_id']);
                if (!$set_results) {
                    $msg = 'Unable to update the record: could not change old to not current.';
                    $err_code = ExceptionHelper::getCodeNumberModel('update not permitted');
                    throw new ModelException($msg, $err_code);
                }
            }
            catch (ModelException $e) {
                throw new ModelException($e->getMessage(), $e->getCode(), $e);
            }
            try {
                $results = $this->o_content->create($a_values);
                if (empty($results)) {
                    $this->o_db->rollbackTransaction();
                    $err_code = ExceptionHelper::getCodeNumberModel('update unknown');
                    throw new ModelException('Unknown Error', $err_code);
                }
                $this->o_db->commitTransaction();
                return true;
            }
            catch (ModelException $e) {
                $this->o_db->rollbackTransaction();
                throw new ModelException($e->getMessage(), $e->getCode(), $e);
            }
        }
        else {
            try {
                return $this->o_content->update($a_values);
            }
            catch (ModelException $e) {
                throw new ModelException($e->getMessage(), $e->getCode(), $e);
            }
        }
    }

    ### Utility Methods

    /**
     * Creates the common sql used throughout the class.
     *
     * Sets the class property $select_sql.
     */
    private function buildBasicSelectSql():void
    {
        $prefix = $this->lib_prefix;
        $a_content_fields = $this->o_content->getDbFields();
        $a_page_fields    = $this->o_page->getDbFields();
        $a_blocks_fields  = $this->o_blocks->getDbFields();
        $a_select_fields = [
            'c' => $a_content_fields,
            'p' => $a_page_fields,
            'b' => $a_blocks_fields
        ];
        $select_fields = $this->mergeAndBuildSqlSelect($a_select_fields, true);
        $this->select_sql = /** @lang text */
            "
            SELECT {$select_fields}
            FROM {$prefix}content as c
            JOIN {$prefix}page_blocks_map as pbm
              ON c.c_pbm_id = pbm.pbm_id{{pbm_extra}}
            JOIN {$prefix}blocks as b
              ON b.b_id = pbm.pbm_block_id{{blocks_extra}}
            JOIN {$prefix}page as p
              ON p.page_id = pbm.pbm_page_id{{page_extra}}
        ";
    }
}
