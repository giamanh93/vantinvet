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

if (isset($this->source->data->link) && count ( $this->source->data->link ) != 0) {
	foreach ( $this->source->data->link as $index=>$link ) {
		// Skip outputting
		$relativeLink = str_replace($this->liveSite, '', $link);
		if(array_key_exists($relativeLink, $this->outputtedLinksBuffer)) {
			continue;
		}
		// Else store to prevent duplication
		$this->outputtedLinksBuffer[$relativeLink] = true;
		
		// Normalize and fallback publish up - publication date fields
		$publish_up = gmdate('Y-m-d\TH:i:s\Z', time());
?>
<url>
<loc><?php echo htmlspecialchars($link, null, 'UTF-8', false); ?></loc>
<news:news>
<news:publication>
<news:name><?php echo htmlspecialchars($this->cparams->get( 'gnews_publication_name', JFactory::getApplication()->getCfg('sitename'))); ?></news:name>
<news:language><?php echo $this->sysLang; ?></news:language>
</news:publication>
<?php if(!in_array('', $this->sourceparams->get ( 'gnews_genres', array('')))): ?>
<news:genres><?php echo implode(', ', $this->sourceparams->get ( 'gnews_genres' ));?></news:genres>
<?php endif; ?>
<news:publication_date><?php $dateObj = new JDate($publish_up); $dateObj->setTimezone(new DateTimeZone($this->globalConfig->get('offset')));echo $dateObj->toISO8601(true);?></news:publication_date>
<news:title><?php echo htmlspecialchars($this->source->data->title[$index]); ?></news:title>
</news:news>
</url>
<?php
	}
}