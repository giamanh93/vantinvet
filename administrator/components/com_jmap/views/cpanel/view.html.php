<?php
// namespace administrator\components\com_jmap\views\cpanel;
/**
 * @package JMAP::CPANEL::administrator::components::com_jmap
 * @subpackage views
 * @subpackage cpanel
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );

/**
 * CPanel view
 *
 * @package JMAP::CPANEL::administrator::components::com_jmap
 * @subpackage views
 * @subpackage cpanel
 * @since 1.0
 */
class JMapViewCpanel extends JMapView {

	/**
	 * Render iconset for cpanel
	 *
	 * @param $link string
	 * @param $image string
	 * @access private
	 * @return string
	 */
	private function getIcon($link, $image, $text, $target = '', $title = null, $class = null) {
		$mainframe = JFactory::getApplication ();
		$lang = JFactory::getLanguage ();
		?>
		<div style="float:<?php echo ($lang->isRTL()) ? 'right' : 'left'; ?>;">
			<div class="icon">
				<a <?php echo $title . $class;?> <?php echo $target;?> href="<?php echo JFilterOutput::ampReplace($link); ?>">
					<img src="components/com_jmap/images/<?php echo $image;?>" />
					<span><?php echo $text; ?></span>
				</a>
			</div>
		</div>
		<?php
	}
	
	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */
	protected function addDisplayToolbar() {
		$doc = JFactory::getDocument();
		$doc->addStyleDeclaration('.icon-48-jmap{background-image:url("components/com_jmap/images/jmap-48x48.png")}');
		JToolBarHelper::title( JText::_('COM_JMAP_CPANEL_TOOLBAR' ), 'jmap' );
		JToolBarHelper::custom('cpanel.display', 'home', 'home', 'COM_JMAP_CPANEL', false);
	}
	
