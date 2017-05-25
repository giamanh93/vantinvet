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
defined('_JEXEC') or die('Restricted access');

// Additional query string params
$additionalQueryStringParams =  $this->sourceparams->get ( 'additionalquerystring', null);
$sefItemid = $this->sourceparams->get ( 'sef_itemid', null);
$guessItemid = $this->sourceparams->get ( 'guess_sef_itemid', 0);

if($sefItemid > 0 && !$guessItemid) {
	$additionalQueryStringParams .= ',Itemid=' . $sefItemid;
}
$additionalQueryStringParams = trim($additionalQueryStringParams, ',');
if($additionalQueryStringParams) {
	$additionalQueryStringParams = '&' . preg_replace('/,\s*/i', '&', $additionalQueryStringParams);
	$additionalQueryStringParams =  preg_replace('/\s+/i', '', $additionalQueryStringParams);
}

// SEF links replacements
$sefLinksReplacements = false;
$sefLinksSourceReplacements = trim($this->sourceparams->get ( 'sef_links_replacements_source', ''));
$sefLinksTargetReplacements = trim($this->sourceparams->get ( 'sef_links_replacements_target', ''));
if($this->sourceparams->get ( 'enable_sef_links_replacements', 0) && $sefLinksSourceReplacements) {
	$sefLinksReplacements['source'] = explode(',', $sefLinksSourceReplacements);
	$sefLinksReplacements['target'] = explode(',', $sefLinksTargetReplacements);
}

$targetOption = $this->source->chunks->option;
$targetViewName = $this->sourceparams->get ( 'view', null );
$targetView = $targetViewName ? '&view=' . $targetViewName : null;

// Supported adapters for Router Helper
$supportedRouterHelperAdapters = array(	'com_k2'=>false,
										'com_easyblog'=>false,
										'com_easydiscuss'=>false,
										'com_contact'=>false,
										'com_weblinks'=>false,
										'com_newsfeeds'=>false,
										'com_tags'=>false,
										'com_hwdmediashare'=>false,
										'com_eventbooking'=>false,
										'com_edocman'=>false,
										'com_phocadownload'=>false,
										'com_ezrealty'=>false,
										'com_iproperty'=>false,
										'com_djcatalog2'=>false,
										'com_jomestate'=>false,
										'com_eshop'=>false,
										'com_jomdirectory'=>false,
										'com_dms'=>false,
										'com_digicom'=>false);
$supportedRouterHelperAdaptersPaths = array('com_eventbooking'=>'helper',	
											'com_edocman'=>'helper',
											'com_dms'=>'helper');
$supportedRouterHelperAdaptersFiles = array('com_easyblog'=>'router', 'com_easydiscuss'=>'router');
$supportedRouterHelperAdaptersDependencies = array('com_easyblog'=>'constants.php', 'com_easydiscuss'=>'constants.php');
$missingDependencies = false;

// Adapter for com_eshop
if($targetOption == 'com_eshop') {
	if(version_compare(JVERSION, '3.0', 'ge') && JMapLanguageMultilang::isEnabled()) {
		$supportedRouterHelperAdaptersFiles['com_eshop'] = 'routev3';
	}
}

if(array_key_exists($targetOption, $supportedRouterHelperAdapters)) {
	$folderPath = array_key_exists($targetOption, $supportedRouterHelperAdaptersPaths) ? $supportedRouterHelperAdaptersPaths[$targetOption] : 'helpers';
	$filePath = array_key_exists($targetOption, $supportedRouterHelperAdaptersFiles) ? $supportedRouterHelperAdaptersFiles[$targetOption] : 'route';
	
	// Check for frontend dependencies
	$dependencies = array_key_exists($targetOption, $supportedRouterHelperAdaptersDependencies) ? $supportedRouterHelperAdaptersDependencies[$targetOption] : null;
	if($dependencies) {
		$missingDependencies = file_exists(JPATH_SITE . '/components/'.$targetOption.'/'.$dependencies) ? false : true;
	}
	
	if(file_exists(JPATH_SITE . '/components/'.$targetOption.'/'.$folderPath.'/'.$filePath.'.php') && !$missingDependencies) {
		include_once JPATH_SITE . '/components/'.$targetOption.'/'.$folderPath.'/'.$filePath.'.php';
		$supportedRouterHelperAdapters[$targetOption] = true;
		$liveSite = $this->liveSite;
	}
	
	// Exception for Easyblog 5
	if(	$targetOption == 'com_easyblog' &&
		file_exists(JPATH_ADMINISTRATOR . '/components/'.$targetOption.'/includes/'.$filePath.'.php')) { // Not the frontend router as a standard but a proprietary admin router
		include_once JPATH_ADMINISTRATOR . '/components/'.$targetOption.'/includes/easyblog.php'; // Include base class
		include_once JPATH_ADMINISTRATOR . '/components/'.$targetOption.'/includes/'.$filePath.'.php'; // Finally include the router helper
		$supportedRouterHelperAdapters[$targetOption] = true;
		$liveSite = $this->liveSite;
	}
	
	// Exception for Easydiscuss 4
	if(	$targetOption == 'com_easydiscuss' &&
		file_exists(JPATH_ADMINISTRATOR . '/components/'.$targetOption.'/includes/'.$filePath.'.php')) { // Not the frontend router as a standard but a proprietary admin router
		include_once JPATH_ADMINISTRATOR . '/components/'.$targetOption.'/includes/easydiscuss.php'; // Include base class
		include_once JPATH_ADMINISTRATOR . '/components/'.$targetOption.'/includes/'.$filePath.'.php'; // Finally include the router helper
		$supportedRouterHelperAdapters[$targetOption] = true;
		$liveSite = $this->liveSite;
	}
}

