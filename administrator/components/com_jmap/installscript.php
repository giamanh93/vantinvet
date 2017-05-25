<?php
//namespace administrator\components\com_jmap;
/**  
 * @package JMAP::administrator::components::com_jmap 
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html   
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' ); 
jimport ( 'joomla.filesystem.file' );

/** 
 * Script to manage install/update/uninstall for component. Follow class convention
 * @package JMAP::administrator::components::com_jmap  
 */
class com_jmapInstallerScript {
	/*
	* Find mimimum required joomla version for this extension. It will be read from the version attribute (install tag) in the manifest file
	*/
	private $minimum_joomla_release = '3.0';
	
	/*
	 * $parent is the class calling this method.
	 * $type is the type of change (install, update or discover_install, not uninstall).
	 * preflight runs before anything else and while the extracted files are in the uploaded temp folder.
	 * If preflight returns false, Joomla will abort the update and undo everything already done.
	 */
	function preflight($type, $parent) {
	
	}
	
	/*
	 * $parent is the class calling this method.
	 * install runs after the database scripts are executed.
	 * If the extension is new, the install method is run.
	 * If install returns false, Joomla will abort the install and undo everything already done.
	 */
	function install($parent) {
		$database = JFactory::getDBO ();
		$lang = JFactory::getLanguage ();
		$lang->load ( 'com_jmap' );

		// All operation ok
		echo (JText::_('COM_JMAP_INSTALL_SUCCESS'));
		
		// INSTALL UTILITY PLUGIN - Current installer instance
		$componentInstaller = JInstaller::getInstance ();
		if(!$componentInstaller->getPath ( 'source' )) {
			$componentInstaller = $parent->getParent();
		}
		
		$pathToPlugin = $componentInstaller->getPath ( 'source' ) . '/plugin';
		// New plugin installer
		$pluginInstaller = new JInstaller ();
		if (! $pluginInstaller->install ( $pathToPlugin )) {
			echo '<p>' . JText::_ ( 'COM_JMAP_ERROR_INSTALLING_UTILITY_PLUGIN' ) . '</p>';
		} else {
			$query = "UPDATE #__extensions SET " .
					 $database->quoteName('enabled') . " = 1," .
					 $database->quoteName('ordering') . " = 2" .
					 "\n WHERE type = 'plugin' AND element = " . $database->quote ( 'jmap' ) .
					 "\n AND folder = " . $database->quote ( 'system' );
			$database->setQuery ( $query );
			if (! $database->execute ()) {
				echo '<p>' . JText::_ ( 'COM_JMAP_ERROR_PUBLISHING_UTILITY_PLUGIN' ) . '</p>';
			}
			// Redirect plugin ordered before the JMap Utilities to override the handleError custom 404 page if needed
			$query = "UPDATE #__extensions SET " .
					 $database->quoteName('ordering') . " = 1" .
					 "\n WHERE type = 'plugin' AND element = " . $database->quote ( 'redirect' ) .
					 "\n AND folder = " . $database->quote ( 'system' );
			$database->setQuery ( $query );
			$database->execute ();
		}
		
		// INSTALL PINGOMATIC PLUGIN - Current installer instance
		$pathToPlugin = $componentInstaller->getPath ( 'source' ) . '/pluginping';
		// New plugin installer
		$pluginInstaller = new JInstaller ();
		if (! $pluginInstaller->install ( $pathToPlugin )) {
			echo '<p>' . JText::_ ( 'COM_JMAP_ERROR_INSTALLING_PINGOMATIC_PLUGIN' ) . '</p>';
		} else {
			$query = "UPDATE #__extensions SET " .
					 $database->quoteName('enabled') . " = 1," .
					 $database->quoteName('ordering') . " = 0" .
					 "\n WHERE type = 'plugin' AND element = " . $database->quote ( 'pingomatic' ) .
					 "\n AND folder = " . $database->quote ( 'content' );
			$database->setQuery ( $query );
			if (! $database->execute ()) {
				echo '<p>' . JText::_ ( 'COM_JMAP_ERROR_PUBLISHING_PINGOMATIC_PLUGIN' ) . '</p>';
			}
		}
		
		// INSTALL SITE MODULE - Current installer instance
		$pathToSiteModule = $componentInstaller->getPath ( 'source' ) . '/modules/site';
		// New module installer
		$moduleInstaller = new JInstaller ();
		if (! $moduleInstaller->install ( $pathToSiteModule )) {
			echo '<p>' . JText::_ ( 'COM_JMAP_ERROR_INSTALLING_MODULE' ) . '</p>';
		}
		
		// INSTALL ADMIN MODULE - Current installer instance
		$pathToAdminModule = $componentInstaller->getPath ( 'source' ) . '/modules/admin';
		// New module installer
		$moduleInstaller = new JInstaller ();
		if (! $moduleInstaller->install ( $pathToAdminModule )) {
			echo '<p>' . JText::_ ( 'COM_JMAP_ERROR_INSTALLING_ADMIN_MODULE' ) . '</p>';
		} else {
			$query = "UPDATE #__modules" .
					 "\n SET " . $database->quoteName('published') . " = 1," .
					 "\n" . $database->quoteName('position') . " = " . $database->quote('icon') . "," .
					 "\n" . $database->quoteName('ordering') . " = 99" .
					 "\n WHERE " . $database->quoteName('module') . " = " . $database->quote('mod_jmapquickicon') .
					 "\n AND " . $database->quoteName('client_id') . " = 1";
			$database->setQuery($query);
			if(!$database->execute()) {
				echo JText::_('COM_JMAP_ERROR_PUBLISHING_ADMIN_MODULE');
			}
			
			// Publish all pages for default on joomla1.6+
			$query	= $database->getQuery(true);
			$query->select('id');
			$query->from('#__modules');
			$query->where($database->quoteName('module') . '=' . $database->quote('mod_jmapquickicon'));
			$query->where($database->quoteName('client_id') . '= 1');

			$database->setQuery($query);
			$lastIDForModule = $database->loadResult();
				
			// Now insert
			try {
				$query	= $database->getQuery(true);
				$query->insert('#__modules_menu');
				$query->set($database->quoteName('moduleid') . '=' . $database->quote($lastIDForModule));
				$query->set($database->quoteName('menuid') . '= 0');
				$database->setQuery($query);
				$database->execute();
			} catch (Exception $e) {
				// Already existing no insert - do nothing all true
			}
		}
		
		// Robots.txt images management
		$targetRobot = null;
		// Try standard robots.txt
		if(JFile::exists(JPATH_ROOT . DIRECTORY_SEPARATOR . 'robots.txt')) {
			$targetRobot = JPATH_ROOT . DIRECTORY_SEPARATOR . 'robots.txt';
		} elseif (JFile::exists(JPATH_ROOT . DIRECTORY_SEPARATOR . 'robots.txt.dist')) { // Fallback on distribution version
			$targetRobot = JPATH_ROOT . DIRECTORY_SEPARATOR . 'robots.txt.dist';
		} else {
			$targetRobot = false; // Not found do nothing
		}
		
		// Robots.txt found!
		if($targetRobot !== false) {
			// If file permissions ko
			if(!$robotContents = JFile::read($targetRobot)) {
				echo JText::_('COM_JMAP_JSITEMAP_REMEMBER_SET_ROBOTS_FOR_IMAGES');
			}
			
			// Repair the standard Joomla robots.txt for nowadays Google indexing
			$managedRobotContents = preg_replace('#Disallow: .*/images.*#', '', $robotContents);
			$managedRobotContents = preg_replace('#Disallow: .*/media.*#', '', $managedRobotContents);
			$managedRobotContents = preg_replace('#Disallow: .*/templates.*#', '', $managedRobotContents);
			$managedRobotContents = preg_replace('#Disallow: .*/components.*#', '', $managedRobotContents);
			$managedRobotContents = preg_replace('#Disallow: .*/modules.*#', '', $managedRobotContents);
			$managedRobotContents = preg_replace('#Disallow: .*/plugins.*#', '', $managedRobotContents);

			// Perform only once to fix the JS/CSS blocking resources
			if(!preg_match('#Allow: \/\*\.js\*#', $managedRobotContents)) {
				$managedRobotContents = preg_replace('#User-agent: \*#i', 'User-agent: *' .
														PHP_EOL . 'Allow: /*.js*' .
														PHP_EOL . 'Allow: /*.css*' .
														PHP_EOL . 'Allow: /*.png*' .
														PHP_EOL . 'Allow: /*.jpg*' .
														PHP_EOL . 'Allow: /*.gif*' .
														PHP_EOL , $managedRobotContents);
			}
			
			// If file permissions ko on rewrite updated contents
			$originalPermissions = null;
			if($managedRobotContents) {
				if(!is_writable($targetRobot)) {
					$originalPermissions = intval(substr(sprintf('%o', fileperms($targetRobot)), -4), 8);
					@chmod($targetRobot, 0755);
				}
				if(@!JFile::write($targetRobot, $managedRobotContents)) {
					echo JText::_('COM_JMAP_JSITEMAP_REMEMBER_SET_ROBOTS_FOR_IMAGES');
				}
				// Check if permissions has been changed and recover the original in that case
				if($originalPermissions) {
					@chmod($targetRobot, $originalPermissions);
				}
			}
		}
		
		// DB UPDATES PROCESSING
		$queryFields = 	"SHOW COLUMNS " .
						"\n FROM " . $database->quoteName('#__jmap_metainfo');
		$database->setQuery($queryFields);
		try {
			$elements = $database->loadColumn();
			if(!in_array('meta_image', $elements)) {
				$addFieldQuery = "ALTER TABLE " .  $database->quoteName('#__jmap_metainfo') .
								 "\n ADD " . $database->quoteName('meta_image') .
								 "\n VARCHAR(255) NULL AFTER " .  $database->quoteName('meta_desc');
				$database->setQuery($addFieldQuery)->execute();
			}
			if(!in_array('excluded', $elements)) {
				$addFieldQuery = "ALTER TABLE " .  $database->quoteName('#__jmap_metainfo') .
								 "\n ADD " . $database->quoteName('excluded') .
								 "\n TINYINT(1) NOT NULL DEFAULT 0 AFTER " .  $database->quoteName('published');
				$database->setQuery($addFieldQuery)->execute();
			}
		} catch (Exception $e) { }
		
		// Processing complete
		return true;
	}
	
