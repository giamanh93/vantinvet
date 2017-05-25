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

$includeExternalLinks =  $this->sourceparams->get ( 'include_external_links', 1 );

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

// Get menus object
$menusArray = $this->application->getMenu()->getMenu();

if (count ( $this->source->data )) {
	foreach ( $this->source->data as $elm ) { 
		// Check to ensure is counting valid request
		if(!$this->HTTPClient->isValidRequest()) {
			break;
		}
		
		// Skip menu external links
		if($elm->type == 'url' && !$includeExternalLinks) {
			continue;
		}
		
		// Always skip external urls in XML sitemaps
		if($elm->type == 'url' && strpos($elm->link, $this->liveSite) === false) {
			continue;
		}
		
		// Avoid place link for separator and alias
		if(in_array($elm->type, array('separator', 'alias', 'heading'))) {
			continue;
		}
		
		$link = $elm->link;
		if (isset ( $elm->id )) {
			switch (@$elm->type) {
				case 'separator' :
				case 'alias' :
				case 'heading' :
					break;
				case 'url' :
					if (preg_match ( "#^/?index\.php\?#", $link )) {
						if (strpos ( $link, 'Itemid=' ) === FALSE) {
							if (strpos ( $link, '?' ) === FALSE) {
								$link .= '?Itemid=' . $elm->id;
							} else {
								$link .= '&amp;Itemid=' . $elm->id;
							}
						}
					}
					break;
				default :
					if (strpos ( $link, 'Itemid=' ) === FALSE) {
						$link .= '&amp;Itemid=' . $elm->id;
					}
					break;
			}
		}
		
		if (strcasecmp ( substr ( $link, 0, 9 ), 'index.php' ) === 0) {
			$link = JRoute::_ ( $link );
		}
		
		if (($elm->link == 'index.php') or strpos ( $elm->link, 'view=frontpage' )) { // HOME
			$link = '';
		}
		
		// SEF patch for better match uri con $link override
		if ($elm->type == 'component' && array_key_exists($elm->id, $menusArray)) {
			$link = 'index.php?Itemid=' . $elm->id;
			$link = JRoute::_ ( $link );
		}
		
		// Skip outputting
		if(array_key_exists($link, $this->outputtedLinksBuffer)) {
			continue;
		}
		// Else store to prevent duplication
		$this->outputtedLinksBuffer[$link] = true;
		
		// HTTP Request to remote URL to get videos
		$headers = array('Accept'=>'text/html', 'User-Agent'=>'Googlebot-Video/1.0');
		$HTTPResponse = $this->HTTPClient->get( preg_match('/^http/i', $link) ? $link : $this->liveSiteCrawler . (strpos($link, '/') === 0 ? $link : '/'.$link), $headers);
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
<loc><?php echo preg_match('/^http/i', $link) ? htmlspecialchars($link, null, 'UTF-8', false) : $this->liveSite . htmlspecialchars($link, null, 'UTF-8', false); ?></loc>
<?php echo $bufferVideos; ?>
</url>
<?php 
endif;
	}
}