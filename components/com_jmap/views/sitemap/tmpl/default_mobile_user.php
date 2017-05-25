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

// Include common template init
include 'default_common_user.php';

if (count ( $this->source->data ) != 0) {  
	foreach ( $this->source->data as $elm ) {
		$title = isset($titleIdentifier) &&  $titleIdentifier != ''  ? $elm->{$titleIdentifier} : null;
		
		// Additional fields
		$additionalQueryStringFromObjectProp = null;
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
		
		// Skip outputting
		if(array_key_exists($seflink, $this->outputtedLinksBuffer)) {
			continue;
		}
		// Else store to prevent duplication
		$this->outputtedLinksBuffer[$seflink] = true;
		?>
<url>
<loc><?php echo $this->liveSite . $seflink; ?></loc>
<mobile:mobile/>
</url>
<?php 
	}
}