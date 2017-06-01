
var getTodoApp = function (holderId, undefined) {
	'use strict';

	var _data = {},
		_queue = $.Deferred().resolve(),
		_templates = {
			items: $.templates('#items_tmpl'),
			login: $.templates('#login_tmpl'),
			register: $.templates('#register_tmpl')
		},
		_holder = $('#' + holderId);

	function getClosestHash(object) {
		return $(object).closest('[data-item-hash]').data('itemHash');
	}

	return {
		init: function () {
			var self = this;

			_holder
				.on('click', 'input:submit', function (event) {
					self.update($(this).closest('form'));
					event.preventDefault();
				})
				.on('submit', 'form', function (event) {
					event.preventDefault();
				})
				.on('click', '[for="toggle-all"]', function (event) {
					var form = $(this).closest('form'),
						checked = $('.toggle-all', _holder).prop('checked');
					self.setAllCompleted(!checked).show().update(form);
					event.preventDefault();
				})
				.on('click', '.clear-completed', function (event) {
					var form = $(this).closest('form');
					self.clearCompleted().show().update(form);
					event.preventDefault();
				})
				.on('click', '.filters a', function (event) {
					var filter = $(this).attr('href').substr(2);
					self.setFilter(filter).show();
					event.preventDefault();
				})
				.on('keydown', '.new-todo', function (event) {
					var form = $(this).closest('form');
					if (13 == event.which) {
						self.addItem($(this).val()).show().update(form);
						$('.new-todo').focus();
					}
				})
				.on('click', '.toggle', function (event) {
					var index = self.getIndexByHash(getClosestHash(this)),
						isComplete = $(this).prop('checked'),
						form = $(this).closest('form');
					self.doneItem(index, isComplete).show().update(form);
					event.preventDefault();
				})
				.on('click', '.destroy:visible', function (event) {
					var index = self.getIndexByHash(getClosestHash(this)),
						form = $(this).closest('form');
					self.removeItem(index).show().update(form);
					event.preventDefault();
				})
				.on('keyup blur change', '.edit', function (event) {
					var index = self.getIndexByHash(getClosestHash(this)),
						form = $(this).closest('form');
					self.setItemText(index, $(this).val());
					if ('focusout' == event.type || ('keyup' == event.type && 13 == event.which)) {
						self.setActiveItem(-1).show().update(form);
					}
					event.preventDefault();
				})
				.on('dblclick', '[data-item-hash]:not(.editing)', function (event) {
					var index = self.getIndexByHash(getClosestHash(this));
					self.setActiveItem(index).show();
					event.preventDefault();
				})
			;
			this.update($('form[name="init"]'));
		},
		update: function (form) {
			var data = {
					items: _data.items
				},
				name, index;
			for (name in data) {
				if (data.hasOwnProperty(name)) {
					form.find(':input[name="' + name + '"]').val(JSON.stringify(data[name]));
				}
			}
			_queue.done((function (data, self) {
				return $.ajax({
					url: 'ajax.php',
					data: data,
					method: 'POST',
					context: this
				}).done(function (data) {
					self.setData(data).show();
				});
			})(form.serialize(), this));

			return this;
		},
		show: function () {
			if (undefined !== _data.skipShow) {
				delete _data.skipShow;
			} else if (_templates.hasOwnProperty(_data.action)) {
				_holder.html(_templates[_data.action].render($.extend({}, _data, this.getCalculatedData())));
			}

			return this;
		},
		setFilter: function (filter) {
			if (_data.filters.hasOwnProperty(filter)) {
				_data.filter = filter;
				window.localStorage.setItem('filter', filter);
			}

			return this;
		},
		addItem: function (text) {
			var hash = this.getHashByText(text);

			if (0 != text.length && !_data.map.hasOwnProperty(hash)) {
				_data.items.push({
					index: _data.items.length,
					isComplete: false,
					hash: this.getHashByText(text),
					text: text
				});
			}

			return this;
		},
		getIndexByHash: function (hash) {
			var index;
			if (_data.map.hasOwnProperty(hash)) {
				index = _data.map[hash];
			} else {
				index = 0;
			}

			return index;
		},
		getHashByText: function (text) {
			return md5(text).toLowerCase();
		},
		setItemText: function (index, text) {
			var hash = this.getHashByText(text);
			if (0 != text.length && !_data.map.hasOwnProperty(hash)) {
				delete _data.map[_data.items[index].hash];
				_data.map[hash] = index;
				_data.items[index].text = text;
			} else {
				this.removeItem(index);
			}

			return this;
		},
		setActiveItem: function (index) {
			_data.activeItemIndex = index;

			return this;
		},
		doneItem: function (index, mode) {
			_data.items[index].isComplete = !!mode;

			return this;
		},
		setAllCompleted: function (isComplete) {
			var index;
			for (index = 0; index < _data.items.length; index++) {
				_data.items[index].isComplete = isComplete;
			}

			return this;
		},
		clearCompleted: function () {
			var index;
			for (index = 0; index < _data.items.length; index++) {
				if (_data.items[index].isComplete) {
					this.removeItem(index);
				}
			}

			return this;
		},
		removeItem: function (index) {
			_data.items[index].isRemoved = true;
			delete _data.map[_data.items[index].hash];

			return this;
		},
		setData: function (data) {
			var index;
			_data = $.extend({
				action: '',
				items: [],
				map: {},
				filter: window.localStorage.getItem('filter') || 'all',
				filters: this.getFilters(),
				activeItemIndex: -1
			}, _data, data);
			for (index = 0; index < _data.items.length; index++) {
				_data.items[index].index = index;
				_data.map[_data.items[index].hash] = index;
			}

			return this;
		},
		getCalculatedData: function () {
			var data = {
					items: [],
					itemLeftCount: 0,
					isAllCompleted: true
				}, index;
			for (index = _data.items.length; index-- > 0;) {
				if (_data.filters[_data.filter].method(_data.items[index])) {
					data.items.push(_data.items[index]);
				}
				if (!_data.items[index].isRemoved && !_data.items[index].isComplete) {
					data.itemLeftCount++;
					data.isAllCompleted = false;
				}
			}

			return data;
		},
		getItems: function () {
			var items = [], index;
			for (index = _data.items.length; index-- > 0;) {
				if (_data.filters[_data.filter].method(_data.items[index])) {
					items.push(_data.items[index]);
				}
			}

			return items;
		},
		getFilters: function () {
			return {
				all: {
					title: 'All',
					method: function (item) {
						return !item.isRemoved;
					}
				},
				active: {
					title: 'Active',
					method: function (item) {
						return !item.isRemoved && !item.isComplete;
					}
				},
				complete: {
					title: 'Completed',
					method: function (item) {
						return !item.isRemoved && item.isComplete;
					}
				}
			};
		}
	};
};

(function ($) {
	'use strict';

	var todoApp = getTodoApp('todoapp');
	todoApp.init();
})(jQuery);


