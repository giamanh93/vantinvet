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
echo "<?xml version='1.0' encoding='UTF-8'?>" . PHP_EOL;
if($this->xslt) {
	echo "<?xml-stylesheet type='text/xsl' href='" . JURI::root() . "components/com_jmap/xslt/xml-mobile-sitemap.xsl'?>" . PHP_EOL;
}
?>
<urlset xmlns:mobile="http://www.google.com/schemas/sitemap-mobile/1.0" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php
foreach ( $this->data as $source ) {	
	// Strategy pattern source type template visualization
	if ($source->type) {
		$this->source = $source;
		$this->sourceparams = $source->params;
		$this->asCategoryTitleField = $this->findAsCategoryTitleField($source);
		if($this->sourceparams->get('xmlmobileinclude', 1)) {
			$subTemplateName = $this->_layout . '_mobile_' . $source->type . '.php';
			if (file_exists ( JPATH_COMPONENT_SITE . '/views/sitemap/tmpl/' . $subTemplateName )) {
				echo $this->loadTemplate ( 'mobile_' . $source->type );
			}
		}
	}
}
?>
</urlset>