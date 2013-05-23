<?php
try {
    $o_db = new PDO('mysql:host=localhost;dbname=mxg', 'mxg', 'letmxgin');
    $sql = "
        SELECT item_id
        FROM wer_item
        ORDER BY item_id
    ";
    $o_db_stmt_1 = $o_db->query($sql);
    $a_item_ids = $o_db_stmt_1->fetchAll(PDO::FETCH_ASSOC);
    print '<pre>';
//    print_r($a_item_ids);
//    print "<br>";
    $a_keys = array_rand($a_item_ids, 10);
    $a_values = array();
    foreach ($a_keys as $key_id) {
        $value = $a_item_ids[$key_id]['item_id'];
        $a_values[] = array(':item_id' => $value);
    }
    print_r($a_values);
    print '<br>';
    /*
    $sql = "
        SELECT i.*, d.*
        FROM wer_item as i, wer_item_data as d
        WHERE i.item_id = :item_id
        AND i.item_id = d.data_item_id
        ORDER BY i.item_name
    ";
    */
    $sql = "
        SELECT item_id, item_name
        FROM wer_item
        WHERE item_id = :item_id
        AND item_active = 1
        ORDER BY item_name
    ";
    $o_db_stmt = $o_db->prepare($sql);

    if($o_db_stmt !== false) {
        foreach ($a_values as $a_value) {
            foreach ($a_value as $key => $value) {
                $o_db_stmt->bindValue($key, $value, PDO::PARAM_INT);
                $o_db_stmt->execute();
                $results = $o_db_stmt->fetchAll(PDO::FETCH_ASSOC);
                print_r($results);
            }
        }
    }

    print '</pre>';

} catch (PDOException $e) {
    print 'Error!: ' . $e->getMessage() . "<br>";
    die();
}
?>
