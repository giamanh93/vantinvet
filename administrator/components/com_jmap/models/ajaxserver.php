<?php
//namespace components\com_jmap\models; 
/** 
 * @package JMAP::AJAXSERVER::components::com_jmap 
 * @subpackage models
 * @author Joomla! Extensions Store
 * @copyright (C)2015 Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined('_JEXEC') or die('Restricted access');
jimport('joomla.filesystem.file');

/**
 * Ajax Server model responsibilities
 *
 * @package JMAP::AJAXSERVER::components::com_jmap  
 * @subpackage models
 * @since 1.0
 */
interface IAjaxserverModel {
	public function loadAjaxEntity($id, $param, $DIModels) ;
}

/** 
 * Classe che gestisce il recupero dei dati per il POST HTTP
 * @package JMAP::AJAXSERVER::components::com_jmap  
 * @subpackage models
 * @since 1.0
 */
class JMapModelAjaxserver extends JMapModel implements IAjaxserverModel {
	/**
	 * Check if an extension is currently installed on Joomla system and answer accordingly with an encoded object
	 *
	 * @access private
	 * @param string $tableName
	 * @param Object[] $additionalModels Array for additional injected models type hinted by interface
	 * @return Object
	 */
	private function checkExtension($extensionName, $additionalModels = null) {
		// Query to check
		$query = "SELECT " . $this->_db->quoteName('extension_id') . 
				 "\n FROM " . $this->_db->quoteName('#__extensions') . 
				 "\n WHERE " . $this->_db->quoteName('element') . " = " . $this->_db->Quote($extensionName);
		$this->_db->setQuery($query);
		$extensionID = $this->_db->loadResult();

		$response = (object) array('extensionFound' => (bool) $extensionID);

		return $response;
	}

	/**
	 * Check if an extension is currently installed on Joomla system and answer accordingly with an encoded object
	 *
	 * @access private
	 * @param string $tableName
	 * @param Object[] $additionalModels Array for additional injected models type hinted by interface
	 * @return Object
	 */
	private function loadDataSources($additionalModels = null) {
		// Response JSON object
		$response = new stdClass();

		try {
			// Default for published data sources
			$where[] = "\n v.published = 1";

			$query = "SELECT v.id, v.type, v.name" .
					 "\n FROM #__jmap AS v" .
					 "\n WHERE " . implode(' AND ', $where) .
					 "\n ORDER BY v.ordering ASC";
			$this->_db->setQuery ( $query );

			$response->datasources = $this->_db->loadObjectList ();
			if ($this->_db->getErrorNum ()) {
				throw new JMapException(JText::sprintf('COM_JMAP_ERROR_RETRIEVING_DATA', $this->_db->getErrorMsg()), 'error');
			}

			// All completed succesfully
			$response->result = true;
		} catch (JMapException $e) {
			$response->result = false;
			$response->exception_message = $e->getMessage();
			return $response;
		} catch (Exception $e) {
			$jmapException = new JMapException(JText::sprintf('COM_JMAP_ERROR_RETRIEVING_DATA', $e->getMessage()), 'error');
			$response->result = false;
			$response->exception_message = $jmapException->getMessage();
			return $response;
		}

		return $response;
	}
	
	/**
	 * Get the top level host domain for each kind of URL needed to avoid Alexa redirects on CURL exec
	 *
	 * @access private
	 * @param string $url
	 * @return string
	 */
	private function getHost($url) {
		if (strpos ( $url, "http" ) !== false) {
			$httpurl = $url;
		} else {
			$httpurl = "http://" . $url;
		}
		$parse = parse_url ( $httpurl );
		$domain = $parse ['host'];
	
		$parts = explode ( ".", $domain );
		$count = sizeof ( $parts ) - 1;
	
		if ($count > 1) {
			$slicedParts = array_slice( $parts, -2, 1 );
			$slice = ( strlen( reset( $slicedParts ) ) == 2 || in_array(reset( $slicedParts ), array('com', 'org', 'gov', 'net'))) && ( count( $parts ) > 2 ) ? 3 : 2;
			$result = implode( '.', array_slice( $parts, ( 0 - $slice ), $slice ) );
		} else {
			$result = $domain;
		}
		return $result;
	}
	
