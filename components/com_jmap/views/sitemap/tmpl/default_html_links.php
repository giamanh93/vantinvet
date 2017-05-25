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

if (isset($this->source->data->link) && count ( $this->source->data->link )) {
	echo '<ul class="jmap_filetree"><li><span class="folder">' . $sourceTitle. '</span><ul>';
	foreach ( $this->source->data->link as $index=>$link ) {
			echo '<li>' . '<a target="' . $openTarget . '" href="' . $link . '" >' . $this->source->data->title[$index] . '</a></li>';
		}
	echo '</ul></li></ul>';
}