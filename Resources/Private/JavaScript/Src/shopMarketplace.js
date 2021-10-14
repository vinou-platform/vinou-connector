var t3vinprefixMarketplace = '.tx-vinou-marketplace ';

var vinouShopMarketplace = {

	deleteItemButtons: t3vinprefixMarketplace + 'a.delete-basket-item',
	// addForms: t3vinprefix + 'form.add-item-form',

	init: function() {
		console.debug('initMarketplace!!');
		this.bindEvents();
		// this.loadConfig();
		// this.bindEvents();
		// this.loadSettings();
		// this.createDropper();
		// this.initBasket();
	},

	bindEvents: function() {
		var ctrl = this;

		if (!document.querySelector(t3vinprefixMarketplace))
			return false;

		var quantityChangeButtons = document.querySelectorAll(t3vinprefixMarketplace + 'button.inc,' + t3vinprefixMarketplace + 'button.dec');
		for (var i = 0; i < quantityChangeButtons.length; i++) {
			quantityChangeButtons[i].addEventListener('click',function(event) {

				var basketRow = this.closest('.basket-row');
				var itemId = basketRow.getAttribute('data-item-id');
				var input = basketRow.querySelector('input[data-item-id="'+itemId+'"]');
				var valueLabel = basketRow.querySelector('.quantity-overlay');
				var min = 0;
				var max = input.getAttribute('data-max') ? parseInt(input.getAttribute('data-max')) : 99;

				event.preventDefault();
				// var form = this.form;
				// console.log(form);
				// var input = form.querySelector('[name="quantity"]');
				// var submit = form.querySelector('[type="submit"]');
				var current = parseInt(input.value);

				if(this.getAttribute('class').indexOf('inc') > -1) {
		 			if (current < max)
		 				input.value = current + 1;
		 			else
		 				input.value = max;
				}
				if(this.getAttribute('class').indexOf('dec') > -1){
					// var min = input.getAttribute('min') ? parseInt(input.getAttribute('min')) : 1;
					if (current > min)
						input.value = current - 1;
					else
						input.value = min;
				}

				if(valueLabel) valueLabel.innerHTML = parseInt(input.value);

				ctrl.setBasketValidation(false);

				return false;
			});
		}

		var deleteButtons = document.querySelectorAll(ctrl.deleteItemButtons);
		for (var i = 0; i < deleteButtons.length; i++) {
			deleteButtons[i].addEventListener("click", (function(event) {
				event.preventDefault();
				console.debug('delete');
				var basketRow = this.closest('.basket-row');
				//TO-Do: Prüfen ob die versandkosten des weinguts noch gelöscht werden müssen wenn kein anderer artikel des weinguts da ist
				basketRow.parentNode.removeChild(basketRow);
				ctrl.setBasketValidation(false);
				return false;
			}));
		}

		document.querySelector(t3vinprefixMarketplace + 'a.button-to-refresh').addEventListener('click',function(event) {
			event.preventDefault();
			var btn = document.querySelector(t3vinprefixMarketplace + 'button.button-to-refresh');
			btn.click();
		});

		document.querySelector(t3vinprefixMarketplace + 'a.button-to-checkout').addEventListener('click',function(event) {
			event.preventDefault();
			var btn = document.querySelector(t3vinprefixMarketplace + 'button.button-to-checkout');
			btn.click();
		});

	},

	setBasketValidation: function(validation) {
		console.debug('setBasketValidation');
		if (!validation) {
			var btn = document.querySelector(t3vinprefixMarketplace + 'a.button-to-checkout');
			if (btn)
				btn.parentNode.removeChild(btn);

			var btn = document.querySelector(t3vinprefixMarketplace + 'a.button-to-refresh');
				if (btn){
					btn.classList.add('grow')
					btn.classList.add('refresh-required');
					setTimeout(function(){
						btn.classList.remove('grow')
					}, 400);

				}
		}
	}






}
vinouShopMarketplace.init();