	/**
	 * Fetch SEO stats from remote services both Google and Alexa,
	 * based on Seo stats lib that is able to calculate Google Page rank
	 *
	 *
	 * @access private
	 * @param Object[] $additionalModels Array for additional injected models type hinted by interface
	 * @return Object
	 */
	private function fetchSeoStats($additionalModels = null) {
		// Response JSON object
		$response = new stdClass ();
		$cParams = $this->getComponentParams();
		
		// Set the resulting array
		$pageRanksArray = array (
				'googlerank' => JText::_ ( 'COM_JMAP_NA' ),
				'alexarank' => JText::_ ( 'COM_JMAP_NA' ),
				'alexabacklinks' => JText::_ ( 'COM_JMAP_NA' ),
				'alexapageloadtime' => JText::_ ( 'COM_JMAP_NA' ),
				'googleindexedlinks' => JText::_ ( 'COM_JMAP_NA' ),
				'alexagraph' => '',
				'semrushrank' => JText::_ ( 'COM_JMAP_NA' ),
				'semrushkeywords' => JText::_ ( 'COM_JMAP_NA' ),
				'semrushcompetitors' => JText::_ ( 'COM_JMAP_NA' ),
				'semrushgraph' => ''
		);
		
		try {
			if (! function_exists ( 'curl_init' )) {
				throw new JMapException ( JText::_ ( 'COM_JMAP_CURL_NOT_SUPPORTED' ), 'error' );
			}

			if (1 == ini_get ( 'safe_mode' ) || 'on' === strtolower ( ini_get ( 'safe_mode' ) )) {
				throw new JMapException ( JText::_ ( 'COM_JMAP_PHP_SAFEMODE_ON' ), 'error' );
			}

			// API REQUEST, define target URL, site default or custom url
			$siteUrl = JUri::root(false);
			$customUrl = JComponentHelper::getParams('com_jmap')->get('seostats_custom_link', null);
			$url = $customUrl ? $customUrl : $siteUrl;
			
			if($cParams->get('seostats_gethost', 1)) {
				$alexaHostUrl = $this->getHost($url);
			} else {
				$alexaHostUrl = $url;
			}

			// Create a new SEOstats instance.
			$seostats = new JMapSeostats ();

			// Bind the URL to the current SEOstats instance.
			if ($seostats->setUrl ( $url )) {
				$pageRanksArray ['alexarank'] = JMapSeostatsServicesAlexa::getGlobalRank ($alexaHostUrl);
				$pageRanksArray ['alexabacklinks'] = JMapSeostatsServicesAlexa::getBacklinkCount ($alexaHostUrl);
				$pageRanksArray ['alexapageloadtime'] = JMapSeostatsServicesAlexa::getPageLoadTime ($alexaHostUrl);
				$pageRanksArray ['alexagraph'] = JMapSeostatsServicesAlexa::getTrafficGraph (1, $alexaHostUrl, 800, 320, 12);
				$pageRanksArray ['googlerank'] = JMapSeostatsServicesAlexa::getBounceRate ($alexaHostUrl);
				$pageRanksArray ['googleindexedlinks'] = JMapSeostatsServicesGoogle::getSiteindexTotal ();

				// SEMRush stats
				$topLevelDomain = 'us';
				$parsedUrl = explode('.', $url);
				$topLevelDomainDetected = array_pop($parsedUrl);
				if(in_array($topLevelDomainDetected, JMapSeostatsServicesSemrush::getDBs())) {
					$topLevelDomain = $topLevelDomainDetected;
				}
				$pageRanksArray ['semrushrank'] = JMapSeostatsServicesSemrush::getDomainRank($url, $topLevelDomain);
				$pageRanksArray ['semrushkeywords'] = JMapSeostatsServicesSemrush::getOrganicKeywords($url, $topLevelDomain);
				$pageRanksArray ['semrushcompetitors'] = JMapSeostatsServicesSemrush::getCompetitors($url, $topLevelDomain);
				$pageRanksArray ['semrushgraph'] = JMapSeostatsServicesSemrush::getDomainGraph(1, $url, $topLevelDomain);

				// If no semrush keywords/competitors data fallback to Alexa scraping
				if($pageRanksArray['semrushkeywords'] == JText::_ ( 'COM_JMAP_NA' )) {
					$pageRanksArray['semrushkeywords'] = JMapSeostatsServicesAlexa::getKeywords ($alexaHostUrl);
				}
				if($pageRanksArray['semrushcompetitors'] == JText::_ ( 'COM_JMAP_NA' )) {
					$pageRanksArray['semrushcompetitors'] = JMapSeostatsServicesAlexa::getCompetitors ($alexaHostUrl);
				}

				// All completed successfully
				$response->result = true;
				$response->seostats = $pageRanksArray;
			}
		} catch ( JMapException $e ) {
			$response->result = false;
			$response->exception_message = $e->getMessage ();
			$response->errorlevel = $e->getErrorLevel();
			$response->seostats = $pageRanksArray;
			return $response;
		} catch ( Exception $e ) {
			$jmapException = new JMapException ( JText::sprintf ( 'COM_JMAP_ERROR_RETRIEVING_SEOSTATS', $e->getMessage () ), 'error' );
			$response->result = false;
			$response->exception_message = $jmapException->getMessage ();
			$response->errorlevel = $jmapException->getErrorLevel();
			$response->seostats = $pageRanksArray;
			return $response;
		}

		return $response;
	}
	
