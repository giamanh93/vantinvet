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

// Inject custom styles for mindmap template
$minheightRootFolders = $this->cparams->get('minheight_root_folders', 35) . 'px';
$minheightSubFolders = $this->cparams->get('minheight_sub_folders', 30) . 'px';
$minheightLeaf = $this->cparams->get('minheight_leaf', 20) . 'px';
$minWidthColumns = $this->cparams->get('minwidth_columns', 120) . 'px';
$fontSizeBoxes = $this->cparams->get('font_size_boxes', 12) . 'px';
$rootFoldersColor = $this->cparams->get('root_folders_color', '#F60');
$rootFoldersBorderColor = $this->cparams->get('root_folders_border_color', '#943B00');
$rootFoldersTextColor = $this->cparams->get('root_folders_text_color', '#FFF');
$subFoldersColor = $this->cparams->get('sub_folders_color', '#99CDFF');
$subFoldersBorderColor = $this->cparams->get('sub_folders_border_color', '#11416F');
$subFoldersTextColor = $this->cparams->get('sub_folders_text_color', '#11416F');
$leafFoldersColor = $this->cparams->get('leaf_folders_color', '#EBEBEB');
$leafFoldersBorderColor = $this->cparams->get('leaf_folders_border_color', '#6E6E6E');
$leafFoldersTextColor = $this->cparams->get('leaf_folders_text_color', '#505050');
$connectionsColor = $this->cparams->get('connections_color', '#CCC');
$expandIconset = $this->cparams->get('expand_iconset', 'square-blue');
$pathImgs = JUri::root() . 'components/com_jmap/js/images';
$this->document->addStyleDeclaration(
"#jmap_sitemap div.jmapcolumn>ul>li>span.folder{min-height:$minheightRootFolders;}
#jmap_sitemap div.jmapcolumn span.folder{min-height:$minheightSubFolders;}
#jmap_sitemap div.jmapcolumn ul li a{min-height:$minheightLeaf;}
#jmap_sitemap div.jmapcolumn>ul{min-width:$minWidthColumns;}
#jmap_sitemap div.jmapcolumn>ul{font-size:$fontSizeBoxes;}
#jmap_sitemap div.jmapcolumn>ul>li>span.folder{background-color:$rootFoldersColor;border:2px solid $rootFoldersBorderColor;color:$rootFoldersTextColor;}
#jmap_sitemap div.jmapcolumn span.folder{background-color:$subFoldersColor;border:2px solid $subFoldersBorderColor;color:$subFoldersTextColor;}
#jmap_sitemap div.jmapcolumn ul li a{background-color:$leafFoldersColor;border:2px solid $leafFoldersBorderColor;color:$leafFoldersTextColor;}
#jmap_sitemap ul.treeview>li>ul.jmap_filetree:last-child li.expandable:before,
#jmap_sitemap ul.treeview>li>ul.jmap_filetree:last-child li.last:before,
#jmap_sitemap div.jmapcolumn>ul.treeview>li>ul:last-child li.last:before,
#jmap_sitemap div.jmapcolumn>ul.treeview>li>ul:last-child li.expandable:last-child:before,
#jmap_sitemap div.jmapcolumn>ul>li.lastCollapsable,
#jmap_sitemap .treeview div.lastCollapsable-hitarea,#jmap_sitemap .treeview div.lastExpandable-hitarea,
#jmap_sitemap ul.treeview>li.lastCollapsable ul li:before{border-color:$connectionsColor}
#jmap_sitemap div.expandable-hitarea{background:url($pathImgs/toggle-expand-$expandIconset.png) no-repeat;background-position: 6px;}
#jmap_sitemap div.collapsable-hitarea{background:url($pathImgs/toggle-collapse-$expandIconset.png) no-repeat;background-position: 6px;}");
