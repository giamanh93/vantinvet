<?php
/**
 * @author Joomla! Extensions Store
 * @package JMAP::plugins::content
 * @copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
jimport ( 'joomla.plugin.plugin' );

/**
 * Observer class notified on events <<testable_behavior>>
 *
 * @author Joomla! Extensions Store
 * @package JMAP::plugins::content
 * @since 3.0
 */
class plgContentPingomatic extends JPlugin {
	/**
	 * Application reference
	 *
	 * @access private
	 * @var Object
	 */
	private $app;
	
	/**
	 * Plugin execution context
	 *
	 * @access private
	 * @var String
	 */
	private $context;
	
	/**
	 * Curl adapter reference
	 *
	 * @access private
	 * @var Object
	 */
	private $curlAdapter;
	
	/**
	 * Pinger class for webblog services such as Pingomatic
	 *
	 * @access private
	 * @var Object
	 */
	private $pingerInstance;
	
	/**
	 * Database connector
	 *
	 * @access private
	 * @var Object
	 */
	private $db;
	
	/**
	 * Component config params
	 *
	 * @access private
	 * @var Object
	 */
	private $cParams;
	
	/**
	 * Adapters mapping based on context and route helper
	 *
	 * @access private
	 * @var array
	 */
	private $adaptersMapping;
	
	/**
	 * Load the CURL library needed from JMap Framework
	 *
	 * @access private
	 * @return boolean
	 */
	private function loadCurlLib() {
		// Check lib availability and load it
		if (file_exists ( JPATH_ROOT . '/administrator/components/com_jmap/framework/http/http.php' )) {
			include_once (JPATH_ROOT . '/administrator/components/com_jmap/framework/http/http.php');
			include_once (JPATH_ROOT . '/administrator/components/com_jmap/framework/http/response.php');
			include_once (JPATH_ROOT . '/administrator/components/com_jmap/framework/http/transport.php');
			include_once (JPATH_ROOT . '/administrator/components/com_jmap/framework/http/transport/curl.php');
			
			// Instantiate dependency
			$this->curlAdapter = new JMapHttp ( new JMapHttpTransportCurl (), $this->cParams );
			
			return true;
		}
		
		return false;
	}
	
	/**
	 * Load the Pinger lib to ping weblog services
	 *
	 * @access private
	 * @return boolean
	 */
	private function loadPingerLib() {
		// Check lib availability and load it
		if (file_exists ( JPATH_ROOT . '/administrator/components/com_jmap/framework/pinger/weblog.php' )) {
			include_once (JPATH_ROOT . '/administrator/components/com_jmap/framework/pinger/weblog.php');

			// Instantiate dependency
			$this->pingerInstance = new JMapPingerWeblog();

			return true;
		}
	
		return false;
	}
	
	/**
	 * Send auto ping for this article URL available in the ping table using the curl adapter
	 *
	 * @access private
	 * @return boolean
	 */
	private function autoSendPing($title, $url, $rssurl, $services) {
		// Load safely the CURL JMap lib without autoloader
		if ($this->loadCurlLib ()) {
			// Array of POST vars
			$post = array ();
			$post ['title'] = $title;
			$post ['blogurl'] = $url;
			$post ['rssurl'] = $rssurl;
			$post = array_merge ( $post, ( array ) $services );
			
			// Post HTTP request to Pingomatic
			$httpResponse = $this->curlAdapter->post ( 'http://pingomatic.com/ping/', $post, array (), 5, 'JSitemap Professional Pinger' );
			
			// Check if HTTP status code is OK
			if ($httpResponse->code != 200) {
				throw new RuntimeException ( JText::_ ( 'COM_JMAP_AUTOPING_ERROR_HTTP_STATUSCODE' ) );
			}
		}
		
		return true;
	}
	
