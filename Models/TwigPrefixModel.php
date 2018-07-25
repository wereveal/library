<?php
/**
 * Class TwigPrefixModel
 * @package Ritc_Library
 */
namespace Ritc\Library\Models;

use Ritc\Library\Abstracts\ModelAbstract;
use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Helper\Arrays;
use Ritc\Library\Services\DbModel;

/**
 * Does database operations on the twig_prefix table.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v1.1.0
 * @date    2018-06-15 11:45:29
 * @change_log
 * - v1.1.0         - Refactored to use ModelAbstract   - 2018-06-15 wer
 * - v1.0.1         - bug fixes                         - 2018-04-03 wer
 * - v1.0.0         - Initial production version        - 2017-12-12 wer
 * - v1.0.0-alpha.0 - Initial version                   - 2017-05-13 wer
 */
class TwigPrefixModel extends ModelAbstract
{
    /**
     * TwigPrefixModel constructor.
     * @param \Ritc\Library\Services\DbModel $o_db
     */
    public function __construct(DbModel $o_db)
    {
        $this->setupProperties($o_db, 'twig_prefix');
        $this->setRequiredKeys(['tp_prefix', 'tp_path']);
    }

    ### Abstract Methods ###
    # create(array $a_values = [])
    # read(array $a_search_for = [], array $a_search_params = [])
    # update(array $a_values = [], array $a_do_not_change = [])
    # delete($id = -1)
    ###

    ### Specific Methods ###
    /**
     * Checks the values to see if they are trying to set the record to be saved/updated to be the default prefix.
     *
     * @param array $a_values
     * @return array
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function clearDefaultPrefix(array $a_values = []):array
    {
        $meth       = ' -- ' . __METHOD__;
        $is_default = 'false';
        if (Arrays::isArrayOfAssocArrays($a_values)) {
            foreach ($a_values as $key => $a_record) {
                if (!empty($a_record['tp_default']) && $a_record['tp_default'] === 'true') {
                    if ($is_default === 'false') {
                        $is_default = 'true';
                        try {
                            if (!$this->updateDefaultPrefixOff()) {
                                $this->error_message = 'Could not set other prefix as not default.';
                                throw new ModelException($this->error_message . $meth, 110);
                            }
                        }
                        catch (ModelException $e) {
                            $this->error_message = 'Could not set other prefix as not default.';
                            throw new ModelException($this->error_message . $meth, 110);
                        }
                    }
                    else {
                        $a_values[$key]['tp_default'] = 'false'; // only one can be default.
                    }
                }
            }

        }
        else if (!empty($a_values['tp_default']) && $a_values['tp_default'] === 'true') {
            try {
                if (!$this->updateDefaultPrefixOff()) {
                    $this->error_message = 'Could not set other prefix as not default.';
                    throw new ModelException($this->error_message . $meth, 110);
                }
            }
            catch (ModelException $e) {
                $this->error_message = 'Could not set other prefix as not default.';
                throw new ModelException($this->error_message . $meth, 110);
            }
        }
        return $a_values;
    }

    /**
     * Sets the records that are specified as default to not default.
     *
     * @return bool
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function updateDefaultPrefixOff():bool
    {
        $meth = ' -- ' . __METHOD__;
        $sql = "UPDATE {$this->db_table} SET tp_default = 'false' WHERE tp_default = 'true'";
        try {
            return $this->o_db->update($sql);
        }
        catch (ModelException $e) {
            throw new ModelException($e->errorMessage() . $meth, $e->getCode());
        }
    }
}
