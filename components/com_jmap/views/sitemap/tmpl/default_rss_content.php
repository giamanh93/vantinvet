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

// Get default menu - home and check if a single article is linked, if so skip to avoid duplicated content
$homeArticleID = false;
$defaultMenu = $this->application->getMenu()->getDefault(JFactory::getLanguage()->getTag());
if(	isset($defaultMenu->query['option']) &&
	isset($defaultMenu->query['view']) &&
	$defaultMenu->query['option'] == 'com_content' &&
	$defaultMenu->query['view'] == 'article') {
	$homeArticleID = (int)$defaultMenu->query['id'];
}

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
	require_once (JPATH_BASE . '/components/com_content/helpers/route.php');
	foreach ( $this->source->data as $index=>$elm ) {
		// Check if valid iteration
		if($this->limitRecent) {
			if($index < $this->limitRecent) {} else {break;}
		}
		
		// Element category empty da right join
		if(!$elm->id) {
			continue;
		}
		
		// Article found as linked to home, skip and avoid duplicate link
		if((int)$elm->id === $homeArticleID) {
			continue;
		}
		
		$elm->slug = $elm->alias ? ($elm->id . ':' . $elm->alias) : $elm->id;
		$seolink = JRoute::_ ( ContentHelperRoute::getArticleRoute ( $elm->slug, $elm->catslug, $elm->language  ) );

		// Skip outputting
		if(array_key_exists($seolink, $this->outputtedLinksBuffer)) {
			continue;
		}
		// Else store to prevent duplication
		$this->outputtedLinksBuffer[$seolink] = true;
		 
		// Normalize and fallback publish up - publication date fields
		$elm->publish_up = (isset($elm->publish_up) && $elm->publish_up && $elm->publish_up != '0000-00-00 00:00:00' && $elm->publish_up != -1) ? $elm->publish_up : gmdate('Y-m-d\TH:i:s\Z', time());

		// Exclude plugins placeholders if required
		if(is_array($excludeWords)) {
			$elm->jsitemap_rss_desc = str_replace($excludeWords, '', $elm->jsitemap_rss_desc);
		}
		
		// Process plugins placeholders if required
		if($this->cparams->get('rss_process_content_plugins', 0)) {
			JPluginHelper::importPlugin('content');
			$dispatcher = JEventDispatcher::getInstance();
			$dummyParams = new JRegistry();
			$elm->text = $elm->jsitemap_rss_desc;
			$dispatcher->trigger('onContentPrepare', array ('com_content.article', &$elm, &$dummyParams, 0));
			$elm->jsitemap_rss_desc = $elm->text;
		}
?>
<item>
<title><?php echo htmlspecialchars($elm->title, ENT_COMPAT, 'UTF-8'); ?></title>
<link><?php echo str_replace(' ', '%20', $this->liveSite . $seolink ); ?></link>
<guid isPermaLink="true"><?php echo str_replace(' ', '%20', $this->liveSite . $seolink ); ?></guid>
<description><![CDATA[<?php echo str_replace(array('<![CDATA[', ']]>'), '', $this->relToAbsLinks($elm->jsitemap_rss_desc));?>]]></description>
<category><?php echo htmlspecialchars($elm->category, ENT_COMPAT, 'UTF-8');?></category>
<pubDate><?php $dateObj = new JDate($elm->publish_up); $dateObj->setTimezone(new DateTimeZone($this->globalConfig->get('offset')));echo htmlspecialchars($dateObj->toRFC822(true), ENT_COMPAT, 'UTF-8');?></pubDate>
</item>
<?php
	}
}