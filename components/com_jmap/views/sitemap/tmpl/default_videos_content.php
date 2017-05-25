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

$showPageBreaks = $this->cparams->get ( 'show_pagebreaks', 1 );

// Get default menu - home and check if a single article is linked, if so skip to avoid duplicated content
$homeArticleID = false;
$defaultMenu = $this->application->getMenu()->getDefault(JFactory::getLanguage()->getTag());
if(	isset($defaultMenu->query['option']) &&
	isset($defaultMenu->query['view']) &&
	$defaultMenu->query['option'] == 'com_content' &&
	$defaultMenu->query['view'] == 'article') {
	$homeArticleID = (int)$defaultMenu->query['id'];
}

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

if (count ( $this->source->data ) != 0) {
	require_once (JPATH_BASE . '/components/com_content/helpers/route.php');
	foreach ( $this->source->data as $elm ) {
		// Element category empty da right join
		if(!$elm->id) {
			continue;
		}
		
		// Article found as linked to home, skip and avoid duplicate link
		if((int)$elm->id === $homeArticleID) {
			continue;
		}
		
		// Check to ensure is counting valid request
		if(!$this->HTTPClient->isValidRequest()) {
			break;
		}
		
		$elm->slug = $elm->alias ? ($elm->id . ':' . $elm->alias) : $elm->id;
		$seflink = JRoute::_ ( ContentHelperRoute::getArticleRoute ( $elm->slug, $elm->catslug, $elm->language ) );

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

// Expandible content crawling feature
if(!empty($elm->expandible) && $showPageBreaks) {
	foreach ($elm->expandible as $index=>$subPageBreak) {
		$seflink = JRoute::_ ( ContentHelperRoute::getArticleRoute ( $elm->slug, $elm->catslug, $elm->language ) . '&limitstart=' . ($index + 1));

		// HTTP Request to remote URL to get videos
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
	}
}