	/**
	 * Fetch informations from the Google API for SERP to check the indexing status of a link
	 *
	 * @access private
	 * @param string $linkUrl
	 * @param Object[] $additionalModels Array for additional injected models type hinted by interface
	 * @return Object
	 */
	private function getIndexedStatus($linkUrl, $additionalModels = null) {
		// Response JSON object
		$response = new stdClass ();
		$cParams = $this->getComponentParams();
		
		// Random user agents DB
		$userAgents=array(
				"Mozilla/5.0 (Windows NT 6.3; rv:36.0) Gecko/20100101 Firefox/36.0",
				"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10; rv:33.0) Gecko/20100101 Firefox/33.0",
				"Mozilla/5.0 (X11; Linux i586; rv:31.0) Gecko/20100101 Firefox/31.0",
				"Mozilla/5.0 (Windows NT 6.1; WOW64; rv:31.0) Gecko/20130401 Firefox/31.0",
				"Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.36",
				"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2227.1 Safari/537.36",
				"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/36.0.1944.0 Safari/537.36",
				"Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2224.3 Safari/537.36",
				"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_3) AppleWebKit/537.75.14 (KHTML, like Gecko) Version/7.0.3 Safari/7046A194A",
				"Mozilla/5.0 (iPad; CPU OS 6_0 like Mac OS X) AppleWebKit/536.26 (KHTML, like Gecko) Version/6.0 Mobile/10A5355d Safari/8536.25",
				"Mozilla/5.0 (Windows NT 6.1; WOW64; Trident/7.0; AS; rv:11.0) like Gecko",
				"Mozilla/5.0 (compatible, MSIE 11, Windows NT 6.3; Trident/7.0; rv:11.0) like Gecko",
				"Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; WOW64; Trident/6.0)",
				"Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/6.0)",
				"Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/5.0)",
		 		"Mozilla/5.0 (compatible; MSIE 10.0; Macintosh; Intel Mac OS X 10_7_3; Trident/6.0)");
	    $ua = $userAgents[rand (0, count($userAgents) - 1)];

		// Set number of max results to evaluate in the SERP
		$maxResults = $cParams->get('linksanalyzer_serp_numresults', 10);
		$engine = $cParams->get('linksanalyzer_indexing_engine', 'webcrawler');
		$referer = $engine == 'webcrawler' ? 'http://www.webcrawler.com/' : 'http://www.bing.com/';

	    // Format the request header array
		$headers = array (
				'Cache-Control' => 'max-age=0',
				'User-Agent' => $ua,
				'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
				'Referer' => $referer,
				'Accept-Language' => 'en-GB, en'
		);

		// Start querying the SERP search engine
		try {
			if (! class_exists( 'DOMDocument' )) {
				throw new JMapException ( JText::_ ( 'COM_JMAP_DOMDOCUMENT_NOT_SUPPORTED' ), 'error' );
			}
			
			// Initialize indexing status to false AKA 'Not indexed'
			$response->indexing_status = 0;
			
			// Check if the query URL has been requested
			if ($linkUrl) {
				// Instantiante a new HTTP client
				$httpClient = new JMapHttp();
				
				// Remove the html prefix in the url and separators if any, this helps the algo to be more exact
				if($cParams->get('linksanalyzer_remove_separators', 1)) {
					$linkUrl = preg_replace('#http.?:\/\/#i', '', $linkUrl);
					$linkUrl = JString::str_ireplace('-', ' ', $linkUrl);
					$linkUrl = JString::str_ireplace('_', ' ', $linkUrl);
					$linkUrl = preg_replace('/\s[^0-9]\s/i', ' ', $linkUrl);
				}
				
				// Remove the html prefix in the url and separators if any, this helps the algo to be more exact
				$removeSlashes = $cParams->get('linksanalyzer_remove_slashes', 2);
				if($removeSlashes == 1 || ($removeSlashes >= 2 && substr_count($linkUrl, '/') <= ($removeSlashes - 1))) {
					$linkUrl = JString::str_ireplace('/', ' ', $linkUrl);
				}
				
				// Perform the query to the http://www.webcrawler.com and limit the URL length to max 106 chars
				$encodedURL = urlencode(JString::substr($linkUrl, 0 , 106));

				// Switch the web crawler
				if($engine == 'webcrawler') {
					$httpResponse = $httpClient->get('http://www.webcrawler.com/info.wbcrwl.sbox/search/web?q=' . $encodedURL . '&submit=Search', $headers);
				} else {
					$httpResponse = $httpClient->get('http://www.bing.com/search?q=' . $encodedURL, $headers);
				}
				
				// If the web service returns a HTTP 200 OK go on to parse results
				if($httpResponse->code == 200) {
					// Get the response body
					$responseBody = $httpResponse->body;

					// New instance of DOMDocument parser
					$doc = new DOMDocument('1.0', 'UTF-8');
					libxml_use_internal_errors(true);
					
					//Load the DOMDocument document
					$doc->loadHTML($responseBody);
					libxml_clear_errors();

					// Set up the DOMXPath and the css className to find in the document for the target SERP elements
					$finder = new DomXPath($doc);
					
					// Find SERP nodes, object of DOMNodeList
					if($engine == 'webcrawler') {
						$classname = 'resultDisplayUrl';
						$resultsNodes = $finder->query("//div[contains(concat(' ', normalize-space(@class), ' '), ' $classname ')]");
					} else {
						$classname = 'b_attribution';
						$resultsNodes = $finder->query("//div[contains(concat(' ', normalize-space(@class), ' '), ' $classname ')]");
					}
				
					// If SERP nodes has been found go on to check if the link is indexed for this domain
					if($resultsNodes->length) {
						// Evaluate n SERP results based on the config param
						for ($i=0,$k=$maxResults; $i<$maxResults; $i++) {
							// Get node by object instance, DOMNode
							$node = $resultsNodes->item($i);
							if(is_object($node)) {
								// Security safe, check again if the class name is correct
								if($node->getAttribute('class') == $classname) {
									$trimmedNode = trim($node->nodeValue);
									$urlArray = explode('/', $trimmedNode);
									// Extract the SERP domain and assume that it's indexed for the current query
									$serpDomain = $urlArray[0];
									if($cParams->get('linksanalyzer_remove_separators', 1)) {
										$serpDomain = JString::str_ireplace('-', ' ', $serpDomain);
										$serpDomain = JString::str_ireplace('_', ' ', $serpDomain);
									}
									if(preg_match('/' . $serpDomain . '/i', $linkUrl)) {
										$response->indexing_status = 1;
										break;
									}
								}
							}
						}
					} else {
						// No SERP found for this link, return as not available info
						$response->indexing_status = -1;
					}
				}
				
				// Final all went well, no exceptions triggered
				$response->result = true;
			}
		} catch ( JMapException $e ) {
			$response->result = false;
			$response->exception_message = $e->getMessage ();
			return $response;
		} catch ( Exception $e ) {
			$jmapException = new JMapException ( JText::sprintf ( 'COM_JMAP_ANALYZER_INDEXING_ERROR', $e->getMessage () ), 'error' );
			$response->result = false;
			$response->exception_message = $jmapException->getMessage ();
			return $response;
		}
	
		return $response;
	}
	
