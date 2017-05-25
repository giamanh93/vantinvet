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
	echo "<?xml-stylesheet type='text/xsl' href='" . JURI::root() . "components/com_jmap/xslt/xml-images-sitemap.xsl'?>" . PHP_EOL;
}
?>
<urlset xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">
<?php
foreach ( $this->data as $source ) {	
	// Check to ensure is counting valid request
	if(!$this->HTTPClient->isValidRequest()) {
		break;
	}
	// Strategy pattern source type template visualization
	if ($source->type) {
		$this->source = $source;
		$this->sourceparams = $source->params;
		$this->asCategoryTitleField = $this->findAsCategoryTitleField($source);
		if($this->sourceparams->get('xmlimagesinclude', 1)) {
			$subTemplateName = $this->_layout . '_images_' . $source->type . '.php';
			if (file_exists ( JPATH_COMPONENT_SITE . '/views/sitemap/tmpl/' . $subTemplateName )) {
				echo $this->loadTemplate ( 'images_' . $source->type );
			}
		}
	}
}
?>
</urlset>