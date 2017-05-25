<?php
// namespace administrator\components\com_jmap\models;
/**
 * @package JMAP::ANALYZER::administrator::components::com_jmap
 * @subpackage models
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
jimport ( 'joomla.filesystem.file' );

/**
 * Analyzer concrete model
 * Operates not on DB but directly on a cached copy of the XML sitemap file
 *
 * @package JMAP::ANALYZER::administrator::components::com_jmap
 * @subpackage models
 * @since 2.3.3
 */
class JMapModelAnalyzer extends JMapModel {
	/**
	 * Number of XML records
	 * 
	 * @access private
	 * @var Int
	 */
	private $recordsNumber;
	
	/**
	 * Counter result set
	 *
	 * @access public
	 * @return int
	 */
	public function getTotal() {
		// Return simply the XML records number
		return $this->recordsNumber;
	}
	
	/**
	 * Main get data method
	 *
	 * @access public
	 * @return Object[]
	 */
	public function getData() {
		// Load data from XML file, parse it to load records
		$cachedSitemapFilePath = JPATH_COMPONENT_ADMINISTRATOR . '/cache/analyzer/';
		
		// Has sitemap some vars such as lang or Itemid?
		$sitemapLang = $this->getState('sitemaplang', '');
		$sitemapLinksLang = $sitemapLang ? $sitemapLang . '/' : '';
		$sitemapLang = $sitemapLang ? '_' . $sitemapLang : '';
		
		$sitemapDataset = $this->getState('sitemapdataset', '');
		$sitemapDataset = $sitemapDataset ? '_dataset' . (int)$sitemapDataset : '';
		
		$sitemapItemid = $this->getState('sitemapitemid', '');
		$sitemapItemid = $sitemapItemid ? '_menuid' . (int)$sitemapItemid : '';
		
		// Final name
		$cachedSitemapFilename = 'sitemap_xml' . $sitemapLang . $sitemapDataset . $sitemapItemid . '.xml'; 
		
		// Start processing
		try {
			// Now check if the file correctly exists
			if(JFile::exists($cachedSitemapFilePath . $cachedSitemapFilename)) {
				$loadedSitemapXML = simplexml_load_file($cachedSitemapFilePath . $cachedSitemapFilename);
			} else {
				throw new JMapException ( JText::sprintf ( 'COM_JMAP_ANALYZER_NOCACHED_FILE_EXISTS', $this->_db->getErrorMsg () ), 'error' );
			}
			
			// Execute HTTP request and associate HTTP response code with each record links
			$httpClient = new JMapHttp();
			if($loadedSitemapXML->url->count()) {
				// Manage splice pagination here for the XML records
				$convertedIteratorToArray = iterator_to_array($loadedSitemapXML->url, false);
				
				// Store number of records for pagination
				$this->recordsNumber = count($convertedIteratorToArray);
				
				// Execute pagination splicing if any limit is set
				$limit = $this->getState ( 'limit' );
				if($limit) {
					$loadedSitemapXML = array_splice($convertedIteratorToArray, $this->getState ( 'limitstart' ), $this->getState ( 'limit' ));
				} else {
					$loadedSitemapXML = $convertedIteratorToArray;
				}
				
				// Now start the Analyzer
				$linksAnalyzerWorkingMode = $this->getComponentParams()->get('linksanalyzer_workingmode', 1);
				if($linksAnalyzerWorkingMode) {
					$siteRouter = JRouterSite::getInstance('site', array('mode'=>JROUTER_MODE_SEF));
				}
				// Check the Analyzer Analysis working mode for the validation of links
				$validationAnalysisStandard = (bool)($this->getComponentParams()->get('linksanalyzer_validation_analysis', 2) == 2);
				
				if( version_compare(JVERSION, '3.4', '>=')) {
					JApplicationCms::getInstance('site')->loadLanguage();
				}
				$headers = array('Accept'=>'text/html', 'User-Agent'=>'Googlebot-Image/1.0');
				foreach ($loadedSitemapXML as $url) {
					if($validationAnalysisStandard) {
						$httpResponse = $httpClient->get($url->loc, $headers);
						$url->httpstatus = $httpResponse->code;
					}
					
					// Find informations about the link, component and menu itemid if any, need a workaround to parse correctly from backend
					if($linksAnalyzerWorkingMode) {
						$baseAdmin = JURI::base();
						$baseSite = str_replace('/administrator', '', $baseAdmin);
						$fakedUrl = str_replace($baseSite, $baseAdmin, $url->loc);
						$fakedUrl = str_replace($sitemapLinksLang, '', $fakedUrl);
						// Now instantiate and parse the faked url from backend, replacing the uri base it will be = site
						$uriObject = JURI::getInstance((string)$fakedUrl);
						$parseUri = $siteRouter->parse($uriObject);
					}
					
					// Now augment the object to show informations
					$url->component = isset($parseUri['option']) ? $parseUri['option'] : JText::_('COM_JMAP_ANALYZER_NOINFO');
					$url->menuId = isset($parseUri['Itemid']) ? $parseUri['Itemid'] : JText::_('COM_JMAP_ANALYZER_NOINFO');
					$url->menuTitle = JText::_('COM_JMAP_ANALYZER_NOINFO');
					// Translate human menu
					if(isset($parseUri['Itemid'])) {
						$query = "SELECT" . $this->_db->quoteName('title') .
								 "\n FROM #__menu" .
								 "\n WHERE " . 
								 $this->_db->quoteName('id') . " = " . (int)$parseUri['Itemid'];
						$menuTitle = $this->_db->setQuery($query)->loadResult();
						$url->menuTitle = $menuTitle;
					}
				}
				
				// Perform array sorting if any
				$order = $this->getState('order', null);
				$jmapAnalyzerOrderDir = $this->getState('order_dir', 'asc');
				
				if($validationAnalysisStandard && $order == 'httpstatus') {
					function cmpAsc($a, $b){
						return ((int)$a->httpstatus < (int)$b->httpstatus) ? -1 : 1;
					}
					function cmpDesc($a, $b){
						return ((int)$a->httpstatus > (int)$b->httpstatus) ? -1 : 1;
					}
					$callbackName = ($jmapAnalyzerOrderDir == 'asc') ? 'cmpAsc' : 'cmpDesc';
					usort($loadedSitemapXML, $callbackName);
				}
				
				if($order == 'link') {
					function cmpAsc($a, $b){
						return strcmp($a->loc, $b->loc);
					}
					function cmpDesc($a, $b){
						return strcmp($b->loc, $a->loc);
					}
					$callbackName = ($jmapAnalyzerOrderDir == 'asc') ? 'cmpAsc' : 'cmpDesc';
					usort($loadedSitemapXML, $callbackName);
				}
			} else {
				throw new JMapException ( JText::sprintf ( 'COM_JMAP_ANALYZER_EMPTY_SITEMAP', $this->_db->getErrorMsg () ), 'notice' );
			}
		} catch ( JMapException $e ) {
			$this->app->enqueueMessage ( $e->getMessage (), $e->getErrorLevel () );
			$loadedSitemapXML = array ();
		} catch ( Exception $e ) {
			$jmapException = new JMapException ( $e->getMessage (), 'error' );
			$this->app->enqueueMessage ( $jmapException->getMessage (), $jmapException->getErrorLevel () );
			$loadedSitemapXML = array ();
		}
		
		return $loadedSitemapXML;
	}
	
