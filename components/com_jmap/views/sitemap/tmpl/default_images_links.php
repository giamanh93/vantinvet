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
$imagetitleProcessorRegexp = $this->sourceparams->get('imagetitle_processor', $this->cparams->get('imagetitle_processor', 'title|alt'));
$fakeImages = $this->cparams->get ( 'fake_images_processor', 0 );
$lazyloadImages = $this->cparams->get ( 'lazyload_images_processor', 0 );
$linkableCatsMode = $this->sourceparams->get ( 'linkable_content_cats', 1 );
$includeDescriptionOnly = $this->cparams->get ( 'include_description_only', 0 );
$cdnProtocol = $this->cparams->get ('cdnprotocol', '');

// Init exclusion
$imgFilterInclude = array();
if(trim($this->sourceparams->get ( 'images_filter_include', '' ))) {
	$imgFilterInclude = explode(',', $this->sourceparams->get ( 'images_filter_include', '' ));
}
$imgFilterExclude = array();
if(trim($this->sourceparams->get ( 'images_filter_exclude', 'pdf,print,email,templates' ))) {
	$imgFilterExclude = explode(',', $this->sourceparams->get ( 'images_filter_exclude', 'pdf,print,email,templates' ));
}

// Init and merge global inclusions filters data sources wide
if($globalImagesFilterInclude = trim($this->cparams->get ( 'images_global_filter_include', '' ))) {
	$imgGlobalFilterInclude = explode(',', $globalImagesFilterInclude);
	$imgFilterInclude = array_merge($imgFilterInclude, $imgGlobalFilterInclude);
}
// Init and merge global exclusions filters data sources wide
if($globalImagesFilterExclude = trim($this->cparams->get ( 'images_global_filter_exclude', '' ))) {
	$imgGlobalFilterExclude = explode(',', $globalImagesFilterExclude);
	$imgFilterExclude = array_merge($imgFilterExclude, $imgGlobalFilterExclude);
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
		$this->imagesOutputtedLinksBuffer = array();
		
		// HTTP Request to remote URL '$seolink' to get images
		$headers = array('Accept'=>'text/html', 'User-Agent'=>'Googlebot-Image/1.0');
		$HTTPResponse = $this->HTTPClient->get($link, $headers);
		$pageHtml = $HTTPResponse->body;
		// Images RegExp extraction
		$imagesArrayResults = array();
		preg_match_all ( $this->mainImagesRegex, $pageHtml, $imagesArrayResults );
		$imagesTags = $imagesArrayResults[0];
		$imagesLinks = $imagesArrayResults[4];
		
		// Crawl and merge images also from dummy gallery images?
		if($fakeImages) {
			preg_match_all ( '/(<a)([^>])*(href=["\']([^"\']+\.(jpg|gif|png))["\'])([^>])*/i', $pageHtml, $dummyImagesArrayResults );
			$dummyImagesTags = $dummyImagesArrayResults[0];
			$dummyImagesLinks = $dummyImagesArrayResults[4];
			$imagesTags = array_merge($imagesTags, $dummyImagesTags);
			$imagesLinks = array_merge($imagesLinks, $dummyImagesLinks);
		}
		
		// Crawl and merge images also from dummy gallery images?
		if($lazyloadImages) {
			preg_match_all ( '/(<img)([^>])*((data-src|data-lazyload)=["\']([^"\']+\.(jpg|gif|png))["\'])([^>])*/i', $pageHtml, $lazyloadImagesArrayResults );
			$lazyloadImagesTags = $lazyloadImagesArrayResults[5];
			$lazyloadImagesLinks = $lazyloadImagesArrayResults[5];
			$imagesTags = array_merge($imagesTags, $lazyloadImagesTags);
			$imagesLinks = array_merge($imagesLinks, $lazyloadImagesLinks);
		}
		
$bufferImages = null;
if(!empty($imagesLinks)):
ob_start();
foreach ($imagesLinks as $index=>$imageLink):
// Skip outputting
if(array_key_exists($imageLink, $this->imagesOutputtedLinksBuffer)) {
	continue;
}
$validImage = true;
$found = false;
$optionalImageTitle = false;
// Extended images filtering include
if(is_array($imgFilterInclude) && count($imgFilterInclude)):
	foreach ($imgFilterInclude as $filterInclude) :
		if(strstr($imagesTags[$index], trim($filterInclude))) {
			$found = true;
			break;
		}
	endforeach;
if(!$found):
$validImage = false;
endif;
endif;

// Extended images filtering exclude
if(is_array($imgFilterExclude) && count($imgFilterExclude)):
	foreach ($imgFilterExclude as $filterExclude) :
		if(strstr($imagesTags[$index], trim($filterExclude))) {
			$validImage = false;
			break;
		}
	endforeach;
endif;
// Image not valid so don't insert in sitemap
if(!$validImage)
	continue;
// Check for optional image title
if(stristr($imagesTags[$index], 'title=') || stristr($imagesTags[$index], 'alt=')) {
	$optionalTitleMatches = array();
	// Crawl and merge images also from dummy gallery images?
	if($fakeImages) {
		preg_match('/(<img|<a)([^>])*(('.$imagetitleProcessorRegexp.')=(["\'])([^\5]*)\5)/iU', $imagesTags[$index], $optionalTitleMatches);
	} else {
		preg_match('/(<img)([^>])*(('.$imagetitleProcessorRegexp.')=(["\'])([^\5]*)\5)/iU', $imagesTags[$index], $optionalTitleMatches);
	}
	if(!empty($optionalTitleMatches[6])) {
		$optionalImageTitle = '<image:title>' . htmlspecialchars(html_entity_decode($optionalTitleMatches[6], null, 'UTF-8'), null, 'UTF-8', false) . '</image:title>' . PHP_EOL;
	}
}
// Check if image description is required and missing
if(!$optionalImageTitle && $includeDescriptionOnly) {
	continue;
}
$this->imagesOutputtedLinksBuffer[$imageLink] = true;
?>
<image:image>
<image:loc><?php echo htmlspecialchars(preg_match('/^http|^\/\//i', $imageLink) ? ($cdnProtocol && strpos($imageLink, 'http') === false ? $cdnProtocol . $imageLink : $imageLink) : $this->liveSite . '/' . ltrim($imageLink, '/'), null, 'UTF-8', false);?></image:loc>
<?php echo $optionalImageTitle;?>
</image:image>
<?php 
endforeach;
$bufferImages = ob_get_clean();
endif;

// Se sono presenti immagini
if(isset($bufferImages) && !empty($bufferImages)):
?>
<url>
<loc><?php echo htmlspecialchars($link, null, 'UTF-8', false); ?></loc>
<?php echo $bufferImages; ?>
</url>
<?php 
endif;
	}
}