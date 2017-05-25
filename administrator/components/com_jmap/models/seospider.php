<?php
// namespace administrator\components\com_jmap\models;
/**
 * @package JMAP::SEOSPIDER::administrator::components::com_jmap
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
 * @package JMAP::SEOSPIDER::administrator::components::com_jmap
 * @subpackage models
 * @since 3.8
 */
class JMapModelSeospider extends JMapModel {
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
		$cachedSitemapFilePath = JPATH_COMPONENT_ADMINISTRATOR . '/cache/seospider/';
		
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
				throw new JMapException ( JText::sprintf ( 'COM_JMAP_SEOSPIDER_NOCACHED_FILE_EXISTS', $this->_db->getErrorMsg () ), 'error' );
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
				
				// Perform array sorting if any
				$order = $this->getState('order', null);
				$jmapAnalyzerOrderDir = $this->getState('order_dir', 'asc');
				
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
				throw new JMapException ( JText::sprintf ( 'COM_JMAP_SEOSPIDER_EMPTY_SITEMAP', $this->_db->getErrorMsg () ), 'notice' );
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
		return array();
	}
}