<div class="form-container">
    <p>Add item</p>
    <form class="add-item" action="" method="POST">
        <input type="hidden" id="action" name="action" value="add">
        <div class="input item">
            <label for="item">Item:</label> <br>
            <input type="text" list="items-list" id="item" name="item" maxlength="50" required>
            <datalist id="items-list">
            </datalist>
        </div>
        <div class="input amount">
            <label for="amount">Amount:</label> <br>
            <input type="number" id="amount" name="amount" value="1" min="1" max="1000" required>
        </div>
        <div class="button">
            <input type="submit" value="Add">
        </div>
    </form>
</div>