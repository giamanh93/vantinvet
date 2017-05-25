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
$showtitle = $this->sourceparams->get ( 'showtitle', 1 );
$openTarget = $this->sourceparams->get ( 'opentarget', $this->cparams->get ('opentarget') );
$linkableCatsMode = $this->sourceparams->get ( 'linkable_content_cats', 1 );

if (! $showtitle) {
	$sourceTitle = '&nbsp;';
}

if (count ( $this->source->data )) {
	// 2) + 3) If categorization detected for datasource elements according to adiacency/multi adiacency setup, Feature Detection
	if(isset($this->source->itemsTree) && isset($this->source->categoriesTree)) {
		// Used for HTML user format sitemap, it gives feature for multilevel nested tree
		if(!function_exists('recursePluginCats')) {
			function recursePluginCats($id,
					$itemsByCats,
					$catChildrenByCats,
					$level = 0,
					$liveSite,
					$openTarget,
					$linkableCatsMode,
					$marginSide) {
				if(isset($catChildrenByCats[$id])) {
					foreach ( $catChildrenByCats[$id] as $catChild ) {
						$itemsOfCategory = isset ($itemsByCats[$catChild->category_id]) ? ($itemsByCats[$catChild->category_id]) : null;
						// Multilevel tree for items and parent containing cats
						// Set for empty category root nodes that should not be clickable
						$noExpandableNode = count($itemsOfCategory) ? '' : ' noexpandable';
						$categoryItem = $linkableCatsMode ? '<a target="' . $openTarget . '" href="' .  $liveSite . $catChild->category_link . '" >' . $catChild->category_title . '</a>' : $catChild->category_title;
						echo '<ul class="jmap_filetree" style="' . $marginSide . $level * 15 .'px"><li class="' . $noExpandableNode . '">';
						echo '<span class="folder">' . $categoryItem . '</span>';
						echo '<ul>';
		
						if(count($itemsOfCategory)) {
							foreach ($itemsOfCategory as $itemOfCategory) {
								echo '<li>' . '<a target="' . $openTarget . '" href="' .  $liveSite . $itemOfCategory->link . '" >' . $itemOfCategory->title . '</a></li>';
							}
						}
						echo '</ul></li></ul>';
						recursePluginCats($catChild->category_id,
							$itemsByCats,
							$catChildrenByCats,
							$level+1,
							$liveSite,
							$openTarget,
							$linkableCatsMode,
							$marginSide);
					}
				}
			}
		}
		echo '<ul class="jmap_filetree"><li><span class="folder">' . $sourceTitle. '</span>';
		// Recursive function for plugins, start building tree
		recursePluginCats(0, 
					$this->source->itemsTree, 
					$this->source->categoriesTree, 
					0, 
					$this->liveSite, 
					$openTarget,
					$linkableCatsMode,
					$this->marginSide);
		echo '</li></ul>';
	} else {  // 1) No categorization detected for datasource elements
		echo '<ul class="jmap_filetree"><li><span class="folder">' . $sourceTitle. '</span><ul>';
		foreach ( $this->source->data as $item ) {
				echo '<li>' . '<a target="' . $openTarget . '" href="' . $this->liveSite . $item->link . '" >' . $item->title . '</a></li>';
			}
		echo '</ul></li></ul>';
	}
}