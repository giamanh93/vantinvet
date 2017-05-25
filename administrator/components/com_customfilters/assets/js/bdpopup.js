/**
 * bdpopup v.2.0 2013-5-13 18:14 Creates a popup window from a part of a page.
 * Usefull for forms as it does not open a new window and what is set here can
 * be submited with the rest form
 * 
 * @author Sakis Terzis
 * @license GNU/GPL v.2
 * @copyright Copyright (C) 2013 breakDesigns.net. All rights reserved
 */

function displayPopup(id) {
	var link = 'show_popup' + id;
	var hideTags = 'hide_popup' + id;
	var closeBtn = 'close_btn' + id;
	var closeElements = new Array(document.id(hideTags), document.id(closeBtn));

	// hide irrelevant settings
	var selected_val = document.id('type_id' + id).getElement(':selected').value;
	var adv_settings_window = document.id('window' + id);
	if (adv_settings_window) {
		var setting_rows = adv_settings_window.getElements('li');
		Array.each(setting_rows, function(row, index) {
			var row_class = row.get('class');
			if (row_class.contains('setting')
					&& !row_class.contains('setting' + selected_val))
				row.addClass('cfhide');
			else
				row.removeClass('cfhide');
		});
	}

	document.id(link).addEvent('click', function() {
		closeOpenPopups();
		var elname = 'window' + id;
		document.id(elname).setStyle('display', 'block');
	});

	if (closeElements.length > 0) {
		Array.each(closeElements,function(e) {
			e.addEvent('click', function() {
				var elname = 'window' + id;
				document.id(elname).setStyle('display', 'none');
			});
		});
	}
}

function closeOpenPopups() {
	var windows = $$windows = $$('.bdpopup');
	var winLength = windows.length;
	for ( var i = 0; i < winLength; i++) {
		windows[i].setStyle('display', 'none');
	}
}
