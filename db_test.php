<?php

require __DIR__ . '/database/database.php';

$db = new Database();

$connection = $db->open_connection();


$id = $db->get_item_id($connection, 'beer');
echo "id of beer is $id, should be 4\n";
/*
$id = $db->get_item_id($connection, 'notThere');
echo "id of notThere is $id, should be -1\n";


$executed = $db->add_to_items($connection, 'orange');
echo $executed;


$id_amount = $db->check_item_in_list($connection, 1);
echo "expected: id = 1, amount = 3, actual: ";
var_dump($id_amount);

$max = $db->get_max_position_in_list($connection);
echo "max position: $max";

$removed = $db->remove_item_from_list($connection, "banana");
echo "Removed state of banana: $removed";

$result = $db->get_all_items();
var_dump($result);

*/

$connection->close();


$db->update_checked(30, 1);