	/*
	 * $parent is the class calling this method.
	 * update runs after the database scripts are executed.
	 * If the extension exists, then the update method is run.
	 * If this returns false, Joomla will abort the update and undo everything already done.
	 */
	function update($parent) {
		// Execute always sql install file to get added updates in that file, disregard DBMS messages and Joomla queue for user
		$parentParent = $parent->getParent();
		$parentManifest = $parentParent->getManifest();
		try {
			// Install/update always without error handling case legacy J Error
			JError::setErrorHandling(E_ALL, 'ignore');
			if (isset($parentManifest->install->sql)) {
				$parentParent->parseSQLFiles($parentManifest->install->sql);
			}
			// Force refresh of the SEO stats on update to the 4.0 SEMrush new branch
			echo ("<script>if(window.sessionStorage !== null){sessionStorage.removeItem('seostats');}</script>");
		} catch (Exception $e) {
			// Do nothing for user for Joomla 3.x case, case Exception handling
		}
		
		// Install on update in same way
		$this->install($parent);
	}
	
	/*
	 * $parent is the class calling this method.
	 * $type is the type of change (install, update or discover_install, not uninstall).
	 * postflight is run after the extension is registered in the database.
	 */
	function postflight($type, $parent) { 
		// define the following parameters only if it is an original install
		if ($type == 'install') {  
			
			// Preferences
			$params ['show_title'] = '1';
			$params ['title_type'] = 'maintitle';
			$params ['defaulttitle'] = '';
			$params ['headerlevel'] = '1';
			$params ['classdiv'] = 'sitemap';
			$params ['show_pagebreaks'] = '0';
 			$params ['opentarget'] = '_self';
			$params ['include_external_links'] = '1';
			$params ['unique_pagination'] = '1';
			$params ['registration_email'] = '';
			$params ['custom_404_page_status'] = '0';
			$params ['custom_404_page_override'] = '1';
			$params ['custom_404_page_mode'] = 'html';
			$params ['custom_404_page_text'] = 'Sorry, this page is not available!';
			
			// Sitemap aspect
			$params ['sitemap_html_template'] = '';
			$params ['show_icons'] = '1';
			$params ['animated'] = '1';
			$params ['animate_speed'] = '200';
			$params ['minheight_root_folders'] = '35';
			$params ['minheight_sub_folders'] = '30';
			$params ['minheight_leaf'] = '20';
			$params ['minwidth_columns'] = '120';
			$params ['font_size_boxes'] = '12';
			$params ['root_folders_color'] = '#F60';
			$params ['root_folders_border_color'] = '#943B00';
			$params ['root_folders_text_color'] = '#FFF';
			$params ['sub_folders_color'] = '#99CDFF';
			$params ['sub_folders_border_color'] = '#11416F';
			$params ['sub_folders_text_color'] = '#11416F';
			$params ['leaf_folders_color'] = '#EBEBEB';
			$params ['leaf_folders_border_color'] = '#6E6E6E';
			$params ['leaf_folders_text_color'] = '#505050';
			$params ['connections_color'] = '#CCC';
			$params ['expand_iconset'] = 'square-blue';
			$params ['draggable_sitemap'] = '0';
			$params ['template_override'] = '0';
			$params ['treeview_scripts'] = '1';
			$params ['show_expanded'] = '0';
			$params ['expand_location'] = 'location';
			$params ['column_sitemap'] = '0';
			$params ['column_maxnum'] = '3';
			$params ['multilevel_categories'] = '0';
			$params ['hide_empty_cats'] = '0';
			$params ['expand_first_level'] = '0';
			$params ['merge_alias_menu'] = '0';
			
			//Caching
			$params ['enable_view_cache'] = '0';
			$params ['lifetime_view_cache'] = '1';
			$params ['rss_lifetime_view_cache'] = '60';
			$params ['enable_precaching'] = '0';
			$params ['precaching_limit_xml'] = '5000';
			$params ['precaching_limit_images'] = '50';
			$params ['split_sitemap'] = '0';
			$params ['split_chunks'] = '50000';
			$params ['splitting_hardcoded_rootnode'] = '1';
			
			//Sitemap settings
			$params ['gnews_publication_name'] = '';
			$params ['gnews_limit_recent'] = '0';
			$params ['gnews_limit_valid_days'] = '2';
			$params ['gnews_genres'] = array('Blog');
			$params ['imagetitle_processor'] = 'title|alt';
			$params ['max_images_requests'] = '50';
			$params ['regex_images_crawler'] = 'advanced';
			$params ['fake_images_processor'] = '0';
			$params ['lazyload_images_processor'] = '0';
			$params ['include_description_only'] = '0';
			$params ['sh404sef_multilanguage'] = '0';
			$params ['images_global_filter_include'] = '';
			$params ['images_global_filter_exclude'] = '';
			$params ['videos_global_filter_include'] = '';
			$params ['videos_global_filter_exclude'] = '';
			$params ['cdn_protocol'] = '';
			$params ['rss_channel_name'] = '';
			$params ['rss_channel_description'] = '';
			$params ['rss_channel_image'] = '';
			$params ['rss_webmaster_name'] = '';
			$params ['rss_webmaster_email'] = '';
			$params ['rss_channel_excludewords'] = '';
			$params ['rss_limit_valid_days'] = '';
			$params ['rss_limit_recent'] = '';
			$params ['rss_process_contentplugins'] = '0';
			$params ['geositemap_enabled'] = '0';
			$params ['geositemap_address'] = '';
			$params ['geositemap_name'] = '';
			$params ['geositemap_author'] = '';
			$params ['geositemap_description'] = '';
			
			// Advanced settings
			$params ['include_archived'] = '0';
			$params ['multiple_content_sources'] = '0';
			$params ['enable_articles_exclusions'] = '1';
			$params ['disable_acl'] = 'enabled';
			$params ['showalways_language_dropdown'] = '';
			$params ['lists_limit_pagination'] = '10';
			$params ['selectable_limit_pagination'] = '10';
			$params ['seostats_custom_link'] = '';
			$params ['seostats_enabled'] = '1';
			$params ['seostats_site_query'] = '1';
			$params ['seostats_gethost'] = '1';
			$params ['linksanalyzer_workingmode'] = '1';
			$params ['linksanalyzer_validation_analysis'] = '2';
			$params ['linksanalyzer_indexing_analysis'] = '1';
			$params ['linksanalyzer_indexing_engine'] = 'webcrawler';
			$params ['linksanalyzer_serp_numresults'] = '10';
			$params ['linksanalyzer_remove_separators'] = '1';
			$params ['linksanalyzer_remove_slashes'] = '2';
			$params ['seospider_crawler_delay'] = '0';
			$params ['autoping'] = '0';
			$params ['default_autoping'] = '0';
			$params ['sitemap_links_sef'] = '0';
			$params ['sitemap_links_forceformat'] = '0';
			$params ['sitemap_links_random'] = '0';
			$params ['append_livesite'] = '1';
			$params ['custom_sitemap_domain'] = '';
			$params ['custom_http_port'] = '';
			$params ['resources_limit_management'] = '1';
			$params ['advanced_multilanguage'] = '0';
			$params ['socket_mode'] = 'dns';
			$params ['site_itemid'] = '';
			$params ['metainfo_urldecode'] = '1';
			$params ['includejquery'] = '1';
			$params ['enable_debug'] = '0';
			
			// Google Analytics
			$params ['ga_domain'] = '';
			$params ['wm_domain'] = '';
			$params ['ga_api_key'] = '';
			$params ['ga_client_id'] = '';
			$params ['ga_client_secret'] = '';
			$params ['inject_gajs'] = '0';
			$params ['gajs_code'] = '';
			
			$this->setParams ( $params );   
		} 
	}
	
