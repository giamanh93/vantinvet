<?php
// namespace administrator\components\com_jmap\models;
/**
 * @package JMAP::METAINFO::administrator::components::com_jmap
 * @subpackage models
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
jimport ( 'joomla.filesystem.file' );

/**
 * Metainfo concrete model
 * Operates not on DB but directly on a cached copy of the XML sitemap file
 *
 * @package JMAP::METAINFO::administrator::components::com_jmap
 * @subpackage models
 * @since 3.2
 */
class JMapModelMetainfo extends JMapModel {
	/**
	 * Number of XML records
	 * 
	 * @access private
	 * @var Int
	 */
	private $recordsNumber;
	
	/**
	 * Main get data method
	 *
	 * @access public
	 * @return Object[]
	 */
	public function getData() {
		// Load data from XML file, parse it to load records
		$cachedSitemapFilePath = JPATH_COMPONENT_ADMINISTRATOR . '/cache/metainfo/';
		
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
		
		// Detect PHP 7
		$php7 = false;
		if (version_compare(PHP_VERSION, '7.0', '>=')) {
			$php7 = true;
		}
		
		// Start processing
		try {
			// Now check if the file correctly exists
			if(JFile::exists($cachedSitemapFilePath . $cachedSitemapFilename)) {
				$loadedSitemapXML = simplexml_load_file($cachedSitemapFilePath . $cachedSitemapFilename);
			} else {
				throw new JMapException ( JText::sprintf ( 'COM_JMAP_METAINFO_NOCACHED_FILE_EXISTS', $this->_db->getErrorMsg () ), 'error' );
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
				
				// Get a search filter
				$searchFilter = $this->state->get('searchpageword', null);
				$stateFilter = $this->state->get('state', null);
				$excludeStateFilter = $this->state->get('excludestate', null);
				// Cycle on every links, filter by search word if any and augment with link metainfo
				foreach ($loadedSitemapXML as $index=>&$url) {
					// Evaluate filtering by search word
					if($searchFilter) {
						// Evaluate position or exact match
						if($this->getState('exactsearchpage', null)) {
							$isMatching = $url->loc == $searchFilter;
						} else {
							$isMatching = (stripos($url->loc, $searchFilter) !== false);
						}
						if(!$isMatching) {
							array_splice($loadedSitemapXML, $index, 1);
							
							// Re-assign array
							if($php7) {
								$loadedSitemapXML = array_values($loadedSitemapXML);
							}
							continue;
						}
					}
					
					$url = (object)(array)$url;
					// Load meta info for this link from the table and augment the array and object
					$query = "SELECT *" .
							 "\n FROM " . $this->_db->quoteName('#__jmap_metainfo') .
							 "\n WHERE " . $this->_db->quoteName('linkurl') . " = " . $this->_db->quote($url->loc);
					$metaInfos = $this->_db->setQuery($query)->loadObject();
					// This link has valid metainfo
					if(isset($metaInfos->id)) {
						$url->metainfos = $metaInfos;
					}
					
					// Evaluate filtering by state
					if($stateFilter) {
						if(isset($url->metainfos->published)) {
							$isMatching = ((int)$url->metainfos->published === ($stateFilter == 'P' ? 1 : 0));
							if(!$isMatching) {
								array_splice($loadedSitemapXML, $index, 1);
								// Re-assign array
								if($php7) {
									$loadedSitemapXML = array_values($loadedSitemapXML);
								}
								continue;
							}
						} else {
							array_splice($loadedSitemapXML, $index, 1);
							// Re-assign array
							if($php7) {
								$loadedSitemapXML = array_values($loadedSitemapXML);
							}
							continue;
						}
					}
					
					// Evaluate filtering by exclude state
					if(is_numeric($excludeStateFilter)) {
						if(isset($url->metainfos->excluded)) {
							$isMatching = ((int)$url->metainfos->excluded === (int)$excludeStateFilter);
							if(!$isMatching) {
								array_splice($loadedSitemapXML, $index, 1);
								// Re-assign array
								if($php7) {
									$loadedSitemapXML = array_values($loadedSitemapXML);
								}
								continue;
							}
						} else {
							if((int)$excludeStateFilter == 1) {
								array_splice($loadedSitemapXML, $index, 1);
								// Re-assign array
								if($php7) {
									$loadedSitemapXML = array_values($loadedSitemapXML);
								}
								continue;
							}
						}
					}
				}
				
				// Perform array sorting if any
				$order = $this->getState('order', null);
				$jmapMetainfoOrderDir = $this->getState('order_dir', 'asc');
				
				if($order == 'link') {
					function cmpAsc($a, $b){
						return strcmp($a->loc, $b->loc);
					}
					function cmpDesc($a, $b){
						return strcmp($b->loc, $a->loc);
					}
					$callbackName = ($jmapMetainfoOrderDir == 'asc') ? 'cmpAsc' : 'cmpDesc';
					usort($loadedSitemapXML, $callbackName);
				}
			} else {
				throw new JMapException ( JText::sprintf ( 'COM_JMAP_METAINFO_EMPTY_SITEMAP', $this->_db->getErrorMsg () ), 'notice' );
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
	 * Delete DB meta info completely
	 *
	 * @access public
	 * @return boolean
	 */
	public function deleteEntity($ids) {
		try {
			$query = "DELETE FROM #__jmap_metainfo";
			$this->_db->setQuery($query);
			if(!$this->_db->execute()) {
				throw new JMapException($this->_db->getErrorMsg(), 'error');
			}
		} catch (JMapException $e) {
			$this->setError($e);
			return false;
		} catch (Exception $e) {
			$jMapException = new JMapException($e->getMessage(), 'error');
			$this->setError($jMapException);
			return false;
		}
		return true;
	}
	
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
	 * Return select lists used as filter for listEntities
	 *
	 * @access public
	 * @return array
	 */
	public function getFilters() {
		$filters = array ();
		$filters ['state'] = JHTML::_ ( 'grid.state', $this->getState ( 'state' ) );
		
		$excludeStates = array();
		$excludeStates[] = JHTML::_('select.option', null, JText::_('COM_JMAP_ALL_METAINFO_LINKS'));
		$excludeStates[] = JHTML::_('select.option', 1, JText::_('COM_JMAP_EXCLUDED_LINKS'));
		$excludeStates[] = JHTML::_('select.option', 0, JText::_('COM_JMAP_NOT_EXCLUDED_LINKS'));
		$filters ['excludestate'] = JHTML::_ ( 'select.genericlist', $excludeStates, 'filter_excludestate', 'onchange="Joomla.submitform();"', 'value', 'text', $this->getState ( 'excludestate' ));
		
		return $filters;
	}
}