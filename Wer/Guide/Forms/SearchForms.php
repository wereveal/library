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
 *  @ingroup guide_
**/

namespace Wer\Guide\Forms;

use Wer\Guide\Forms\Entity\QuickSearch as QuickSearchEntity;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Request;

class SearchForms
{
    protected $o_factory;
    public function __construct()
    {
        $this->o_factory = new FormFactory;
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
        $o_form = $this->o_factory->createBuilder('form', $o_qsentity, $a_options)
            ->add('searchTerms', 'text')
            ->add('search', 'button')
            ->getForm();
        return $o_form->createView();
    }
}
