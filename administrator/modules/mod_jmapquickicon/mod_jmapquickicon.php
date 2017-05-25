<?php
/**
 * @author Joomla! Extensions Store
 * @package JMAP::modules::mod_jmap
 * @copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

// Manage partial language translations
$jLang = JFactory::getLanguage();
$jLang->load('com_jmap', JPATH_BASE . '/components/com_jmap', 'en-GB', true, true);
if($jLang->getTag() != 'en-GB') {
	$jLang->load('com_jmap', JPATH_BASE, null, true, false);
	$jLang->load('com_jmap', JPATH_BASE . '/components/com_jmap', null, true, false);
}
$doc = JFactory::getDocument();
$currentVersion = strval(simplexml_load_file(JPATH_BASE . '/components/com_jmap/jmap.xml')->version);
$doc->addScriptDeclaration ( 'function jmapCompareVersions(e,a){var r,t,c=">",n=0,s={dev:-6,alpha:-5,a:-5,beta:-4,b:-4,RC:-3,rc:-3,"#":-2,p:1,pl:1},u=function(e){return e=(""+e).replace(/[_\-+]/g,"."),e=e.replace(/([^.\d]+)/g,".$1.").replace(/\.{2,}/g,"."),e.length?e.split("."):[-8]},l=function(e){return e?isNaN(e)?s[e]||-7:parseInt(e,10):0};for(e=u(e),a=u(a),t=Math.max(e.length,a.length),r=0;t>r;r++)if(e[r]!==a[r]){if(e[r]=l(e[r]),a[r]=l(a[r]),e[r]<a[r]){n=-1;break}if(e[r]>a[r]){n=1;break}}if(!c)return n;switch(c){case">":case"gt":return n>0;case">=":case"ge":return n>=0;case"<=":case"le":return 0>=n;case"===":case"=":case"eq":return 0===n;case"<>":case"!==":case"ne":return 0!==n;case"":case"<":case"lt":return 0>n;default:return null}};jQuery.get("index.php?option=com_jmap&task=cpanel.getUpdates&format=raw",function(a){a&&"object"==typeof a&&(jQuery("span[data-bind=jmap_version]").html(a.latest),jmapCompareVersions(a.latest,"'.$currentVersion.'")?jQuery("i.icon-cancel, span[data-status=outdated]").show():jQuery("i.icon-checkmark, span[data-status=updated]").show())},"json");');

if (version_compare ( JVERSION, '3.0', '>=' )) : 
JHtml::_('jquery.framework');
?>
	<div class="sidebar-nav quick-icons">
		<h2 class="nav-header">JSitemap</h2>
		<ul class="nav nav-list">
		<li>
			<a href="<?php echo JRoute::_('index.php?option=com_jmap'); ?>">
				<img style="width:24px; height:24px;" alt="" src="<?php echo JUri::base() . 'components/com_jmap/images/jmap-32x32.png'?>" />
				<span><?php echo JText::_('COM_JMAP_CPANEL');?></span>
			</a>
			<a href="<?php echo JRoute::_('index.php?option=com_jmap&task=config.display'); ?>">
				<img style="width:24px; height:24px;" alt="" src="<?php echo JUri::base() . 'components/com_jmap/images/icon-32-config.png'?>" />
				<span><?php echo JText::_('COM_JMAP_CONFIG');?></span>
			</a>
			<a href="<?php echo JRoute::_('index.php?option=com_jmap'); ?>">
				<?php echo JText::_('COM_JMAP_MODULEPANEL_STATE');?>
			</a>
		</li>
		</ul>
	</div>
<?php else : 
$doc->addScript(JURI::root() . '/components/com_jmap/js/jquery.js');
?>
	<div id="cpanel">
		<div class="icon-wrapper" style="float:left;">
			<div class="icon">
				<a href="<?php echo JRoute::_('index.php?option=com_jmap'); ?>">
					<img style="width:48px; height:48px;" alt="" src="" />
					<span>test</span>
				</a>
			</div>
		</div>
	</div>
<?php endif; ?>
