<?php



class Database {

    /**
     * opens and returns a connection to database
     */
    function open_connection() {
        require __DIR__ . '/db_config.php';
        $c = new mysqli($db_config['server'], $db_config['login'], $db_config['password'], $db_config['database']);
        if ($c->connect_error) {
            return null;
        }
        return $c;
    }

    /**
     * Searches item table by name of item, returns id of item or -1 if not found
     */
    function get_item_id($connection, $item) {
        $stmt = $connection->prepare("SELECT id FROM items WHERE items.name LIKE ?");
        $stmt->bind_param('s', $item);
        $stmt->execute();
        $id = null;
        $stmt->bind_result($id);
        $result = [];
        while ($stmt->fetch()) {
            $result[] = $id;
        }
        if (count($result) === 0) {
            return -1;
        }
        elseif (count($result) > 1) {
            echo "Database contains multiple items with the same name $item";
        }
        return $result[0];
    }

    /**
     * Adds new item to items table
     */
    function add_to_items($connection, $item_name){
        $stmt = $connection->prepare("INSERT INTO items (name) VALUES (?)");
        $stmt->bind_param('s', $item_name);
        $executed = $stmt->execute();
        if (!$executed) {
            echo $stmt->error;
        }
        return $executed;
    }

    function get_all_items() {
        $connection = $this->open_connection();
        $stmt = $connection->prepare("SELECT name FROM items");
        $executed = $stmt->execute();
        if (!$executed) {
            echo $stmt->error;
            return null;
        }
        $result = [];
        $item = null;
        $stmt->bind_result($item);
        while($stmt->fetch()) {
            $result[] = $item;
        }
        $connection->close();
        return $result;
    }

    /*
    function get_similar_items($item_name) {
        $connection = $this->open_connection();
        $stmt = $connection->prepare("SELECT items.name FROM items WHERE items.name LIKE ?% LIMIT 3");
        $stmt->bind_param('s', $item_name);
        $stmt->execute();
        $item = null;
        $stmt->bind_result($item);
        $result = [];
        while ($stmt->fetch()) {
            $result[] = $item;
        }
        $connection->close();
        return $result;
    }
    */

    /**
     * Checks if item is in list table. Returns array with id and amount if found, null if not found. item_id is FK to items table.
     */
    function check_item_in_list($connection, $item_id) {
        $stmt = $connection->prepare("SELECT id, amount FROM list WHERE item_id = ?");
        $stmt->bind_param('i', $item_id);
        $stmt->execute();
        $id = null;
        $amount = null;
        $result = [];
        $stmt->bind_result($id, $amount);
        while ($stmt->fetch()) {
            $result[] = $id;
            $result[] = $amount;
        }
        if (count($result) === 0) {
            return null;
        }
        return $result;
    }

    /**
     * Adds new item to list table. item_id is FK to items table
     */
    function add_new_to_list($connection, $item_id, $amount, $position) {
        $stmt = $connection->prepare("INSERT INTO list (item_id, amount, position) VALUES (?, ?, ?)");
        $stmt->bind_param('iii', $item_id, $amount, $position);
        $executed = $stmt->execute();
        if (!$executed) {
            echo $stmt->error;
        }
        return $executed;
    }

    /**
     * Updates int column of item in list table (amount or position). id is id of item in list table
     */
    function update_row_in_list($connection, $id, $column, $new_value) {
        $stmt = $connection->prepare("UPDATE list SET $column = ? WHERE id = ?");
        $stmt->bind_param('ii', $new_value, $id);
        $executed = $stmt->execute();
        if (!$executed) {
            echo $stmt->error;
        }
        return $executed;
    }

    /**
     * Finds max position of item in list table, returns 0 for empty table
     */
    function get_max_position_in_list($connection) {
        $stmt = $connection->prepare("SELECT MAX(position) FROM list");
        $stmt->execute();
        $max = null;
        $stmt->bind_result($max);
        $result = [];
        while ($stmt->fetch()) {
            $result[] = $max;
        }
        if (count($result) === 0) {
            return 0;
        }
        elseif (count($result) > 1) {
            echo "Table list contains multiple items with the same position";
        }
        return $result[0];
    }

