var vinouShop = {

	addForms: 'form.add-item-form',
	updateForms: 'form.edit-item-form',
	deleteItemButtons: 'a.delete-basket-item',
	quantityEditInputs: 'form.basket-edit-form input[name="quantity"]',
	token: null,
	dropper: null,
	quantity: 0,
	timeout: null,
	tempquantity: 0,
	currency: ' €',
	basketToCheckout: null,
	basketMessage: null,
	nextRangeHint: null,
	packageRanges: null,

	init: function() {
		this.basketToCheckout = document.getElementById('basket-to-checkout'),
		this.basketMessage = document.getElementById('basket-flash-message'),
		this.nextRangeHint = document.getElementById('next-range-hint'),
		this.bindEvents();
		this.createDropper();
		this.updateBasketCount();
		this.buildPackageRanges();
	},

	buildPackageRanges: function() {
		var ranges = document.querySelectorAll('#package-steps-table .range');
		if (ranges.length == 0)
			return false;

		this.packageRanges = [];
		for (var i = 0; i < ranges.length; i++) {
			this.packageRanges.push({
				from: ranges[i].getAttribute('data-from'),
				to: ranges[i].getAttribute('data-to'),
			});
		}
	},

	detectPackageRange: function(quantity) {
		var ctrl = this;
		var result = {
			valid: false,
			current: null,
			prevRange: null,
			nextRange: null
		};
		for (var i = 0; i < ctrl.packageRanges.length; i++) {
			if (quantity > parseInt(ctrl.packageRanges[i].from) && quantity > parseInt(ctrl.packageRanges[i].to))
				result.prevRange = ctrl.packageRanges[i];

			if (quantity < parseInt(ctrl.packageRanges[i].from) && quantity < parseInt(ctrl.packageRanges[i].to)) {
				if ((result.nextRange && result.nextRange.from > ctrl.packageRanges[i].from) || !result.nextRange)
					result.nextRange = ctrl.packageRanges[i];
			}

			if (quantity >= parseInt(ctrl.packageRanges[i].from) && quantity <= parseInt(ctrl.packageRanges[i].to)) {
				result.valid = true;
				result.current = ctrl.packageRanges[i];
			}
		}

		console.log(result);
		return result;
	},

	createDropper: function() {
		this.dropper = document.createElement("DIV");
		this.dropper.id = 'basket-dropper';
		document.body.appendChild(this.dropper);
	},

	addItemToBasket: function(item) {
		console.log(item);
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

		var postData = {
			data: item
		};

		ctrl.basketAction('addItem',postData,(function(){
			if (this.status === 200) {
				toast.show('Erfolgreich','Ihre Position wurde in den Warenkorb gelegt');
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
				toast.show('Erfolgreich','Ihre Position wurde gelöscht');
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
				row.querySelector('.price strong').innerHTML = price.toFixed(2).replace('.',',') + ctrl.currency;
			}
			ctrl.basketAction('editItem',postData,(function(){
				if (this.status === 200) {
					toast.show('Erfolgreich','Ihre Position wurde geändert');
					ctrl.updateBasketCount();
				}
			}));
		} else {
			ctrl.basketAction('deleteItem',{id:form.id},(function(){
				if (this.status === 200) {
					toast.show('Erfolgreich','Ihre Position wurde gelöscht');
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
		var card = document.querySelector('.basket-status');
		card.setAttribute('data-status','normal');
		ctrl.basketAction('get',{},(function(){
			if (this.status == 200) {
				response = JSON.parse(this.responseText);
				var summary = {
					quantity: 0,
					net: 0,
					tax: 0,
					gross: 0
				};

				if (response.basketItems) {
					response.basketItems.forEach((function(item){
						summary.quantity += item.quantity;

						var gross = item.quantity * item.item.gross;
						if (item.item.prices && item.item.prices[0])
							gross = item.item.prices[0].gross;

						var net = gross / (1 + item.item.taxrate / 100)
						net = Math.round(net * 100) / 100;;
						var tax = gross - net;

						summary.gross += gross;
						summary.tax += tax;
						summary.net += net;
					}))

					ctrl.basketAction('checkquantity', {
						quantity: summary.quantity
					},(function(){
						if (!document.getElementById('basket-table') || summary.quantity == 0)
							return true;

						var checkout = 'disabled';
						var message = 'visible';
						if (this.status == 200) {
							checkout = 'ready';
							message = 'hidden';
						}

						if (ctrl.basketToCheckout)
							ctrl.basketToCheckout.setAttribute('data-status', checkout);
						if (ctrl.basketMessage)
							ctrl.basketMessage.setAttribute('data-status', message);

						if (ctrl.nextRangeHint) {
							var range = ctrl.detectPackageRange(summary.quantity);
							if (range.valid) {
								ctrl.nextRangeHint.innerText = '';
								ctrl.nextRangeHint.setAttribute('data-status', 'hidden');
							}
							else {
								var diff = range.nextRange.from - summary.quantity;
								var text = 'Noch ' + parseInt(diff) + ' Flasche';
								if (diff > 1)
									text += 'n';
								ctrl.nextRangeHint.innerText = text;
								ctrl.nextRangeHint.setAttribute('data-status', 'visible');
							}

						}
					}));

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
							packageGross = response.gross.replace('.',',') + ctrl.currency;
						} else {
							packageGross = '0,00' + ctrl.currency;
						}

						if (packagePrice)
							packagePrice.innerHTML = packageGross;

						document.querySelector('.basket-status .juwel').innerHTML = summary.quantity;
						ctrl.quantity = summary.quantity;
						card.setAttribute('data-status','updated');
						ctrl.updateBasketSum(summary);

						var basketTable = document.getElementById('basket-table');
						if (basketTable && ctrl.quantity === 0) {
							basketTable.parentNode.removeChild(basketTable);
						}
					}));
				} else {
					document.querySelector('.basket-status .juwel').innerHTML = summary.quantity;
					ctrl.quantity = summary.quantity;
					card.setAttribute('data-status','updated');
					ctrl.updateBasketSum(summary);
				}
			}
		}));
	},

	updateBasketSum: function(sum) {
		var ctrl = this;
		for (var key in sum) {
			if (sum.hasOwnProperty(key)) {
				el = document.querySelector('#basket-table #basket-sum-' + key);
				if (el)
					el.innerHTML = sum[key].toFixed(2).replace('.',',') + ctrl.currency;
			}
		}

		var quantityWarning = document.getElementById('quantity-warning');
		var basketControls = document.querySelector('.basket-controls');

		if (quantityWarning && basketControls && basketControls.hasAttribute('data-minbasketsize')) {
			var minquantity = basketControls.getAttribute('data-minbasketsize');
			var status = sum.quantity < minquantity ? 'hidden' : 'visible';
			quantityWarning.setAttribute('data-checkout', status);
			basketControls.setAttribute('data-status', status);
		}
	},

	basketAction: function(action,data,callback) {
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
				description: 'Gerne liefern wir Ihnen Ihre Weine. Bitte bestätigen Sie uns, dass Sie dafür die notwendigen Altersbeschränkungen erfüllen.',
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
	},

	setUpdateTimeout: function(field) {
		var ctrl = this;
		if (ctrl.timeout) {
			clearTimeout(ctrl.timeout);
			ctrl.timeout = null;
		}
		ctrl.timeout = setTimeout(function(){
			if (ctrl.tempquantity != field.value && field.value > 0) {
				ctrl.tempquantity = null;
				ctrl.updateBasketItem(ctrl.serializeForm(field.form));
			}
		}, 500);
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