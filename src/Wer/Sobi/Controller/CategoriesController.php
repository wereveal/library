<?php

namespace Wer\SobiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Wer\SobiBundle\Entity\SobiCategories;
use Wer\SobiBundle\Entity\SobiCatsRelations;
use Wer\SobiBundle\Entity\WerCategory;

class CategoriesController extends Controller
{
    /**
     * List the Categories in Sobi
     * @return Render object
    **/
    public function indexAction()
    {
        $a_twig = array(
            'title'=>'Hello Categories Controller',
            'stylesheets'=>'',
            'body'=>'Hello Categories Controller',
            'javascripts'=>''
        );
        /*
        $o_repository = $this->getDoctrine()
            ->getRepository("WerSobiBundle:SobiCategories");
        $a_categories = $o_repository->findAll();
        */
        $a_categories = $this->selectSobiCatsWithParents();
        $a_twig['categories'] = $a_categories;
        return $this->render('WerSobiBundle:Default:categories.html.twig', $a_twig);
    }
    /**
     * Import the Categories from Sobi tables into Guide tables
     * @return Render object
    **/
    public function importAction()
    {
        $a_categories = $this->selectSobiCatsWithParents();
        $this->truncateWerCategory();
        $this->truncateWerCategoryRelations();
        $results = $this->addWerCatsWithParents($a_categories);
        return $this->listAction();
    }
    /**
     * List the Categories imported into the Guide tables
     * @return Render object
    **/
    public function listAction()
    {
        $a_twig = array(
            'title'=>'List Imported Categories',
            'stylesheets'=>'',
            'body'=>'List Imported Categories',
            'javascripts'=>''
        );
        $a_twig['categories'] = $this->selectWerCatsWithParents();
        return $this->render('WerSobiBundle:Default:categories.html.twig', $a_twig);
    }

    ### Database Operations ###
    /**
     * Insert into db tables wer_category and wer_category_relations
     * based on data passed in from the db tables sobi_categories and
     * sobi_cats_relations
     * @return bool success or failure
    **/
    private function addWerCatsWithParents($a_sobi_cats = '')
    {
        if ($a_sobi_cats == '') { return FALSE; }
        $cat_sql = "
            INSERT INTO wer_category
                (cat_id, cat_name, cat_description, cat_image, cat_order, cat_active)
            VALUES
                (:cat_id, :cat_name, :cat_description, :cat_image, :cat_order, :cat_active)";
        $relations_sql = "
            INSERT INTO wer_category_relations (cr_parent_id, cr_child_id)
            VALUES (:parent_id, :child_id)";
        $a_wer_cat_params = array();
        $a_wer_cat_relations_params = array();
        $conn = $this->get('database_connection');
        $conn->beginTransaction();
        try {
            $cat_stmt = $conn->prepare($cat_sql);
            $rel_stmt = $conn->prepare($relations_sql);
            foreach ($a_sobi_cats as $a_category) {
                $cat_stmt->bindValue(
                    ':cat_id',
                    $a_category['catid'] );
                $cat_stmt->bindValue(
                    ':cat_name',
                    $a_category['name'] == NULL
                        ? ''
                        : $a_category['name'] );
                $cat_stmt->bindValue(
                    ':cat_description',
                    $a_category['description'] == NULL
                        ? ''
                        : $a_category['description'] );
                $cat_stmt->bindValue(
                    ':cat_image',
                    $a_category['image'] == NULL
                        ? ''
                        : $a_category['image'] );
                $cat_stmt->bindValue(
                    ':cat_order',
                    $a_category['ordering'] == NULL
                        ? ''
                        : $a_category['ordering'] );
                $cat_stmt->bindValue(
                    ':cat_active',
                    $a_category['published'] == NULL
                        ? ''
                        : $a_category['published'] );
                $rel_stmt->bindValue(
                    ':parent_id',
                    $a_category['parentid'] );
                $rel_stmt->bindValue(
                    ':child_id',
                    $a_category['catid'] );
                $cat_stmt->execute();
                $rel_stmt->execute();
            }
            $conn->commit();
        }
        catch(Exception $e) {
            $conn->rollback();
            throw $e;
        }
    }
    /**
     * Get all the rows of sobi_categories db table
     * Joined to sobi_cats_relations to get the parent id for each cat row
     * @return array rows of categories
    **/
    private function selectSobiCatsWithParents()
    {
        $conn = $this->get('database_connection');
        $sql = '
            SELECT cr.catid AS child_id, cr.parentid AS parent_id, c.name, c.image, c.image_position, c.description,
                c.introtext, c.published, c.checked_out, c.checked_out_time, c.ordering,
                c.access, c.count, c.params, c.icon
            FROM sobi_cats_relations AS cr
            JOIN sobi_categories AS c
            WHERE cr.catid = c.catid
            ORDER BY cr.parentid, c.name';
        return $conn->fetchAll($sql);
    }
    /**
     * Get all the rows of wer_category db table
     * @return array rows of categories
    **/
    private function selectWerCats()
    {
        $conn = $this->get('database_connection');
        $sql = '
            SELECT cat_id, cat_name, cat_description, cat_image, cat_order, cat_active
            FROM wer_category
            ORDER BY cat_name';
        return $conn->fetchAll($sql);
    }
    /**
     * Get all the rows of wer_category db table
     * @return array rows of categories
    **/
    private function selectWerCatsWithParents()
    {
        $conn = $this->get('database_connection');
        $sql = '
            SELECT wcr.cr_parent_id AS parent_id, wcr.cr_child_id as child_id,
                wc.cat_id, wc.cat_name AS name, wc.cat_description, wc.cat_image,
                wc.cat_order, wc.cat_active
            FROM wer_category_relations AS wcr
            JOIN wer_category AS wc
            WHERE wcr.cr_child_id = wc.cat_id
            ORDER BY wcr.cr_parent_id, wc.cat_name';
        return $conn->fetchAll($sql);
    }
    /**
     * Empties the wer_category table of data
    **/
    private function truncateWerCategory()
    {
        $conn = $this->get('database_connection');
        $sql = 'TRUNCATE TABLE wer_category';
        $conn->query($sql);
    }
    /**
     * Empties the wer_category_relations table
    **/
    private function truncateWerCategoryRelations()
    {
        $conn = $this->get('database_connection');
        $sql = 'TRUNCATE TABLE wer_category_relations';
        $conn->query($sql);
    }
}
