var wines = document.querySelectorAll('input[name*="[quantity]"]');

function calculateSum() {
	var sum = 0;
	for (var i = 0; i < wines.length; i++) {
		if (wines[i].value > 0) {
			var priceId = wines[i].id.replace('quantity','price');
			var priceField = document.getElementById(priceId);
			var wineValue = parseFloat(priceField.value) * parseInt(wines[i].value);
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
}

for (var i = 0; i < wines.length; i++) {
	wines[i].addEventListener('input',function(){
		calculateSum();
	});
}


jQuery(function(){
	$('.vinouWineList.slider').slick({
		centerMode: true,
		infinite: true,
		dots: true,
		prevArrow: '<a href="#" class="slickNav prev"></a>',
		nextArrow: '<a href="#" class="slickNav next"></a>'
	});
});