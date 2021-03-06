
class Controller {

    constructor() {
        this.model = new Model("");
    }

    getRowId(itemId) {
        return "item-id-" + itemId;
    }

    getItemId(rowId) {
        var pattern = /^item-id-(?<tileNum>[0-9]+)$/
        var matches = rowId.match(pattern);
        if (matches != null) {
            return parseInt(matches[1]);   // matches is array: whole match on idx 0, capture group on idx 1
        }
        return -1;
    }

    getRowIdOfButton(button) {
        return button.parentElement.parentElement.id;    // <tr id><td><button>..
    }

    getNthChildNode(node, n) {
        var children = node.children;
        if (children.lenght <= n) {
            return null;
        }
        return children[n];
    }

    getItemInRow(rowId) {
        var row = document.getElementById(rowId);
        var itemCell = this.getNthChildNode(row, 0);
        var item = itemCell.textContent;
        return item;
    }

    getAmountInRow(rowId) {
        var row = document.getElementById(rowId);
        var amountCell = this.getNthChildNode(row, 1);
        var amount = amountCell.textContent;
        return amount;
    }

    removeRowFromList(itemId, error = null) {
        if (error !== null) {
            console.log(error);
            return;
        }
        var rowId = this.getRowId(itemId);
        var row = document.getElementById(rowId);
        row.remove();
    }

    handleRemove(removeButton) {
        var rowId = this.getRowIdOfButton(removeButton);
        var itemId = this.getItemId(rowId);
        if (itemId === -1) {
            console.log("Error with removing row, row id " + rowId + "not parsed.")
            return;
        }
        this.model.removeItem(itemId, (itemId, error) => this.removeRowFromList(itemId, error));
    }

    handleEdit(editButton) {
        // var body = document.getElementsByTagName("body")[0];
        // body.classList.toggle("blur");
        var dialog = document.getElementById("dialog");
        dialog.classList.toggle("hidden"); 
        var rowId = this.getRowIdOfButton(editButton);

        var itemName = this.getItemInRow(rowId);
        var itemText = document.getElementById("item-processed");
        itemText.textContent = itemName;

        var amount = this.getAmountInRow(rowId);
        var amountInput = document.getElementById("new-amount");
        //amountInput.setAttribute("value", amount);
        amountInput.value = amount;

        var itemId = this.getItemId(rowId);
        var saveButton = document.getElementById("save");
        saveButton.removeEventListener("click", (event) => this.handleSave(itemId));
        saveButton.addEventListener("click", (event) => this.handleSave(itemId));
    }

    handleCancel() {
        var dialog = document.getElementById("dialog");
        dialog.classList.toggle("hidden"); 
    }

    handleSave(itemId) {
        var amountInput = document.getElementById("new-amount");
        var newAmount = amountInput.value;
        this.model.updateAmount((error) => this.reload(error), itemId, newAmount);
    }

    addDataListOptions(options, error = null) {
        if (error !== null) {
            console.log(error);
            return;
        }
        var dataList = document.getElementById("items-list")
        for (let option of options) {
            let optionElement = document.createElement("option");
            optionElement.setAttribute("value", option);
            dataList.appendChild(optionElement);
        }
    }

    handleItemInputClick() {
        this.model.getAllItems((items, error) => this.addDataListOptions(items, error));
    }

    reload(error = null) {
        if (error) {
            console.log(error);
        }
        location.reload();
    }

    handleSwitch(switchButton) {
        var row1Id = this.getRowIdOfButton(switchButton);
        var item1Id = this.getItemId(row1Id);
        var row1 = document.getElementById(row1Id);
        var row2 = row1.nextElementSibling;
        var row2Id = row2.id;
        var item2Id = this.getItemId(row2Id);
        this.model.switchItems((error) => this.reload(error), item1Id, item2Id);
    }

    handleCheck(checkBox) {
        var rowId = this.getRowIdOfButton(checkBox);
        var itemId = this.getItemId(rowId);
        var checked = checkBox.checked;
        this.model.updateChecked((error) => this.reload(error), itemId, checked);
    }
}

window.onload = function() {

    var controller = new Controller();
    var removeButtons = document.getElementsByClassName('remove');
    for (let remove of removeButtons) {
        remove.addEventListener("click", (event) => controller.handleRemove(event.currentTarget));
    }

    var editButtons = document.getElementsByClassName("edit");
    for (let edit of editButtons) {
        edit.addEventListener("click", (event) => controller.handleEdit(event.currentTarget));
    }

    var cancelButton = document.getElementById("cancel");
    cancelButton.addEventListener("click", (event) => controller.handleCancel());

    var itemInput = document.getElementById("item");
    itemInput.addEventListener("click", controller.handleItemInputClick());

    var switchButtons = document.getElementsByClassName('switch');
    for (let switchButton of switchButtons) {
        switchButton.addEventListener("click", (event) => controller.handleSwitch(event.currentTarget));
    }

    var checkBoxes = document.getElementsByClassName("check");
    for (let box of checkBoxes) {
        box.addEventListener("click", (event) => controller.handleCheck(event.currentTarget));
    }
}
