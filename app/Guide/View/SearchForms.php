<?php
/**
 *  Creates forms for the Guide.
 *  @file SearchForms.php
 *  @class SearchForms
 *  @author William Reveal  <bill@revealitconsulting.com>
 *  @version 0.1
 *  @par Change Log
 *      v0.1 - Initial version 2012-06-13
 *  @par Wer Guide version 1.0
 *  @date 2013-06-13 10:48:12
 *  @ingroup guide
**/

namespace Wer\Guide\View;

use Wer\Guide\Model\Entity\QuickSearchEntity;

class SearchForms
{
    public function __construct()
    {
    }
    /**
     *  Returns a SF rendered form.
     *  @param Request $request
     *  @return object
    **/
    public function quickSearch(Request $request)
    {
        $o_qsentity = new QuickSearchEntity();
        $o_qsentity->setSearchTerms('');
        $o_qsentity->setSearch('Search');
        $a_options = array();
    }
}