    /**
     * Adds item to list table. If item is already present in table, increases amount.
     */
    function add_to_list($connection, $item_id, $amount) {
        $item_data = $this->check_item_in_list($connection, $item_id);
        if ($item_data === null) { // item not in list
            $position = $this->get_max_position_in_list($connection) + 1;   // if list empty - max pos = 0 -> added item has position 1
            $added = $this->add_new_to_list($connection, $item_id, $amount, $position);
            return $added;
        }
        // item is already in list
        $id = $item_data[0];
        $new_amount = $amount + $item_data[1];
        $updated = $this->update_row_in_list($connection, $id, 'amount', $new_amount);
        return $updated;
    }

    /**
     * Adds item to list.
     */
    function add_item($item, $amount) {
        $connection = $this->open_connection();
        $item_id = $this->get_item_id($connection, $item);
        if ($item_id === -1) {
            $this->add_to_items($connection, $item);
            $item_id = $this->get_item_id($connection, $item);
        }
        $added = $this->add_to_list($connection, $item_id, $amount);
        $connection->close();
        return $added;
    }

    function do_get_list($connection) {
        $stmt = $connection->prepare("SELECT l.id, i.name, l.amount, l.position, l.checked FROM list AS l LEFT JOIN items AS i ON l.item_id = i.id ORDER BY l.position");
        $stmt->execute();
        $id = null;
        $name = null;
        $amount = null;
        $position = null;
        $checked = null;
        $stmt->bind_result($id, $name, $amount, $position, $checked);
        $result = [];
        while($stmt->fetch()) {
            $result[] = (object) [
                'id' => $id,
                'item' => $name,
                'amount' => $amount,
                'position' => $position,
                'checked' => $checked
            ];
        }
        return $result;
    }

    function get_list() {
        $connection = $this->open_connection();
        $list = $this->do_get_list($connection);
        $connection->close();
        return $list;
    }

    /**
     * Removes item from list table. id is id in list table
     */
    function do_remove_from_list($connection, $id) {
        $stmt = $connection->prepare("DELETE FROM list WHERE id = ?");
        $stmt->bind_param('i', $id);
        $executed = $stmt->execute();
        if (!$executed) {
            echo $stmt->error;
            return false;
        }
        return true;
    }

    /**
     * Removes item from list table by name. item_id is id in list table
     */
    function remove_item_from_list($item_id) {
        $connection = $this->open_connection();
        $removed = $this->do_remove_from_list($connection, $item_id);
        $connection->close();
        return $removed;
    }

    /**
     * Update amount of item in list. item_id is id in list table
     */
    function update_amount($item_id, $new_amount) {
        $connection = $this->open_connection();
        $updated = $this->update_row_in_list($connection, $item_id, 'amount', $new_amount);
        $connection->close();
        return $updated;
    }

    /**
     * Finds position of item in list, if not found returns -1. item_id is id in list table
     */
    function get_position_in_list($connection, $item_id) {
        $stmt = $connection->prepare("SELECT position FROM list WHERE id = ?");
        $stmt->bind_param('i', $item_id);
        $executed = $stmt->execute();
        if (!$executed) {
            return -1;
        }
        $pos = null;
        $result = [];
        $stmt->bind_result($pos);
        while ($stmt->fetch()) {
            $result[] = $pos;
        }
        return $result[0];
    }

    /**
     * Switch positions of 2 items in list table. item1 and item2 are ids in list table
     */
    function switch_positions($item1, $item2) {
        $connection = $this->open_connection();
        $pos1 = $this->get_position_in_list($connection, $item1);
        $pos2 = $this->get_position_in_list($connection, $item2);
        if ($pos1 === -1 or $pos2 === -1) {
            return false;
        }
        $upd1 = $this->update_row_in_list($connection, $item1, 'position', $pos2);
        $upd2 = $this->update_row_in_list($connection, $item2, 'position', $pos1);
        $connection->close();
        return $upd1 and $upd2;
    }

    function update_checked($item_id, $checked) {
        $connection  = $this->open_connection();
        $stmt = $connection->prepare("UPDATE list SET checked = ? WHERE id = ?");
        $stmt->bind_param('ii', $checked, $item_id);
        $executed = $stmt->execute();
        if (!$executed) {
            echo $stmt->error;
            return false;
        }
        $connection->close();
        return true;
    }

}