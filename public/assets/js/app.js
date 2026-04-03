/*
Purple HR shared UI bootstrap script
*/


function dismissPageLoader() {
	var $loaderWrapper = $('#loader-wrapper');
	if ($loaderWrapper.length) {
		$loaderWrapper.stop(true, true).fadeOut(200, function() {
			$(this).remove();
		});
	}
}

$(window).on('load', function() {
	dismissPageLoader();
});

setTimeout(function() {
	dismissPageLoader();
}, 1200);

$(document).ready(function() {
	dismissPageLoader();
	var $wrapper = $('.main-wrapper');
	var $pageWrapper = $('.page-wrapper');
	var $slimScrolls = $('.slimscroll');

	var Sidemenu = function() {
		this.$menuItem = $('#sidebar-menu a');
	};

	function init() {
		$('#sidebar-menu a').on('click', function(e) {
			if($(this).parent().hasClass('submenu')) {
				e.preventDefault();
			}
			if(!$(this).hasClass('subdrop')) {
				$('ul', $(this).parents('ul:first')).slideUp(350);
				$('a', $(this).parents('ul:first')).removeClass('subdrop');
				$(this).next('ul').slideDown(350);
				$(this).addClass('subdrop');
			} else if($(this).hasClass('subdrop')) {
				$(this).removeClass('subdrop');
				$(this).next('ul').slideUp(350);
			}
		});
		$('#sidebar-menu ul li.submenu a.active').parents('li:last').children('a:first').addClass('active').trigger('click');
	}

	init();

	$('body').append('<div class="sidebar-overlay"></div>');
	var sidebarStateKey = 'purplehr.sidebar.mini';

	function applyDesktopSidebarState(isMini) {
		$wrapper.toggleClass('mini-sidebar', !!isMini);
		$wrapper.removeClass('expand-menu');
	}

	try {
		if (window.localStorage.getItem(sidebarStateKey) === '1') {
			applyDesktopSidebarState(true);
		}
	} catch (e) {
		// localStorage is unavailable in some privacy contexts.
	}

	$(document).on('click', '#toggle_btn', function(e) {
		e.preventDefault();
		var shouldMini = !$wrapper.hasClass('mini-sidebar');
		applyDesktopSidebarState(shouldMini);

		try {
			window.localStorage.setItem(sidebarStateKey, shouldMini ? '1' : '0');
		} catch (err) {
			// Ignore storage write failures and keep in-session behavior.
		}

		return false;
	});

	$(document).on('mouseenter', '.sidebar', function() {
		if ($wrapper.hasClass('mini-sidebar')) {
			$wrapper.addClass('expand-menu');
		}
	});

	$(document).on('mouseleave', '.sidebar', function() {
		$wrapper.removeClass('expand-menu');
	});

	$(document).on('click', '#mobile_btn', function() {
		$wrapper.toggleClass('slide-nav');
		$('.sidebar-overlay').toggleClass('opened');
		$('html').addClass('menu-opened');
		$('#task_window').removeClass('opened');
		return false;
	});

	$('.sidebar-overlay').on('click', function () {
		$('html').removeClass('menu-opened');
		$(this).removeClass('opened');
		$wrapper.removeClass('slide-nav');
		$('.sidebar-overlay').removeClass('opened');
		$('#task_window').removeClass('opened');
	});

	$(document).on('click', '#task_chat', function() {
		$('.sidebar-overlay').toggleClass('opened');
		$('#task_window').addClass('opened');
		return false;
	});

	if($('.select').length > 0) {
		$('.select').select2({
			minimumResultsForSearch: -1,
			width: '100%'
		});
	}

	if($('.modal').length > 0 ){
		var modalUniqueClass = '.modal';
		$('.modal').on('show.bs.modal', function() {
			var $element = $(this);
			var $uniques = $(modalUniqueClass + ':visible').not($(this));
			if ($uniques.length) {
				$uniques.modal('hide');
				$uniques.one('hidden.bs.modal', function() {
					$element.modal('show');
				});
				return false;
			}
		});
	}

	if($('.floating').length > 0 ){
		$('.floating').on('focus blur', function (e) {
			$(this).parents('.form-focus').toggleClass('focused', (e.type === 'focus' || this.value.length > 0));
		}).trigger('blur');
	}

	if($slimScrolls.length > 0) {
		$slimScrolls.slimScroll({
			height: 'auto',
			width: '100%',
			position: 'right',
			size: '7px',
			color: '#ccc',
			wheelStep: 10,
			touchScrollStep: 100
		});
		var wHeight = $(window).height() - 60;
		$slimScrolls.height(wHeight);
		$('.sidebar .slimScrollDiv').height(wHeight);
		$(window).resize(function() {
			var rHeight = $(window).height() - 60;
			$slimScrolls.height(rHeight);
			$('.sidebar .slimScrollDiv').height(rHeight);
		});
	}

	var pHeight = $(window).height();
	$pageWrapper.css('min-height', pHeight);
	$(window).resize(function() {
		var prHeight = $(window).height();
		$pageWrapper.css('min-height', prHeight);
	});

	if($('.datetimepicker').length > 0) {
		$('.datetimepicker').datetimepicker({
			format: 'DD-MM-YYYY',
			icons: {
				up: 'fa fa-angle-up',
				down: 'fa fa-angle-down',
				next: 'fa fa-angle-right',
				previous: 'fa fa-angle-left'
			}
		});
	}

	if($('.datatable').length > 0) {
		$('.datatable').each(function() {
			var $table = $(this);
			var headerCount = $table.find('thead th').length;
			var $firstRow = $table.find('tbody tr:first');
			var bodyCount = 0;

			if ($firstRow.length) {
				$firstRow.children('td, th').each(function() {
					bodyCount += parseInt($(this).attr('colspan') || 1, 10);
				});
			}

			if (!headerCount || (bodyCount && headerCount !== bodyCount)) {
				return;
			}

			if (!$.fn.DataTable.isDataTable($table)) {
				$table.DataTable({
					bFilter: false,
				});
			}
		});
	}

	if($('[data-toggle="tooltip"]').length > 0) {
		$('[data-toggle="tooltip"]').tooltip();
	}

	if($('.clickable-row').length > 0 ){
		$('.clickable-row').click(function() {
			window.location = $(this).data('href');
		});
	}

	$(document).on('click', '#check_all', function() {
		$('.checkmail').click();
		return false;
	});
	if($('.checkmail').length > 0) {
		$('.checkmail').each(function() {
			$(this).on('click', function() {
				$(this).closest('tr').toggleClass('checked');
			});
		});
	}

	$(document).on('click', '.mail-important', function() {
		$(this).find('i.fa').toggleClass('fa-star').toggleClass('fa-star-o');
	});

	if($('.summernote').length > 0) {
		$('.summernote').summernote({
			height: 200,
			minHeight: null,
			maxHeight: null,
			focus: false
		});
	}

	$(document).on('click', '#task_complete', function() {
		$(this).toggleClass('task-completed');
		return false;
	});

	if($('#customleave_select').length > 0) {
		$('#customleave_select').multiselect();
	}
	if($('#edit_customleave_select').length > 0) {
		$('#edit_customleave_select').multiselect();
	}

	$(document).on('click', '.leave-edit-btn', function() {
		$(this).removeClass('leave-edit-btn').addClass('btn btn-white leave-cancel-btn').text('Cancel');
		$(this).closest('div.leave-right').append('<button class="btn btn-primary leave-save-btn" type="submit">Save</button>');
		$(this).parent().parent().find('input').prop('disabled', false);
		return false;
	});
	$(document).on('click', '.leave-cancel-btn', function() {
		$(this).removeClass('btn btn-white leave-cancel-btn').addClass('leave-edit-btn').text('Edit');
		$(this).closest('div.leave-right').find('.leave-save-btn').remove();
		$(this).parent().parent().find('input').prop('disabled', true);
		return false;
	});

	$(document).on('change', '.leave-box .onoffswitch-checkbox', function() {
		var id = $(this).attr('id').split('_')[1];
		if ($(this).prop('checked') === true) {
			$('#leave_'+id+' .leave-edit-btn').prop('disabled', false);
			$('#leave_'+id+' .leave-action .btn').prop('disabled', false);
		} else {
			$('#leave_'+id+' .leave-action .btn').prop('disabled', true);
			$('#leave_'+id+' .leave-cancel-btn').parent().parent().find('input').prop('disabled', true);
			$('#leave_'+id+' .leave-cancel-btn').closest('div.leave-right').find('.leave-save-btn').remove();
			$('#leave_'+id+' .leave-cancel-btn').removeClass('btn btn-white leave-cancel-btn').addClass('leave-edit-btn').text('Edit');
			$('#leave_'+id+' .leave-edit-btn').prop('disabled', true);
		}
	});

	$('.leave-box .onoffswitch-checkbox').each(function() {
		var id = $(this).attr('id').split('_')[1];
		if ($(this).prop('checked') === true) {
			$('#leave_'+id+' .leave-edit-btn').prop('disabled', false);
			$('#leave_'+id+' .leave-action .btn').prop('disabled', false);
		} else {
			$('#leave_'+id+' .leave-action .btn').prop('disabled', true);
			$('#leave_'+id+' .leave-cancel-btn').parent().parent().find('input').prop('disabled', true);
			$('#leave_'+id+' .leave-cancel-btn').closest('div.leave-right').find('.leave-save-btn').remove();
			$('#leave_'+id+' .leave-cancel-btn').removeClass('btn btn-white leave-cancel-btn').addClass('leave-edit-btn').text('Edit');
			$('#leave_'+id+' .leave-edit-btn').prop('disabled', true);
		}
	});
});
