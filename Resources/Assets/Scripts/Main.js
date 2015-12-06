$(document).ready(function(){
	$('.card-expandable').cardExpandable();
	$('.repeater').repeater({
		'itemAdded': function() {
			updateControllerItemSources();
		}
	});

	$('body').on('keyup', '.card-header-field', function() {
		$(this).parents('.card').find('.card-header .card-header-text').text($(this).val() + $(this).data('card-suffix'));
	});

	$('.dropdown-add .dropdown-menu a').click(function(e){
		e.preventDefault();
		$($(this).attr('href') + ' .repeater-item-add').click();
	});

	function updateControllerItemSources() {
		$('[item-source="controllerActionCombinations"]').each(function(){
			if ($(this).parents('.repeater-template').length > 0) {
				return;
			}
			var select = $(this);
			if (select.attr('data-value')) {
				var selected = select.attr('data-value').split(',');
			} else {
				var selected = [];
			}
			if ($(this).find('optgroup').length > 0) {
				selected = jQuery.makeArray($(this).val());
				$(this).find('optgroup').remove();
			}
			$('#repeater-Typo3-Components-ControllerComponent .controller-name').each(function(){
				var controllerName = $(this).val();
				var group = $('<optgroup label="' + controllerName + 'Controller" />');
				$(this).parents('.card').find('.controller-action').each(function(){
					if (!$(this).val()) {
						return;
					}
					var value = controllerName + ':' + $(this).val();
					//console.log(value, selected.indexOf(value), selected, $(this).val());
					if (selected.indexOf(value) > -1) {
						group.append('<option value="' + value + '" selected="1">' + $(this).val() + 'Action</option>');
					} else {
						group.append('<option value="' + value + '">' + $(this).val() + 'Action</option>');
					}
				});
				select.append(group);
				if (select.attr('multiple')) {
					select.multiSelect({ selectableOptgroup: true });
					select.multiSelect('refresh');
				}
			});
		});
	}

	$(this).on('change', '.controller-action', function(){
		updateControllerItemSources();
	});
	updateControllerItemSources();
});