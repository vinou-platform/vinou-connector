var vinouShop = {

	addForms: 'form.add-item-form',
	updateForms: 'form.edit-item-form',
	deleteItemButtons: 'a.delete-basket-item',
	quantityEditInputs: 'form.basket-edit-form input[name="quantity"]',
	card: document.querySelector('.basket-status'),
	campaign: {
		hashInput: document.querySelector('input#campaign-code'),
		check: null,
		addButton: document.querySelector('a#add-campaign'),
		removeButton: document.querySelector('a#remove-campaign'),
		discount: false,
		data: false
	},
	token: null,
	dropper: null,
	settings: {
		minBasketSize: null,
		packageSteps: null,
	},
	config: false,
	quantity: 0,
	timeout: null,
	tempquantity: 0,
	temphash: '',
	errors: {
		minBasketSize: {
			title: 'Mindestbestellmenge nicht erreicht',
			description: 'Du hast noch nicht die nötige Mindestbestellmenge von <strong>###value### Flaschen</strong> im Warenkorb',
			type: 'warning',
			checkout: 'hidden'
		},
		packageSteps: {
			title: 'Falsche Bestellmenge',
			description: 'Die größe Deines Warenkorbes entspricht nicht unseren Versandstaffeln. <strong>Wir versenden unsere Ware nur in ###value###er Kartons</strong>.',
			type: 'warning',
			checkout: 'hidden'
		}
	},

	init: function() {
		this.loadConfig();
		this.bindEvents();
		this.loadSettings();
		this.createDropper();
		this.initBasket();
	},

	loadConfig: function() {
		if (typeof vinouConfig != 'undefined')
			this.config = vinouConfig;
	},

	loadSettings: function() {
		var ctrl = this;
		minBasketSize = ctrl.card.getAttribute('data-minbasketsize');
		if (minBasketSize && parseInt(minBasketSize) > 0)
			ctrl.settings.minBasketSize = minBasketSize;
		packageSteps = ctrl.card.getAttribute('data-packagesteps');
		if (packageSteps && packageSteps != '')
			ctrl.settings.packageSteps = packageSteps.split(',');
	},

	createDropper: function() {
		this.dropper = document.createElement("DIV");
		this.dropper.id = 'basket-dropper';
		document.body.appendChild(this.dropper);
	},

	initBasket: function() {
		var ctrl = this;
		campaignRow = document.getElementById('campaign-row')
		if (campaignRow && campaignRow.getAttribute('data-hash')) {
			ctrl.basketAction('loadCampaign', {
				hash: campaignRow.getAttribute('data-hash')
			}, function() {
				if (this.status === 200) {
					ctrl.campaign.data = JSON.parse(this.responseText);
					ctrl.updateBasketCount();
				}
			});
		} else {
			ctrl.updateBasketCount();
		}
	},

	addItemToBasket: function(item) {
		var ctrl = this;
		var detail = document.querySelector('.wine-details');
		var listitem = document.getElementById('shop-list-item-' + item.item_id);
		if (!detail && !
			listitem)
			return false;
		var parent = detail ? detail : listitem;

		var image = parent.querySelector('.image img').cloneNode(true);
		ctrl.dropper.setAttribute('data-status','visible');
		ctrl.dropper.innerHTML = '';
		ctrl.dropper.appendChild(image);

		var offset = parent.querySelector('.add-item-form').getBoundingClientRect();
		ctrl.dropper.style.top = parseInt(offset.top) + 'px';
		ctrl.dropper.style.left = parseInt(offset.left) + 'px';

		var target = document.querySelector('.basket-status .juwel').getBoundingClientRect();
		ctrl.dropper.setAttribute('data-status','tobasket');
		ctrl.dropper.style.top = parseInt(target.top) + 'px';
		ctrl.dropper.style.left = parseInt(target.left) + 'px';

		var timeOut = setTimeout((function(){
			ctrl.dropper.setAttribute('data-status','hidden');
		}),800);

		var postData = {
			data: item
		};

		ctrl.basketAction('addItem',postData,(function(){
			if (this.status === 200) {

				var messageType = typeof ctrl.config.basket.messageType == 'string' ? ctrl.config.basket.messageType : 'toast';

				switch (messageType) {
					case 'dialog':
						switch (item.item_type) {
							case 'bundle':
								var quantityText = item.quantity + ' Weinpaket(e) ';
								break;

							case 'product':
								var quantityText = item.quantity + ' Stück ';
								break;

							default:
								var quantityText = item.quantity + ' Flasche(n) ';
								break;
						}


						new vDialog({
							title: '<span class="fa fa-shopping-cart"></span> Warenkorb',
							description: '<strong>' + quantityText + '</strong> wurde(n) in den Warenkorb gelegt.',
							yes: 'OK',
							type: 'OK',
							ok: function() {

							}
						});
						break;

					case 'toast':
						toast.show('Erfolgreich','Deine Position wurde in den Warenkorb gelegt');
						break;
				}


				ctrl.updateBasketCount();
				var overlay = document.querySelector('#shop-list-item-' + item.item_id + ' .basket-overlay');
				if (overlay) {
					response = JSON.parse(this.responseText);
					overlay.setAttribute('data-status','visible');
					var inputs = overlay.querySelectorAll('input');
					for (var i = 0; i < inputs.length; i++) {
						if (response.data[inputs[i].name])
							inputs[i].value = response.data[inputs[i].name];
					}
				}
			}
		}));
	},

	deleteItemFromBasket: function(id, wineid) {
		var ctrl = this;
		var postData = {
			id: id
		};
		var row = document.getElementById('basket-row-' + id);
		if (row) row.setAttribute('data-status', 'hidden');
		ctrl.basketAction('deleteItem',postData,(function(){
			if (this.status === 200) {
				toast.show('Erfolgreich','Deine Position wurde gelöscht');
				ctrl.updateBasketCount();
				if (row) row.parentNode.removeChild(row);
				var overlay = document.querySelector('#shop-list-item-' + wineid + ' .basket-overlay');
				if (overlay) overlay.setAttribute('data-status','hidden');
			}
		}));
	},

	updateBasketItem: function(form) {
		var ctrl = this;
		var postData = {
			id: form.id,
			data: {
				quantity: form.quantity
			}
		};
		if (form.quantity > 0) {
			var row = document.getElementById('basket-row-' + form.id);
			if (row) {
				var price = form.quantity * parseFloat(form.price);
				row.querySelector('.price strong').innerHTML = price.toFixed(2).replace('.',',') + ' €';
			}
			ctrl.basketAction('editItem',postData,(function(){
				if (this.status === 200) {
					toast.show('Erfolgreich','Deine Position wurde geändert');
					ctrl.updateBasketCount();
				}
			}));
		} else {
			ctrl.basketAction('deleteItem',{id:form.id},(function(){
				if (this.status === 200) {
					toast.show('Erfolgreich','Deine Position wurde gelöscht');
					ctrl.updateBasketCount();
					var row = document.getElementById('basket-row-' + form.id);
					if (row)
						row.parentNode.removeChild(row);
				}
			}));
		}
	},

	updateBasketCount: function(){
		var ctrl = this;
		ctrl.card.setAttribute('data-status','normal');
		ctrl.basketAction('get',{},(function(){
			if (this.status == 200) {
				response = JSON.parse(this.responseText);
				var summary = {
					quantity: 0,
					net: 0,
					tax: 0,
					gross: 0,
					valid: response.valid ? response.valid : false
				};

				if (response.basketItems) {
					response.basketItems.forEach((function(item){

						if (item.item_type == 'bundle' && parseInt(item.item.package_quantity) > 0) {
							summary.quantity += parseInt(item.quantity) * parseInt(item.item.package_quantity);
						}
						else
							summary.quantity += item.quantity;


						if (item.item.prices && item.item.prices[0]) {
							summary.tax += item.quantity * item.item.prices[0].tax;
							summary.net += item.quantity * item.item.prices[0].net;
							summary.gross += item.quantity * item.item.prices[0].gross;
						} else {
							summary.tax += item.quantity * item.item.tax;
							summary.net += item.quantity * item.item.net;
							summary.gross += item.quantity * item.item.gross;
						}
					}))

					ctrl.basketAction('findPackage',{
						type: 'bottles',
						quantity: summary.quantity
					},(function(){
						response = JSON.parse(this.responseText);
						packagePrice = document.querySelector('#package-row .package-price');

						if (response && response.gross) {
							summary.tax += 1 * response.tax;
							summary.net += 1 * response.net;
							summary.gross += 1 * response.gross;
							packageGross = response.gross.replace('.',',') + ' €';
						} else {
							packageGross = '0,00 €';
						}

						if (packagePrice)
							packagePrice.innerHTML = packageGross;

						if (ctrl.campaign.data) {
							ctrl.basketAction('campaignDiscount', {
							}, function() {
								discount = JSON.parse(this.responseText);
								if (this.status === 200) {
									ctrl.campaign.discount = discount;
									ctrl.toggleCampaignRow();
									summary.tax += 1 * discount.tax;
									summary.net += 1 * discount.net;
									summary.gross += 1 * discount.gross;
									ctrl.updateBasketSum(summary);
								}
							});
						} else {
							ctrl.toggleCampaignRow();
							ctrl.updateBasketSum(summary);
						}

					}));
				} else {
					ctrl.updateBasketSum(summary);
				}
			}
		}));
	},

	updateBasketSum: function(sum) {
		var ctrl = this;
		document.querySelector('.basket-status .juwel').innerHTML = sum.quantity;
		ctrl.quantity = sum.quantity;
		ctrl.card.setAttribute('data-status','updated');

		for (var key in sum) {
			if (sum.hasOwnProperty(key)) {
				el = document.querySelector('#basket-table #basket-sum-' + key);
				if (el)
					el.innerHTML = sum[key].toFixed(2).replace('.',',') + ' €';
			}
		}

		ctrl.setBasketError(sum.valid);

		var basketTable = document.getElementById('basket-table');
		if (basketTable && ctrl.quantity === 0) {
			basketTable.parentNode.removeChild(basketTable);
		}
	},

	setBasketError: function(error) {
		var ctrl = this;
		var container = document.getElementById('basket-errors');
		var controls = document.querySelector('.basket-controls');
		if (container) {
			container.innerHTML = '';
			var status = 'visible';
			if (ctrl.errors[error]) {
				var message = document.createElement("P");
				message.className = 'inline-message basket-warnings';
				message.setAttribute('data-type', ctrl.errors[error].type);

				var title = document.createElement("SPAN");
				title.className = 'title';
				title.innerHTML = ctrl.errors[error].title;
				message.appendChild(title);

				var setting = ctrl.settings[error];
				var description = document.createElement("SPAN");
				description.className = 'description';
				if (typeof setting != 'string')
					setting = ctrl.settings[error].join(', ');
				description.innerHTML = ctrl.errors[error].description.replace('###value###', setting);

				message.appendChild(description);

				container.appendChild(message);
				message.setAttribute('data-status', 'visible');

				status = ctrl.errors[error].checkout;
			}

			if (controls)
				controls.setAttribute('data-status', status);

		}
	},

	basketAction: function(action,data,callback) {
		var args = Array.prototype.slice.call(arguments, 3);
		var request = new XMLHttpRequest();
		request.onload = function() {
			if (request.readyState === 4) {
				if (request.status != 200) {
					console.log(action);
					console.log(data);
					console.log(request);
				}

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
		data.action = action;
		request.open("POST", "/?eID=vinouActions", true);
		request.setRequestHeader("Content-type", "application/json");
		request.send(JSON.stringify(data));
	},

	submitAddForm: function(form) {
		var ctrl = this;
		if (ctrl.quantity === 0) {
			new vDialog({
				title: 'Altersabfrage',
				description: 'Gerne liefern wir Dir Deine Weine. Bitte bestätige uns, dass Du dafür die notwendigen Altersbeschränkungen erfüllst.',
				yes: 'Ja, ich bin 18',
				no: 'Abbrechen',
				ok: function() {
					ctrl.addItemToBasket(ctrl.serializeForm(form));
				}
			});
		} else {
			ctrl.addItemToBasket(ctrl.serializeForm(form));
		}
	},

	bindEvents: function() {
		var ctrl = this;
		var dropper = document.getElementById('basket-dropper');

		var addForms = document.querySelectorAll(ctrl.addForms);
		for (var i = 0; i < addForms.length; i++) {
			if (addForms[i].addEventListener) {
				addForms[i].addEventListener("submit", (function(event) {
					event.preventDefault();
					ctrl.submitAddForm(this);
				}), true);
			}
			else {
				addForms[i].attachEvent('onsubmit', (function(event){
					event.preventDefault();
					ctrl.submitAddForm(this);
				}));
			}

			var addButton = addForms[i].querySelector('.add-basket');
			addButton.addEventListener('click',function(event) {
				event.preventDefault();
				ctrl.submitAddForm(this.form);
			});
		}

		var incButtons = document.querySelectorAll('button.inc');
		for (var i = 0; i < incButtons.length; i++) {
			incButtons[i].addEventListener('click',function(event) {
				event.preventDefault();
				var input = this.parentNode.querySelector('input[name="quantity"]');
				var current = input.value;
				ctrl.tempquantity = current;
				var max = input.getAttribute('max') ? input.getAttribute('max') : 99;
				input.value = current < max ? parseInt(current) + 1 : max;
				ctrl.setUpdateTimeout(input);
				return false;
			});
		}

		var decButtons = document.querySelectorAll('button.dec');
		for (var i = 0; i < decButtons.length; i++) {
			decButtons[i].addEventListener('click',function(event) {
				event.preventDefault();
				var input = this.parentNode.querySelector('input[name="quantity"]');
				var current = input.value;
				ctrl.tempquantity = current;
				var min = input.getAttribute('min') ? input.getAttribute('min') : 1;
				input.value = current > min ? parseInt(current) - 1 : min;
				ctrl.setUpdateTimeout(input);
				return false;
			});
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


			var quantityField = updateForms[i].querySelector('input[name="quantity"]');
			quantityField.addEventListener('focus', function(event) {
				ctrl.tempquantity = this.value;
			});

			quantityField.addEventListener('blur', function(event) {
				ctrl.setUpdateTimeout(this);
			});

			quantityField.addEventListener('input', function(event) {
				ctrl.setUpdateTimeout(this);
			});
		}



		var deleteButtons = document.querySelectorAll(ctrl.deleteItemButtons);
		for (var i = 0; i < deleteButtons.length; i++) {
			deleteButtons[i].addEventListener("click", (function(event) {
				event.preventDefault();
				var id = this.getAttribute('data-id');
				var wineid = this.getAttribute('data-wine-id');
				ctrl.deleteItemFromBasket(id, wineid);
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

		if (ctrl.campaign.hashInput) {
			ctrl.campaign.hashInput.addEventListener('input', function(event) {
				ctrl.setCampaignTimeout(this);
			});
		}

		if (ctrl.campaign.addButton) {
			ctrl.campaign.addButton.addEventListener('click', function(event) {
				event.preventDefault();
				ctrl.addCampaign();
				return false;
			});
		}

		if (ctrl.campaign.removeButton) {
			ctrl.campaign.removeButton.addEventListener('click', function(event) {
				event.preventDefault();
				ctrl.removeCampaign();
				return false;
			});
		}
	},

	setUpdateTimeout: function(field) {
		var ctrl = this;
		clearTimeout(ctrl.timeout);
		ctrl.timeout = setTimeout(function(){
			if (ctrl.tempquantity != field.value && field.value > 0) {
				ctrl.tempquantity = null;
				ctrl.updateBasketItem(ctrl.serializeForm(field.form));
			}
		}, 250);
	},

	setCampaignTimeout: function(field) {
		var ctrl = this;
		clearTimeout(ctrl.campaign.check);
		ctrl.campaign.check = setTimeout(function(){
			if (ctrl.temphash != field.value && field.value != '') {
				ctrl.temphash = field.value;
				ctrl.basketAction('findCampaign', {
					hash: field.value
				}, function(){
					campaign = JSON.parse(this.responseText);
					if (this.status == 200) {
						ctrl.campaign.data = campaign;
						ctrl.campaign.addButton.setAttribute('data-status', 'enabled');
					}
					else
						ctrl.campaign.addButton.setAttribute('data-status', 'disabled');
				});
			}
		}, 750);
	},

	addCampaign: function() {
		var ctrl = this;
		var hash = ctrl.campaign.hashInput ? ctrl.campaign.hashInput.value : false;
		if (!hash)
			return false;

		ctrl.basketAction('loadCampaign', {
			hash: hash
		}, function() {
			if (this.status === 200) {
				toast.show('Erfolgreich','Die Kampagne wurde erfolgreich aktiviert');

				var data = JSON.parse(this.responseText);
				if (data.rebate_type == 'absolute') {
					data.net = -1 * data.net;
					data.tax = -1 * data.tax;
					data.gross = -1 * data.gross;
				}

				ctrl.campaign.data = data;
				ctrl.updateBasketCount();
			}
		});
	},

	removeCampaign: function() {
		var ctrl = this;
		ctrl.campaign.data = false;
		ctrl.campaign.discount = false;
		ctrl.basketAction('removeCampaign', {
		}, function() {
			if (this.status === 200) {
				ctrl.updateBasketCount();
			}

		});
	},

	toggleCampaignRow: function() {
		var ctrl = this;
		var row = document.getElementById('campaign-row');
		var properties = [
			{field: 'name'},
			{field: 'hash'},
			{field: 'id'},
			{field: 'gross'},
			{field: 'description'},
			{field: 'gross', target: 'attribute'},
			{field: 'hash', target: 'attribute'}
		];

		if (!row)
			return false;

		properties.forEach(function(property){
			var field = property.field;
			var target = row.querySelector('[data-property="campaign-' + field + '"]');

			if (field == 'description');
				console.log(target);


			if (field == 'gross')
				var value = ctrl.campaign.discount[field] ? ctrl.campaign.discount[field].replace('.',',') + ' €'  : '';
			else
				var value = ctrl.campaign.data[field] ? ctrl.campaign.data[field] : '';

			if (property.target == 'attribute') {
				target = row;
				if (target) {
					row.setAttribute('data-' + field, value);
				}
			}

			else if (target) {
				target.innerHTML = value;
			}
		});

		row.setAttribute('data-status', !ctrl.campaign.data ? 'hidden' : 'visible');
		return true;
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

	isTouchDevice: function() {
		var prefixes = ' -webkit- -moz- -o- -ms- '.split(' ');
		var mq = function(query) {
			return window.matchMedia(query).matches;
		}

		if (('ontouchstart' in window) || window.DocumentTouch && document instanceof DocumentTouch) {
			return true;
		}

		var query = ['(', prefixes.join('touch-enabled),('), 'heartz', ')'].join('');
		return mq(query);
	}
}
vinouShop.init();