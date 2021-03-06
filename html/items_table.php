<div class="table-container">
    <table class="shopping-list">
        <tr>
            <th>Item</th>
            <th>Amount</th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
        </tr>
        <?php 
        $counter = 1;
        foreach ($table_data as $obj) { ?>
        <tr id="item-id-<?= htmlspecialchars($obj->id) ?>">
            <td><?= htmlspecialchars($obj->item) ?></td>
            <td><?= htmlspecialchars($obj->amount) ?></td>
            <td>
                <?php if ($counter !== count($table_data)) { ?>
                <button type="button" class="switch">↓↑</button>
                <?php } ?>
            </td>
            <td><button type="button" class="edit">Edit</button></td>
            <td><button type="button" class="remove">Remove</button></td>
            <td><input type="checkbox" class="check" <?php if ($obj->checked) {echo "checked";}?> ></td>
        </tr>
        <?php 
        $counter++;
        } ?>
    </table>
</div>