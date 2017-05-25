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

/**
 * Requires fields from the plugin data source:
 * - jsitemap_rss_desc
 * - catname
 * - publish_up
 */

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
		// Skip outputting
		if(!isset($elm->jsitemap_rss_desc)) {
			continue;
		}
		if(array_key_exists($elm->link, $this->outputtedLinksBuffer)) {
			continue;
		}
		
		// Else store to prevent duplication
		$this->outputtedLinksBuffer[$elm->link] = true;

		// Exclude plugins placeholders if required
		if(is_array($excludeWords)) {
			$elm->jsitemap_rss_desc = str_replace($excludeWords, '', $elm->jsitemap_rss_desc);
		}
?>
<item>
<title><?php echo htmlspecialchars($elm->title, ENT_COMPAT, 'UTF-8'); ?></title>
<link><?php echo str_replace(' ', '%20', $this->liveSite . $elm->link ); ?></link>
<guid isPermaLink="true"><?php echo str_replace(' ', '%20', $this->liveSite . $elm->link ); ?></guid>
<description><![CDATA[<?php echo str_replace(array('<![CDATA[', ']]>'), '', $this->relToAbsLinks($elm->jsitemap_rss_desc));?>]]></description>
<category><?php echo isset($elm->catname) ? htmlspecialchars($elm->catname, ENT_COMPAT, 'UTF-8') : null;?></category>
<pubDate><?php $dateObj = new JDate($elm->publish_up); $dateObj->setTimezone(new DateTimeZone($this->globalConfig->get('offset')));echo htmlspecialchars($dateObj->toRFC822(true), ENT_COMPAT, 'UTF-8');?></pubDate>
</item>
<?php
	}
}