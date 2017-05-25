<?php
/** 
 * @package JMAP::SITEMAP::components::com_jmap
 * @subpackage views
 * @subpackage sitemap
 * @subpackage tmpl
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );

$includeExternalLinks =  $this->sourceparams->get ( 'include_external_links', 1 );

// Get menus object
$menusArray = $this->application->getMenu()->getMenu();

if (count ( $this->source->data )) {
	foreach ( $this->source->data as $elm ) { 
		// Skip menu external links
		if($elm->type == 'url' && !$includeExternalLinks) {
			continue;
		}
		
		// Avoid place link for separator, alias, heading, external url
		if(in_array($elm->type, array('url', 'separator', 'alias', 'heading'))) {
			continue;
		}
		
		// Get language associations for this content, if not found skip and go on
		$associatedMenus = JMapHelpersAssociations::getMenuAssociations($elm->id);
		if(count($associatedMenus) <= 1) {
			continue;
		}
		
		$link = $elm->link;
		if (isset ( $elm->id )) {
			if (strpos ( $link, 'Itemid=' ) === FALSE) {
				$link .= '&Itemid=' . $elm->id;
			}
		}
		
		if (strcasecmp ( substr ( $link, 0, 9 ), 'index.php' ) === 0) {
			$link = JRoute::_ ( $link );
		}
		
		if (($elm->link == 'index.php') or strpos ( $elm->link, 'view=frontpage' )) { // HOME
			$link = '';
		}
		
		// SEF patch for better match uri con $link override
		if ($elm->type == 'component' && array_key_exists($elm->id, $menusArray)) {
			$link = 'index.php?Itemid=' . $elm->id;
			$link = JRoute::_ ( $link );
		}
		
		// Skip outputting
		if(array_key_exists($link, $this->outputtedLinksBuffer)) {
			continue;
		}
		// Else store to prevent duplication
		$this->outputtedLinksBuffer[$link] = true;
		
		$link = htmlspecialchars($link, null, 'UTF-8', false);
		?>
<url>
<loc><?php echo preg_match('/^http/i', $link) ? $link : $this->liveSite . (strpos($link, '/') === 0 ? $link : '/'.$link) ; ?></loc>
<?php foreach ($associatedMenus as $alternate):
$alternateLink = htmlspecialchars(JRoute::_ ( 'index.php?Itemid=' . $alternate->id . '&lang=' . $alternate->sef ), null, 'UTF-8', false);
$alternateLink = preg_match('/^http/i', $alternateLink) ? $alternateLink : $this->liveSite . (strpos($alternateLink, '/') === 0 ? $alternateLink : '/'.$alternateLink);
?>
<xhtml:link rel="alternate" hreflang="<?php echo $alternate->sef?>" href="<?php echo $alternateLink;?>" />
<?php endforeach;?>
</url>
<?php 
	} 
}