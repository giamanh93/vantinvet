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

if (count ( $this->source->data ) != 0) {
	foreach ( $this->source->data as $item ) {
		if($this->cparams->get('gnews_limit_recent', false) && isset($item->publish_up)) {
			$itemPeriod = time() - strtotime($item->publish_up);
			$itemPeriod = (int)(round($itemPeriod / 60 / 60 / 24));
		 	if($itemPeriod > 2) {
		 		continue;
		 	}
		}

		// Skip outputting
		if(array_key_exists($item->link, $this->outputtedLinksBuffer)) {
			continue;
		}
		// Else store to prevent duplication
		$this->outputtedLinksBuffer[$item->link] = true;
		
		// Normalize and fallback publish up - publication date fields
		$item->publish_up = (isset($item->publish_up) && $item->publish_up && $item->publish_up != '0000-00-00 00:00:00' && $item->publish_up != -1) ? $item->publish_up : gmdate('Y-m-d\TH:i:s\Z', time());
?>
<url>
<loc><?php echo $this->liveSite . $item->link; ?></loc>
<news:news>
<news:publication>
<news:name><?php echo htmlspecialchars($this->cparams->get( 'gnews_publication_name', JFactory::getApplication()->getCfg('sitename'))); ?></news:name>
<news:language><?php echo $this->sysLang; ?></news:language>
</news:publication>
<?php if(isset($item->access) && $item->access > 1): ?>
<news:access>Registration</news:access>
<?php endif; ?>
<?php if(!in_array('', $this->sourceparams->get ( 'gnews_genres', array('')))): ?>
<news:genres><?php echo implode(', ', $this->sourceparams->get ( 'gnews_genres' ));?></news:genres>
<?php endif; ?>
<news:publication_date><?php $dateObj = new JDate($item->publish_up); $dateObj->setTimezone(new DateTimeZone($this->globalConfig->get('offset')));echo $dateObj->toISO8601(true);?></news:publication_date>
<news:title><?php echo htmlspecialchars($item->title); ?></news:title>
<?php if(isset($item->metakey) && trim($item->metakey)):?>
<news:keywords><?php echo trim(htmlspecialchars($item->metakey)); ?></news:keywords>
<?php endif; ?>
</news:news>
</url>
<?php
	}
}