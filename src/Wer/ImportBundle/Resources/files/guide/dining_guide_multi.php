<?php
date_default_timezone_set("America/Chicago");
$doc_root = __DIR__ . "/test/";

$db = new mysqli("10.177.89.101", "rcreader", "letmoomaxin", "rcreader_www");
if( mysqli_connect_errno() ) {
    printf( "Connect failed %s\n", mysqli_connect_error() );
    exit();
}
$section_sql = "
    SELECT cat.catid, cat.`name`, cat.`description`, cat.introtext, cat.`published`
    FROM jos_sobi2_categories AS cat
    WHERE cat.catid = 59
";
$o_sections = $db->query($section_sql, MYSQLI_USE_RESULT);
if ($o_sections !== false) {
    $a_sections = $o_sections->fetch_all();
    foreach($a_sections as $a_section) {
        $sec_id = $a_section['catid'];
        $o_sec_file = fopen($doc_root . "/section_{$sec_id}.php", "w+");
        $write_this =
<<<EOT
<?php
\$a_sec_{$sec_id}_data = array(
    "sec_name"        => "{$a_sections['name']}",
    "sec_description" => "{$a_sections['introtext']} {$a_sections['description']}",
    "sec_image"       => "",
    "sec_order"       => 1,
    "sec_active"      => 1,
    "sec_old_cat_id"  => {$a_sections['catid']}
);
\$a_sec_{$sec_id}_cat_files = array(
EOT;
        fwrite($o_sec_file, $write_this);
        
        $cat_sql = "
            SELECT cat.`catid` , cat.`name`, cat.`description`, cat.`introtext`, cat.`image`, cat.`ordering`
            FROM jos_sobi2_categories AS cat
            JOIN jos_sobi2_cats_relations AS cr
            WHERE cr.parentid = {$sec_id}
            AND cr.catid = cat.catid
            AND cat.published = 1
            ORDER BY cat.`catid`
        ";        
        $o_cats = $db->query($cat_sql, MYSQLI_USE_RESULT);
        if ($o_cats !== false) {
            $a_cats = $o_cats->fetch_all(MYSQLI_ASSOC);
            foreach ($a_cats as $a_cat) {
                print "c - ";
                $old_cat_id = $a_cat['catid'];
                $cat_file_name = "cat_{$old_cat_id}.php";
                fwrite($o_sec_file, $cat_file_name . ", ");
                $o_cat_file = fopen($doc_root . "/" . $cat_file_name, "w+");
                $cat_name = $a_cat['name'];
                $write_this =
<<<EOT
\$a_cat_{$old_cat_id} = array(
    "cat_name"        => "{$cat_name}",
    "cat_description" => "{$a_cat['introtext']} {$a_cat['description']}",
    "cat_image"       => "{$a_cat['image']}",
    "cat_order"       => "{$a_cat['ordering']}",
    "cat_active"      => 1,
    "cat_old_cat_id"  => "{$old_cat_id}",
);
\$a_cat_{$old_cat_id}_item_files = array(
EOT;
                fwrite($o_cat_file, $write_this);
                $items_sql = "
                    SELECT i.`itemid`, i.`title`, cir.catid AS cat_id
                    FROM `jos_sobi2_item` as i, `jos_sobi2_cat_items_relations` as cir
                    WHERE i.`itemid` = cir.`itemid`
                    AND i.published = 1
                    AND cir.catid = {$old_cat_id}
                    ORDER BY i.title
                ";
                $o_items = $db->query($items_sql, MYSQLI_USE_RESULT);
                if ($o_items !== false) {
                    $a_items = $o_items->fetch_all(MYSQLI_ASSOC);
                    foreach ($a_items as $a_item) {
                        print "i ";
                        $item_id = $a_item['itemid'];
                        $item_name = $a_item['title'];
                        $item_file_name = 'item_{$item_id}.php';
                        fwrite($o_cat_file, $item_file_name . ", ");
                        $o_item_file = fopen("item_{$item_id}.php", 'w+');
                        $write_this =
<<<EOT
$a_item_{$item_id} = array(
    "item_name"   => "{$item_name}",
    "item_active" => 1,
    "item_old_id" => {$item_id}
);
$a_item_{$item_id}_data = array(
EOT;
                        fwrite($o_item_file, $write_this);
                        $fields_sql = "
                            SELECT fieldid, data_txt, itemid as item_id
                            FROM jos_sobi2_fields_data
                            WHERE itemid = {$item_id}
                            ORDER BY fieldid
                        ";
                        $o_item_data = $db->query($fields_sql, MYSQLI_USE_RESULT);
                        if ($o_item_data !== false) {
                            $a_item_data = $o_item_data->fetch_all(MYSQLI_ASSOC);
                            foreach ($a_item_data as $a_data) {
                                $field_id = $a_field['fieldid'];
                                $field_data = $a_field['data_txt'];
                                if (trim($field_data) != '') {
                                    if (substr($field_data, -5, 4) == 'opt_') {
                                        $opt_values_sql = "
                                            SELECT langValue as opt_value
                                            FROM jos_sobi2_language
                                            WHERE langKey LIKE '{$field_data}'
                                        ";
                                        $o_opt_values = $db->query($opt_values_sql, MYSQLI_USE_RESULT);
                                        if ($o_opt_values !== false) {
                                            $a_opt_values = $o_opt_values->fetch_assoc();
                                            $field_data = $a_opt_values['opt_value'];
                                            $o_opt_values->free();
                                        }
                                    }
                                    $field_names_sql = "
                                        SELECT langKey AS field_key, langValue AS field_value
                                        FROM jos_sobi2_language
                                        WHERE fieldid = $field_id
                                    ";
                                    $o_field_names = $db->query($field_names_sql, MYSQLI_USE_RESULT);
                                    if ($o_field_names !== false) {
                                        $a_field_names = $o_field_names->fetch_assoc();
                                        print "f";
                                        $field_key = str_replace('field_', '', $a_field_names['field_key']);
                                        $write_this =
<<<EOT
array(
    'field_key'   => '{$field_key}',
    'field_value' => '{$a_field_names['field_value']}',
    'field_data'  => '{$field_data}',
    'item_id'     => '{$item_id}'
), // field
EOT;
                                        fwrite($o_item_file, $write_this);
                                        $o_field_names->free();
                                    } // if o_field_names
                                } // if field data
                            } // foreach item data
                        } // if o_item_data
                        fwrite($o_item_file, ");\n?>");
                        fclose($o_item_file);
                    } // foreach item
                } // if o_items
                fwrite($o_cat_file, ");\n?>");
                fclose($o_cat_file);
            } // foreach category
        } // if ($o_cats)
        fwrite($o_sec_file, ");\n?>");
        fclose($o_sec_file);
    } // end foreach a_sections
}
$o_sections->free();
?>