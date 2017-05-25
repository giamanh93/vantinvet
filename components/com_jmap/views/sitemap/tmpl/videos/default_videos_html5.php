<?php
/** 
 * @package JMAP::SITEMAP::components::com_jmap
 * @subpackage views
 * @subpackage sitemap
 * @subpackage tmpl
 * @subpackage videos 
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );

// Ensure that DOMDocument is enabled
if (!class_exists('DOMDocument') || !$this->htmlResponseReference) {
	return;
}

// Load the document
$doc = new DOMDocument('1.0', 'UTF-8');
libxml_use_internal_errors(true);
$doc->loadHTML($this->htmlResponseReference);
libxml_clear_errors();
$videos = $doc->getElementsByTagName('video');

// DOMNodeList iterator
foreach ($videos as $video) {
	if($video->hasAttributes()) {
		// Video is required
		$videoTitle = $video->getAttribute('title');
		if(!$videoTitle) {
			continue;
		}
		// Filters the video
		$this->videoTitle = $videoTitle;
		$this->loadTemplate('videos_filtering');
		
		// Only valid videos will be included in the sitemap
		if($this->validVideo){
			$videoDescription = $video->getAttribute('alt');
			$videoDescription = $videoDescription ? $videoDescription : $videoTitle;
			$videoPoster = $video->getAttribute('poster');
			$videoDuration = $video->getAttribute('data-duration');
			$childSources = $video->getElementsByTagName('source');
			$hasSources = false;
			foreach ($childSources as $childSource) {
				$hasSources = true;
				break;
			}
			
			// Video poster or sources are required
			if(!$videoPoster || !$hasSources) {
				continue;
			}
			
			// Prevent duplicated videos, calculate video hash
			$videoHash = strtolower(str_replace(' ', '', $videoTitle));
			// Skip outputting
			if(array_key_exists($videoHash, $this->outputtedVideosBuffer)) {
				continue;
			}
			// Else store to prevent videos duplication
			$this->outputtedVideosBuffer[$videoHash] = true;
?>
<video:video>
<video:thumbnail_loc><?php echo htmlspecialchars($videoPoster, ENT_COMPAT, 'UTF-8');?></video:thumbnail_loc>
<video:title><?php echo htmlspecialchars($videoTitle, ENT_COMPAT, 'UTF-8');?></video:title>
<video:description><![CDATA[<?php echo substr($videoDescription, 0, 2048);?>]]></video:description>
<?php 	
			$childSources = $video->childNodes;
			foreach ($childSources as $source) {
				// Embed always only the first source tag encontered to ensure XML validation
				if($source->nodeName == 'source' && $source->hasAttributes()) {
					$videoSrc = $source->getAttribute('src');
?>
<video:player_loc allow_embed="yes" autoplay="ap=1"><?php echo $videoSrc;?></video:player_loc>
<?php
break;
				}
			}
?>
<?php if($videoDuration):?>
<video:duration><?php echo $videoDuration;?></video:duration>
<?php endif;?>
<video:live>no</video:live>
</video:video> 
<?php
		}
	}
}