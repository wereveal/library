<?php
/**
 * Class ContentModelTester.
 *
 * @package Ritc_Library
 */
namespace Ritc\Library\Tests;

use Ritc\Library\Models\ContentModel;
use Ritc\Library\Services\DbModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\LogitTraits;
use Ritc\Library\Traits\TesterTraits;

/**
 * Tests the ContentModel class.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version 1.0.0-alpha.1
 * @date    2021-12-01 14:08:32
 * @change_log
 * - 1.0.0-alpha.1 - updated to php 8 standards                 - 2021-12-01 wer
 * - 1.0.0-alpha.0 - Initial version.                           - 2018-07-25 wer
 * @todo ContentModelTester.php - Everything
 */
class ContentModelTester
{
    use LogitTraits;
    use TesterTraits;

    protected ContentModel $o_model;

    /**
     * ContentModelTester constructor.
     *
     * @param Di $o_di
     */
    public function __construct(Di $o_di)
    {
        $a_test_params = [
            'namespace'     => 'Ritc\Library\Models',
            'class_name'    => 'ContentModel',
            'instance_name' => 'o_model'
        ];
        $this->setupTests($a_test_params);
        /** @var DbModel $o_db */
        $o_db = $o_di->get('db');
        $this->o_model = new ContentModel($o_db);
        $this->a_object_names = ['o_model'];
        $this->setupElog($o_di);
    }
}
