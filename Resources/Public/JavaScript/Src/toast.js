var toast = {

	wrapper: null,
	title: null,
	description: null,
	screentime: 2000,

	init: function() {
		this.createDom();
	},

	createDom: function() {
		this.wrapper = document.createElement("DIV");
		this.wrapper.id = 'toast-wrapper';

		this.title = document.createElement("H3");
		this.title.id = 'toast-title';

		this.description = document.createElement("P");
		this.description.id ="toast-description";

		this.wrapper.appendChild(this.title);
		this.wrapper.appendChild(this.description);

		document.body.appendChild(this.wrapper);
	},

	clear: function() {
		var $ctrl = this;
		$ctrl.title.innerText = '';
		$ctrl.description.innerText = '';
	},

	show: function(title, description) {
		var $ctrl = this;
		$ctrl.title.innerText = title;
		$ctrl.description.innerText = description;
		$ctrl.wrapper.setAttribute('data-status', 'visible');
		window.setTimeout(function(){
			$ctrl.wrapper.removeAttribute('data-status');
			$ctrl.clear();
		}, $ctrl.screentime)
	}
}
toast.init();

var vTools = {
	detectOrientation: function(element) {
		var w = element.offsetWidth;
		var h = element.offsetHeight;
		if (w < h) {
			element.setAttribute('data-orientation','portrait');
		} else if (w === h) {
			element.setAttribute('data-orientation','square');
		} else {
			element.setAttribute('data-orientation','landscape');
		}
	}
}