	/**
	 * Check what sitemaps are cached on disk to show accordingly green labels
	 *
	 * @access private
	 * @param $idEntity
	 * @param $additionalModels
	 *
	 * @Return array
	 */
	private function getPrecachedSitemaps($queryStringLinksArray, $additionalModels = null) {
		// Response JSON object
		$response = new stdClass ();

		try {
			// Init empty status
			$response->sitemapLinksStatus = array ();

			// Start to set an associative array based on url parsing and file existance
			if (! empty ( $queryStringLinksArray ) && is_array ( $queryStringLinksArray )) {
				$joomlaConfig = JFactory::getConfig();
				$localTimeZone = new DateTimeZone($joomlaConfig->get('offset'));
				foreach ( $queryStringLinksArray as $singlePostedSitemapLink ) {
					$filename = 'sitemap_';
					$extractedQuery = parse_url ( $singlePostedSitemapLink, PHP_URL_QUERY );
					parse_str ( $extractedQuery, $parsedLink );
					// Evaluate format
					if (! empty ( $parsedLink ['format'] )) {
						$filename .= $parsedLink ['format'];
					}
					// Evaluate language
					if (! empty ( $parsedLink ['lang'] )) {
						$filename .= '_' . $parsedLink ['lang'];
					}
					// Evaluate dataset
					if (! empty ( $parsedLink ['dataset'] )) {
						$filename .= '_dataset' . $parsedLink ['dataset'];
					}
					// Evaluate Itemid
					if (! empty ( $parsedLink ['Itemid'] )) {
						$filename .= '_menuid' . $parsedLink ['Itemid'];
					}

					if (JFile::exists ( JPATH_COMPONENT_SITE . '/precache/' . $filename . '.xml' )) {
						// get last generation time
						$lastGenerationTimestamp = filemtime ( JPATH_COMPONENT_SITE . '/precache/' . $filename . '.xml' );
						$dateObject = new JDate($lastGenerationTimestamp);
						$dateObject->setTimezone($localTimeZone);

						$response->sitemapLinksStatus [$singlePostedSitemapLink] = array (
								'cached' => true,
								'lastgeneration' => $dateObject->format('Y-m-d', true)
						);
					} else {
						$response->sitemapLinksStatus [$singlePostedSitemapLink] = false;
					}
				}
			}

			// All completed succesfully
			$response->result = true;
		} catch ( Exception $e ) {
			$jmapException = new JMapException ( $e->getMessage (), 'error' );
			$response->result = false;
			$response->exception_message = $jmapException->getMessage ();
			return $response;
		}

		return $response;
	}

	/**
	 * Get file info to delete and check if file for precache exists
	 * In that case delete the file and clear cache
	 *
	 * @access private
	 * @param $fileInfo
	 * @param $additionalModels
	 *
	 * @Return array
	 */
	private function deletePrecachedSitemap($fileInfo, $additionalModels = null) {
		// Response JSON object
		$response = new stdClass();
	
		try {
			// Resource Action detection dall'HTTP method name
			$HTTPMethod = $this->app->input->server->get('REQUEST_METHOD', 'POST');
	
			if ($HTTPMethod !== 'POST') {
				throw new JMapException(JText::_('COM_JMAP_INVALID_RESTFUL_METHOD'), 'error');
			}
	
			// Start to set an associative array based on url parsing and file existance
			if(!empty($fileInfo)) {
				$filename = 'sitemap_';
				// Evaluate format
				if(!empty($fileInfo->format)) {
					$filename .= $fileInfo->format;
				}
				// Evaluate language
				if(!empty($fileInfo->lang)) {
					$filename .= '_' . $fileInfo->lang;
				}
				// Evaluate dataset
				if(!empty($fileInfo->dataset)) {
					$filename .= '_dataset' . $fileInfo->dataset;
				}
				// Evaluate Itemid
				if(!empty($fileInfo->Itemid)) {
					$filename .= '_menuid' . $fileInfo->Itemid;
				}
					
				if(JFile::exists(JPATH_COMPONENT_SITE . '/precache/' . $filename . '.xml')) {
					if(!@unlink(JPATH_COMPONENT_SITE . '/precache/' . $filename . '.xml')) {
						throw new JMapException(JText::_('COM_JMAP_PRECACHING_ERROR_DELETING_FILE'), 'error');
					}

					// Check also if a temp precached file is still present and clear it
					if(JFile::exists(JPATH_COMPONENT_SITE . '/precache/temp_' . $filename . '.xml')) {
						@unlink(JPATH_COMPONENT_SITE . '/precache/temp_' . $filename . '.xml');
					}
				}
			}
	
			// All completed succesfully
			$response->result = true;
		} catch (Exception $e) {
			$jmapException = new JMapException($e->getMessage(), 'error');
			$response->result = false;
			$response->exception_message = $jmapException->getMessage();
			return $response;
		}
	
		return $response;
	}
	
	/**
	 * Load fields for selected database table
	 * 
	 * @access private
	 * @param string $tableName
	 * @param Object[] $additionalModels Array for additional injected models type hinted by interface
	 * @return array
	 */
	private function loadTableFields($tableName, $additionalModels = null) {
		// Fields select list
		$queryFields = "SHOW COLUMNS " . 
					   "\n FROM " . $this->_db->quoteName($tableName);
		$this->_db->setQuery($queryFields);
		$elements = $this->_db->loadColumn();

		return $elements;
	}

	/**
	 * Manage store/update Pingomatic entity record
	 * 
	 * @access private
	 * @param $idEntity
	 * @param $additionalModels
	 *
	 * @Return array
	 */
	private function storeUpdatePingomatic($idEntity, $additionalModels = null) {
		// Response JSON object
		$response = new stdClass();

		// Store on ORM Table
		$table = $this->getTable('Pingomatic');
		$this->requestArray[$this->requestName]['lastping'] = date('Y-m-d H:i:s');
		try {
			if (!$table->bind($this->requestArray[$this->requestName], true)) {
				throw new JMapException($table->getError(), 'error');
			}

			if (!$table->check()) {
				throw new JMapException($table->getError(), 'error');
			}

			if (!$table->store(false)) {
				throw new JMapException($table->getError(), 'error');
			}
		} catch (JMapException $e) {
			$response->result = false;
			$response->errorMsg = $e->getMessage();
			return $response;
		} catch (Exception $e) {
			$jmapException = new JMapException($e->getMessage(), 'error');
			$response->result = false;
			$response->errorMsg = $jmapException->getMessage();
			return $response;
		}

		// Manage exceptions from DB Model and return to JS domain
		$response->result = true;
		$response->id = $table->id;
		$response->lastping = $table->lastping;

		return $response;
	}
	