	/**
	 * Control panel display
	 *        	
	 * @access public
	 * @param string $tpl
	 * @return void
	 */
	public function display($tpl = null) {
		$doc = $this->document;
		$componentParams = $this->getModel()->getState('cparams');
		$base = JUri::root();
		$this->loadJQuery($doc);
		$this->loadBootstrap($doc);
		$doc->addStylesheet ( JURI::root ( true ) . '/administrator/components/com_jmap/css/cpanel.css' );
		$doc->addStylesheet ( JURI::root ( true ) . '/administrator/components/com_jmap/css/jquery.fancybox.css' );
		$doc->addScript ( JURI::root ( true ) . '/administrator/components/com_jmap/js/chart.js' );
		$doc->addScript ( JURI::root ( true ) . '/administrator/components/com_jmap/js/cpanel.js' );
		$doc->addScript ( JURI::root ( true ) . '/administrator/components/com_jmap/js/analyzer.js' );
		$doc->addScript ( JURI::root ( true ) . '/administrator/components/com_jmap/js/metainfo.js' );
		$doc->addScript ( JURI::root ( true ) . '/administrator/components/com_jmap/js/seospider.js' );
		
		if($componentParams->get('seostats_enabled', 1)) {
			$doc->addScript ( JURI::root ( true ) . '/administrator/components/com_jmap/js/seostats.js' );
		}
		
		if($componentParams->get('enable_precaching', 0)) {
			$doc->addScript ( JURI::root ( true ) . '/administrator/components/com_jmap/js/xmlprecaching.js' );
		}
		$doc->addCustomTag ('<script type="text/javascript" src="' . JURI::root ( true ) . '/administrator/components/com_jmap/js/jquery.fancybox.pack.js' . '"></script>');
		
		if($componentParams->get('geositemap_enabled', 0) && $componentParams->get('geositemap_address', null)) {
			$doc->addScript ( 'https://maps.google.com/maps/api/js?key=AIzaSyDNlp3wN1Al_ksW92rmb5Y96RQGn68tKb8' );
			$doc->addScript ( JURI::root ( true ) . '/administrator/components/com_jmap/js/gmap.js' );
			$doc->addScriptDeclaration("var jmap_geositemapAddress='" . addslashes($componentParams->get('geositemap_address', '')) . "';");
		}
		
		// Inject js translations
		$translations = array (	'COM_JMAP_ROBOTSPROGRESSTITLE',
							  	'COM_JMAP_ROBOTSPROGRESSSUBTITLE',
							  	'COM_JMAP_ROBOTSPROGRESSSUBTITLESUCCESS',
								'COM_JMAP_ROBOTSPROGRESSSUBTITLEERROR',
								'COM_JMAP_PRECACHING_TITLE',
								'COM_JMAP_START_PRECACHING_PROCESS',
								'COM_JMAP_PRECACHING_NO_DATASOURCES_FOUND',
								'COM_JMAP_PRECACHING_PROCESS_RUNNING',
								'COM_JMAP_PRECACHING_PROCESS_COMPLETED',
								'COM_JMAP_PRECACHING_REPORT_DATASOURCE',
								'COM_JMAP_PRECACHING_REPORT_DATASOURCE_TYPE',
								'COM_JMAP_PRECACHING_REPORT_LINKS',
								'COM_JMAP_PRECACHING_DATA_SOURCE_COMPLETED',
								'COM_JMAP_PRECACHING_DATASOURCES_RETRIEVED',
								'COM_JMAP_PRECACHING_PROCESS_FINALIZING',
								'COM_JMAP_PRECACHING_INTERRUPT',
								'COM_JMAP_PRECACHING_CACHED',
								'COM_JMAP_PRECACHING_NOT_CACHED',
								'COM_JMAP_PRECACHING_CLEARING',
								'COM_JMAP_PRECACHING_CLEAR_CACHE',
								'COM_JMAP_PUBLISHED_DATA_SOURCE_CHART',
								'COM_JMAP_TOTAL_DATA_SOURCE_CHART',
								'COM_JMAP_DATASETS_CHART',
								'COM_JMAP_MENU_DATA_SOURCE_CHART',
								'COM_JMAP_USER_DATA_SOURCE_CHART',
								'COM_JMAP_ANALYZER_TITLE',
								'COM_JMAP_ANALYZER_PROCESS_RUNNING',
								'COM_JMAP_ANALYZER_STARTED_SITEMAP_GENERATION',
								'COM_JMAP_ANALYZER_ERROR_STORING_FILE',
								'COM_JMAP_ANALYZER_GENERATION_COMPLETE',
								'COM_JMAP_METAINFO_TITLE',
								'COM_JMAP_METAINFO_PROCESS_RUNNING',
								'COM_JMAP_METAINFO_STARTED_SITEMAP_GENERATION',
								'COM_JMAP_METAINFO_ERROR_STORING_FILE',
								'COM_JMAP_METAINFO_GENERATION_COMPLETE',
								'COM_JMAP_SEOSTATS_LOADING',
								'COM_JMAP_ALEXA_GRAPH',
								'COM_JMAP_SEMRUSH_GRAPH',
								'COM_JMAP_NULL_RESPONSEDATA',
								'COM_JMAP_ERROR_HTTP',
								'COM_JMAP_CLICKTOUPDATE', 
								'COM_JMAP_EXPIREON',
								'COM_JMAP_UPDATEPROGRESSTITLE',
								'COM_JMAP_DOWNLOADING_UPDATE_SUBTITLE',
								'COM_JMAP_INSTALLING_UPDATE_SUBTITLE',
								'COM_JMAP_COMPLETED_UPDATE_SUBTITLE',
								'COM_JMAP_PINGING_SITEMAP_TOBAIDU',
								'COM_JMAP_PINGING_SITEMAP_TOBAIDU_PLEASEWAIT',
								'COM_JMAP_PINGING_SITEMAP_TOBAIDU_COMPLETE',
								'COM_JMAP_SEOSPIDER_TITLE',
								'COM_JMAP_SEOSPIDER_PROCESS_RUNNING',
								'COM_JMAP_SEOSPIDER_STARTED_SITEMAP_GENERATION',
								'COM_JMAP_SEOSPIDER_ERROR_STORING_FILE',
								'COM_JMAP_SEOSPIDER_GENERATION_COMPLETE',
								'COM_JMAP_CRONJOB_GENERATED_SITEMAP_FILE',
								'COM_JMAP_PING_SITEMAP_CRONJOB',						
								'COM_JMAP_ROBOTS_SITEMAP_ENTRY_CRONJOB',
								'COM_JMAP_PING_GOOGLE',
								'COM_JMAP_PING_BING',
								'COM_JMAP_PING_YANDEX',
								'COM_JMAP_PING_BAIDU');
		$this->injectJsTranslations($translations, $doc);
		
		// Check for custom link domain
		$customDomain = trim($componentParams->get('custom_sitemap_domain', ''));
		$livesite = $customDomain ? rtrim($customDomain, '/') : substr_replace(JUri::root(), "", -1, 1);
		
		if($customDomain) {
			$customHttpPort = trim($componentParams->get('custom_http_port', ''));
			$getPort = $customHttpPort ? ':' . $customHttpPort : null;
			if($getPort) {
				$livesite = rtrim($livesite . $getPort, '/');
			}
			
			$adminRoute = JRoute::_('index.php');
			$pathSubdomain = explode('/administrator', $adminRoute);
			if(!empty($pathSubdomain[0])) {
				$livesite = rtrim($livesite . $pathSubdomain[0], '/');
			}
		}
		
		$user = JFactory::getUser();
		
		$lists = $this->get('Lists');
		$infoData = $this->get('Data');
		$doc->addScriptDeclaration('var jmapChartData = ' . json_encode($infoData));
		$doc->addScriptDeclaration("var jmap_baseURI='$base';");
		$doc->addScriptDeclaration("var jmap_linksRandom=" . $componentParams->get('sitemap_links_random', 0) . ";");
		$doc->addScriptDeclaration("var jmap_forceFormat=" . $componentParams->get('sitemap_links_forceformat', 0) . ";");
		$doc->addScriptDeclaration("var jmap_validationAnalysis=" . $componentParams->get('linksanalyzer_validation_analysis', 2) . ";");
		$doc->addScriptDeclaration("var jmap_splittingStatus=" . $componentParams->get('split_sitemap', 0) . ";");
		$doc->addScriptDeclaration("var jmap_livesite='" . $livesite . "';");
		
		// Assign SEF mode
		$this->siteRouter = JRouterSite::getInstance('site', array('mode'=>JROUTER_MODE_SEF));
		$this->showSefLinks = $componentParams->get('sitemap_links_sef', false);
		$this->joomlaSefLinks = JFactory::getConfig()->get('sef', true);
		$this->siteItemid = null;
		if($this->showSefLinks && $this->joomlaSefLinks) {
			$siteItemid = trim($componentParams->get('site_itemid', null));
			if($siteItemid && is_numeric($siteItemid)) {
				$menuItem = JMenu::getInstance('site')->getItem((int)$siteItemid);
				if(isset($menuItem->alias)) {
					$menuAlias = $menuItem->alias;
					$doc->addScriptDeclaration("var jmap_sef_alias_links='$menuAlias';");
					$this->siteItemid = '&Itemid=' . (int)$siteItemid;
				}
			}
		}
		
		// Buffer delle icons
		ob_start ();
		$this->getIcon ( 'index.php?option=com_jmap&task=sources.display', 'icon-48-data.png', JText::_('COM_JMAP_SITEMAP_SOURCES' ), '', 'title="' . JText::_('COM_JMAP_SITEMAP_SOURCES' ) . '"');
		$this->getIcon ( 'index.php?option=com_jmap&task=wizard.display', 'icon-48-wizard.png', JText::_('COM_JMAP_NEW_WIZARD_DATASOURCE' ), '', 'title="' . JText::_('COM_JMAP_NEW_WIZARD_DATASOURCE' ) . '"');
		$this->getIcon ( $livesite . '/index.php?option=com_jmap&view=sitemap', 'icon-48-html_sitemap.png', JText::_('COM_JMAP_SHOW_HTML_MAP' ), 'target="_blank"', 'title="' . JText::_('COM_JMAP_SHOW_HTML_MAP' ) . '"', 'data-role="torefresh"' );
		$this->getIcon ( '#xmlsitemap', 'icon-48-xml_sitemap.png', JText::_('COM_JMAP_SHOW_XML_MAP' ), '', 'title="' . JText::_('COM_JMAP_SHOW_XML_MAP' ) . '"', 'class="fancybox"' );
		$this->getIcon ( '#xmlsitemap_xslt', 'icon-48-xsl_sitemap.png', JText::_('COM_JMAP_SHOW_XML_MAP_XSLT' ), '', 'title="' . JText::_('COM_JMAP_SHOW_XML_MAP_XSLT' ) . '"', 'class="fancybox"' );
		$this->getIcon ( '#xmlsitemap_export', 'icon-48-xml_export.png', JText::_('COM_JMAP_EXPORT_XML_SITEMAP' ), '', 'title="' . JText::_('COM_JMAP_EXPORT_XML_SITEMAP' ) . '"', 'class="fancybox"' );
		$this->getIcon ( substr_replace(JURI::root(), "", -1, 1) . '/index.php?option=com_jmap&task=sitemap.exportxml&format=xml', 'icon-48-analyze.png', JText::_('COM_JMAP_ANALYZE_MAP' ), '', 'title="' . JText::_('COM_JMAP_ANALYZE_MAP' ) . '"', 'class="jmap_analyzer"' );
		$this->getIcon ( substr_replace(JURI::root(), "", -1, 1) . '/index.php?option=com_jmap&task=sitemap.exportxml&format=xml', 'icon-48-seospider.png', JText::_('COM_JMAP_SEOSPIDER' ), '', 'title="' . JText::_('COM_JMAP_SEOSPIDER' ) . '"', 'class="jmap_seospider"' );
		$this->getIcon ( substr_replace(JURI::root(), "", -1, 1) . '/index.php?option=com_jmap&task=sitemap.exportxml&format=xml', 'icon-48-metainfo.png', JText::_('COM_JMAP_METAINFO' ), '', 'title="' . JText::_('COM_JMAP_METAINFO' ) . '"', 'class="jmap_metainfo"' );
		$this->getIcon ( 'index.php?option=com_jmap&task=indexing.display', 'icon-48-indexing.png', JText::_('COM_JMAP_SITEMAP_INDEXING' ), '', 'title="' . JText::_('COM_JMAP_SITEMAP_INDEXING' ) . '"');
		$this->getIcon ( 'index.php?option=com_jmap&task=datasets.display', 'icon-48-datasets.png', JText::_('COM_JMAP_SITEMAP_DATASETS' ), '', 'title="' . JText::_('COM_JMAP_SITEMAP_DATASETS' ) . '"');
		
		if($user->authorise('core.edit', 'com_jmap')) {
			$this->getIcon ( 'index.php?option=com_jmap&task=cpanel.editEntity', 'icon-48-robots.png', JText::_('COM_JMAP_ROBOTS_EDITOR' ), '', 'title="' . JText::_('COM_JMAP_ROBOTS_EDITOR' ) . '"', 'class="fancybox_iframe"' );
			$this->getIcon ( 'index.php?option=com_jmap&task=htaccess.editEntity', 'icon-48-htaccess.png', JText::_('COM_JMAP_HTACCESS_EDITOR' ), '', 'title="' . JText::_('COM_JMAP_HTACCESS_EDITOR' ) . '"', 'class="fancybox_iframe"' );
		}
		$this->getIcon ( 'index.php?option=com_jmap&task=pingomatic.display', 'icon-48-pingomatic.png', JText::_('COM_JMAP_PINGOMATIC_LINKS' ), '', 'title="' . JText::_('COM_JMAP_PINGOMATIC_LINKS' ) . '"');
		$this->getIcon ( '#rssfeed', 'icon-48-rss-feed.png', JText::_('COM_JMAP_SHOW_RSS_FEED' ), '', 'title="' . JText::_('COM_JMAP_SHOW_RSS_FEED' ) . '"', 'class="fancybox rss"' );
		
		// Access check.
		if ($user->authorise('jmap.google', 'com_jmap')) {
			$this->getIcon ( 'index.php?option=com_jmap&task=google.display&googlestats=webmasters', 'icon-48-googlewebmasters.png', JText::_('COM_JMAP_GOOGLE_WEBMASTERS' ), '', 'title="' . JText::_('COM_JMAP_GOOGLE_WEBMASTERS_TITLE' ) . '"');
			$this->getIcon ( 'index.php?option=com_jmap&task=google.display', 'icon-48-google.png', JText::_('COM_JMAP_GOOGLE' ), '', 'title="' . JText::_('COM_JMAP_GOOGLE_ANALYTICS_TITLE' ) . '"');
		}
		
		// Access check.
		if ($user->authorise('core.admin', 'com_jmap')) {
			$this->getIcon ( 'index.php?option=com_jmap&task=config.display', 'icon-48-config.png', JText::_('COM_JMAP_CONFIG' ), '', 'title="' . JText::_('COM_JMAP_CONFIG' ) . '"' );
		}
		
		$this->getIcon ( 'http://storejextensions.org/jsitemap_professional_documentation.html', 'icon-48-help.png', JText::_('COM_JMAP_HELPTITLE' ), '', 'title="' . JText::_('COM_JMAP_HELPTITLE' ) . '"' );
		
		echo '<div style="display:none" id="xmlsitemap">';
		$this->getIcon ( $livesite . '/index.php?option=com_jmap&view=sitemap&format=xml', 'icon-48-xml_sitemap_standard.png', JText::_('COM_JMAP_SHOW_XML_STANDARD_MAP' ), 'target="_blank"' );
		$this->getIcon ( $livesite . '/index.php?option=com_jmap&view=sitemap&format=images', 'icon-48-xml_sitemap_images.png', JText::_('COM_JMAP_SHOW_XML_IMAGES_MAP' ), 'target="_blank"' );
		$this->getIcon ( $livesite . '/index.php?option=com_jmap&view=sitemap&format=gnews', 'icon-48-xml_sitemap_gnews.png', JText::_('COM_JMAP_SHOW_XML_GNEWS_MAP' ), 'target="_blank"' );
		$this->getIcon ( $livesite . '/index.php?option=com_jmap&view=sitemap&format=mobile', 'icon-48-xml_sitemap_mobile.png', JText::_('COM_JMAP_SHOW_XML_MOBILE_MAP' ), 'target="_blank"' );
		$this->getIcon ( $livesite . '/index.php?option=com_jmap&view=sitemap&format=videos', 'icon-48-xml_sitemap_videos.png', JText::_('COM_JMAP_SHOW_XML_VIDEOS_MAP' ), 'target="_blank"' );
		$this->getIcon ( $livesite . '/index.php?option=com_jmap&view=geositemap&format=xml', 'icon-48-xml_sitemap_geoxml.png', JText::_('COM_JMAP_SHOW_XML_GEOSITEMAP_MAP' ), 'data-language="1" target="_blank"' );
		echo '</div>';
		
		echo '<div style="display:none" id="xmlsitemap_xslt">';
		$this->getIcon ( $livesite . '/index.php?option=com_jmap&view=sitemap&format=xml&xslt=1', 'icon-48-xml_sitemap_standard.png', JText::_('COM_JMAP_SHOW_XML_STANDARD_MAP' ), 'target="_blank"' );
		$this->getIcon ( $livesite . '/index.php?option=com_jmap&view=sitemap&format=images&xslt=1', 'icon-48-xml_sitemap_images.png', JText::_('COM_JMAP_SHOW_XML_IMAGES_MAP' ), 'target="_blank"' );
		$this->getIcon ( $livesite . '/index.php?option=com_jmap&view=sitemap&format=gnews&xslt=1', 'icon-48-xml_sitemap_gnews.png', JText::_('COM_JMAP_SHOW_XML_GNEWS_MAP' ), 'target="_blank"' );
		$this->getIcon ( $livesite . '/index.php?option=com_jmap&view=sitemap&format=mobile&xslt=1', 'icon-48-xml_sitemap_mobile.png', JText::_('COM_JMAP_SHOW_XML_MOBILE_MAP' ), 'target="_blank"' );
		$this->getIcon ( $livesite . '/index.php?option=com_jmap&view=sitemap&format=videos&xslt=1', 'icon-48-xml_sitemap_videos.png', JText::_('COM_JMAP_SHOW_XML_VIDEOS_MAP' ), 'target="_blank"' );
		$this->getIcon ( $livesite . '/index.php?option=com_jmap&view=sitemap&format=hreflang&xslt=1', 'icon-48-xml_sitemap_hreflang.png', JText::_('COM_JMAP_SHOW_XML_HREFLANG_MAP' ), 'data-language="1" target="_blank"' );
		echo '</div>';
		
		echo '<div style="display:none" id="xmlsitemap_export">';
		$this->getIcon ( $livesite . '/index.php?option=com_jmap&task=sitemap.exportxml&format=xml', 'icon-48-xml_sitemap_standard.png', JText::_('COM_JMAP_EXPORT_XML_STANDARD_MAP' ));
		$this->getIcon ( $livesite . '/index.php?option=com_jmap&task=sitemap.exportxml&format=images', 'icon-48-xml_sitemap_images.png', JText::_('COM_JMAP_EXPORT_XML_IMAGES_MAP' ));
		$this->getIcon ( $livesite . '/index.php?option=com_jmap&task=sitemap.exportxml&format=gnews', 'icon-48-xml_sitemap_gnews.png', JText::_('COM_JMAP_EXPORT_XML_GNEWS_MAP' ));
		$this->getIcon ( $livesite . '/index.php?option=com_jmap&task=sitemap.exportxml&format=mobile', 'icon-48-xml_sitemap_mobile.png', JText::_('COM_JMAP_EXPORT_XML_MOBILE_MAP' ));
		$this->getIcon ( $livesite . '/index.php?option=com_jmap&task=sitemap.exportxml&format=videos', 'icon-48-xml_sitemap_videos.png', JText::_('COM_JMAP_EXPORT_XML_VIDEOS_MAP' ));
		$this->getIcon ( $livesite . '/index.php?option=com_jmap&task=sitemap.exportxml&format=hreflang', 'icon-48-xml_sitemap_hreflang.png', JText::_('COM_JMAP_EXPORT_XML_HREFLANG_MAP' ), 'data-language="1" class="last-child"');
			echo '<label class="label label-primary">' . JText::_('COM_JMAP_CRONJOB_XML_LINK') . '</label>';?>
			<input data-role="sitemap_links" class="sitemap_links hasClickPopover" type="text" value="<?php echo JFilterOutput::ampReplace($livesite . '/index.php?option=com_jmap&task=sitemap.exportxml&format=xml&cronjobclient=1');?>" />
			<?php 
			echo '<label class="label label-primary">' . JText::_('COM_JMAP_CRONJOB_IMAGES_LINK') . '</label>';?>
			<input data-role="sitemap_links" class="sitemap_links hasClickPopover" type="text" value="<?php echo JFilterOutput::ampReplace($livesite . '/index.php?option=com_jmap&task=sitemap.exportxml&format=images&cronjobclient=1');?>" />
			<?php 
			echo '<label class="label label-primary">' . JText::_('COM_JMAP_CRONJOB_GNEWS_LINK') . '</label>';?>
			<input data-role="sitemap_links" class="sitemap_links hasClickPopover" type="text" value="<?php echo JFilterOutput::ampReplace($livesite . '/index.php?option=com_jmap&task=sitemap.exportxml&format=gnews&cronjobclient=1');?>" />
			<?php 
			echo '<label class="label label-primary">' . JText::_('COM_JMAP_CRONJOB_MOBILE_LINK') . '</label>';?>
			<input data-role="sitemap_links" class="sitemap_links hasClickPopover" type="text" value="<?php echo JFilterOutput::ampReplace($livesite . '/index.php?option=com_jmap&task=sitemap.exportxml&format=mobile&cronjobclient=1');?>" />
			<?php 
			echo '<label class="label label-primary">' . JText::_('COM_JMAP_CRONJOB_VIDEOS_LINK') . '</label>';?>
			<input data-role="sitemap_links" class="sitemap_links hasClickPopover" type="text" value="<?php echo JFilterOutput::ampReplace($livesite . '/index.php?option=com_jmap&task=sitemap.exportxml&format=videos&cronjobclient=1');?>" />
			<?php 
			echo '<label class="label label-primary">' . JText::_('COM_JMAP_CRONJOB_HREFLANG_LINK') . '</label>';?>
			<input data-role="sitemap_links" class="sitemap_links hasClickPopover" type="text" value="<?php echo JFilterOutput::ampReplace($livesite . '/index.php?option=com_jmap&task=sitemap.exportxml&format=hreflang&cronjobclient=1');?>" />
		<?php
		echo '</div>';
		
		echo '<div style="display:none" id="rssfeed">';
		$this->getIcon ( $livesite . '/index.php?option=com_jmap&view=sitemap&format=rss', 'icon-48-xml_sitemap.png', JText::_('COM_JMAP_SHOW_RSS_FEED' ), 'target="_blank"');
		$this->getIcon ( $livesite . '/index.php?option=com_jmap&view=sitemap&format=rss&xslt=1', 'icon-48-xsl_sitemap.png', JText::_('COM_JMAP_SHOW_RSS_FEED_FORMATTED' ), 'target="_blank"');
		$this->getIcon ( $livesite . '/index.php?option=com_jmap&task=sitemap.exportxml&format=rss', 'icon-48-xml_export.png', JText::_('COM_JMAP_EXPORT_RSS_FEED' ));
		echo '<label class="label label-primary">' . JText::_('COM_JMAP_RSS_FEED_LINK') . '</label>';
		if(!$this->showSefLinks || !$this->joomlaSefLinks):?>
		<input data-role="sitemap_links" class="sitemap_links" type="text" value="<?php echo JFilterOutput::ampReplace($livesite . '/index.php?option=com_jmap&view=sitemap&format=rss');?>" />
		<?php else:?>
		<input data-role="sitemap_links_sef" class="sitemap_links" type="text" data-valuenosef="<?php echo JFilterOutput::ampReplace($livesite . '/index.php?option=com_jmap&view=sitemap&format=rss');?>" value="<?php echo $livesite . preg_replace('/(\/[A-Za-z0-9_-~]*)*\/administrator\//i', '/', $this->siteRouter->build('index.php?option=com_jmap&view=sitemap&format=rss' . $this->siteItemid));?>"/>
		<?php endif;
		echo '</div>';

		$contents = ob_get_clean ();
		 
		// Assign reference variables
		$this->icons = $contents;
		$this->livesite = $livesite;
		$this->componentParams = $componentParams;
		$this->infodata = $infoData;
		$this->lists = $lists;
		$this->updatesData = $this->getModel()->getUpdates($this->get('httpclient'));
		$this->currentVersion = strval(simplexml_load_file(JPATH_COMPONENT_ADMINISTRATOR . '/jmap.xml')->version);
		
		// Aggiunta toolbar
		$this->addDisplayToolbar();
		
		// Output del template
		parent::display ();
	}
	
