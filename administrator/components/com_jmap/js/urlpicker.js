/**
 * Injector script to get sitemap links from content parent window
 * 
 * @package JMAP::PINGOMATIC::administrator::components::com_jmap 
 * @subpackage js 
 * @author Joomla! Extensions Store
 * @copyright (C)2015 Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
*/
jQuery(function($){
	$('ul.jmap_filetree a').on('click', function(jqEvent){
		jqEvent.preventDefault();
		var parentDocument = window.parent.document;
		
		var parentLinkTitle = $('#title', parentDocument);
		var parentLinkURL = $('#linkurl', parentDocument);
		
		$(parentLinkTitle).val($(this).text());
		$(parentLinkURL).val($(this).attr('href'));
		return false;
	});
});