	/**
	 * Manage store/update for menu priorities
	 *
	 * @access private
	 * @param $params
	 * @param $additionalModels
	 *
	 * @Return array
	 */
	private function storeUpdatePriority($params, $additionalModels = null) {
		// Response JSON object
		$response = new stdClass();
	
		// Store on ORM Table
		$table = $this->getTable($params->type);
		$table->load((int)$params->itemId);
		$table->priority = $params->priorityValue;
	
		try {
			// Switch on subaction
			if(!isset($params->task)) {
				throw new JMapException(JText::_('COM_JMAP_VALIDATON_ERROR_NOPRIORITY'), 'warning');
			}
			if($params->task == 'store') {
				if (!$table->store(false, $params->itemId)) {
					throw new JMapException($table->getError(), 'warning');
				}
			} else {
				// Check if record still exists in database
				if(!$table->id) {
					throw new JMapException(JText::_('COM_JMAP_VALIDATON_ERROR_NOPRIORITY'), 'warning');
				}
				// Delete always
				if (!$table->delete()) {
					throw new JMapException($table->getError(), 'warning');
				}
			}
		} catch (JMapException $e) {
			$response->result = false;
			$response->errorMsg = $e->getMessage();
			return $response;
		} catch (Exception $e) {
			$jmapException = new JMapException($e->getMessage(), 'warning');
			$response->result = false;
			$response->errorMsg = $jmapException->getMessage();
			return $response;
		}
	
		// Manage exceptions from DB Model and return to JS domain
		$response->result = true;
	
		return $response;
	}
	/**
	 * Get existing priority value for menu items
	 *
	 * @access private
	 * @param $params
	 * @param $additionalModels
	 *
	 * @Return array
	 */
	private function getPriority($params, $additionalModels = null) {
		// Response JSON object
		$response = new stdClass();
		
		// Store on ORM Table
		$table = $this->getTable($params->type);
		
		try {
			if (!$table->load((int)$params->iditem)) {
				throw new JMapException($table->getError(), 'warning');
			}
			
			// Load a non existing record
			if(!$table->id) {
				throw new JMapException('nopriority', 'warning');
			}
		} catch (JMapException $e) {
			$response->result = false;
			$response->errorMsg = $e->getMessage();
			return $response;
		} catch (Exception $e) {
			$jmapException = new JMapException($e->getMessage(), 'warning');
			$response->result = false;
			$response->errorMsg = $jmapException->getMessage();
			return $response;
		}
		
		// Manage exceptions from DB Model and return to JS domain
		$response->result = true;
		$response->priority = $table->priority;
		
		return $response;
	}

	/**
	 * Manage robots.txt entry
	 *
	 * @access private
	 * @param $idEntity
	 * @param $additionalModels
	 *
	 * @Return array
	 */
	private function robotsSitemapEntry($queryStringLink, $additionalModels = null) {
		// Response JSON object
		$response = new stdClass();

		try {
			// Resource Action detection dall'HTTP method name
			$HTTPMethod = $this->app->input->server->get('REQUEST_METHOD', 'POST');

			if ($HTTPMethod !== 'POST') {
				throw new JMapException(JText::_('COM_JMAP_INVALID_RESTFUL_METHOD'), 'error');
			}

			// Update robots.txt add entry Sitemap if not exists
			$targetRobot = null;
			// Try standard robots.txt
			if(JFile::exists(JPATH_ROOT . '/robots.txt')) {
				$targetRobot = JPATH_ROOT . '/robots.txt';
			} elseif (JFile::exists(JPATH_ROOT . '/robots.txt.dist')) { // Fallback on distribution version
				$targetRobot = JPATH_ROOT . '/robots.txt.dist';
			} else {
				throw new JMapException(JText::_('COM_JMAP_ROBOTS_NOTFOUND'), 'error');
			}
			
			// Robots.txt found!
			if($targetRobot !== false) {
				// If file permissions ko
				if(!$robotContents = JFile::read($targetRobot)) {
					throw new JMapException(JText::_('COM_JMAP_ERROR_READING_ROBOTS'), 'error');
				}
				
				$newEntry = null;
				// Entry for this sitemap 
				if(!stristr($robotContents, 'Sitemap: ' . $queryStringLink)) {
					$toAppend = null;
					// Check if JSitemap added already some entries
					if(!stristr($robotContents, '# JSitemap')) {
						// Empty line double EOL
						$toAppend = PHP_EOL . PHP_EOL . '# JSitemap entries';
					}
					$toAppend .= PHP_EOL . 'Sitemap: ' . $queryStringLink;
					$newEntry = $robotContents . $toAppend;
				}
				
				// If file permissions ko on rewrite updated contents
				if($newEntry) {
					$originalPermissions = null;
					if(!is_writable($targetRobot)) {
						$originalPermissions = intval(substr(sprintf('%o', fileperms($targetRobot)), -4), 8);
						@chmod($targetRobot, 0755);
					}
					if(@!JFile::write($targetRobot, $newEntry)) {
						throw new JMapException(JText::_('COM_JMAP_ERROR_WRITING_ROBOTS'), 'error');
					}
					// Check if permissions has been changed and recover the original in that case
					if($originalPermissions) {
						@chmod($targetRobot, $originalPermissions);
					}
				} else {
					throw new JMapException(JText::_('COM_JMAP_ENTRY_ALREADY_ADDED'), 'error');
				}
			}
			
			// All completed succesfully
			$response->result = true;
		} catch (JMapException $e) {
			$response->result = false;
			$response->errorMsg = $e->getMessage();
			return $response;
		} catch (Exception $e) {
			$jmapException = new JMapException($e->getMessage(), 'error');
			$response->result = false;
			$response->errorMsg = $jmapException->getMessage();
			return $response;
		}
		
		return $response;
	}