	/**
	 * Route save single article to the corresponding SEF link
	 *
	 * @access private
	 * @return string
	 */
	private function routeArticleToSefMenu($articleID, $catID, $language, $article) {
		// Try to route the article to a single article menu item view
		$helperRouteClass = $this->context ['class'];
		$classMethod = $this->context ['method'];
		
		// Route helper native by component, com_content, com_k2
		if (! isset ( $this->context ['routing'] )) {
			$articleHelperRoute = $helperRouteClass::$classMethod ( $articleID, $catID, $language );
		} else {
			// Route helper universal JSitemap, com_zoo
			$articleHelperRoute = $helperRouteClass::$classMethod ( $article->option, $article->view, $article->id, $article->catid, null );
			if ($articleHelperRoute) {
				$articleHelperRoute = '?Itemid=' . $articleHelperRoute;
			}
		}
		
		// Extract Itemid from the helper routed URL
		$extractedItemid = preg_match ( '/Itemid=\d+/i', $articleHelperRoute, $result );
		
		if (isset ( $result [0] )) {
			// Get uri instance avoidng subdomains already included in the routing chunks
			$uriInstance = JUri::getInstance();
			$resourceLiveSite = rtrim($uriInstance->getScheme() . '://' . $uriInstance->getHost(), '/');

			$extractedItemid = $result [0];
			$siteRouter = JRouterSite::getInstance ( 'site', array (
					'mode' => JROUTER_MODE_SEF 
			) );
			$articleMenuRouted = $siteRouter->build ( '?' . $extractedItemid )->toString ();
			
			// Check if multilanguage is enabled
			if (JMapLanguageMultilang::isEnabled ()) {
				$defaultLanguage = JComponentHelper::getParams('com_languages')->get('site');
				if ($language != '*') {
					// New language manager instance
					$languageManager = JMapLanguageMultilang::getInstance ( $language );
				} else {
					// Get the default language tag
					// New language manager instance
					$languageManager = JMapLanguageMultilang::getInstance ( $defaultLanguage );
				}
				
				// Extract the language tag
				$selectedLanguage = $languageManager->getTag();
				$languageFilterPlugin = JPluginHelper::getPlugin('system', 'languagefilter');
				$languageFilterPluginParams = new JRegistry($languageFilterPlugin->params);
				if($defaultLanguage == $selectedLanguage && $languageFilterPluginParams->get('remove_default_prefix', 0)) {
					$articleMenuRouted = str_replace ( '/administrator', '', $articleMenuRouted );
				} else {
					$localeTag = $languageManager->getLocale ();
					$sefTag = $localeTag [4];
					$articleMenuRouted = str_replace ( '/administrator', '/' . $sefTag, $articleMenuRouted );
				}
			} else {
				$articleMenuRouted = str_replace ( '/administrator', '', $articleMenuRouted );
			}
			$articleMenuRouted = preg_match('/http/i', $articleMenuRouted) ? $articleMenuRouted : $resourceLiveSite . '/' . ltrim($articleMenuRouted, '/');
			return $articleMenuRouted;
		} else {
			// Check if routing is valid otherwise throw exception
			throw new RuntimeException ( JText::_ ( 'COM_JMAP_AUTOPING_ERROR_NOSEFROUTE_FOUND' ) );
		}
	}
	
