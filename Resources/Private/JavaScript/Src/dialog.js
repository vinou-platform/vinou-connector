function vDialog(params) {

	this.params = params || {};
	this.params.ok = params.ok || function() {};
	this.params.cancel = params.cancel || function() {};
	this.params.yes = params.yes || 'Ja';
	this.params.no = params.no || 'Nein';
	this.params.title = params.title || 'Dialog';
	this.params.description = params.description || 'Description';
	this.params.type = params.type || 'YesNo'

	this.init();
}

vDialog.prototype = {
	wrapperId: 'dialog-wrapper',
	wrapper: null,
	init: function(params) {
		this.create();
		this.actions();
	},
	create: function() {
		var $ctrl = this;

		$ctrl.wrapper = document.createElement( "div" );
		$ctrl.wrapper.id = $ctrl.wrapperId;
		$ctrl.wrapper.setAttribute('data-status', 'hidden');
		var html = "<div id='dialog' class='" + $ctrl.params.type.toLowerCase() + "-dialog'>";

		html += "<h3 id='dialog-title'>" + $ctrl.params.title + "</h3>";
		html += "<div id='dialog-description'>";
		html += "<p>" + $ctrl.params.description + "</p>";

		if ($ctrl.params.type == 'YesNo')
			html += "<button id='dialog-cancel'>" + $ctrl.params.no + "</button>";

		html += "<button type='button' id='dialog-ok'>" + $ctrl.params.yes + "</button>";
		html += "</div></div>";

		$ctrl.wrapper.innerHTML = html;

		document.body.appendChild($ctrl.wrapper);
		$ctrl.wrapper.setAttribute('data-status', 'visible');
	},
	exit: function() {
		var $ctrl = this;

		$ctrl.wrapper.setAttribute('data-status','hidden');
		setTimeout(function() {
			document.body.removeChild($ctrl.wrapper);
		}, 1000);
	},
	actions: function() {
		var $ctrl = this;

		$ctrl.wrapper.querySelector( "#dialog-ok" ).addEventListener( "click", function() {
			$ctrl.exit();
			setTimeout(function() {
				$ctrl.params.ok();
			}, 1000);
		}, false);

		if ($ctrl.params.type == 'YesNo') {
			$ctrl.wrapper.querySelector( "#dialog-cancel" ).addEventListener( "click", function() {
				$ctrl.exit();
				setTimeout(function() {
					$ctrl.params.cancel();
				}, 1000);
			}, false);
		}
	}
};