	/**
	 * Store meta info for a given url
	 *
	 * @access private
	 * @param Object $dataObject
	 * @param Object[] $additionalModels Array for additional injected models type hinted by interface
	 * @return Object
	 */
	private function saveMeta($dataObject, $additionalModels = null) {
		// Response JSON object
		$response = new stdClass();
	
		try {
			// Check if the link already exists in this table
			$selectQuery = "SELECT" . $this->_db->quoteName('id') .
						   "\n FROM " . $this->_db->quoteName('#__jmap_metainfo') .
						   "\n WHERE" .
						   $this->_db->quoteName('linkurl') . " = " . $this->_db->quote($dataObject->linkurl);
			$linkExists = $this->_db->setQuery ( $selectQuery )->loadResult();

			// If the link exists just update it, otherwise insert a new one
			if($linkExists) {
				$query = "UPDATE" .
						 "\n " . $this->_db->quoteName('#__jmap_metainfo') .
						 "\n SET " .
						 "\n " . $this->_db->quoteName('meta_title') . " = " . $this->_db->quote($dataObject->meta_title) . "," .
						 "\n " . $this->_db->quoteName('meta_desc') . " = " . $this->_db->quote($dataObject->meta_desc) . "," .
						 "\n " . $this->_db->quoteName('meta_image') . " = " . $this->_db->quote($dataObject->meta_image) . "," .
						 "\n " . $this->_db->quoteName('robots') . " = " . $this->_db->quote($dataObject->robots) .
						 "\n WHERE " .
						 "\n " . $this->_db->quoteName('linkurl') . " = " . $this->_db->quote($dataObject->linkurl);
				$this->_db->setQuery ( $query );
			} else {
				$query = "INSERT INTO" .
						 "\n " . $this->_db->quoteName('#__jmap_metainfo') . "(" .
						 $this->_db->quoteName('linkurl') . "," .
						 $this->_db->quoteName('meta_title') . "," .
						 $this->_db->quoteName('meta_desc') . "," .
						 $this->_db->quoteName('meta_image') . "," .
						 $this->_db->quoteName('robots') . "," .
						 $this->_db->quoteName('published') . "," .
						 $this->_db->quoteName('excluded') . ") VALUES (" .
						 $this->_db->quote($dataObject->linkurl) . "," .
						 $this->_db->quote($dataObject->meta_title) . "," .
						 $this->_db->quote($dataObject->meta_desc) . "," .
						 $this->_db->quote($dataObject->meta_image) . "," .
						 $this->_db->quote($dataObject->robots) . "," .
						 $this->_db->quote($dataObject->published) . "," .
						 $this->_db->quote($dataObject->excluded) . ")";
				$this->_db->setQuery ( $query );
			}
			$this->_db->execute ();
			if ($this->_db->getErrorNum ()) {
				throw new JMapException(JText::sprintf('COM_JMAP_METAINFO_ERROR_STORING_DATA', $this->_db->getErrorMsg()), 'error');
			}
	
			// All completed succesfully
			$response->result = true;
		} catch (JMapException $e) {
			$response->result = false;
			$response->exception_message = $e->getMessage();
			return $response;
		} catch (Exception $e) {
			$jmapException = new JMapException(JText::sprintf('COM_JMAP_ERROR_RETRIEVING_DATA', $e->getMessage()), 'error');
			$response->result = false;
			$response->exception_message = $jmapException->getMessage();
			return $response;
		}
	
		return $response;
	}
	
	/**
	 * Store meta info for a given url
	 *
	 * @access private
	 * @param Object $dataObject
	 * @param Object[] $additionalModels Array for additional injected models type hinted by interface
	 * @return Object
	 */
	private function deleteMeta($dataObject, $additionalModels = null) {
		// Response JSON object
		$response = new stdClass();
	
		try {
			// Check if the link already exists in this table
			$selectQuery = "SELECT" . $this->_db->quoteName('id') .
						   "\n FROM " . $this->_db->quoteName('#__jmap_metainfo') .
						   "\n WHERE" .
						   $this->_db->quoteName('linkurl') . " = " . $this->_db->quote($dataObject->linkurl);
			$linkExists = $this->_db->setQuery ( $selectQuery )->loadResult();

			// If the link exists just update it, otherwise insert a new one
			if($linkExists) {
				$query = "DELETE" .
						 "\n FROM " . $this->_db->quoteName('#__jmap_metainfo') .
						 "\n WHERE " .
						 "\n " . $this->_db->quoteName('linkurl') . " = " . $this->_db->quote($dataObject->linkurl);
				$this->_db->setQuery ( $query );
				$this->_db->execute ();
				if ($this->_db->getErrorNum ()) {
					throw new JMapException(JText::sprintf('COM_JMAP_METAINFO_ERROR_STORING_DATA', $this->_db->getErrorMsg()), 'error');
				}
			} else {
				$response->result = true;
				$response->exception_message = JText::_('COM_JMAP_NO_METAINFO_SAVED');
				return $response;
			}
	
			// All completed succesfully
			$response->result = true;
		} catch (JMapException $e) {
			$response->result = false;
			$response->exception_message = $e->getMessage();
			return $response;
		} catch (Exception $e) {
			$jmapException = new JMapException(JText::sprintf('COM_JMAP_ERROR_RETRIEVING_DATA', $e->getMessage()), 'error');
			$response->result = false;
			$response->exception_message = $jmapException->getMessage();
			return $response;
		}
	
		return $response;
	}
	