	/**
	 * Method to be called everytime an article in backend is saved,
	 * it's responsible to check and find if the SEF link of the article has been
	 * added to the Pingomatic table, and if found submit the ping form through CURL http adapter
	 *
	 * @param string $context
	 *        	The context of the content passed to the plugin (added in 1.6)
	 * @param object $article
	 *        	A JTableContent object
	 * @param boolean $isNew
	 *        	If the content is just about to be created
	 *        	
	 * @return boolean true if function not enabled, is in front-end or is new. Else true or
	 *         false depending on success of save function.
	 */
	public function onContentAfterSave($context, $article, $isNew) {
		// Avoid operations if plugin is executed in frontend
		if (! $this->cParams->get ( 'default_autoping', 0 ) && ! $this->cParams->get ( 'autoping', 0 )) {
			return;
		}
		
		// Ensure to process only native Joomla articles
		if (array_key_exists ( $context, $this->adaptersMapping )) {
			// Extract the correct route helper
			$routeHelper = $this->adaptersMapping [$context] ['file'];
			// Include needed files for the correct multilanguage routing from backend to frontend of the save articles
			if (file_exists ( $routeHelper )) {
				include_once ($routeHelper);
				
				// Store the context for static class method call
				$this->context = $this->adaptersMapping [$context];
			}
			
			// Start HTTP submission process, manage users exceptions if debug is enabled
			try {
				// Try attempt to resolve the article to a single menu or container category SEF link
				$hasArticleMenuRoute = $this->routeArticleToSefMenu ( $article->id, $article->catid, $article->language, $article );
				
				// If article has been resolved, fetch pings URLs from jmap_pingomatic table and do lookup
				if ($hasArticleMenuRoute) {
					// Check if the auto Pingomatic ping based on records is enabled
					if($this->cParams->get ( 'autoping', 0 )) {
						$query = $this->db->getQuery ( true );
						$query->select ( '*' );
						$query->from ( $this->db->quoteName ( '#__jmap_pingomatic' ) );
						$query->where ( $this->db->quoteName ( 'blogurl' ) . '=' . $this->db->quote ( $hasArticleMenuRoute ) );
						
						// Is there a found pinged link for this article scope?
						$foundPingUrl = $this->db->setQuery ( $query )->loadObject ();
						if ($foundPingUrl) {
							// Retrieve ping record info and submit form using CURL adapter, else do nothing
							$titleToPing = $foundPingUrl->title;
							$urlToPing = $foundPingUrl->blogurl;
							$rssUrlToPing = $foundPingUrl->rssurl;
							$servicesToPing = json_decode ( $foundPingUrl->services );
							
							// If ping is OK update the pinging status and datetime in the Pingomatic table
							if ($this->autoSendPing ( $titleToPing, $urlToPing, $rssUrlToPing, $servicesToPing )) {
								$query = $this->db->getQuery ( true );
								$query->update ( $this->db->quoteName ( '#__jmap_pingomatic' ) );
								$query->set ( $this->db->quoteName ( 'lastping' ) . ' = ' . $this->db->quote ( date ( 'Y-m-d H:i:s' ) ) );
								$query->where ( $this->db->quoteName ( 'id' ) . '=' . ( int ) $foundPingUrl->id );
								$this->db->setQuery ( $query )->execute ();
								
								// Everything complete fine, ping sent and updated!
								if ($this->cParams->get ( 'enable_debug', 0 )) {
									$this->app->enqueueMessage ( JText::_ ( 'COM_JMAP_AUTOPING_COMPLETED_SUCCESFULLY' ), 'notice' );
								}
							}
						} else {
							// Display post message after save only if debug is enabled
							if ($this->cParams->get ( 'enable_debug', 0 )) {
								$this->app->enqueueMessage ( JText::_ ( 'COM_JMAP_AUTOPING_ERROR_NOPING_CONTENT_FOUND' ), 'notice' );
							}
						}
					}
					
					// Check if the default Pingomatic/Weblogs ping is enabled
					if($this->cParams->get ( 'default_autoping', 0 )) {
						// Always submit autoping using XMLRPC web services
						if($this->loadPingerLib()) {
							// Get debug state
							$debugEnabled = $this->cParams->get ( 'enable_debug', 0 );
							$pingomaticPinged = $this->pingerInstance->ping_ping_o_matic($article->title, $hasArticleMenuRoute);
							if($debugEnabled && $pingomaticPinged) {
								$this->app->enqueueMessage ( JText::_( 'COM_JMAP_AUTOPING_DEFAULT_AUTOPING_SENT_PINGOMATIC' ), 'notice' );
							}

							$googlePinged = $this->pingerInstance->ping_google($article->title, $hasArticleMenuRoute);
							if($debugEnabled && $googlePinged) {
								$this->app->enqueueMessage ( JText::_( 'COM_JMAP_AUTOPING_DEFAULT_AUTOPING_SENT_GOOGLE' ), 'notice' );
							}

							$weblogsPinged = $this->pingerInstance->ping_weblogs_com($article->title, $hasArticleMenuRoute);
							if($debugEnabled && $weblogsPinged) {
								$this->app->enqueueMessage ( JText::_( 'COM_JMAP_AUTOPING_DEFAULT_AUTOPING_SENT_WEBLOGS' ), 'notice' );
							}
							
							$blogsPinged = $this->pingerInstance->ping_blo_gs($article->title, $hasArticleMenuRoute);
							if($debugEnabled && $blogsPinged) {
								$this->app->enqueueMessage ( JText::_( 'COM_JMAP_AUTOPING_DEFAULT_AUTOPING_SENT_BLOGS' ), 'notice' );
							}
							
							$baiduPinged = $this->pingerInstance->ping_baidu($article->title, $hasArticleMenuRoute);
							if($debugEnabled && $baiduPinged) {
								$this->app->enqueueMessage ( JText::_( 'COM_JMAP_AUTOPING_DEFAULT_AUTOPING_SENT_BAIDU' ), 'notice' );
							}
						}
					}
				}
			} catch ( Exception $e ) {
				// Display post message after save only if debug is enabled
				if ($this->cParams->get ( 'enable_debug', 0 )) {
					$this->app->enqueueMessage ( $e->getMessage (), 'notice' );
				}
			}
		}
	}
	
