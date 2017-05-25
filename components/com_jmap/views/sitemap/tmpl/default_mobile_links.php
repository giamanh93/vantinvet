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

$priority =  $this->sourceparams->get ( 'priority', '0.5' );
$changefreq = $this->sourceparams->get ( 'changefreq', 'daily' );
$linkableCatsMode = $this->sourceparams->get ( 'linkable_content_cats', 1 );

// Inject items links
if (isset($this->source->data->link) && count ( $this->source->data->link ) != 0) {  
	foreach ( $this->source->data->link as $index=>$link ) {
		// Manage modified date if exists
		$lastmod = gmdate('Y-m-d\TH:i:s\Z');
		
		// Skip outputting
		$relativeLink = str_replace($this->liveSite, '', $link);
		if(array_key_exists($relativeLink, $this->outputtedLinksBuffer)) {
			continue;
		}
		
		// Else store to prevent duplication
		$this->outputtedLinksBuffer[$relativeLink] = true;
		?>
<url>
<loc><?php echo htmlspecialchars($link, null, 'UTF-8', false); ?></loc>
<mobile:mobile/>
</url>
<?php 
	}
}