/*
# ------------------------------------------------------------------------
# Vina Category Menu for VirtueMart for Joomla 3
# ------------------------------------------------------------------------
# Copyright(C) 2014 www.VinaGecko.com. All Rights Reserved.
# @license http://www.gnu.org/licenseses/gpl-3.0.html GNU/GPL
# Author: VinaGecko.com
# Websites: http://vinagecko.com
# Forum:    http://vinagecko.com/forum/
# ------------------------------------------------------------------------
*/
jQuery(document).ready(function($) {
	
	/* get menu selector */
	$('.vina-cmenu-vmart').each(function() {
	
		var menuId = $(this).attr('id');
		
		$('#' + menuId + '').prepend('<div id="indicatorContainer"><div id="pIndicator"><div id="cIndicator"></div></div></div>');
		var activeElement = $('#' + menuId + '>ul>li:first');

		$('#' + menuId + '>ul>li').each(function() {
			if($(this).hasClass('active')) {
				activeElement = $(this);
			}
		});

		var posLeft 		= activeElement.position().left;
		var elementWidth 	= activeElement.width();
		
		posLeft = posLeft + elementWidth/2 -6;
		if(activeElement.hasClass('has-sub')) {
			posLeft -= 6;
		}	

		$('#' + menuId + ' #pIndicator').css('left', posLeft);
		var element, leftPos, indicator = $('#' + menuId + ' #pIndicator');
		
		$('#' + menuId + '>ul>li').hover(function() {
			element = $(this);
			var w = element.width();
			
			if($(this).hasClass('has-sub')) {
				leftPos = element.position().left + w/2 - 12;
			}
			else {
				leftPos = element.position().left + w/2 - 6;
			}

			$('#' + menuId + ' #pIndicator').css('left', leftPos);
		}
		, function() {
			$('#' + menuId + ' #pIndicator').css('left', posLeft);
		});
		$('#' + menuId + '>ul li.has-sub').prepend('<span href="#" class="open-submenu"></span>');
		$('#' + menuId + '>ul').prepend('<li id="menu-button"><a>Menu</a></li>');
		$('#' + menuId + ' #menu-button').click(function(){
			if($(this).parent().hasClass('open')) {
				$(this).parent().removeClass('open');
			}
			else {
				$(this).parent().addClass('open');
			}
		});
		$('#' + menuId + '>ul li.has-sub .open-submenu').click(function(){
			if($(this).parent().hasClass('open')) {
				$(this).parent().removeClass('open');
			}
			else {
				$(this).parent().addClass('open');
			}
		});
	});
});