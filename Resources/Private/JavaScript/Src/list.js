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