	/**
	 * Store meta info for a given url
	 *
	 * @access private
	 * @param Object $dataObject
	 * @param Object[] $additionalModels Array for additional injected models type hinted by interface
	 * @return Object
	 */
	private function stateMeta($dataObject, $additionalModels = null) {
		// Response JSON object
		$response = new stdClass();
	
		try {
			// Check if the link already exists in this table
			$selectQuery = "SELECT" . $this->_db->quoteName('id') .
						   "\n FROM " . $this->_db->quoteName('#__jmap_metainfo') .
						   "\n WHERE" .
			$this->_db->quoteName('linkurl') . " = " . $this->_db->quote($dataObject->linkurl);
			$linkExists = $this->_db->setQuery ( $selectQuery )->loadResult();
	
			// If the link exists just update it, otherwise insert a new one
			if($linkExists) {
				$query = "UPDATE" .
						 "\n " . $this->_db->quoteName('#__jmap_metainfo') .
						 "\n SET " .
						 "\n " . $this->_db->quoteName($dataObject->field) . " = " . (int)($dataObject->fieldValue) .
						 "\n WHERE " .
						 "\n " . $this->_db->quoteName('linkurl') . " = " . $this->_db->quote($dataObject->linkurl);
				$this->_db->setQuery ( $query );
				$this->_db->execute ();
				if ($this->_db->getErrorNum ()) {
					throw new JMapException(JText::sprintf('COM_JMAP_METAINFO_ERROR_STORING_DATA', $this->_db->getErrorMsg()), 'error');
				}
			} elseif(!$linkExists && $dataObject->field == 'excluded') {
				$query = "INSERT INTO" .
						 "\n " . $this->_db->quoteName('#__jmap_metainfo') . "(" .
						 $this->_db->quoteName('linkurl') . "," .
						 $this->_db->quoteName('published') . "," .
						 $this->_db->quoteName('excluded') . ") VALUES (" .
						 $this->_db->quote($dataObject->linkurl) . "," .
						 $this->_db->quote(0) . "," .
						 $this->_db->quote($dataObject->fieldValue) . ")";
				$this->_db->setQuery ( $query );
				$this->_db->execute ();
				if ($this->_db->getErrorNum ()) {
					throw new JMapException(JText::sprintf('COM_JMAP_METAINFO_ERROR_STORING_DATA', $this->_db->getErrorMsg()), 'error');
				}
			} else {
				$response->result = true;
				$response->exception_message = JText::_('COM_JMAP_NO_METAINFO_SAVED');
				return $response;
			}
	
			// All completed succesfully
			$response->result = true;
		} catch (JMapException $e) {
			$response->result = false;
			$response->exception_message = $e->getMessage();
			return $response;
		} catch (Exception $e) {
			$jmapException = new JMapException(JText::sprintf('COM_JMAP_ERROR_RETRIEVING_DATA', $e->getMessage()), 'error');
			$response->result = false;
			$response->exception_message = $jmapException->getMessage();
			return $response;
		}
	
		return $response;
	}

	/**
	 * Submit the sitemap to Baidu using XML-RPC
	 *
	 * @static
	 * @access private
	 * @param string $sitemapLink
	 * @param Object[] $additionalModels Array for additional injected models type hinted by interface
	 * @return Object
	 */
	private function submitSitemapToBaidu($sitemapLink, $additionalModels = null) {
		// Response JSON object
		$response = new stdClass ();
	
		try {
			// Ensure CURL support
			if (! function_exists ( 'curl_init' )) {
				throw new JMapException ( JText::_ ( 'COM_JMAP_CURL_NOT_SUPPORTED' ), 'error' );
			}

			require_once JPATH_COMPONENT_ADMINISTRATOR . '/framework/pinger/xmlrpc.php';

			$xml_rpc_server = "ping.baidu.com";
			$xml_rpc_port = 80;
			$xml_rpc_path = "/ping/RPC2";
			$xml_rpc_method = "weblogUpdates.ping";

			$param1 = new jmap_xmlrpcval ( $sitemapLink, 'string' );
			$param2 = new jmap_xmlrpcval ( $sitemapLink, 'string' );

			// create the message
			$message = new jmap_xmlrpcmsg ( $xml_rpc_method, array($param1, $param2) );
			$client = new jmap_xmlrpc_client ( $xml_rpc_path, $xml_rpc_server, $xml_rpc_port );
			$xmlrpc_response = $client->send ( $message );

			if (!$xmlrpc_response) {
				throw new JMapException ( JText::_ ( 'COM_JMAP_XMLRPC_NORESPONSE' ), 'error' );
			}
			if ($xmlrpc_response->faultCode () != 0) {
				throw new JMapException ( JText::sprintf ( 'COM_JMAP_XMLRPC_FAULTCODE', $xmlrpc_response->faultCode (), $xmlrpc_response->faultString ()), 'error' );
			}

			// All went well
			$response->result = true;
		} catch ( JMapException $e ) {
			$response->result = false;
			$response->exception_message = $e->getMessage ();
			$response->errorlevel = $e->getErrorLevel();
			return $response;
		} catch ( Exception $e ) {
			$jmapException = new JMapException ( JText::sprintf ( 'COM_JMAP_ERROR_PINGING_SITEMAP_TOBAIDU', $e->getMessage () ), 'error' );
			$response->result = false;
			$response->exception_message = $jmapException->getMessage ();
			$response->errorlevel = $jmapException->getErrorLevel();
			return $response;
		}
	
		return $response;
	}

	/**
	 * Get license informations about this user subscription license email code
	 * Use the RESTFul interface API on the remote License resource
	 *
	 * @static
	 * @access private
	 * @param Object[] $additionalModels Array for additional injected models type hinted by interface
	 * @return Object
	 */
	private function getLicenseStatus($additionalModels = null) {
		// Get email license code
		$code = JComponentHelper::getParams('com_jmap')->get('registration_email', null);
	
		// Instantiate HTTP client
		$HTTPClient = new JMapHttp();
	
		/*
		 * Status domain code
		* Remote API Call
		*/
		$headers = array('Accept'=>'application/json', 'User-agent' => 'JSitemap Pro updater');
		if($code) {
			try {
				$prodCode = 'jsitemappro';
				$cdFuncUsed = 'str_' . 'ro' . 't' . '13';
				$HTTPResponse = $HTTPClient->get($cdFuncUsed('uggc' . '://' . 'fgberwrkgrafvbaf' . '.bet') . "/option,com_easycommerce/action,licenseCode/email,$code/productcode,$prodCode", $headers);
			} catch (Exception $e) {
				$HTTPResponse = new stdClass();
				$HTTPResponse->body = '{"success":false,"reason":"connection_error","details":"' . $e->getMessage() . '"}';
			}
		} else {
			$HTTPResponse = new stdClass();
			$HTTPResponse->body = '{"success":false,"reason":"nocode_inserted"}';
		}
			
		// Deserializing della response
		try {
			$objectHTTPResponse = json_decode($HTTPResponse->body);
			if(!is_object($objectHTTPResponse)) {
				throw new Exception('decoding_error');
			}
		} catch (Exception $e) {
			$HTTPResponse = new stdClass();
			$HTTPResponse->body = '{"success":false,"reason":"' . $e->getMessage() . '"}';
			$objectHTTPResponse = json_decode($HTTPResponse->body);
		}
	
		return $objectHTTPResponse;
	}
	
