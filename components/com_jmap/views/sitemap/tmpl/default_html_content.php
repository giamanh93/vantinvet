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

$catsave = null;
$close = '';
$showPageBreaks = $this->cparams->get ( 'show_pagebreaks', 1 );
$openTarget =  $this->sourceparams->get ( 'opentarget', $this->cparams->get ('opentarget') );
$linkableContentCats = $this->sourceparams->get ( 'linkable_content_cats', 0 );

// Check if mindmind template
$sitemapTemplate = $this->cparams->get('sitemap_html_template', '');
$isMindMap = $sitemapTemplate == 'mindmap' ? true : false;

if(($mergeMenuTreeMode = $this->sourceparams->get ( 'merge_menu_tree', null )) && !$isMindMap)  {
	$this->document->addScriptDeclaration('jmapMergeMenuTree["com_content"] = "' . $mergeMenuTreeMode . '";');
}
$mergeMenuTreeLevels = $this->sourceparams->get ( 'merge_menu_tree_levels', 'toplevel' );

// Get default menu - home and check if a single article is linked, if so skip to avoid duplicated content
$homeArticleID = false;
$defaultMenu = $this->application->getMenu()->getDefault(JFactory::getLanguage()->getTag());
if(	isset($defaultMenu->query['option']) &&
	isset($defaultMenu->query['view']) &&
	$defaultMenu->query['option'] == 'com_content' &&
	$defaultMenu->query['view'] == 'article') {
	$homeArticleID = (int)$defaultMenu->query['id'];
}

if (count ( $this->source->data ) != 0) {
	require_once (JPATH_BASE . '/components/com_content/helpers/route.php');
	$first = true;
	
	// If a containing folder is required
	$containingFolderTitle = $this->sourceparams->get('showtitle', 1) && trim($this->sourceparams->get('title', null));
	if($containingFolderTitle) {
		echo '<ul data-hash="com_content.container" class="jmap_filetree" style="' . $this->marginSide . '0px"><li><span class="folder">' . $this->sourceparams->get('title') . '</span>';
	}
	
	foreach ( $this->source->data as $elm ) {
		// Article found as linked to home, skip and avoid duplicate link
		if((int)$elm->id === $homeArticleID) {
			continue;
		}
		
		// Set for empty category root nodes that should not be clickable
		$noExpandableNode = $elm->id ? '' : ' noexpandable';
		$category = ($isMindMap || !$linkableContentCats) ? $elm->category : '<a target="' . $openTarget . '" href="' . JRoute::_ ( ContentHelperRoute::getCategoryRoute($elm->catid, $elm->language ) ) . '">' . $elm->category . '</a>';
		if($mergeMenuTreeLevels == 'toplevel') {
			$topLevelCategoryId = $elm->level > 1 ? @$topLevelCategoryId : $elm->catid;
		} else {
			$topLevelCategoryId = $elm->catid;
		}
		
		if ($elm->catid != $catsave && ! $first) {
			echo '</ul></li></ul>';
			echo '<ul data-hash="com_content.category.' . $topLevelCategoryId . '" class="jmap_filetree" style="' . $this->marginSide . (15 * ($elm->level - 1)) . 'px"><li class="' . $noExpandableNode . '"><span class="folder">' . $category . '</span>';
			echo '<ul>';
			$catsave = $elm->catid;
		} else {
			if ($first) {
				echo '<ul data-hash="com_content.category.' . $topLevelCategoryId . '" class="jmap_filetree" style="' . $this->marginSide . (15 * ($elm->level - 1)) . 'px"><li class="' . $noExpandableNode . '"><span class="folder">' . $category . '</span>';
				echo '<ul>';
				$first = false;
				$catsave = $elm->catid;
			}
		}
		
		$elm->slug = $elm->alias ? ($elm->id . ':' . $elm->alias) : $elm->id;
		$seolink = $this->liveSite . JRoute::_ ( ContentHelperRoute::getArticleRoute ( $elm->slug, $elm->catslug, $elm->language ) );

		echo '<li>';
		
		if($elm->id) {
			echo '<a target="' . $openTarget . '" href="' . $seolink . '" >' . $elm->title . '</a>';
		}
		
		if(!empty($elm->expandible) && $showPageBreaks) {
			echo '<ul>';
			foreach ($elm->expandible as $index=>$subPageBreak) {
				$seolink = $this->liveSite . JRoute::_ ( ContentHelperRoute::getArticleRoute ( $elm->slug, $elm->catslug, $elm->language ) . '&limitstart=' . ($index + 1));
				echo '<li>' . '<a target="' . $openTarget . '" href="' . $seolink . '" >' . $subPageBreak . '</a></li>';
			}
			echo '</ul>';
		}
		echo '</li>';
	}
	
	echo '</ul></li></ul>';
	
	// If a containing folder is required
	if($containingFolderTitle) {
		echo '</li></ul>';
	}
}