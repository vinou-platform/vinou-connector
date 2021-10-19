var t3vinprefix = '.tx-vinou ';

var vinouShop = {

	addForms: t3vinprefix + 'form.add-item-form',
	updateForms: t3vinprefix + 'form.edit-item-form',
	deleteItemButtons: t3vinprefix + 'a.delete-basket-item',
	quantityEditInputs: t3vinprefix + 'form.basket-edit-form input[name="quantity"]',
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
	items: [],
	sum: {
		quantity: 0,
		bottles: 0,
		net: 0,
		tax: 0,
		gross: 0,
		valid: false
	},
	config: {
		basket: {
			messageType: 'toast'
		}
	},
	quantity: 0,
	timeout: null,
	checkBasketTimeout: null,
	tempquantity: 0,
	temphash: '',
	errors: {
		minBasketSize: {
			title: 'Mindestbestellmenge nicht erreicht',
			description: 'Du hast noch nicht die nötige Mindestbestellmenge von <strong>###value### Flaschen</strong> im Warenkorb',
			type: 'error',
			checkout: 'hidden'
		},
		packageSteps: {
			title: 'Falsche Bestellmenge',
			description: 'Die größe Deines Warenkorbes entspricht nicht unseren Versandstaffeln. <strong>Wir versenden ###value### Flaschen.</strong> Du möchtest eine Sondergröße beauftragen oder die passende Versandstaffel ist nicht dabei? Dann setz Dich direkt mit uns in Verbindung.',
			factor: 'Die größe Deines Warenkorbes entspricht nicht unseren Versandstaffeln. <strong>Wir versenden ausschließlich Gesamtmengen die durch ###value### teilbar sind.</strong> Du möchtest eine Sondergröße beauftragen? Dann setz Dich direkt mit uns in Verbindung.',
			type: 'error',
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
		if (campaignRow && campaignRow.getAttribute('data-hash'))
			ctrl.loadCampaign();
		else
			ctrl.updateBasketCount();
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
						if (response.data[inputs[i].name]){
							inputs[i].value = response.data[inputs[i].name];

							if(inputs[i].name == 'quantity')
								ctrl.refreshQuantityOverlay(inputs[i]);

						}
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
				if (row) row.parentNode.removeChild(row);
				var overlay = document.querySelector('#shop-list-item-' + wineid + ' .basket-overlay');
				if (overlay) overlay.setAttribute('data-status','hidden');
				toast.show('Erfolgreich','Deine Position wurde gelöscht');

				var campaignRow = document.getElementById('campaign-row');
				if (campaignRow && campaignRow.getAttribute('data-hash'))
					ctrl.loadCampaign();
				else
					ctrl.updateBasketCount();
			}
		}));
	},

	updateBasketItem: function(form) {
		var ctrl = this;
		var data = ctrl.serializeForm(form);
		var postData = {
			id: data.id,
			data: {
				quantity: data.quantity
			}
		};
		if (data.quantity > 0) {
			var row = document.getElementById('basket-row-' + data.id);
			if (row) {

				var valueLabel = row.querySelector('.quantity-overlay');
				if (valueLabel)
					valueLabel.innerHTML = data.quantity;

				var price = data.quantity * parseFloat(data.gross);
				console.log(price);
				row.querySelector('.price strong').innerHTML = price.toFixed(2).replace('.',',') + ' €';
			}
			ctrl.basketAction('editItem',postData,(function(){
				response = JSON.parse(this.responseText);
				if (this.status === 200) {
					toast.show('Erfolgreich','Deine Position wurde geändert');
					var campaignRow = document.getElementById('campaign-row');
					if (campaignRow && campaignRow.getAttribute('data-hash'))
						ctrl.loadCampaign();
					else
						ctrl.updateBasketCount();
				}
			}));
		} else {
			ctrl.basketAction('deleteItem',{id:data.id},(function(){
				if (this.status === 200) {
					toast.show('Erfolgreich','Deine Position wurde gelöscht');
					var campaignRow = document.getElementById('campaign-row');
					if (campaignRow && campaignRow.getAttribute('data-hash'))
						ctrl.loadCampaign();
					else
						ctrl.updateBasketCount();

					var row = document.getElementById('basket-row-' + data.id);
					if (row)
						row.parentNode.removeChild(row);
				}
			}));
		}
	},

	updateBasketCount: function(){
		var ctrl = this;
		ctrl.card.setAttribute('data-status','normal');
		ctrl.sum.net = 0;
		ctrl.sum.tax = 0;
		ctrl.sum.gross = 0;
		ctrl.sum.quantity = 0;
		ctrl.basketAction('get',{},(function(){
			if (this.status == 200) {
				response = JSON.parse(this.responseText);
				ctrl.sum.valid = response.valid ? response.valid : false;

				if (response.basketItems) {
					response.basketItems.forEach((function(item){

						if (item.item_type == 'bundle' && parseInt(item.item.package_quantity) > 0) {
							ctrl.sum.quantity += parseInt(item.quantity) * parseInt(item.item.package_quantity);
						}
						else
							ctrl.sum.quantity += item.quantity;


						if (item.item.prices && item.item.prices[0]) {
							ctrl.sum.tax += item.quantity * item.item.prices[0].tax;
							ctrl.sum.net += item.quantity * item.item.prices[0].net;
							ctrl.sum.gross += item.quantity * item.item.prices[0].gross;
						} else {
							ctrl.sum.tax += item.quantity * item.item.tax;
							ctrl.sum.net += item.quantity * item.item.net;
							ctrl.sum.gross += item.quantity * item.item.gross;
						}
					}))

					ctrl.basketAction('findPackage',{
						type: 'bottles',
						quantity: ctrl.sum.quantity
					},(function(){
						response = JSON.parse(this.responseText);
						packageRow = document.querySelector('#package-row');


						if (response && response.gross) {
							ctrl.sum.tax += 1 * response.tax;
							ctrl.sum.net += 1 * response.net;
							ctrl.sum.gross += 1 * response.gross;
							packageGross = response.gross.replace('.',',') + ' €';
						} else {
							packageGross = '0,00 €';
						}

						if (packageRow) {
							packagePrice = packageRow.querySelector('.package-price');
							packageRow.setAttribute('data-id', response.id);
							packagePrice.innerHTML = packageGross;
						}

						ctrl.updateBasketSum();

					}));
				} else {
					ctrl.updateBasketSum();
				}
			}
		}));
	},

	updateBasketSum: function() {
		var ctrl = this;
		var basketStatus = document.querySelector('.basket-status');
		if(basketStatus){
			basketStatus.setAttribute('data-count', ctrl.sum.quantity);
			if(ctrl.sum.quantity) {
				basketStatus.classList.add('filled');
				basketStatus.classList.remove('empty');
			} else {
				basketStatus.classList.add('empty');
				basketStatus.classList.remove('filled');
			}
		}

		document.querySelector('.basket-status .juwel').innerHTML = ctrl.sum.quantity;
		ctrl.quantity = ctrl.sum.quantity;
		ctrl.card.setAttribute('data-status','updated');

		if (typeof ctrl.campaign.discount.gross != 'undefined') {
			ctrl.sum.gross = parseFloat(ctrl.sum.gross) + parseFloat(ctrl.campaign.discount.gross);
			ctrl.sum.net = parseFloat(ctrl.sum.net) + parseFloat(ctrl.campaign.discount.net);
			ctrl.sum.tax = parseFloat(ctrl.sum.tax) + parseFloat(ctrl.campaign.discount.tax);
		}

		for (var key in ctrl.sum) {
			if (ctrl.sum.hasOwnProperty(key)) {
				el = document.querySelector('#basket-table #basket-sum-' + key);
				if (el && typeof ctrl.sum[key] == 'number')
					el.innerHTML = ctrl.sum[key].toFixed(2).replace('.',',') + ' €';
			}
		}

		ctrl.setBasketError(ctrl.sum.valid);

		clearTimeout(ctrl.checkBasketTimeout);
		ctrl.checkBasketTimeout = setTimeout(function(){
			ctrl.checkBasketTableVisibility();
		}, 400);
	},

	checkBasketTableVisibility: function() {
		var ctrl = this;
		var basketTable = document.getElementById('basket-table');
		if (basketTable && ctrl.quantity === 0) {
			basketTable.parentNode.removeChild(basketTable);
		}
	},

	isNumeric: function(search) {
		return /^-?[\d.]+(?:e-?\d+)?$/.test(search);
	},

	setBasketError: function(error) {
		var ctrl = this;
		var container = document.getElementById(t3vinprefix + 'basket-errors');
		var controls = document.querySelector(t3vinprefix + '.basket-controls');
		if (container) {
			container.innerHTML = '';
			var status = 'visible';
			console.log(error);
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

				// Dirty switch for getting factor in packageSteps
				// ToDo: Replace with language handling from api
				if (ctrl.isNumeric(ctrl.settings[error]) > 0 && error == 'packageSteps')
					description.innerHTML = ctrl.errors[error].factor.replace('###value###', setting);
				else
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
		request.open('POST', '/?vinou-command', true);
		request.setRequestHeader("Content-type", "application/json");
		request.send(JSON.stringify(data));
	},

	/*
	 * set quantity in overlay field if exists
	 */
	refreshQuantityOverlay: function(field) {
		var ctrl = this;
		var valueLabel = field.closest('form').querySelector('.quantity-overlay');
		if(valueLabel) valueLabel.innerHTML = parseInt(field.value);
		},

	submitAddForm: function(form) {
		console.log(form);
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
				ctrl.submitAddForm(this.closest('form'));
			});

			var quantityField = addForms[i].querySelector('[name="quantity"]');
			quantityField.addEventListener('change', function(event) {
				ctrl.refreshQuantityOverlay(this);
			});

		}

		var updateForms = document.querySelectorAll(ctrl.updateForms);
		for (var i = 0; i < updateForms.length; i++) {
			if (updateForms[i].addEventListener) {
				updateForms[i].addEventListener("submit", (function(event) {
					event.preventDefault();
					ctrl.updateBasketItem(this);
				}), true);
			}
			else {
				updateForms[i].attachEvent('onsubmit', (function(event){
					event.preventDefault();
					ctrl.updateBasketItem(this);
				}));
			}


			var quantityField = updateForms[i].querySelector('[name="quantity"]');
			quantityField.addEventListener('focus', function(event) {
				ctrl.tempquantity = this.value;
			});

			quantityField.addEventListener('change', function(event) {
				ctrl.refreshQuantityOverlay(this);
				ctrl.setUpdateTimeout(this);
			});

			quantityField.addEventListener('blur', function(event) {
				ctrl.setUpdateTimeout(this);
			});

			quantityField.addEventListener('input', function(event) {
				ctrl.setUpdateTimeout(this);
			});
		}

		var quantityChangeButtons = document.querySelectorAll(t3vinprefix + 'button.inc,' + t3vinprefix + 'button.dec');
		for (var i = 0; i < quantityChangeButtons.length; i++) {
			quantityChangeButtons[i].addEventListener('click',function(event) {
				event.preventDefault();
				var form = this.form;
				var input = form.querySelector('[name="quantity"]');
				var submit = form.querySelector('[type="submit"]');
				var current = parseInt(input.value);

				if(this.getAttribute('class').indexOf('inc') > -1) {
					var max = input.getAttribute('max') ? parseInt(input.getAttribute('max')) : 99;
					console.debug('current: ' + current);
		 			console.debug('max: ' + max);
		 			if (current < max) {
		 				ctrl.tempquantity = current;
		 				input.value = current + 1;
		 			}
		 			else
		 				input.value = max;
				}
				if(this.getAttribute('class').indexOf('dec') > -1){
					var min = input.getAttribute('min') ? parseInt(input.getAttribute('min')) : 1;
					if (current > min) {
						ctrl.tempquantity = current;
						input.value = current - 1;
					}
					else
						input.value = min;
				}

				ctrl.refreshQuantityOverlay(input);

				if (form.getAttribute('class').indexOf('edit-item-form') > -1 && !submit){
					ctrl.setUpdateTimeout(input);
				}
				return false;
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
				ctrl.loadCampaign();
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


		var forms = document.querySelectorAll(t3vinprefix + 'form');
		for (var i = 0; i < forms.length; i++) {
			if (forms[i].addEventListener) {
				forms[i].addEventListener("submit", (function(event) {
					ctrl.disableForm(this);
				}), true);
			}
			else {
				forms[i].attachEvent('onsubmit', (function(event){
					ctrl.disableForm(this);
				}));
			}
		}
	},

	disableForm: function(form) {
		var ctrl = this;
		var submit = form.querySelector('[type="submit"]');
		if (submit)
			submit.disabled = true;
		var spinner = form.querySelector('.spinner-wrapper');
		if (spinner && typeof spinner != 'undefined')
			spinner.style.display = 'block';
	},

	setUpdateTimeout: function(field) {
		var ctrl = this;
		clearTimeout(ctrl.timeout);
		ctrl.timeout = setTimeout(function(){
			if (ctrl.tempquantity != field.value && field.value > 0) {
				ctrl.tempquantity = null;
				ctrl.updateBasketItem(field.form);
			}
		}, 750);
	},

	collectBasketData: function() {
		var ctrl = this;
		ctrl.items = [];
		var rows = document.querySelectorAll(t3vinprefix + '.item-row');
		for (var i = 0; i < rows.length; i++) {
			var row = rows[i];
			if (row.id == 'campaign-row')
				continue;

			var form = row.querySelector('form');
			if (row.id == 'package-row') {
				var item = {
					item_type: 'package',
					item_id: row.getAttribute('data-id'),
					quantity: 1,
					net: row.getAttribute('data-net'),
					tax: row.getAttribute('data-tax'),
					taxrate: row.getAttribute('data-taxrate'),
					gross: row.getAttribute('data-gross')
				}
				ctrl.items.push(item);
			}
			else if (typeof form != 'undefined') {
				var data = ctrl.serializeForm(form);
				var item = {
					quantity: data.quantity,
					gross: (data.quantity * data.gross).toFixed(2),
					taxrate: data.taxrate,
					item_type: data.item_type,
					item_id: data.item_id
				};

				item.net = (item.gross / (1 + (item.taxrate / 100))).toFixed(2);
				item.tax = (item.gross - item.net).toFixed(2);
				ctrl.items.push(item);
			}
			else
				continue;
		}
	},

	setCampaignTimeout: function(field) {
		var ctrl = this;
		clearTimeout(ctrl.campaign.check);
		ctrl.campaign.check = setTimeout(function(){
			if (ctrl.temphash != field.value && field.value != '') {
				field.disabled = true;

				if (typeof ctrl.campaign.data.hash == 'string' && ctrl.campaign.data.hash == field.value) {
					field.value = '';
					return false;
				}

				var loader = document.querySelector('#campaign-table .loader');
				if (loader)
					loader.setAttribute('data-status', 'visible');

				ctrl.temphash = field.value;
				ctrl.collectBasketData();
				ctrl.basketAction('findCampaign', {
					hash: field.value,
					items: ctrl.items
				}, function(){
					var response = JSON.parse(this.responseText);

					field.disabled = false;

					if (loader)
						loader.setAttribute('data-status', 'hidden');

					if (this.status == 200 && response.info == 'success')
						ctrl.campaign.addButton.setAttribute('data-status', 'enabled');

					else {
						var error = typeof response.errors[0] != 'undefined' ? response.errors[0] : response.data;
						switch (error) {
							case 'ERROR_MIN_QUANTITY_NOT_REACHED':
								var errorText = 'Mindeststückzahl für den Rabatt-Code nicht erreicht!';
								break;

							default:
								var errorText = 'Rabatt-Code konnte nicht gefunden werden!';
								break;
						}

						toast.show('Fehler!', errorText);
						ctrl.campaign.addButton.setAttribute('data-status', 'disabled');
					}
				});
			}
		}, 750);
	},

	loadCampaign: function() {
		var ctrl = this;
		var hash = false;
		var campaignRow = document.querySelector('#campaign-row');

		// load hash from html DOM  if not empty
		if (campaignRow && campaignRow.getAttribute('data-hash') != '')
			hash = campaignRow.getAttribute('data-hash');

		// load hash from input field if not empty
		if (ctrl.campaign.hashInput && ctrl.campaign.hashInput.value != '')
			hash = ctrl.campaign.hashInput.value;

		// if hash is still false break
		if (!hash)
			return false;

		ctrl.campaign.data = false;
		ctrl.campaign.discount = false;
		ctrl.collectBasketData();

		ctrl.basketAction('loadCampaign', {
			hash: hash,
			items: ctrl.items
		}, function(){
			var response = JSON.parse(this.responseText);

			if (this.status == 200) {
				ctrl.campaign.data = response.data;
				ctrl.campaign.discount = response.summary;
				ctrl.campaign.hashInput.value = '';
				campaignRow.setAttribute('data-hash', response.data.hash);
				ctrl.campaign.addButton.setAttribute('data-status', 'disabled');

				var campaignTable = document.getElementById('campaign-table');
				if (campaignTable)
					campaignTable.setAttribute('data-status', 'hidden');
			}
			else {
				var error = typeof response.errors[0] != 'undefined' ? response.errors[0] : response.data;
				switch (error) {
					case 'ERROR_MIN_QUANTITY_NOT_REACHED':
						var errorText = 'Mindeststückzahl für den Rabatt-Code nicht erreicht!';
						break;

					default:
						var errorText = 'Rabatt-Code konnte nicht gefunden werden!';
						break;
				}

				if (errorText)
					toast.show('Fehler!', errorText);

			}

			ctrl.toggleCampaignRow();


		});
	},

	removeCampaign: function() {
		var ctrl = this;
		ctrl.campaign.data = false;
		ctrl.campaign.discount = false;
		ctrl.basketAction('removeCampaign', {
		}, function() {
			if (this.status === 200) {
				ctrl.campaign.data = false;
				ctrl.campaign.discount = false;
				ctrl.toggleCampaignRow();
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
		ctrl.updateBasketCount();
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