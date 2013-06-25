<?php

namespace Wer\Sobi\Controller;

use Symfony\\Framework\Controller\Controller;
use Wer\Sobi\Helper\OutputHelper;

class ImportController extends Controller
{
    private $o_ohelper;

    public function __construct()
    {
        $this->o_ohelper = new OutputHelper();
    }
    public function indexAction($a_message = '')
    {
        if ($a_message == '') {
            $a_message = $this->o_ohelper->messageProperties('');
        } else {
            $a_message = $this->o_ohelper->messageProperties($a_message['message'], $a_message['type']);
        }
        $a_twig = array(
            'title'=>'Hello Import Controller',
            'stylesheets'=>'',
            'a_message'=>$a_message,
            'body'=>'Hello Import Controller',
            'javascripts'=>''
        );

        return $this->render('WerSobi:Default:import.html.twig', $a_twig);
    }
    public function doitAction()
    {
        $a_message = array('message'=>'', 'type'=>'');
        $results = $this->truncateWerTables();
        if ($results == false) {
            return $this->indexAction(
                array(
                    'message'=>'Could not truncate the database tables.',
                    'type'=>'failure'
                )
            );
        }
        $a_sobi_cats = $this->selectSobiCatsWithParents();
        $a_sections = $this->createSectionsArray($a_sobi_cats);
        if ($this->addWerSection( $a_sections ) === false) {
            return $this->indexAction(
                array(
                    'message'=>'Could not add sections to the the database.',
                    'type'=>'failure'
                )
            );
        }
        $a_sections = $this->selectWerSections();
        foreach($a_sections as $a_section) {
            $a_cats = $this->selectSobiCatsForWerSection($a_section['sec_old_cat_id']);
            if ($a_cats != array()) {
                foreach ($a_cats as $a_category) {
                    $new_cat_id = $this->addWerCategory($a_category);
                    if ($new_cat_id !== false) {
                        $new_section_category_id = $this->addWerSectionCategory(
                            $a_section['sec_id'], $new_cat_id
                        );
                        if ($new_section_category_id === false) {
                            return $this->indexAction(
                                array(
                                    'message'=>'Could not add the section category relations',
                                    'type'=>'failure'
                                )
                            );
                        }
                    } else {
                        return $this->indexAction(
                            array(
                                'message'=>'Could not add the category',
                                'type'=>'failure'
                            )
                        );
                    }
                }
            }

        }
        foreach ($a_sobi_cats as $a_sobi_category) {
            switch ($a_sobi_category['parent_id']) {
                case -1:
                case 0:
                case 1:
                    $results = '';
                    break;
                default:
                    $results = $this->selectWerSection($a_sobi_category['parent_id'], 'old');
            }
            if ($results === false) {
                $new_cat_id = $this->addWerCategory($a_sobi_category);
                if ($new_cat_id !== false) {
                    $a_parent_category = $this->selectWerCategory($a_sobi_category["parent_id"], "old");
                    $new_cat_relation_id = $this->addWerCategoryRelations(
                        $a_parent_category['cat_id'], $new_cat_id
                    );
                /*
                    print "parent_id: "
                        . $a_parent_category['cat_id']
                        . " child id: "
                        . $new_cat_id
                        . " new cat relation id: "
                        . $new_cat_relation_id
                        . "<br />";
               **/
                    if ($new_cat_relation_id === false) {
                        return $this->indexAction(
                            array(
                                'message'=>"Could not add the sub category's parent child relationsip.",
                                'type'=>'failure'
                            )
                        );
                    }
                } else {
                    return $this->indexAction(
                        array(
                            'message'=>'Could not add the sub category: ' . $a_sobi_category['name'],
                            'type'=>'failure'
                        )
                    );
                }
            }
        }
        $a_sobi_items = $this->selectSobiItems();
        $results = $this->addWerObjects($a_sobi_items);
        if ($results === false) {
            return $this->indexAction(
                array(
                    'message'=>'Could not add the objects!',
                    'type'=>'failure'
                )
            );
        }
        $a_wer_objects = $this->selectWerObjects();
        $results = $this->addWerCategoryObjects($a_wer_objects);
        if ($results === false) {
            return $this->indexAction(
                array(
                    'message'=>'Could not add the category object relations!',
                    'type'=>'failure'
                )
            );
        }
        /*
            Now we need to import the sobi_fields_data to the wer_object_data
            This is the big change.
            This assumes that the Fields have been imported already but they only
            need to be done once, while all the rest of this may need multiple
            testings to get right so split is out.
       **/
        foreach ($a_wer_objects as $a_object) {
            print '<pre style="text-align: left">a_object';
            print_r($a_object);
            print '</pre>';

            $a_sobi_item_data = $this->selectSobiItemData($a_object['obj_sobi_id']);
            print '<pre style="text-align: left">a_sobi_item_data';
            print_r($a_sobi_item_data);
            print '</pre>';
            foreach($a_sobi_item_data as $a_data) {
                $a_wer_field_data = $this->selectWerFieldIdFromSobiId($a_data['fieldid']);
                print '<pre style="text-align: left">a_wer_field_data';
                print_r($a_wer_field_data);
                print '</pre>';
                #### NOTE: need to check for a_data[data_txt] substr($a_data['data_txt'], 0, 6) == 'field_'
                #    and if so, look up the value in sobi_language and set the data_txt to that
                ####
                $wer_field_id = $a_wer_field_data['field_id'];
                #### NEED TO SEE if wer_field_id is no empty... problem otherwise, maybe just not save it ####
                print "<p>obj_id: {$a_object['obj_id']}, wer_field_id: {$wer_field_id}, data_txt: {$a_data['data_txt']}</p>";
                $results = $this->addWerObjectData(
                    $a_object['obj_id'],
                    $wer_field_id,
                    $a_data['data_txt']
                );
                if ($results === false) {
                    return $this->indexAction(
                        array(
                            'message'=>'Could not add the object data for object: '
                                . $a_object["obj_id"]
                                . ' '
                                . $a_object['obj_name']
                                . '!',
                            'type'=>'failure'
                        )
                    );
                }
            }
        }

        $a_message['message'] = "It did something... successful??";
        $a_message['type'] = 'info';
        return $this->indexAction($a_message);
    }
    public function fieldsAction()
    {
        $results = $this->truncateWerFieldTables();
        $a_message = array('message'=>'', 'type'=>'');
        $a_sobi_lang = $this->selectSobiLang();
        foreach ($a_sobi_lang as $a_field) {
            $a_field['langKey'] = $this->consolidatedFieldName($a_field);
            $new_field_id = $this->addWerField($a_field);
            if ($new_field_id !== false) {
                $a_opts = $this->selectSobiLangFieldOpt($a_field['fieldid']);
                if ($a_opts != array()) {
                    $more_results = $this->addWerFieldOptions($new_field_id, $a_opts);
                    if ($more_results === false) {
                        break;
                    }
                }
            } else {
                $a_message['message'] = "Could not add field.";
                $a_message['type'] = 'failure';
            }
        }
        return $this->indexAction($a_message);
    }
    ### Misc Methods ####
    private function createSectionsArray($a_old_cats = '')
    {
        if ($a_old_cats == '') { return false; }
        $a_potential_sections = array();
        foreach ($a_old_cats as $category) {
            switch ($category['parent_id']) {
                case -1: // cat not used
                case 0: // cat not used
                    break;
                case 1: // cat is redefined to be a section
                    if ($category['cat_id'] == 1) {
                        break;
                    }
                    $a_potential_sections[] = array(
                        ':sec_name'=>$category['name'],
                        ':sec_description'=>$category['description'],
                        ':sec_active'=>$category['published'],
                        ':sec_old_cat_id'=>$category['cat_id']
                    );
                    break;
                default:
                    // cat is associated with a section... in next step
                    // so do nothing
            }
        }
        // error_log(var_export( $a_potential_sections, TRUE ));
        return $a_potential_sections;
    }
    private function consolidatedFieldName($a_field)
    {
        switch ($a_field['langKey']) {
            case 'field_acceptchecks':
            case 'field_acceptchecks2':
            case 'field_accepts_checks_2':
                return 'field_accepts_checks';
            case 'field_acceptreservations':
            case 'field_accept_reserv_yn':
                return 'field_accept_reservations';
            case 'field_alchoholoptions':
            case 'field_alcoholoptions':
                return 'field_alcohol_options';
            case 'field_attire':
            case 'field_attire_2':
                return 'field_attire';
            case 'field_catering':
            case 'field_catering_2':
                return 'field_catering';
            case 'field_childfriendly':
            case 'field_childfriendly_2':
                return 'field_child_friendly';
            case 'field_delivery':
            case 'field_delivery_2':
                return 'field_delivery';
            case 'field_handicapaccessible':
            case 'field_handicapped_accessible_2':
                return 'field_handicapped_accessible';
            case 'field_outdoorseating':
            case 'field_outdoor_seating_2':
                return 'field_outdoor_seating';
            case 'field_ownership':
            case 'field_ownership2':
            case 'field_ownership_2':
                return 'field_ownership';
            case 'field_privateparties':
            case 'field_private_parties2':
                return 'field_private_parties';
            case 'field_quicklunch':
            case 'field_quick_lunch_2':
                return 'field_quick_lunch';
            case 'field_reservationrequired':
            case 'field_reservation_yesno':
                return 'field_reservation_required';
            case 'field_smokingoptions':
            case 'field_smokingoptions2':
                return 'field_smoking_options';
            case 'field_takeout':
            case 'field_take_out_2':
                return 'field_take_out';
            case 'field_vegetarianentrees':
            case 'field_vegetarian_entrees_2':
                return 'field_vegetarian_entrees';
            case 'field_wi-fi_2':
            case 'field_wifiavailable':
            case 'field_wifi_2':
                return 'field_wifi_available';
            default:
                return $a_field['langKey'];
        }
    }
    private function consolidateFields($a_sobi_fields)
    {
        $a_consolidated_fields = array();
        foreach ($a_sobi_fields as $key=>$a_field) {
            switch ($a_field['langKey']) {
                case 'field_acceptchecks':
                case 'field_acceptchecks2':
                case 'field_accepts_checks_2':
                    $a_consolidated_fields['field_accepts_checks'][] = $a_field['fieldid'];
                    break;
                case 'field_acceptreservations':
                case 'field_accept_reserv_yn':
                    $a_consolidated_fields['field_accept_reservations'][] = $a_field['fieldid'];
                    break;
                case 'field_alchoholoptions':
                case 'field_alcoholoptions':
                    $a_consolidated_fields['field_alcohol_options'][] = $a_field['fieldid'];
                    break;
                case 'field_attire':
                case 'field_attire_2':
                    $a_consolidated_fields['field_attire'][] = $a_field['fieldid'];
                    break;
                case 'field_catering':
                case 'field_catering_2':
                    $a_consolidated_fields['field_catering'][] = $a_field['fieldid'];
                    break;
                case 'field_childfriendly':
                case 'field_childfriendly_2':
                    $a_consolidated_fields['field_child_friendly'][] = $a_field['fieldid'];
                    break;
                case 'field_delivery':
                case 'field_delivery_2':
                    $a_consolidated_fields['field_delivery'][] = $a_field['fieldid'];
                    break;
                case 'field_handicapaccessible':
                case 'field_handicapped_accessible_2':
                    $a_consolidated_fields['field_handicapped_accessible'][] = $a_field['fieldid'];
                    break;
                case 'field_outdoorseating':
                case 'field_outdoor_seating_2':
                    $a_consolidated_fields['field_outdoor_seating'][] = $a_field['fieldid'];
                    break;
                case 'field_ownership':
                case 'field_ownership2':
                case 'field_ownership_2':
                    $a_consolidated_fields['field_ownership'][] = $a_field['fieldid'];
                    break;
                case 'field_privateparties':
                case 'field_private_parties2':
                    $a_consolidated_fields['field_private_parties'][] = $a_field['fieldid'];
                    break;
                case 'field_quicklunch':
                case 'field_quick_lunch_2':
                    $a_consolidated_fields['field_quick_lunch'][] = $a_field['fieldid'];
                    break;
                case 'field_reservationrequired':
                case 'field_reservation_yesno':
                    $a_consolidated_fields['field_reservation_required'][] = $a_field['fieldid'];
                    break;
                case 'field_smokingoptions':
                case 'field_smokingoptions2':
                    $a_consolidated_fields['field_smoking_options'][] = $a_field['fieldid'];
                    break;
                case 'field_takeout':
                case 'field_take_out_2':
                    $a_consolidated_fields['field_take_out'][] = $a_field['fieldid'];
                    break;
                case 'field_vegetarianentrees':
                case 'field_vegetarian_entrees_2':
                    $a_consolidated_fields['field_vegetarian_entrees'][] = $a_field['fieldid'];
                    break;
                case 'field_wi-fi_2':
                case 'field_wifiavailable':
                case 'field_wifi_2':
                    $a_consolidated_fields['field_wifi_available'][] = $a_field['fieldid'];
                    break;
                default:
                    // do nothing
            }
        }
        return $a_consolidated_fields;
    }

