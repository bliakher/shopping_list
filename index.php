<?php

require __DIR__ . '/database/database.php';

function http_bad_request($message) {
    http_response_code(400);
    echo $message;
    die();
}

function is_word($string) {
    $pattern = "/^[a-zA-Z ]+$/";
    return preg_match($pattern, $string);
}

function add_item($database) {

    if(!isset($_POST['item']) or !isset($_POST['amount'])) {
        http_bad_request("Incorrect query. Parameters missing.");
    }
    if (!is_word($_POST['item']) or !is_numeric($_POST['amount'])) {
        http_bad_request("Incorrect query. Parameters in incorrect format.");
    }
    $item = $_POST['item'];
    $amount = intval($_POST['amount']);

    $database->add_item($item, $amount);
}

function remove_item($database) {
    if (!isset($_POST['item'])) {
        http_bad_request("Incorrect query. Parameter item missing.");
    }
    if (!is_numeric($_POST['item'])) {
        http_bad_request("Incorrect query. Parameter item in incorrect format.");
    }
    $itemId = $_POST['item'];
    $removed = $database->remove_item_from_list($itemId);
    return $removed;
}

function update_amount($database) {
    if (!isset($_POST['item']) or !isset($_POST['amount'])) {
        http_bad_request("Incorrect query. Parameters missing.");
    }
    if (!is_numeric($_POST['item']) or !is_numeric($_POST['amount'])) {
        http_bad_request("Incorrect query. Parameters in incorrect format.");
    }
    $item_id = intval($_POST['item']);
    $amount = intval($_POST['amount']);
    $updated = $database->update_amount($item_id, $amount);
    return $updated;
}

function switch_positions($database) {
    if (!isset($_POST['item1']) or !isset($_POST['item2'])) {
        http_bad_request("Incorrect query. Parameters missing.");
    }
    if (!is_numeric($_POST['item1']) or !is_numeric($_POST['item2'])) {
        http_bad_request("Incorrect query. Parameters in incorrect format.");
    }
    $item1 = $_POST['item1'];
    $item2 = $_POST['item2'];
    $switched = $database->switch_positions($item1, $item2);
    return $switched;
}

function update_checked($database) {
    if (!isset($_POST['item']) or !isset($_POST['checked'])) {
        http_bad_request("Incorrect query. Parameters missing.");
    }
    if(is_numeric($_POST['item']) and ($_POST['checked'] === "true" or $_POST['checked'] === "false")) {
        $item_id = intval($_POST['item']);
        $checked = $_POST['checked'] === "true" ? true : false;
        $updated = $database->update_checked($item_id, $checked);
        return $updated;
    }
    http_bad_request("Incorrect query. Parameters in incorrect format.");
}

function get_data_json($database) {
    if (isset($_GET['items']) and $_GET['items'] === 'all') {
        $items = $database->get_all_items();
        $response = $items !== null ? array('ok' => true, 'items' => $items) : array('ok' => false);
        $response_json = json_encode($response);
        return $response_json;
    }
    if (isset($_GET['list']) and $_GET['list'] === 'all') {
        $list = $database->get_list();
        $response = count($list) !== 0 ? array('ok' => true, 'list' => $list) : array('ok' => false);
        $response_json = json_encode($response);
        return $response_json;
    }
    return null;
}

function encode_result($result) {
    $response = array('ok' => $result);
    $response_json = json_encode($response);
    return $response_json;
}

$method = $_SERVER['REQUEST_METHOD'];
$db = new Database();

if ($method === 'GET') {

    if(isset($_GET['format'])) {
        $format = $_GET['format'];
        if ($format === 'json') {
            $json_data = get_data_json($db);
            echo $json_data;
            exit;
        }
    }

    $table_data = $db->get_list();

    require __DIR__ . '/html/page.php';
}

elseif ($method === 'POST') {

    if (!isset($_POST['action'])) {
        http_bad_request("Incorrect query. Parameter action missing.");
    }

    $actions = ['add', 'remove', 'update', 'switch', 'check'];
    $action = $_POST['action'];

    if (!in_array($action, $actions)) {
        http_bad_request("Incorrect query. Wrong action.");
    }

    if ($action === 'add') {
        add_item($db);

        http_response_code(302);
        header("Location: index.php");
        exit;
    }

    if ($action === 'remove') {
        $removed = remove_item($db);
        $response_json = encode_result($removed);
        echo $response_json;
        exit;
    }

    if ($action === 'update') {
        $updated = update_amount($db);
        $response_json = encode_result($updated);
        echo $response_json;
        exit;
    }

    if ($action === 'switch') {
        $switched = switch_positions($db);
        $response_json = encode_result($switched);
        echo $response_json;
        exit;
    
    }

    if ($action == 'check') {
        $updated = update_checked($db);
        $response_json = encode_result($updated);
        echo $response_json;
        exit;
    }




}
