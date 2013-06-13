<?php
/**
 *  Descriptions.
 *  @file QuickSearch.php
 *  @class QuickSearch
 *  @author William Reveal  <bill@revealitconsulting.com>
 *  @version 0.1
 *  @par Change Log
 *      v0.1 - Initial version
 *  @par Wer GuideBundle version 1.0
 *  @date 2013-06-13 10:12:02
 *  @ingroup guide_bundle
**/
namespace Wer\GuideBundle\Forms\Entity;

class QuickSearch
{
    protected $search;
    protected $searchTerms;
    public function getSearch()
    {
        return $this->search;
    }
    public function getSearchTerms()
    {
        return $this->searchTerms;
    }
    public function setSearch($value)
    {
        $this->search = $value;
    }
    public function setSearchTerms($value)
    {
        $this->searchTerms = $value;
    }
}
