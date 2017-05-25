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

// Include common template init
include 'default_common_user.php';

if (count ( $this->source->data ) != 0) {
	foreach ( $this->source->data as $elm ) {
		if($this->cparams->get('gnews_limit_recent', false) && isset($elm->publish_up)) {
			$itemPeriod = time() - strtotime($elm->publish_up);
			$itemPeriod = (int)(round($itemPeriod / 60 / 60 / 24));
		 	if($itemPeriod > 2) {
		 		continue;
		 	}
		}
		$title = isset($titleIdentifier) &&  $titleIdentifier != ''  ? $elm->{$titleIdentifier} : null;

		// Additional fields
		$additionalQueryStringFromObjectProp = null;
		$objectVars = array_diff_key(get_object_vars($elm), $arrayKeysDiff);
		// Filter URL safe alias fields id/catid
		if(isset($objectVars[$idIdentifier]) && $idURLFilter) {
			$objectVars[$idIdentifier] = JFilterOutput::stringURLSafe($objectVars[$idIdentifier]);
		}
		if(isset($objectVars[$catidIdentifier]) && $catidURLFilter) {
			$objectVars[$catidIdentifier] = JFilterOutput::stringURLSafe($objectVars[$catidIdentifier]);
		}
		if(is_array($objectVars) && count($objectVars)) {
			$additionalQueryStringFromObjectProp = '&' . http_build_query($objectVars);
		}

		if(isset($supportedRouterHelperAdapters[$targetOption]) && $supportedRouterHelperAdapters[$targetOption]) {
			include 'adapters/'.$targetOption.'.php';
		} else {
			$guessedItemid = null;
			if($guessItemid) {
				$guessedItemid = JMapRouteHelper::getItemRoute($targetOption, $targetViewName, $elm->{$idIdentifier}, $elm, $mainTable);
				if($guessedItemid) {
					$guessedItemid = '&Itemid=' . $guessedItemid;
				}
			}
			$seflink = JRoute::_ ( 'index.php?option=' . $targetOption . $targetView . $additionalQueryStringFromObjectProp . $additionalQueryStringParams . $guessedItemid);
		}

		// Manage SEF links replacements
		if($sefLinksReplacements) {
			$seflink = str_replace($sefLinksReplacements['source'], $sefLinksReplacements['target'], $seflink);
		}
		
		// Skip outputting
		if(array_key_exists($seflink, $this->outputtedLinksBuffer)) {
			continue;
		}
		// Else store to prevent duplication
		$this->outputtedLinksBuffer[$seflink] = true;
		
		// Normalize and fallback publish up - publication date fields
		$elm->publish_up = (isset($elm->publish_up) && $elm->publish_up && $elm->publish_up != '0000-00-00 00:00:00' && $elm->publish_up != -1) ? $elm->publish_up : gmdate('Y-m-d\TH:i:s\Z', time());
?>
<url>
<loc><?php echo $this->liveSite . $seflink; ?></loc>
<news:news>
<news:publication>
<news:name><?php echo htmlspecialchars($this->cparams->get( 'gnews_publication_name', JFactory::getApplication()->getCfg('sitename'))); ?></news:name>
<news:language><?php echo $this->sysLang; ?></news:language>
</news:publication>
<?php if(isset($elm->access) && $elm->access > 1): ?>
<news:access>Registration</news:access>
<?php endif; ?>
<?php if(!in_array('', $this->sourceparams->get ( 'gnews_genres', array('')))): ?>
<news:genres><?php echo implode(', ', $this->sourceparams->get ( 'gnews_genres' ));?></news:genres>
<?php endif; ?>
<news:publication_date><?php $dateObj = new JDate($elm->publish_up); $dateObj->setTimezone(new DateTimeZone($this->globalConfig->get('offset')));echo $dateObj->toISO8601(true);?></news:publication_date>
<news:title><?php echo htmlspecialchars($elm->title); ?></news:title>
<?php if(isset($elm->metakey) && trim($elm->metakey)):?>
<news:keywords><?php echo trim(htmlspecialchars($elm->metakey)); ?></news:keywords>
<?php endif; ?>
</news:news>
</url>
<?php
	}
}