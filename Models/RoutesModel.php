<?php
/**
 * Class RoutesModel
 * @package Ritc_Library
 */
namespace Ritc\Library\Models;

use Ritc\Library\Abstracts\ModelAbstract;
use Ritc\Library\Services\DbModel;
use Ritc\Library\Traits\DbUtilityTraits;
use Ritc\Library\Traits\LogitTraits;

/**
 * Does all the Model expected operations, database CRUD and business logic.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v3.0.0
 * @date    2018-06-12 08:22:10
 * @change_log
 * - v3.0.0   - Refactored to use ModelAbstract                         - 2018-06-12 wer
 * - v2.0.1   - ModelException changes reflected here                   - 2017-12-12 wer
 * - v2.0.0   - Refactored to use ModelException and moved a couple     - 2017-06-18 wer
 *              methods to RoutesComplexModel.
 * - v1.5.0   - DbUtilityTraits change reflected here                   - 2017-05-09 wer
 * - v1.4.0   - Refactored readWithRequestUri to readByRequestUri       - 2016-04-10 wer
 *              Added readWithUrl to return list of routes with url.
 * - v1.3.0   - updated to use more of the DbUtilityTraits              - 2016-04-01 wer
 * - v1.2.0   - Database structure change reflected here.               - 2016-03-11 wer
 *              Required new method to duplicate old functionality.
 * - v1.1.0   - refactoring to provide better postgresql compatibility  - 11/22/2015 wer
 * - v1.0.2   - Database structure change reflected here.               - 09/03/2015 wer
 * - v1.0.1   - Refactoring elsewhere necessitated changes here         - 07/31/2015 wer
 * - v1.0.0   - first working version                                   - 01/28/2015 wer
 * - v1.0.0β2 - Changed to match some namespace changes, and bug fix    - 11/15/2014 wer
 * - v1.0.0β1 - First live version                                      - 11/11/2014 wer
 */
class RoutesModel extends ModelAbstract
{
    use LogitTraits, DbUtilityTraits;

    /**
     * RoutesModel constructor.
     * @param \Ritc\Library\Services\DbModel $o_db
     */
    public function __construct(DbModel $o_db)
    {
        $this->setupProperties($o_db, 'routes');
        $this->setRequiredKeys(['url_id', 'route_class', 'route_method']);
    }

    /**
     * Methods in ModelAbstract
     *
     * create(array $a_values = [])
     * read(array $a_search_for = [], array $a_search_params = [])
     * update(array $a_values = [], $immutable_field = '', array $a_do_not_change = [])
     * delete($id = -1)
     */
}