	/**
	 * Edit entity view
	 *
	 * @access public
	 * @param Object& $row the item to edit
	 * @return void
	 */
	public function editEntity(&$row) {
		// Load JS Client App dependencies
		$doc = JFactory::getDocument();
		$this->loadJQuery($doc);
		$this->loadBootstrap($doc);
		$translations = array (	'COM_JMAP_ROBOTS_REQUIRED',
								'COM_JMAP_ROBOTS_ENTRY_ADDED',
								'COM_JMAP_CLICKTOUPDATE', 
								'COM_JMAP_EXPIREON',
								'COM_JMAP_UPDATEPROGRESSTITLE',
								'COM_JMAP_DOWNLOADING_UPDATE_SUBTITLE',
								'COM_JMAP_INSTALLING_UPDATE_SUBTITLE');
		$this->injectJsTranslations($translations, $doc);

		// Load specific JS App
		$doc->addStylesheet ( JURI::root ( true ) . '/administrator/components/com_jmap/css/cpanel.css' );
		$doc->addScript ( JURI::root ( true ) . '/administrator/components/com_jmap/js/cpanel.js' );
		$doc->addScriptDeclaration("var jmap_linksRandom=0;");
		$doc->addScriptDeclaration("var jmap_forceFormat=0;");

		$this->option = $this->option;
		$this->robotsVersion = $this->getModel ()->getState ( 'robots_version' );
		$this->record = $row;
	
		parent::display ( 'edit' );
	}
		
	/**
	 * Rendering for installer APP that runs on JSitemap installation iframe
	 * @access public
	 * @return void
	 */
	public function showInstallerApp() {
		$doc = JFactory::getDocument();
		$this->loadJQuery($doc);
		$this->loadBootstrap($doc);
		$doc->addStylesheet ( JURI::root ( true ) . '/administrator/components/com_jmap/css/cpanel.css' );
		$doc->addScript ( JURI::root ( true ) . '/administrator/components/com_jmap/js/installer.js' );
	
		// Set layout
		$this->setLayout('default');
	
		// Format data
		parent::display ('installer');
	}
}