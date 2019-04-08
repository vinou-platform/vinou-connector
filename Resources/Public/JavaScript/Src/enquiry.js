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
			ctrl.wines[i].addEventListener('input',function(){
				ctrl.calculateSum();
			});
		}
	}
}
vinouEnquiry.init();