// Fallback identifiers
$titleIdentifier =  !empty($this->source->chunks->titlefield_as) ?  $this->source->chunks->titlefield_as :  $this->source->chunks->titlefield;
$idIdentifier = !empty($this->source->chunks->idfield_as) ?  $this->source->chunks->idfield_as :  $this->source->chunks->id;
$catidIdentifier = !empty($this->source->chunks->catidfield_as) ?  $this->source->chunks->catidfield_as : !empty($this->source->chunks->catid) ? $this->source->chunks->catid : null;
$idURLFilter = !empty($this->source->chunks->url_filter_id) ? true : false;
$catidURLFilter = !empty($this->source->chunks->url_filter_catid) ? true : false;
$mainTable = $this->source->chunks->table_maintable;

// Init array key diff fields standard
$arrayKeysDiff = array(	$titleIdentifier=>null,
		$this->asCategoryTitleField=>null,
		'jsitemap_level'=>null,
		'jsitemap_category_id'=>null,
		'jsitemap_rss_desc'=>null,
		'metakey'=>null,
		'publish_up'=>null,
		'modified'=>null);

// Used for HTML user format sitemap, it gives feature for multilevel nested tree
if(!function_exists('recurseCats')) {
	function recurseCats($id, 
						$itemsByCats, 
						$catChildrenByCats, 
						$level = 0, 
						$asCategoryTitleField, 
						$liveSite, 
						$targetOption, 
						$targetView, 
						$targetViewName,
						$additionalQueryStringParams, 
						$openTarget, 
						$arrayKeysDiff, 
						$titleIdentifier, 
						$idIdentifier, 
						$idURLFilter, 
						$catidIdentifier, 
						$catidURLFilter,
						$supportedRouterHelperAdapters,
						$guessItemid,
						$mainTable,
						$topLevelCategoryId,
						$marginSide,
						$sefLinksReplacements) {
		if(isset($catChildrenByCats[$id])) {
			foreach ( $catChildrenByCats[$id] as $catChild ) {
				$itemsOfCategory = isset ($itemsByCats[$catChild['id']]) ? ($itemsByCats[$catChild['id']]) : null;
				$catTitleName = $catChild['catname'] ;
				
				// Multilevel tree for items and parent containing cats
				if($asCategoryTitleField) {
					// Set for empty category root nodes that should not be clickable
					$noExpandableNode = count($itemsOfCategory) ? '' : ' noexpandable';
					echo '<ul class="jmap_filetree" style="' . $marginSide . $level * 15 .'px"><li class="' . $noExpandableNode . '"><span class="folder">' . $catTitleName . '</span>';
					echo '<ul>';
				} else {
					// Multilevel tree of categories itself
					$dataHash = null;
					$topLevelCategoryId = $level == 0 ? $catChild['id'] : $topLevelCategoryId;
					if($targetOption && $targetViewName && $topLevelCategoryId) {
						$dataHash = 'data-hash="' . $targetOption . '.' . $targetViewName . '.' . $topLevelCategoryId . '"';
					}
					echo '<ul ' . $dataHash . ' style="'. $marginSide . $level * 15 .'px">';
				}
				
				if(count($itemsOfCategory)) {
					foreach ($itemsOfCategory as $elm) {
						$title = isset($titleIdentifier) &&  $titleIdentifier != '' ? $elm->{$titleIdentifier} : null;
						// Additional fields
						$additionalParamsQueryString = null;
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

						echo '<li>' . '<a target="' . $openTarget . '" href="' .  $liveSite . $seflink . '" >' . $title . '</a></li>';
					}
				}
				echo '</ul>';
				if($asCategoryTitleField) {
					echo '</li></ul>';
				}
				recurseCats($catChild['id'], 
							$itemsByCats, 
							$catChildrenByCats, 
							$level+1, 
							$asCategoryTitleField, 
							$liveSite, 
							$targetOption, 
							$targetView,
							$targetViewName,
							$additionalQueryStringParams, 
							$openTarget, 
							$arrayKeysDiff, 
							$titleIdentifier, 
							$idIdentifier, 
							$idURLFilter, 
							$catidIdentifier, 
							$catidURLFilter,
							$supportedRouterHelperAdapters,
							$guessItemid,
							$mainTable,
							$topLevelCategoryId,
							$marginSide,
							$sefLinksReplacements);
			}
		}
	}
}