	/*
	 * $parent is the class calling this method
	 * uninstall runs before any other action is taken (file removal or database processing).
	 */
	function uninstall($parent) {
		$database = JFactory::getDBO ();
		$lang = JFactory::getLanguage();
		$lang->load('com_jmap');
		 
		echo JText::_('COM_JMAP_UNINSTALL_SUCCESS' );
		
		// UNINSTALL UTILITY PLUGIN - Check if plugin exists
		$query = "SELECT extension_id" .
				 "\n FROM #__extensions" .
				 "\n WHERE type = 'plugin' AND element = " . $database->quote('jmap') .
				 "\n AND folder = " . $database->quote('system');
		$database->setQuery($query);
		$pluginID = $database->loadResult();
		if($pluginID) {
			// New plugin installer
			$pluginInstaller = new JInstaller ();
			if(!$pluginInstaller->uninstall('plugin', $pluginID)) {
				echo '<p>' . JText::_('COM_JMAP_ERROR_UNINSTALLING_UTLITY_PLUGIN') . '</p>';
			}
		}
		
		// UNINSTALL PINGOMATIC PLUGIN - Check if plugin exists
		$query = "SELECT extension_id" .
				 "\n FROM #__extensions" .
				 "\n WHERE type = 'plugin' AND element = " . $database->quote('pingomatic') .
				 "\n AND folder = " . $database->quote('content');
		$database->setQuery($query);
		$pluginID = $database->loadResult();
		if($pluginID) {
			// New plugin installer
			$pluginInstaller = new JInstaller ();
			if(!$pluginInstaller->uninstall('plugin', $pluginID)) {
				echo '<p>' . JText::_('COM_JMAP_ERROR_UNINSTALLING_PINGOMATIC_PLUGIN') . '</p>';
			}
		}
		
		// UNINSTALL SITE MODULE - Check if site module exists
		$query = "SELECT extension_id" .
				 "\n FROM #__extensions" .
				 "\n WHERE type = 'module' AND element = " . $database->quote('mod_jmap') .
				 "\n AND client_id = 0";
		$database->setQuery($query);
		$moduleID = $database->loadResult();
		if(!$moduleID) {
			echo '<p>' . JText::_('COM_JMAP_MODULE_ALREADY_REMOVED') . '</p>';
		} else {
			// New module installer
			$moduleInstaller = new JInstaller ();
			if(!$moduleInstaller->uninstall('module', $moduleID)) {
				echo '<p>' . JText::_('COM_JMAP_ERROR_UNINSTALLING_MODULE') . '</p>';
			}
		}
		
		// UNINSTALL ADMIN MODULE - Check if site module exists
		$query = "SELECT extension_id" .
				 "\n FROM #__extensions" .
				 "\n WHERE type = 'module' AND element = " . $database->quote('mod_jmapquickicon') .
				 "\n AND client_id = 1";
		$database->setQuery($query);
		$moduleID = $database->loadResult();
		if(!$moduleID) {
			echo '<p>' . JText::_('COM_JMAP_MODULE_ALREADY_REMOVED') . '</p>';
		} else {
			// New module installer
			$moduleInstaller = new JInstaller ();
			if(!$moduleInstaller->uninstall('module', $moduleID)) {
				echo '<p>' . JText::_('COM_JMAP_ERROR_UNINSTALLING_MODULE') . '</p>';
			}
		}
		
		// Processing complete
		return true;
	}
	
	/*
	 * get a variable from the manifest file (actually, from the manifest cache).
	 */
	function getParam($name) {
		$db = JFactory::getDbo ();
		$db->setQuery ( 'SELECT manifest_cache FROM #__extensions WHERE name = "jmap"' );
		$manifest = json_decode ( $db->loadResult (), true );
		return $manifest [$name];
	}
	
	/*
	 * sets parameter values in the component's row of the extension table
	 */
	function setParams($param_array) {
		if (count ( $param_array ) > 0) { 
			$db = JFactory::getDbo (); 
			// store the combined new and existing values back as a JSON string
			$paramsString = json_encode ( $param_array );
			$db->setQuery ( 'UPDATE #__extensions SET params = ' . $db->quote ( $paramsString ) . ' WHERE name = "jmap"' );
			$db->execute ();
		}
	}
}