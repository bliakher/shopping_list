
class Model {

    constructor(apiUrl) {
        this.apiUrl = apiUrl;
        this.fetcher = new Fetcher(this.apiUrl);
        this.data = null;
    }

    async getData() {
        if (this.data === null) {

        }
    }


    /**
     * 
     * @param {string} item Name of item to remove
     * @param {Function} callback Function which is called when removal is completed. On success called with one arg - item id
     *                            On failiure 2 args passed to callback - first arg null, second with error msg.
     */
    async removeItem(itemId, callback) {
        var parameters = {'action' : 'remove', 'item' : itemId};
        let response = await this.fetcher.doFetch("POST", parameters);
        if (response === null) {
            callback(null, "Error with fetching data.");
            return;
        }
        if (response.ok) {
            callback(itemId);
            return;
        }
        callback(null, "Removal unsuccessful.");
        return;
    }

    async updateAmount(callback, itemId, newAmount) {
        var parameters = {'action' : 'update', 'item' : itemId, 'amount' : newAmount};
        let response = await this.fetcher.doFetch("POST", parameters);
        if (response === null) {
            callback("Error with fetching data.");
            return;
        }
        if (response.ok) {
            callback();
            return;
        }
        callback("Removal unsuccessful.");
        return;
    }

    async getAllItems(callback) {
        var parameters = {'format' : 'json', 'items' : 'all'};
        let response = await this.fetcher.doFetch("GET", parameters);
        if (response === null) {
            callback(null, "Error with fetching data.");
            return;
        }
        if (response.ok) {
            callback(response.items);
            return;
        }
        callback(null, "Database error.");
    }

    async switchItems(callback, item1, item2) {
        var parameters = {'action' : 'switch', 'item1' : item1, 'item2' : item2};
        let response = await this.fetcher.doFetch("POST", parameters);
        if (response === null) {
            callback("Error with fetching data.");
            return;
        }
        if (response.ok) {
            callback();
            return;
        }
        callback("Database error.");
    }

    async updateChecked(callback, itemId, checked) {
        var parameters = {'action' : 'check', 'item' : itemId, 'checked' : checked};
        let response = await this.fetcher.doFetch("POST", parameters);
        if (response === null) {
            callback("Error with fetching data.");
            return;
        }
        if (response.ok) {
            callback();
            return;
        }
        callback("Database error.");
    }
}

class Fetcher {
    constructor(url) {
        this.apiUrl = url;
    }


    async doFetch(method, parameters) {
        if (method === "GET") {
            var query = new URLSearchParams(parameters);
            var url = this.apiUrl + "?" + query.toString();
            let response = null;
            try {
                let responseJson = await fetch(url, {method : "GET"});
                response = await responseJson.json();
            }
            catch(e) {
                console.log(e);
            }
            return response;
        }
        if (method === "POST") {
            var query = new URLSearchParams(parameters);
            let response = null;
            try {
                let responseJson = await fetch(this.apiUrl, {method : "POST", body : query});
                response = await responseJson.json();
            }
            catch(e) {
                console.log(e);
            }
            return response;
        }
        return null;
    }

    async doFetchNoResponse(method, parameters) {
        if (method === "POST") {
            var query = new URLSearchParams(parameters);
            let response = null;
            try {
                let response = await fetch(this.apiUrl, {method : "POST", body : query});
            }
            catch(e) {
                console.log(e);
            }
            return;
        }
        return;
    }

}