    ### Database Operations ###
    private function addWerCategory($a_values = '')
    {
        if ($a_values == '') { return false; }
        $cat_sql = "
            INSERT INTO wer_category
                ( cat_name, cat_description, cat_image, cat_order, cat_active,
                  cat_old_cat_id
                )
            VALUES
                ( :cat_name, :cat_description, :cat_image, :cat_order,
                  :cat_active, :cat_old_cat_id
                )
        ";
        $conn = $this->get('database_connection');
        $conn->beginTransaction();
        try {
            $cat_stmt = $conn->prepare($cat_sql);
            $cat_stmt->bindValue(':cat_name', $a_values['name']);
            $cat_stmt->bindValue(':cat_description', $a_values['description']);
            $cat_stmt->bindValue(':cat_image', $a_values['image']);
            $cat_stmt->bindValue(':cat_order', $a_values['ordering']);
            $cat_stmt->bindValue(':cat_active', $a_values['published']);
            $cat_stmt->bindValue(':cat_old_cat_id', $a_values['cat_id']);
            $cat_stmt->execute();
            $stmt = $conn->query('SELECT LAST_INSERT_ID() FROM wer_category');
            $a_value = $stmt->fetch();
            $last_id = $a_value[0];
            $conn->commit();
            return $last_id;
        } catch (Exception $e) {
            $conn->rollback();
            error_log($e);
            return false;
        }

    }
    private function addWerCategoryObjects($a_objects = '')
    {
        if ($a_objects == '') { return false; }
        $get_sobi_cat_item = "
            SELECT catid, itemid, ordering
            FROM sobi_cat_items_relations
            WHERE itemid = :sobi_id
            ORDER BY catid
        ";
        $get_cat_id = "
            SELECT cat_id
            FROM wer_category
            WHERE cat_old_cat_id = :catid
        ";
        $insert_category_object = "
            INSERT INTO wer_category_object (co_category_id, co_object_id, co_order)
            VALUES (:co_category_id, :co_object_id, :co_order)
        ";
        $o_conn = $this->get('database_connection');
        $o_conn->beginTransaction();
        try {
            $sobi_cat_stmt   = $o_conn->prepare($get_sobi_cat_item);
            $wer_cat_id_stmt = $o_conn->prepare($get_cat_id);
            $insert_stmt     = $o_conn->prepare($insert_category_object);
            foreach ($a_objects as $a_object) {
                $obj_id = $a_object['obj_id'];
                $sobi_id = $a_object['obj_sobi_id'];
                $sobi_cat_stmt->bindValue(':sobi_id', $sobi_id);
                $sobi_cat_stmt->execute();
                $a_found1 = $sobi_cat_stmt->fetchAll();
                foreach ($a_found1 as $a_found) {
                    $found_item_id = $a_found['itemid'];
                    $found_cat_id = $a_found['catid'];
                    $found_order = $a_found['ordering'];
                    $wer_cat_id_stmt->bindValue(':catid', $found_cat_id);
                    $wer_cat_id_stmt->execute();
                    $a_found2 = $wer_cat_id_stmt->fetch();
                    if ($a_found2 !== null && $a_found2 != '') {
                        $wer_cat_id = $a_found2['cat_id'];
                        $insert_stmt->bindValue(':co_category_id', $wer_cat_id);
                        $insert_stmt->bindValue(':co_object_id', $obj_id);
                        $insert_stmt->bindValue(':co_order', $found_order);
                        $insert_stmt->execute();
                    }
                }
            }
            $o_conn->commit();
            return true;
        } catch (Exception $e) {
            $o_conn->rollback();
            error_log('from ' . __METHOD__ . '  ' . $e);
            return false;
        }
        return false;
    }
    private function addWerCategoryRelations($parent_id = '', $child_id = '')
    {
        if ($parent_id == '' || $child_id == '') {
            return false;
        }
        $sql = "
            INSERT INTO wer_category_relations (cr_parent_id, cr_child_id)
            VALUES (:cr_parent_id, :cr_child_id)
        ";
        $conn = $this->get('database_connection');
        $conn->beginTransaction();
        try {
            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':cr_parent_id', $parent_id);
            $stmt->bindValue(':cr_child_id', $child_id);
            $stmt->execute();
            $stmt = $conn->query('SELECT LAST_INSERT_ID() FROM wer_category_relations');
            $a_value = $stmt->fetch();
            $last_id = $a_value[0];
            $conn->commit();
            return $last_id;
        } catch (Exception $e) {
            $conn->rollback();
            error_log("Error message from " . __METHOD__ . " " . $e);
            return false;
        }
    }
    private function addWerCatsWithParents($a_wer_sections = '', $a_sobi_cats = '')
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
        } catch(Exception $e) {
            $conn->rollback();
            throw $e;
        }
    }
    private function addWerField($a_field = '')
    {
        if ($a_field == '') { return false; }
        $field_type_id = $a_field['fieldType'] == null
            ? ''
            : $a_field['fieldType'];
        $field_name = $a_field['langKey'] == null
            ? ''
            : str_replace('field_', '', $a_field['langKey']);
        $field_short_desc = $a_field['langValue'] == null
            ? ''
            : $a_field['langValue'];
        $field_description = $a_field['description'] == null
            ? ''
            : $a_field['description'];
        $field_enabled = $a_field['enabled'] == null
            ? ''
            : $a_field['enabled'];
        $field_id = $a_field['fieldid'] == null
            ? ''
            : $a_field['fieldid'];
        $sql = "
            INSERT INTO wer_field ( field_type_id, field_name,
                field_short_description, field_description, field_enabled,
                field_old_field_id )
            VALUES ( :field_type_id, :field_name,
                :field_short_description, :field_description, :field_enabled,
                :field_old_field_id )
        ";
        $conn = $this->get('database_connection');
        $conn->beginTransaction();
        try {
            $o_stmt = $conn->prepare($sql);
            $o_stmt->bindValue(':field_type_id', $field_type_id);
            $o_stmt->bindValue(':field_name', $field_name);
            $o_stmt->bindValue(':field_short_description', $field_short_desc);
            $o_stmt->bindValue(':field_description', $field_description);
            $o_stmt->bindValue(':field_enabled', $field_enabled);
            $o_stmt->bindValue(':field_old_field_id', $field_id);
            $o_stmt->execute();
            $stmt = $conn->query('SELECT LAST_INSERT_ID() FROM wer_field');
            $a_value = $stmt->fetch();
            $last_id = $a_value[0];
            $conn->commit();
            return $last_id;
        } catch (Exception $e) {
            $conn->rollback();
            error_log(__METHOD__ . ': ' . $e);
            return false;
        }

    }
    private function addWerFieldOptions($new_field_id = '', $a_opts = '')
    {
        if ($new_field_id == '' || $a_opts == '') {
            return false;
        }
        $sql = "
            INSERT INTO wer_field_option (fo_field_id, fo_field_option)
            VALUES (:fo_field_id, :fo_field_option)";

        $conn = $this->get('database_connection');
        $conn->beginTransaction();
        try {
            $o_stmt = $conn->prepare($sql);
            foreach ($a_opts as $a_option) {
                $o_stmt->bindValue(':fo_field_id', $new_field_id);
                $o_stmt->bindValue(':fo_field_option', $a_option['langValue']);
                $o_stmt->execute();
            }
            $conn->commit();
        } catch (Exception $e) {
            $conn->rollback();
            error_log('from ' . __METHOD__ . '  ' . $e);
            return false;
        }
        return true;
    }
    private function addWerObject($a_item = '')
    {
        if ($a_item == '') { return false; }
        if (!isset( $a_item['sobi_id'] )) {
            $a_item['sobi_id'] = 0;
        }
        $sql = "
            INSERT INTO wer_object (obj_name, obj_updated_on, obj_active, obj_sobi_id)
            VALUES (:obj_name, :obj_updated_on, :obj_active, :obj_sobi_id)";
        $conn = $this->get('database_connection');
        $conn->beginTransaction();
        try {
            $o_stmt = $conn->prepare($sql);
            $o_stmt->bindValue(':obj_name', $a_item['name']);
            $o_stmt->bindValue(':obj_updated_on', date( 'Y-m-d h:i:s' ));
            $o_stmt->bindValue(':obj_active', $a_item['active']);
            $o_stmt->bindValue(':obj_sobi_id', $a_item['sobi_id']);
            $o_stmt->execute();
            $stmt = $conn->query('SELECT LAST_INSERT_ID() FROM wer_object');
            $a_value = $stmt->fetch();
            $last_id = $a_value[0];
            $conn->commit();
            return $last_id;
        } catch (Exception $e) {
            $conn->rollback();
            error_log($e);
            return false;
        }

    }
    private function addWerObjectData($a_object_id = '', $a_field_id = '', $a_object_data = '')
    {
        if ($a_object_id == '' || $a_field_id == '') {
            return false;
        }
        $sql = "
            INSERT INTO wer_object_data (od_field_id, od_object_id, od_data, od_updated_on)
            VALUES (:od_field_id, :od_object_id, :od_data, :od_updated_on)";
        $conn = $this->get('database_connection');
        $conn->beginTransaction();
        try {
            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':od_field_id', $a_field_id);
            $stmt->bindValue(':od_object_id', $a_object_id);
            $stmt->bindValue(':od_data', $a_object_data);
            $stmt->bindValue(':od_updated_on', date( 'Y-m-d H:i:s' ));
            $stmt->execute();
            $conn->commit();
        } catch(Exception $e) {
            $conn->rollback();
            throw $e;
        }
    }
    private function addWerObjects($a_sobi_items = '')
    {
        if ($a_sobi_items == '') { return false; }
        $sql = "
            INSERT INTO wer_object (obj_name, obj_updated_on, obj_active, obj_sobi_id)
            VALUES (:obj_name, :obj_updated_on, :obj_active, :obj_sobi_id)";
        $conn = $this->get('database_connection');
        $conn->beginTransaction();
        try {
            $o_stmt = $conn->prepare($sql);
            foreach ($a_sobi_items as $a_item) {
                $o_stmt->bindValue(':obj_name', $a_item['title']);
                $o_stmt->bindValue(':obj_updated_on', date( 'Y-m-d h:i:s' ));
                $o_stmt->bindValue(':obj_active', $a_item['published']);
                $o_stmt->bindValue(':obj_sobi_id', $a_item['itemid']);
                $o_stmt->execute();
            }
            $conn->commit();
        } catch (Exception $e) {
            $conn->rollback();
            error_log('from ' . __METHOD__ . '  ' . $e);
            return false;
        }
        return true;
    }
    private function addWerSection($a_sections = '')
    {
        $sql = "
            INSERT INTO wer_section (sec_name, sec_description, sec_active, sec_old_cat_id)
            VALUES (:sec_name, :sec_description, :sec_active, :sec_old_cat_id)";
        $conn = $this->get('database_connection');
        $conn->beginTransaction();
        try {
            $o_stmt = $conn->prepare($sql);
            foreach ($a_sections as $a_section) {
                foreach ($a_section as $key => $value) {
                    $o_stmt->bindValue($key, $value);
                }
                $o_stmt->execute();
            }
            $conn->commit();
            return true;
        } catch (Exception $e) {
            $conn->rollback();
            error_log($e);
            return false;
        }
    }
    private function addWerSectionCategory($section_id = '', $category_id = '')
    {
        if ($section_id == '' || $category_id == '') {
            return false;
        }
        $sql = "
            INSERT INTO wer_section_category (sc_section_id, sc_category_id)
            VALUES (:sc_section_id, :sc_category_id)";
        $conn = $this->get('database_connection');
        $conn->beginTransaction();
        try {
            $o_stmt = $conn->prepare($sql);
            $o_stmt->bindValue(':sc_section_id', $section_id);
            $o_stmt->bindValue(':sc_category_id', $category_id);
            $o_stmt->execute();
            $stmt = $conn->query('SELECT LAST_INSERT_ID() FROM wer_section_category');
            $a_value = $stmt->fetch();
            $last_id = $a_value[0];
            $conn->commit();
            return $last_id;
        } catch (Exception $e) {
            $conn->rollback();
            error_log($e);
            return false;
        }
    }
    private function selectSobiCatsForWerSection($section_id = '')
    {
        if ($section_id == '') { return false; }
        $conn = $this->get('database_connection');
        $sql = "
            SELECT cr.catid AS child_id, cr.parentid AS parent_id, c.catid AS cat_id,
                c.name, c.image, c.image_position, c.description,
                c.introtext, c.published, c.checked_out, c.checked_out_time, c.ordering,
                c.access, c.count, c.params, c.icon
            FROM sobi_cats_relations AS cr
            JOIN sobi_categories AS c
            WHERE cr.parentid = {$section_id}
            AND cr.catid = c.catid
            ORDER BY cr.parentid, c.name";
        return $conn->fetchAll($sql);
    }
    private function selectSobiCatsWithParents()
    {
        $conn = $this->get('database_connection');
        $sql = '
            SELECT cr.catid AS child_id, cr.parentid AS parent_id, c.catid AS cat_id,
                c.name, c.image, c.image_position, c.description,
                c.introtext, c.published, c.checked_out, c.checked_out_time, c.ordering,
                c.access, c.count, c.params, c.icon
            FROM sobi_cats_relations AS cr
            JOIN sobi_categories AS c
            WHERE cr.catid = c.catid
            ORDER BY cr.parentid, c.name';
        return $conn->fetchAll($sql);
    }
    private function selectSobiItemData($sobi_item_id = '')
    {
        if ($sobi_item_id == '') { return false; }
        $sql = "
            SELECT fieldid, data_txt, itemid
            FROM sobi_fields_data
            WHERE itemid = {$sobi_item_id}";
        $conn = $this->get('database_connection');
        return $conn->fetchAll($sql);
    }
    private function selectSobiItems()
    {
        $sql = "SELECT itemid, title, published FROM sobi_item ORDER BY itemid";
        $conn = $this->get('database_connection');
        return $conn->fetchAll($sql);
    }
    private function selectSobiLang()
    {
        $sql = "
            SELECT sl.fieldid, sl.langKey, sl.langValue, sl.description,
                   sf.enabled, sf.fieldType, sf.CSSclass
            FROM sobi_language AS sl
            JOIN sobi_fields as sf
            WHERE sl.sobi2Section = 'fields'
            AND sl.fieldid = sf.fieldid
            AND sf.enabled = 1
            ORDER BY sl.langKey
        ";
        $conn = $this->get('database_connection');
        return $conn->fetchAll($sql);
    }
    private function selectSobiLangFieldOpt($field_id)
    {
        $sql = "
            SELECT fieldid, langKey, langValue, description
            FROM sobi_language
            WHERE sobi2Section = 'field_opt'
            AND fieldid = {$field_id}
            ORDER BY langKey
        ";
        $conn = $this->get('database_connection');
        return $conn->fetchAll($sql);
    }
    private function selectSectionForCategory($parent_id)
    {
        $sql = "SELECT sec_id FROM wer_section WHERE sec_old_cat_id = :parent_id";
        $conn = $this->get('database_connection');
        try {
            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':parent_id', $parent_id);
            $stmt->execute();
        } catch (Exception $e) {
            error_log( $e);
            return false;
        }
        $a_section = $conn->fetchAssoc(); // change to find first record
        return $a_section['sec_id'];
    }
    private function selectWerCategory($id = '', $type = '')
    {
        if ($id == '') { return false; }
        switch($type) {
            case 'old':
                $where = "WHERE cat_old_cat_id = {$id}";
                break;
            case 'name':
                $where = "WHERE cat_name LIKE '%{$id}%'";
                break;
            case 'new':
            default:
                $where = "WHERE cat_id = {$id}";
        }
        $conn = $this->get('database_connection');
        $sql = "
            SELECT cat_id, cat_name, cat_description, cat_image, cat_order,
                cat_active, cat_old_cat_id
            FROM wer_category
            {$where}
        ";
        return $conn->fetchAssoc($sql);
    }
    private function selectWerCats()
    {
        $conn = $this->get('database_connection');
        $sql = '
            SELECT cat_id, cat_name, cat_description, cat_image, cat_order, cat_active
            FROM wer_category
            ORDER BY cat_name';
        return $conn->fetchAll($sql);
    }
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
    private function selectWerFieldIdFromSobiId($a_sobi_id = '')
    {
        if ($a_sobi_id == '') { return false; }
        $sql = "
            SELECT field_id
            FROM wer_field
            WHERE field_old_field_id = {$a_sobi_id}";
        $conn = $this->get('database_connection');
        return $conn->fetchAssoc($sql);
    }
    private function selectWerObjects()
    {
        $sql = "SELECT * FROM wer_object ORDER BY obj_id";
        $conn = $this->get('database_connection');
        return $conn->fetchAll($sql);
    }
    private function selectWerSection($id = '', $id_type = '')
    {
        if ($id == '') { return false; }
        switch ($id_type) {
            case 'old':
                $where = "WHERE sec_old_cat_id = {$id}";
                break;
            case 'name':
                $where = "WHERE sec_name LIKE '%{$id}%'";
                break;
            case 'new':
            default:
                $where = "WHERE sec_id = {$id}";
        }
        $conn = $this->get('database_connection');
        $sql = "
            SELECT sec_id, sec_name, sec_description, sec_active, sec_old_cat_id
            FROM wer_section
            {$where}
            ORDER BY sec_name";
        $results = $conn->fetchAssoc($sql);
        if ($results == '' || $results === null) {
            return false;
        } else {
            return $results;
        }
    }
    private function selectWerSections()
    {
        $conn = $this->get('database_connection');
        $sql = '
            SELECT sec_id, sec_name, sec_description, sec_active, sec_old_cat_id
            FROM wer_section
            ORDER BY sec_name';
        return $conn->fetchAll($sql);
    }
    private function truncateAllWerTables()
    {
        $sql1 = 'TRUNCATE TABLE wer_section';
        $sql2 = 'TRUNCATE TABLE wer_section_category';
        $sql3 = 'TRUNCATE TABLE wer_category';
        $sql4 = 'TRUNCATE TABLE wer_category_relations';
        $sql5 = 'TRUNCATE TABLE wer_field';
        $sql6 = 'TRUNCATE TABLE wer_field_option';
        $sql7 = 'TRUNCATE TABLE wer_object_data';
        $sql8 = 'TRUNCATE TABLE wer_object';
        $sql9 = 'TRUNCATE TABLE wer_category_object';

        $conn = $this->get('database_connection');
        $conn->beginTransaction();
        try {
            for ($i = 1; $i <= 9 ; $i++) {
                $sql = 'sql' . $i;
                $conn->query($$sql);
            }
            $conn->commit();
            return TRUE;
        } catch(Exception $e) {
            $conn->rollback();
            error_log($e);
            return FALSE;
        }
        return FALSE;
    }
    private function truncateWerTables()
    {
        $sql1 = 'TRUNCATE TABLE wer_section';
        $sql2 = 'TRUNCATE TABLE wer_section_category';
        $sql3 = 'TRUNCATE TABLE wer_category';
        $sql4 = 'TRUNCATE TABLE wer_category_relations';
        $sql5 = 'TRUNCATE TABLE wer_object_data';
        $sql6 = 'TRUNCATE TABLE wer_object';
        $sql7 = 'TRUNCATE TABLE wer_category_object';

        $conn = $this->get('database_connection');
        $conn->beginTransaction();
        try {
            for ($i = 1; $i <= 7 ; $i++) {
                $sql = 'sql' . $i;
                $conn->query($$sql);
            }
            $conn->commit();
            return TRUE;
        } catch(Exception $e) {
            $conn->rollback();
            error_log($e);
            return FALSE;
        }
        return FALSE;
    }
    private function truncateWerFieldTables()
    {
        $sql1 = 'TRUNCATE TABLE wer_field';
        $sql2 = 'TRUNCATE TABLE wer_field_option';
        $conn = $this->get('database_connection');
        $conn->beginTransaction();
        try {
            for ($i = 1; $i <= 2 ; $i++) {
                $sql = 'sql' . $i;
                $conn->query($$sql);
            }
            $conn->commit();
            return TRUE;
        } catch(Exception $e) {
            $conn->rollback();
            error_log($e);
            return FALSE;
        }
        return FALSE;

    }
}