	/**
	 * Class constructor, manage params from component
	 *
	 * @access private
	 * @return boolean
	 */
	public function __construct(&$subject) {
		// Load component config
		$this->cParams = JComponentHelper::getParams ( 'com_jmap' );
		
		// Framework object dependencies
		$this->app = JFactory::getApplication ();
		$this->db = JFactory::getDbo ();
		
		// Avoid operations if plugin is executed in frontend
		if (! $this->app->getClientId ()) {
			return;
		}
		
		// Avoid operation if not supported extension is detected
		if(!in_array($this->app->input->get('option'), array('com_content', 'com_k2', 'com_zoo'))) {
			return;
		}
		
		parent::__construct ( $subject );
		
		if (file_exists ( JPATH_ROOT . '/administrator/components/com_jmap/framework/language/multilang.php' )) {
			include_once (JPATH_ROOT . '/administrator/components/com_jmap/framework/language/multilang.php');
		}
		
		$this->adaptersMapping = array (
				'com_content.article' => array (
						'file' => JPATH_ROOT . '/components/com_content/helpers/route.php',
						'class' => 'ContentHelperRoute',
						'method' => 'getArticleRoute' 
				),
				'com_k2.item' => array (
						'file' => JPATH_ROOT . '/components/com_k2/helpers/route.php',
						'class' => 'K2HelperRoute',
						'method' => 'getItemRoute' 
				),
				'com_zoo.item' => array (
						'routing' => 'jmap',
						'file' => JPATH_ROOT . '/administrator/components/com_jmap/framework/route/helper.php',
						'class' => 'JMapRouteHelper',
						'method' => 'getItemRoute' 
				) 
		);
		
		// Manage partial language translations
		$jLang = JFactory::getLanguage ();
		$jLang->load ( 'com_jmap', JPATH_ROOT . '/administrator/components/com_jmap', 'en-GB', true, true );
		if ($jLang->getTag () != 'en-GB') {
			$jLang->load ( 'com_jmap', JPATH_SITE, null, true, false );
			$jLang->load ( 'com_jmap', JPATH_SITE . '/administrator/components/com_jmap', null, true, false );
		}
	}
}