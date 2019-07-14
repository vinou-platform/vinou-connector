var vinouEnquiry = {

	wines: document.querySelectorAll('input[name*="[quantity]"]'),

	init: function () {
		this.bindEvents();
	},

	calculateSum: function () {
		var ctrl = this;
		var sum = 0;
		for (var i = 0; i < ctrl.wines.length; i++) {
			if (ctrl.wines[i].value > 0) {
				var priceId = ctrl.wines[i].id.replace('quantity','price');
				var priceField = document.getElementById(priceId);
				var wineValue = parseFloat(priceField.value) * parseInt(ctrl.wines[i].value);
				sum += wineValue;
			}
		}
		var net = sum.toFixed(2) / 1.19;
		var tax = parseFloat(sum.toFixed(2)) - parseFloat(net.toFixed(2));

		var netField = document.getElementById('tx_vinou_enquiry[net]');
		netField.value = net.toFixed(2);
		var taxField = document.getElementById('tx_vinou_enquiry[tax]');
		taxField.value = tax.toFixed(2);
		var grossField = document.getElementById('tx_vinou_enquiry[gross]');
		grossField.value = sum.toFixed(2);
	},

	bindEvents: function () {
		var ctrl = this;
		for (var i = 0; i < ctrl.wines.length; i++) {
			ctrl.wines[i].addEventListener('input',(function(){
				ctrl.calculateSum();
			}));
		}
	}
}
vinouEnquiry.init();
var vinouShop = {

	addForms: 'form.add-item-form',
	updateForms: 'form.basket-edit-form',
	deleteItemButtons: 'a.delete-basket-item',
	quantityEditInputs: 'form.basket-edit-form input[name="quantity"]',
	token: null,
	dropper: null,

	init: function() {
		this.validateLogin();
		this.bindEvents();
		this.createDropper();
	},

	createDropper: function() {
		this.dropper = document.createElement("DIV");
		this.dropper.id = 'basket-dropper';
		document.body.appendChild(this.dropper);
	},

	addItemToBasket: function(item) {
		var ctrl = this;
		var detail = document.querySelector('#wine-detail .wine-image');
		var listitem = document.getElementById('shop-list-item-' + item.item_id);
		if (!detail && !
			listitem)
			return false;
		var parent = detail ? detail : listitem;

		var image = parent.querySelector('img').cloneNode(true);
		ctrl.dropper.setAttribute('data-status','visible');
		ctrl.dropper.innerHTML = '';
		ctrl.dropper.appendChild(image);

		var offset = parent.getBoundingClientRect();
		ctrl.dropper.style.top = parseInt(offset.top) + 'px';
		ctrl.dropper.style.left = parseInt(offset.left) + 'px';

		var target = document.querySelector('.basket-status .juwel').getBoundingClientRect();
		ctrl.dropper.setAttribute('data-status','tobasket');
		ctrl.dropper.style.top = parseInt(target.top) + 'px';
		ctrl.dropper.style.left = parseInt(target.left) + 'px';

		var timeOut = setTimeout((function(){
			ctrl.dropper.setAttribute('data-status','hidden');
		}),800);

		var basket = localStorage.getItem("basket");
		var postData = {
			uuid: basket,
			data: item
		};
		ctrl.doApiRequest('/service/baskets/addItem',postData,(function(){
			if (this.status === 200) {
				toast.show('Erfolgreich','Ihre Position wurde in den Warenkorb gelegt');
				ctrl.updateBasketCount(basket);
			}
		}));
	},

	deleteItemFromBasket: function(id) {
		var ctrl = this;
		var basket = localStorage.getItem("basket");
		var postData = {
			id: id
		};
		ctrl.doApiRequest('/service/baskets/deleteItem',postData,(function(){
			if (this.status === 200) {
				toast.show('Erfolgreich','Ihre Position wurde gelöscht');
				ctrl.updateBasketCount(basket);
				var row = document.getElementById('basket-row-' + id);
				row.parentNode.removeChild(row);
			}
		}));
	},

	updateBasketItem: function(form) {
		var ctrl = this;
		var basket = localStorage.getItem("basket");
		var postData = {
			id: form.id,
			data: {
				quantity: form.quantity
			}
		};
		if (form.quantity > 0) {
			var row = document.getElementById('basket-row-' + form.id);
			var price = form.quantity * parseFloat(form.price);
			row.querySelector('.position-price').innerHTML = price.toFixed(2) + ' €';
			ctrl.doApiRequest('/service/baskets/editItem',postData,(function(){
				if (this.status === 200) {
					toast.show('Erfolgreich','Ihre Position wurde geändert');
					ctrl.updateBasketCount(basket);
				}
			}));
		} else {
			ctrl.doApiRequest('/service/baskets/deleteItem',{id:form.id},(function(){
				if (this.status === 200) {
					toast.show('Erfolgreich','Ihre Position wurde gelöscht');
					ctrl.updateBasketCount(basket);
					var row = document.getElementById('basket-row-' + form.id);
					row.parentNode.removeChild(row);
				}
			}));
		}
	},

	validateBasket: function() {
		var ctrl = this;
		var basket = localStorage.getItem("basket");
		if (!basket) {
			ctrl.createBasket();
		} else {
			ctrl.setCookie('basket',basket,30);
			ctrl.updateBasketCount(basket);
		}
	},

	updateBasketCount: function(basket){
		var ctrl = this;
		var card = document.querySelector('.basket-status');
		card.setAttribute('data-status','normal');
		ctrl.doApiRequest('/service/baskets/get',{
			uuid: basket
		},(function(){
			if (this.status == 200) {
				response = JSON.parse(this.responseText);
				var summary = {
					quantity: 0,
					net: 0,
					tax: 0,
					gross: 0
				};

				response.data.basketItems.forEach((function(item){
					summary.quantity += item.quantity;
					if (item.object.price) {
						summary.gross += item.quantity * item.object.price;
					} else {
						summary.gross += item.quantity * item.object.gross;
					}
				}))

				ctrl.doApiRequest('/service/packaging/find',{
					type: 'bottles',
					bottles: summary.quantity
				},(function(){
					response = JSON.parse(this.responseText);
					if (response.data) {
						price = response.data.price;
						summary.gross += 1 * price;
						price.replace('.',',');
						packagePrice = document.querySelector('#package-row .position-price');
						if (packagePrice)
							packagePrice.innerHTML = price + ' €';
					}

					summary.net = summary.gross / 1.19;
					summary.tax = summary.gross - summary.net;

					document.querySelector('.basket-status .juwel').innerHTML = summary.quantity;
					card.setAttribute('data-status','updated');

					ctrl.updateBasketSum(summary);
				}));
			} else {
				ctrl.createBasket();
			}


		}));
	},

	updateBasketSum: function(sum) {
		for (var key in sum) {
			if (sum.hasOwnProperty(key)) {
				el = document.getElementById('basket-sum-' + key);
				if (el) {
					var price = sum[key].toFixed(2);
					price.replace('.',',');
					el.innerHTML = price + ' €';
				}
			}
		}
	},

	createBasket: function() {
		var ctrl = this;
		ctrl.doApiRequest('/service/baskets/add',null,(function(){
			if (this.status === 200) {
				response = JSON.parse(this.responseText);
				localStorage.setItem("basket",response.data.uuid);
				ctrl.setCookie('basket',response.data.uuid,30);
			}
		}));
	},

	validateLogin: function() {
		var ctrl = this;
		// check if token is set in local storage
		if(!localStorage.getItem("token")){
			// login client if no token is set
			ctrl.login();
		} else {
			// check if token is expired
			ctrl.doApiRequest('/service/check/login',null,(function(){
				if (this.status == 200) {
					ctrl.validateBasket();
				} else {
					console.error('invalid client token');
					ctrl.login();
					// create new token
				}
			}));
		}
	},

	login: function() {
		var ctrl = this;
		// do client login
		this.request('/?eID=clientLogin',null,(function(){
			switch(this.status) {
				case 200:
					response = JSON.parse(this.responseText);
					if (response.token)
						localStorage.setItem("token",response.token);
						// set token into local storage
					ctrl.validateBasket();
					break;
				default:
					console.error('no client token could be generated');
					break;
			}
		}));
	},

	request: function(route,data,callback) {
		var args = Array.prototype.slice.call(arguments, 3);
		var request = new XMLHttpRequest();
		request.onload = function() {
			if (request.readyState === 4) {
				switch (request.status) {
					case 200:
						callback.apply(request, args);
						break;
					case 401:
						callback.apply(request, args);
						break;
					default:
						callback.apply(request, args);
						break;
				}
			}
		};
		request.callback = callback;
		request.open("POST", route, true);
		request.setRequestHeader("Content-type", "application/json");
		request.send(JSON.stringify(data));
	},

	doApiRequest: function(route,data,callback) {
		var args = Array.prototype.slice.call(arguments, 3);
		var request = new XMLHttpRequest();
		request.onload = function() {
			if (request.readyState === 4) {
				switch (request.status) {
					case 200:
						callback.apply(request, args);
						break;
					case 401:
						localStorage.removeItem("token");
						callback.apply(request, args);
						break;
					default:
						callback.apply(request, args);
						break;
				}
			}
		};
		request.callback = callback;
		request.open("POST", "https://api.vinou.de" + route, true);
		request.setRequestHeader("Content-type", "application/json");
		request.setRequestHeader("Authorization", "Bearer " + localStorage.getItem("token"));
		request.send(JSON.stringify(data));
	},

	bindEvents: function() {
		var ctrl = this;
		var dropper = document.getElementById('basket-dropper');

		var addForms = document.querySelectorAll(ctrl.addForms);
		for (var i = 0; i < addForms.length; i++) {
			if (addForms[i].addEventListener) {
				addForms[i].addEventListener("submit", (function(event) {
					event.preventDefault();
					ctrl.addItemToBasket(ctrl.serializeForm(this));
				}), true);
			}
			else {
				addForms[i].attachEvent('onsubmit', (function(event){
					event.preventDefault();
					ctrl.addItemToBasket(ctrl.serializeForm(this));
				}));
			}
		}

		var updateForms = document.querySelectorAll(ctrl.updateForms);
		for (var i = 0; i < updateForms.length; i++) {
			if (updateForms[i].addEventListener) {
				updateForms[i].addEventListener("submit", (function(event) {
					event.preventDefault();
					ctrl.updateBasketItem(ctrl.serializeForm(this));
				}), true);
			}
			else {
				updateForms[i].attachEvent('onsubmit', (function(event){
					event.preventDefault();
					ctrl.updateBasketItem(ctrl.serializeForm(this));
				}));
			}
		}

		var deleteButtons = document.querySelectorAll(ctrl.deleteItemButtons);
		for (var i = 0; i < deleteButtons.length; i++) {
			deleteButtons[i].addEventListener("click", (function(event) {
				event.preventDefault();
				var id = this.getAttribute('data-id');
				ctrl.deleteItemFromBasket(id);
				return false;
			}));
		}

		var delCheck = document.querySelector('#deliveryAdress');
		if (delCheck) {
			delCheck.addEventListener('change',(function(){
				var delForm = document.querySelector('#delivery-fieldset');
				delForm.setAttribute('data-visible',this.checked ? 1 : 0);
				var requiredFields = document.querySelectorAll('#delivery-fieldset [data-required="1"]');
				if (this.checked) {
					requiredFields.forEach((function(item){
						item.setAttribute('required','required');
					}));
				} else {
					requiredFields.forEach((function(item){
						item.removeAttribute('required');
					}));
				}
			}));
		}

	},

	serializeForm: function(form){
		if (!form || form.nodeName !== "FORM") {
			return;
		}
		var i, j, q = {};
		for (i = form.elements.length - 1; i >= 0; i = i - 1) {
			if (form.elements[i].name === "") {
				continue;
			}
			switch (form.elements[i].nodeName) {
			case 'INPUT':
				switch (form.elements[i].type) {
				case 'text':
				case 'number':
				case 'email':
				case 'date':
				case 'hidden':
				case 'password':
				case 'button':
				case 'reset':
				case 'submit':
					q[form.elements[i].name] = encodeURIComponent(form.elements[i].value);
					break;
				case 'checkbox':
				case 'radio':
					if (form.elements[i].checked) {
						q[form.elements[i].name] = encodeURIComponent(form.elements[i].value);
					}
					break;
				}
				break;
			case 'file':
				break;
			case 'TEXTAREA':
				q[form.elements[i].name] = encodeURIComponent(form.elements[i].value);
				break;
			case 'SELECT':
				switch (form.elements[i].type) {
				case 'select-one':
					q[form.elements[i].name] = encodeURIComponent(form.elements[i].value);
					break;
				case 'select-multiple':
					for (j = form.elements[i].options.length - 1; j >= 0; j = j - 1) {
						if (form.elements[i].options[j].selected) {
							q[form.elements[i].name] = encodeURIComponent(form.elements[i].options[j].value);
						}
					}
					break;
				}
				break;
			}
		}
		return q;
	},

	setCookie: function (cname, cvalue, exdays) {
		var d = new Date();
		d.setTime(d.getTime() + (exdays*24*60*60*1000));
		var expires = "expires="+d.toUTCString();
		document.cookie = cname + "=" + cvalue + "; " + expires + "; path=/";
	},

	getCookie: function(cname) {
		var name = cname + "=";
		var ca = document.cookie.split(';');
		for(var i=0; i<ca.length; i++) {
			var c = ca[i];
			while (c.charAt(0)==' ') c = c.substring(1);
			if (c.indexOf(name) == 0) return c.substring(name.length,c.length);
		}
		return "";
	}
}
vinouShop.init();
var vinouList = {

	searchMap: [],
	list: null,
	allowedFilter: ['place','type','vintage','categories'],
	arrayProperties: ['categories'],
	clusterSelector: '.vinou-cluster-item',
	containerId: 'vinou-list',
	container: null,
	options: {
		valueNames: [{
			data: [
				'name',
				'vintage',
				'place',
				'type',
				'categories'
			]
		}],
		page: 5,
		pagination: true
	},

	init: function () {
		this.container = document.getElementById(this.containerId);
		if (this.container) {
			this.initOptions();
			this.initList();
			this.loadFilter();
			this.bindEvents();
			this.initialSearch();
		}
	},

	initOptions: function () {
		$this = this;
		$this.options.page = document.getElementById($this.containerId).getAttribute('data-items-per-page');
	},

	initList: function () {
		$this = this;
		$this.list = new List($this.containerId, $this.options);
		$this.list.sort(
			'sorting',
			{
				order: 'asc',
				alphabet: "0123456789AaBbCcDdEÉeFfGgHhIiJjKkLlMmNnOoPpQqRrSsTtUuVvXxYyZzÅåÄäÖö@\""
			}
		);
		$this.list.update();
		// $this.list.on('updated', function(event){
		// 	vinouShop.addSubmitAddAction();
		// });
		// $this.list.on('updated', function(event){
		// 	console.log(event);
		// 	var element = document.getElementById($this.containerId);
		// 	window.scrollTo(element.offsetLeft,element.offsetTop);
		// });
	},

	isEmpty: function (obj) {
		for(var key in obj) {
			if(obj.hasOwnProperty(key))
				return false;
		}
		return true;
	},

	loadFilter: function () {
		$this = this;
		var url = new URL(window.location.href);
		$this.allowedFilter.forEach((function(filter){
			var value = url.searchParams.get(filter);
			if (value) {
				var checkboxId = 'cluster[' + filter + '][' + value + ']';
				var checkbox = document.getElementById(checkboxId);
				checkbox.checked = true;
				$this.searchMap[filter] = [];
				$this.searchMap[filter].push(value);
				var groupOpener = document.querySelector('#' + filter + '-cluster-group h3');
				groupOpener.setAttribute('data-status','open');
				var groupList = document.querySelector('#' + filter + '-cluster-group ul');
				groupList.style.display = 'block';

			}
		}));
		$this.filterList();
	},

	initialSearch: function() {
		var $this = this;
		var url = new URL(window.location.href);
		var search = url.searchParams.get('search');
		if (search) {
			document.getElementById('search-phrase').value = search;
			$this.list.search(search);
		}
	},

	filterList: function() {
		$this = this;
		$this.list.filter((function(item) {

			for (var property in $this.searchMap) {
				if ($this.arrayProperties.indexOf(property) > -1 && item.values()[property]) {
					var string = item.values()[property];
					var searchArr = string.split(', ');
					var check = false;
					searchArr.forEach((function(word){
						if ($this.searchMap[property].indexOf(word) > -1)
							check = true;
					}));
					if (!check)
						return false;
				} else {
					// check if property value is in property array in search map
					if ($this.searchMap[property].indexOf(item.values()[property]) === -1) {
						return false;
					}
				}
			}
			return true;

		}));
		$this.list.update();
	},

	bindEvents: function() {
		var $this = this;
		var clusters = document.querySelectorAll($this.clusterSelector);
		for (var i = 0; i < clusters.length; i++) {

			clusters[i].addEventListener('change',(function(){

				// prepare search map array
				var identifier = this.getAttribute('data-name');
				if (this.checked) {
					// create property array in searchmap if not exists
					if (typeof $this.searchMap[identifier] === 'undefined')
						$this.searchMap[identifier] = [];
					// push checkbox value in property array
					$this.searchMap[identifier].push(this.value);
				} else {
					// check if checkbox value is in property array in search map
					if (typeof $this.searchMap[identifier] != 'undefined') {
						var index = $this.searchMap[identifier].indexOf(this.value);
						if (index !== -1) {
							$this.searchMap[identifier].splice(index, 1);
						}
					}

					// remove property array in searchmap if its empty
					if ($this.searchMap[identifier].length == 0)
						delete $this.searchMap[identifier];
				}
				$this.filterList();
			}));
		}

		var paginations = document.querySelectorAll('.pagination');
		for (var i = 0; i < paginations.length; i++) {
			paginations[i].addEventListener('click',(function(e){
				if(e.target && e.target.className == 'page'){
					var element = document.getElementById($this.containerId);
					window.scrollTo(element.offsetLeft,element.offsetTop);
				}
			}));
		}

		document.getElementById('search-phrase').addEventListener("keyup", (function(){
			$this.list.search(this.value);
		}));
	}
}
vinouList.init();