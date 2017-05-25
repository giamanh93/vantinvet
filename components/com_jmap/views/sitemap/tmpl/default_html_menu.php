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

$sourceTitle = $this->sourceparams->get ( 'title', $this->source->name );
$showtitle =  $this->sourceparams->get ( 'showtitle', 1 );
$openTarget =  $this->sourceparams->get ( 'opentarget', $this->cparams->get ('opentarget') );
$includeExternalLinks =  $this->sourceparams->get ( 'include_external_links', 1 );
$catKeys = array (
		'id' => 1,
		'catid' => 1,
		'cat_id' => 1,
		'category_id' => 1,
		'category' => 1,
		'list' => 1,
		'listcats' => 1,
		'virtuemart_category_id' => 1
);

if (! $showtitle) {
	$sourceTitle = '&nbsp;';
}
// Get menus object
$menusArray = $this->application->getMenu()->getMenu();

if (count ( $this->source->data )) {
	echo "\n";
	echo '<ul class="jmap_filetree jmap_filetree_menu"><li><span class="folder">' . $sourceTitle . '</span>';
	$lastlevel = 1;
	$actlevel = 1;
	echo '<ul>';
	$close = '</ul>';
	$liclose = '';
	 
	foreach ( $this->source->data as $elm ) {
		// Skip menu external links
		if($elm->type == 'url' && !$includeExternalLinks) {
			continue;
		}
		
		$link = $elm->link;
		if (isset ( $elm->id )) {
			switch (@$elm->type) {
				case 'separator' :
				case 'alias' :
				case 'heading' :
					$link = $elm->link = 'index.php';
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
		
		// SEF patch for menu alias
		if ($elm->type == 'alias' && array_key_exists($elm->id, $menusArray)) {
			$menuParams = json_decode($elm->params);
			$link = 'index.php?Itemid=' . $menuParams->aliasoptions;
			$link = JRoute::_ ( $link );
		}
		
		// Final subdesc to get always absolute url
		$link = preg_match('/^http/i', $link) ? $link : $this->liveSite . (strpos($link, '/') === 0 ? $link : '/'.$link) ;
		// Final sanitize security safe
		$link = htmlentities($link, null, 'UTF-8', false);
		
		// Avoid place link for separator and alias
		if(in_array($elm->type, array('separator', 'heading'))) {
			$link = 'javascript:void(0);" onclick="return false;';
		}
		
		// Parse uri link and get the view for the data-hash generation
		$dataHash = null;
		$mergeAliasHash = null;
		$uriParams = null;
		// Find category key in the query string
		$parsedUri = parse_url($elm->link, PHP_URL_QUERY);
		parse_str($parsedUri, $uriParams);
		$catKeyNameArray = array_intersect_key($uriParams, $catKeys);
		
		// Fallback to menu params
		if(!$catKeyNameArray && $elm->params) {
			$parsedParams = json_decode($elm->params, true);
			$catKeyNameArray = array_intersect_key($parsedParams, $catKeys);
		}
		// Generate hash if valid vars
		if(isset($uriParams['option']) && isset($uriParams['view']) && $catKeyNameArray) {
			$catidValue = isset($uriParams[key($catKeyNameArray)]) ? $uriParams[key($catKeyNameArray)] : $parsedParams[key($catKeyNameArray)];
			if(is_numeric($catidValue) && $catidValue > 0) {
				$dataHash = ' data-hash="' . $uriParams['option'] . '.' . $uriParams['view'] . '.' . $catidValue . '"';
			}
		}
		// Generate merge menu alias hash if valid and activated by config params
		if($this->mergeAliasMenu && $elm->type == 'alias') {
			$mergeAliasHash = ' data-merge="' . $link . '"';
		}
		
		$actlevel = $elm->sublevel;
		if ($lastlevel == $actlevel) {
			echo $liclose;
			echo '<li' . $dataHash . $mergeAliasHash . '>' . '<a target="' . $openTarget . '" href="' . $link . '">' . $elm->title . '</a>';
			$liclose = '</li>';
		} else {
			if ($lastlevel < $actlevel) {
				echo "<ul>\n";
				echo '<li' . $dataHash . $mergeAliasHash . '>' . '<a target="' . $openTarget . '" href="' . $link . '">' . $elm->title . '</a>';
				$liclose = '</li>';
			} else {
				$diff = $lastlevel - $actlevel;
				for($i = 1; $i <= $diff; $i ++) {
					echo "</li></ul>\n";
				}
				echo $liclose;
				echo '<li' . $dataHash . $mergeAliasHash . '>' . '<a target="' . $openTarget . '" href="' . $link . '">' . $elm->title . '</a>';
				$liclose = '</li>';
			}
		}
		$lastlevel = $elm->sublevel;
		echo "\n";
	}
	if ($lastlevel == 0) {
		echo $liclose;
		echo $close;
		echo $liclose;
		echo $close;
	} else {
		for($i = 0; $i <= $lastlevel; $i ++) {
			echo "</li> </ul>\n";
		}
	}
}