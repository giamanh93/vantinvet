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

// Init exclusion
$videoFilterInclude = array();
if(trim($this->sourceparams->get ( 'videos_filter_include', '' ))) {
	$this->videoFilterInclude = explode(',', $this->sourceparams->get ( 'videos_filter_include', '' ));
}
$videoFilterExclude = array();
if(trim($this->sourceparams->get ( 'videos_filter_exclude', '' ))) {
	$this->videoFilterExclude = explode(',', $this->sourceparams->get ( 'videos_filter_exclude', '' ));
}

// Init and merge global inclusions filters data sources wide
if($globalVideosFilterInclude = trim($this->cparams->get ( 'videos_global_filter_include', '' ))) {
	$videoGlobalFilterInclude = explode(',', $globalVideosFilterInclude);
	$this->videoFilterInclude = array_merge($videoFilterInclude, $videoGlobalFilterInclude);
}
// Init and merge global exclusions filters data sources wide
if($globalVideosFilterExclude = trim($this->cparams->get ( 'videos_global_filter_exclude', '' ))) {
	$videoGlobalFilterExclude = explode(',', $globalVideosFilterExclude);
	$this->videoFilterExclude = array_merge($videoFilterExclude, $videoGlobalFilterExclude);
}

// Include common template init
include 'default_common_user.php';

if (count ( $this->source->data ) != 0) {  
	foreach ( $this->source->data as $elm ) {
		// Check to ensure is counting valid request
		if(!$this->HTTPClient->isValidRequest()) {
			break;
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
		
		// HTTP Request to remote URL to get videos
		$headers = array('Accept'=>'text/html', 'User-Agent'=>'Googlebot-Video/1.0');
		$HTTPResponse = $this->HTTPClient->get($this->liveSiteCrawler . $seflink, $headers);
		$pageHtml = $HTTPResponse->body;
		$this->htmlResponseReference = &$pageHtml;
		
		// Videos RegExp extraction
		$videosArrayResultsTotal = array();
		$videosArrayResultsYoutube = array();
		$videosArrayResultsVimeo = array();
		$videosArrayResultsDailymotion = array();
		preg_match_all ("/(youtube).*.com\/(v\/|watch\?v=|embed\/)([a-zA-Z0-9\-_]*)/", $pageHtml, $videosArrayResultsYoutube, PREG_SET_ORDER);
		preg_match_all ("/player.(vimeo).com\/video\/([a-z0-9\-]*)/", $pageHtml, $videosArrayResultsVimeo, PREG_SET_ORDER);
		preg_match_all ("/www.(dailymotion).com\/embed\/video\/([a-z0-9\-]*)/", $pageHtml, $videosArrayResultsDailymotion, PREG_SET_ORDER);
		$videosArrayResultsTotal = array_merge($videosArrayResultsYoutube, $videosArrayResultsVimeo, $videosArrayResultsDailymotion);
		
$bufferVideos = null;
ob_start();
foreach ($videosArrayResultsTotal as $index=>$videoElement):
$this->videoID = array_pop($videoElement);
// Prevent duplicated videos, calculate video hash
$videoHash = $videoElement[1] . $this->videoID;
// Skip outputting
if(array_key_exists($videoHash, $this->outputtedVideosBuffer)) {
	continue;
}
// Else store to prevent videos duplication
$this->outputtedVideosBuffer[$videoHash] = true;

$videoApiEndpoint = sprintf($this->videoApisEndpoints[$videoElement[1]], $this->videoID);
$HTTPResponse = $this->HTTPClient->get($videoApiEndpoint, array('Accept'=>'application/json'));
if(!$HTTPResponse->code == 200){continue;}
$this->apiJsonResponse = json_decode($HTTPResponse->body);
echo $this->loadTemplate('videos_' . $videoElement[1]);
endforeach;

// Always load html5 video parser at once
echo $this->loadTemplate('videos_html5');

$bufferVideos = ob_get_clean();

// If valid videos have been found and crawled let's build the video sitemap
if(isset($bufferVideos) && !empty($bufferVideos)):
?>
<url>
<loc><?php echo $this->liveSite . $seflink; ?></loc>
<?php echo $bufferVideos; ?>
</url>
<?php 
endif;
	}
}