	/**
	 * Perform the asyncronous update of the component
	 * 1- Dowload the remote update package file
	 * 2- Use the Joomla installer to install it
	 * 3- Return status to the js app
	 *
	 * @static
	 * @access private
	 * @param Object[] $additionalModels Array for additional injected models type hinted by interface
	 * @return Object
	 */
	private function downloadComponentUpdate($additionalModels = null) {
		// Response JSON object
		$response = new stdClass ();
		$cdFuncUsed = 'str_' . 'ro' . 't' . '13';
		$ep = $cdFuncUsed('uggc' . '://' . 'fgberwrkgrafvbaf' . '.bet' . '/XZY1306TSPQnifs3243560923kfuxnj35td1rtt45663f.ugzy');
		$file_path = JFactory::getConfig()->get('tmp_path', '/tmp') . '/KML1306GFCDavsf3243560923xshkaw35gq1egg45663s.zip';

		try {
			// Ensure CURL support
			if (! function_exists ( 'curl_init' )) {
				throw new JMapException ( JText::_ ( 'COM_JMAP_CURL_NOT_SUPPORTED' ), 'error' );
			}

			// Firstly test if the server is up and HTTP 200 OK
			$ch = curl_init($ep);
			curl_setopt( $ch, CURLOPT_NOBODY, true );
			curl_setopt( $ch, CURLOPT_HEADER, false );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, false );
			curl_setopt( $ch, CURLOPT_MAXREDIRS, 3 );
			curl_exec( $ch );

			$headerInfo = curl_getinfo( $ch );
			curl_close( $ch );
			if($headerInfo['http_code'] != 200 || !$headerInfo['download_content_length']) {
				throw new JMapException ( JText::_ ( 'COM_JMAP_ERROR_DOWNLOADING_REMOTE_FILE' ), 'error' );
			}

			// 1- Download the remote update package file and put in local file
			$fp = fopen ($file_path, 'w+');
			$ch = curl_init();
			curl_setopt( $ch, CURLOPT_URL, $ep );
			curl_setopt( $ch, CURLOPT_BINARYTRANSFER, true );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, false );
			curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
			curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 60 );
			curl_setopt( $ch, CURLOPT_FILE, $fp );
			curl_exec( $ch );
			curl_close( $ch );
			fclose( $fp );

			if (!filesize($file_path)) {
				throw new JMapException ( JText::_ ( 'COM_JMAP_ERROR_WRITING_LOCAL_FILE' ), 'error' );
			}

			// All went well
			$response->result = true;
		} catch ( JMapException $e ) {
			$response->result = false;
			$response->exception_message = $e->getMessage ();
			$response->errorlevel = $e->getErrorLevel();
			return $response;
		} catch ( Exception $e ) {
			$jmapException = new JMapException ( JText::sprintf ( 'COM_JMAP_ERROR_UPDATING_COMPONENT', $e->getMessage () ), 'error' );
			$response->result = false;
			$response->exception_message = $jmapException->getMessage ();
			$response->errorlevel = $jmapException->getErrorLevel();
			return $response;
		}

		return $response;
	}
	
	/**
	 * Perform the asyncronous update of the component
	 * 1- Dowload the remote update package file
	 * 2- Use the Joomla installer to install it
	 * 3- Return status to the js app
	 *
	 * @static
	 * @access private
	 * @param Object[] $additionalModels Array for additional injected models type hinted by interface
	 * @return Object
	 */
	private function installComponentUpdate($additionalModels = null) {
		// Response JSON object
		$response = new stdClass ();
		$file_path = JFactory::getConfig()->get('tmp_path', '/tmp') . '/KML1306GFCDavsf3243560923xshkaw35gq1egg45663s.zip';
	
		try {
			// Unpack the downloaded package file.
			$package = JInstallerHelper::unpack($file_path, true);
			if(!$package) {
				throw new JMapException ( JText::_ ( 'COM_JMAP_ERROR_EXTRACTING_UPDATES' ), 'error' );
			}

			// 2- Use the Joomla installer to install it
			// New plugin installer
			$updateInstaller = new JInstaller ();
			if (! $updateInstaller->install ( $package['extractdir'] )) {
				throw new JMapException ( JText::_ ( 'COM_JMAP_ERROR_INSTALLING_UPDATES' ), 'error' );
			}

			// Delete dirty files and folder
			unlink($file_path);
			$it = new RecursiveDirectoryIterator($package['extractdir'], RecursiveDirectoryIterator::SKIP_DOTS);
			$files = new RecursiveIteratorIterator($it,
					RecursiveIteratorIterator::CHILD_FIRST);
			foreach($files as $file) {
				if ($file->isDir()){
					rmdir($file->getRealPath());
				} else {
					unlink($file->getRealPath());
				}
			}
			// Delete the now empty folder
			rmdir($package['extractdir']);

			// All went well
			$response->result = true;
		} catch ( JMapException $e ) {
			$response->result = false;
			$response->exception_message = $e->getMessage ();
			$response->errorlevel = $e->getErrorLevel();
			return $response;
		} catch ( Exception $e ) {
			$jmapException = new JMapException ( JText::sprintf ( 'COM_JMAP_ERROR_UPDATING_COMPONENT', $e->getMessage () ), 'error' );
			$response->result = false;
			$response->exception_message = $jmapException->getMessage ();
			$response->errorlevel = $jmapException->getErrorLevel();
			return $response;
		}
	
		return $response;
	}
	
	/**
	 * Mimic an entities list, as ajax calls arrive are redirected to loadEntity public responsibility to get handled
	 * by specific subtask. Responses are returned to controller and encoded from view over HTTP to JS client
	 * 
	 * @access public 
	 * @param string $id Rappresenta l'op da eseguire tra le private properties
	 * @param mixed $param Parametri da passare al private handler
	 * @param Object[]& $DIModels
	 * @return Object& $utenteSelezionato
	 */
	public function loadAjaxEntity($id, $param , $DIModels) {
		//Delega la private functions delegata dalla richiesta sulla entity
		$response = $this->$id($param, $DIModels);

		return $response;
	}
}