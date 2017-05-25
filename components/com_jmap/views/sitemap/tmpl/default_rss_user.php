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

// Include common template init
include 'default_common_user.php';

// Get exclude words if any
$excludeWords = $this->cparams->get('rss_channel_excludewords', null);
if($excludeWords) {
	$excludeWords = explode(',', $excludeWords);
	// Recognize plugins syntax and auto-add closing
	if(is_array($excludeWords)) {
		foreach ($excludeWords as $word) {
			preg_match('/\{.+\}/iU', $word, $result);
			if(isset($result[0])) {
				$excludeWords[] = str_replace('{', '{/', $result[0]);
			}
		}
	}
}

if (count ( $this->source->data ) != 0) {
	foreach ( $this->source->data as $index=>$elm ) {
		// Check if valid iteration
		if($this->limitRecent) {
			if($index < $this->limitRecent) {} else {break;}
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

		// Exclude plugins placeholders if required
		if(is_array($excludeWords)) {
			$elm->jsitemap_rss_desc = str_replace($excludeWords, '', $elm->jsitemap_rss_desc);
		}
?>
<item>
<title><?php echo htmlspecialchars($title, ENT_COMPAT, 'UTF-8'); ?></title>
<link><?php echo str_replace(' ', '%20', $this->liveSite . $seflink ); ?></link>
<guid isPermaLink="true"><?php echo str_replace(' ', '%20', $this->liveSite . $seflink ); ?></guid>
<description><![CDATA[<?php echo str_replace(array('<![CDATA[', ']]>'), '', $this->relToAbsLinks($elm->jsitemap_rss_desc));?>]]></description>
<category><?php echo isset($elm->{$this->asCategoryTitleField}) ? htmlspecialchars($elm->{$this->asCategoryTitleField}, ENT_COMPAT, 'UTF-8') : null;?></category>
<pubDate><?php $dateObj = new JDate($elm->publish_up); $dateObj->setTimezone(new DateTimeZone($this->globalConfig->get('offset')));echo htmlspecialchars($dateObj->toRFC822(true), ENT_COMPAT, 'UTF-8');?></pubDate>
</item>
<?php
	}
}