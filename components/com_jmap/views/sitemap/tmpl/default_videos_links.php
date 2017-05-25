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

$linkableCatsMode = $this->sourceparams->get ( 'linkable_content_cats', 1 );

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

// Inject items links
if (isset($this->source->data->link) && count ( $this->source->data->link ) != 0) {  
	foreach ( $this->source->data->link as $index=>$link ) {
		// Check to ensure is counting valid request
		if(!$this->HTTPClient->isValidRequest()) {
			break;
		}
		
		// Skip outputting
		$relativeLink = str_replace($this->liveSite, '', $link);
		if(array_key_exists($relativeLink, $this->outputtedLinksBuffer)) {
			continue;
		}
		// Else store to prevent duplication
		$this->outputtedLinksBuffer[$relativeLink] = true;
		
		// HTTP Request to remote URL to get videos
		$headers = array('Accept'=>'text/html', 'User-Agent'=>'Googlebot-Video/1.0');
		$HTTPResponse = $this->HTTPClient->get($link, $headers);
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
<loc><?php echo htmlspecialchars($link, null, 'UTF-8', false); ?></loc>
<?php echo $bufferVideos; ?>
</url>
<?php 
endif;
	}
}