	/**
	 * Return select lists used as filter for listEntities
	 *
	 * @access public
	 * @return array
	 */
	public function getFilters() {
		$validationType = (int)($this->getComponentParams()->get('linksanalyzer_validation_analysis', 2));
		
		$filteringFunction = ($validationType == 2) ? 'onchange="Joomla.submitform();"' : 'class="noanalyzer" onchange="JMapAnalyzer.filterOnAsyncPage(this);"';
		$selectName = ($validationType == 2) ? 'filter_type' : 'async_type';
		
		$datasourceTypes = array ();
		$datasourceTypes [] = JHTML::_ ( 'select.option', null, JText::_ ( 'COM_JMAP_ANALYZER_ALL' ) );
		$datasourceTypes [] = JHTML::_ ( 'select.option', '200', JText::_ ( 'COM_JMAP_ANALYZER_200' ) );
		$datasourceTypes [] = JHTML::_ ( 'select.option', '301', JText::_ ( 'COM_JMAP_ANALYZER_301' ) );
		$datasourceTypes [] = JHTML::_ ( 'select.option', '303', JText::_ ( 'COM_JMAP_ANALYZER_303' ) );
		$datasourceTypes [] = JHTML::_ ( 'select.option', '404', JText::_ ( 'COM_JMAP_ANALYZER_404' ) );
		$datasourceTypes [] = JHTML::_ ( 'select.option', '500', JText::_ ( 'COM_JMAP_ANALYZER_500' ) );
		$filters ['type'] = JHTML::_ ( 'select.genericlist', $datasourceTypes, $selectName, $filteringFunction, 'value', 'text', $this->getState ( 'link_type' ) );
		
		return $filters;
	}
}