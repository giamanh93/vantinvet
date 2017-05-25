<?php
// namespace administrator\components\com_jmap\models;
/**
 * @package JMAP::CPANEL::administrator::components::com_jmap
 * @subpackage models
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
define ('SERVER_REMOTE_URI', 'http://storejextensions.org/dmdocuments/updates/');
define ('UPDATES_FORMAT', '.json');
jimport('joomla.filesystem.file');

/**
 * CPanel model responsibility
 *
 * @package JMAP::CPANEL::administrator::components::com_jmap
 * @subpackage models
 * @since 1.0
 */
interface IJMapModelCpanel {
	/**
	 * Aggiorna i nuovi menu sources aggiunti in menu se non presenti come
	 * risorse sources in #__map
	 * 
	 * @access public
	 * @return boolean
	 */
	public function syncMenuSources();
	
	/**
	 * Storing entity by ORM table
	 *
	 * @access public
	 * @return boolean
	 */
	public function storeEntity($buffer = null);
}
  
/**
 * CPanel autorefresh menu responsibility
 *
 * @package JMAP::CPANEL::administrator::components::com_jmap
 * @subpackage models
 * @since 1.0
 */
interface IJMapModelUpdater {
	/**
	 * Connect to remte server through socket to check if some updates
	 * and related informations are available
	 *
	 * @access public
	 * @return boolean
	 */
	public function getUpdates(JMapHttp $httpClient);
}

/**
 * CPanel model concrete implementation
 *
 * @package JMAP::CPANEL::administrator::components::com_jmap
 * @subpackage models
 * @since 1.0
 */
class JMapModelCpanel extends JMapModel implements IJMapModelCpanel, IJMapModelUpdater {
	  
	/**
	 * Costruzione list entities query
	 *
	 * @access private
	 * @param string $field
	 * @param string $value
	 * @return string
	 */
	private function buildListQuery($field, $value, $condition = ' = ', $table = '#__jmap') {
		//Dyna query
		$query = "SELECT COUNT(*)" . 
				 "\n FROM" .
				 "\n" . $this->_db->quoteName($table) . " AS s" . 
				 "\n WHERE " . $this->_db->quoteName($field) . $condition . $this->_db->quote($value);
		return $query;
	}

	/**
	 * Main get data method
	 *
	 * @access public
	 * @return array
	 */
	public function getData() {
		$result = array();
		// Build query
		$query = $this->buildListQuery ('published', 1);
		$this->_db->setQuery ( $query );
		$result['publishedDataSource'] = $this->_db->loadResult ();
		
		$query = $this->buildListQuery ('id', 0, ' > ');
		$this->_db->setQuery ( $query );
		$result['totalDataSource'] = $this->_db->loadResult ();
		
		$query = $this->buildListQuery ('type', 'menu');
		$this->_db->setQuery ( $query );
		$result['menuDataSource'] = $this->_db->loadResult ();
		
		$query = $this->buildListQuery ('type', 'user');
		$this->_db->setQuery ( $query );
		$result['userDataSource'] = $this->_db->loadResult ();
		
		$query = $this->buildListQuery ('published', 1, ' = ', '#__jmap_datasets');
		$this->_db->setQuery ( $query );
		$result['datasets'] = $this->_db->loadResult ();
	
		return $result;
	}
	
	/**
	 * Restituisce le select list usate dalla view per l'interfaccia
	 *
	 * @access public
	 * @param Object& $record
	 * @return array
	 */
	public function getLists($record = null) {
		$lists = array();
	
		$lists['languages'] = null;
		$lists['menu_datasource_filters'] = null;
		$lists['datasets_filters'] = null;
		$languageOptions = JMapHtmlLanguages::getAvailableLanguageOptions();
		$defaultSiteLang = $languageOptions[0]->value;
	
		// Check if multilanguage dropdown is always active
		$cParams = $instance = JComponentHelper::getParams('com_jmap');
		if($cParams->get('showalways_language_dropdown', false)) {
			$languageFilterPluginEnabled = true;
		} else {
			// Detect Joomla Language Filter plugin enabled
			$query = "SELECT " . $this->_db->quoteName('enabled') .
					 "\n FROM #__extensions" .
					 "\n WHERE " . $this->_db->quoteName('element') . " = " . $this->_db->quote('languagefilter') .
					 "\n OR " . $this->_db->quoteName('element') . " = " . $this->_db->quote('jfdatabase');
			$this->_db->setQuery($query);
			$languageFilterPluginEnabled = $this->_db->loadResult();
		}
		if(count($languageOptions) >= 2 && $languageFilterPluginEnabled) {
			$lists['languages']	= JHTML::_('select.genericlist',   $languageOptions, 'language_option', 'class="inputbox"', 'value', 'text', $defaultSiteLang, 'language_option' );
		}
		
		// Check if some valid datasets are available
		$query = "SELECT dset.id AS value, dset.name AS text" .
				 "\n FROM " . $this->_db->quoteName('#__jmap_datasets') . " AS dset" .
				 "\n WHERE dset.published = 1";
		$this->_db->setQuery($query);
		$datasetsFilters = $this->_db->loadObjectList();
		if(count($datasetsFilters)) {
			array_unshift($datasetsFilters, JHTML::_('select.option',  null, '- '. JText::_('COM_JMAP_NODATASET_FILTER' ) .' -' ));
			$lists['datasets_filters']	= JHTML::_('select.genericlist',   $datasetsFilters, 'datasets_filters', 'class="inputbox"', 'value', 'text', null, 'datasets_filters' );
		}

		// Get default list for all menu pointing to com_jmap that have filtered data source active
		$query = "SELECT m.id AS value, m.title AS text, m.params" .
				 "\n FROM " . $this->_db->quoteName('#__menu') . " AS m" .
				 "\n INNER JOIN " . $this->_db->quoteName('#__extensions') . " AS e" .
				 "\n ON m.component_id = e.extension_id" .
				 "\n WHERE " . $this->_db->quoteName('element') . " = " . $this->_db->quote('com_jmap') .
				 "\n AND m.client_id = 0 AND m.published = 1";
		$this->_db->setQuery($query);
		$menuDataSourceFilters = $this->_db->loadObjectList();
		if(count($menuDataSourceFilters)) {
			foreach ($menuDataSourceFilters as $key=>&$singleMenu) {
				$menuParams = json_decode($singleMenu->params);
				if(!isset($menuParams->datasource_filter[0])) {
					array_splice($menuDataSourceFilters, $key, 1);
				}
			}
		}
		if(count($menuDataSourceFilters)) {
			// Check if multilanguage is enabled and the remove default prefix is active
			$pluginLangFilter = JPluginHelper::getPlugin('system', 'languagefilter');
			$removeDefaultPrefix = @json_decode($pluginLangFilter->params)->remove_default_prefix;
			if($lists['languages'] != null && $removeDefaultPrefix) { } else {
				array_unshift($menuDataSourceFilters, JHTML::_('select.option',  null, '- '. JText::_('COM_JMAP_NOMENU_FILTER' ) .' -' ));
				$lists['menu_datasource_filters']	= JHTML::_('select.genericlist',   $menuDataSourceFilters, 'menu_datasource_filters', 'class="inputbox"', 'value', 'text', null, 'menu_datasource_filters' );
			}
		}
		
		return $lists;
	}
	
	/**
	 * Load entity from ORM table
	 *
	 * @access public
	 * @param int $id
	 * @return Object&
	 */
	public function loadEntity($id) {
		try {
			// Update robots.txt add entry Sitemap if not exists
			$targetRobot = null;
			// Try standard robots.txt
			if(JFile::exists(JPATH_ROOT . '/robots.txt')) {
				$targetRobot = JPATH_ROOT . '/robots.txt';
			} elseif (JFile::exists(JPATH_ROOT . '/robots.txt.dist')) { // Fallback on distribution version
				$targetRobot = JPATH_ROOT . '/robots.txt.dist';
				$this->setState('robots_version', 'distribution');
			} else {
				throw new JMapException(JText::_('COM_JMAP_ROBOTS_NOTFOUND'), 'error');
			}
				
			// Robots.txt found!
			if($targetRobot !== false) {
				// If file permissions ko
				if(!$robotContents = JFile::read($targetRobot)) {
					throw new JMapException(JText::_('COM_JMAP_ERROR_READING_ROBOTS'), 'error');
				}
			}
				
		} catch(JMapException $e) {
			$this->setError($e);
			return false;
		}  catch(Exception $e) {
			$jmapException = new JMapException($e->getMessage(), 'error');
			$this->setError($jmapException);
			return false;
		}
		
		return $robotContents;
	}
	
	/**
	 * Storing entity by ORM table
	 *
	 * @access public
	 * @return boolean
	 */
	public function storeEntity($buffer = null) {
		try {
			// Data posted required, otherwise avoid write anything
			if(!$buffer) {
				throw new JMapException(JText::_('COM_JMAP_ROBOTS_NO_DATA'), 'error');
			}
			
			$targetRobot = null;
			// Try standard robots.txt
			if(JFile::exists(JPATH_ROOT . '/robots.txt')) {
				$targetRobot = JPATH_ROOT . '/robots.txt';
			} elseif (JFile::exists(JPATH_ROOT . '/robots.txt.dist')) { // Fallback on distribution version
				$targetRobot = JPATH_ROOT . '/robots.txt.dist';
			} else {
				throw new JMapException(JText::_('COM_JMAP_ROBOTS_NOTFOUND'), 'error');
			}
			
			// If file permissions ko on rewrite updated contents
			$originalPermissions = null;
			if(!is_writable($targetRobot)) {
				$originalPermissions = intval(substr(sprintf('%o', fileperms($targetRobot)), -4), 8);
				@chmod($targetRobot, 0755);
			}
			if(@!JFile::write($targetRobot, $buffer)) {
				throw new JMapException(JText::_('COM_JMAP_ERROR_WRITING_ROBOTS'), 'error');
			}
			// Check if permissions has been changed and recover the original in that case
			if($originalPermissions) {
				@chmod($targetRobot, $originalPermissions);
			}
		} catch(JMapException $e) {
			$this->setError($e);
			return false;
		}  catch(Exception $e) {
			$jmapException = new JMapException($e->getMessage(), 'error');
			$this->setError($jmapException);
			return false;
		}
		return true;
	}
	
	/**
	 * Aggiorna i nuovi menu sources aggiunti in menu se non presenti come
	 * risorse sources in #__map e elimina quelli in stato stale
	 * 
	 * @access public
	 * @return boolean
	 */
	public function syncMenuSources() {
		// 1) Seleziona i menu items in #__menu_types
		$query = "SELECT *" .
		 		  "\n FROM #__menu_types";
		$this->_db->setQuery($query);
		$currentMenus = $this->_db->loadObjectList('title');
		$numCurrentMenus = count($currentMenus); 
		 
		// 2) Seleziona tutte le sources di type=menu in #__jmap
		$query = "SELECT id, name" .
 				 "\n FROM #__jmap" .
		 		 "\n WHERE " .  $this->_db->quoteName('type') . ' = ' . $this->_db->quote('menu');
		$this->_db->setQuery($query);
		$currentMenuSources = $this->_db->loadObjectList('name');
		$numCurrentMenuSources = count($currentMenuSources);
		 
		try {
			// 3) Per differenze determina le sources mancanti o non più presenti
		 	if($numCurrentMenus > $numCurrentMenuSources) { // Sources da inserire
		 		// Se non esiste un array key con il name presente in #__menu_types
		 		$chunksQuery = array();
		 		foreach ($currentMenus as $key=>$menu) {
		 			if(!array_key_exists($menu->title, $currentMenuSources)) {
		 				$chunksQuery[] = "(" .
		 						$this->_db->quote('menu') . ","  .
		 						$this->_db->quote($menu->title) . ","  .
		 						$this->_db->quote($menu->description) . ", 1, 1)";
		 	
		 			}
		 		}
		 		$sql = "INSERT INTO #__jmap (" .
		 				$this->_db->quoteName('type') . ", " .
		 				$this->_db->quoteName('name') . ", " .
		 				$this->_db->quoteName('description') . ", " .
		 				$this->_db->quoteName('published') . ", " .
		 				$this->_db->quoteName('ordering') .
		 				") VALUES " . implode(",\n", $chunksQuery) .
		 				"\n ON DUPLICATE KEY UPDATE " .$this->_db->quoteName('type') . " = " . $this->_db->quote('menu');
		 	
		 		$this->_db->setQuery($sql);
		 		if(!$this->_db->execute()) {
		 			throw new JMapException(JText::_('COM_JMAP_ERRORSYNC_INSERT'), 'notice');
		 		}
					
					// Reorder post insert
				JTable::addIncludePath ( JPATH_ADMINISTRATOR . '/components/com_jmap/tables' );
				$table = JTable::getInstance ( 'Sources', 'Table' );
				if (! $table->reorder ()) {
					throw new JMapException(JText::_('COM_JMAP_ERRORSYNC_REORDER'), 'notice');
				}
			} elseif($numCurrentMenus < $numCurrentMenuSources) { // Sources stale
				$menuNames = array();
				foreach ($currentMenus as $currentMenuName=>$menuObject) {
					$menuNames[] = $this->_db->quote($currentMenuName);
				}
				$implodedValidMenuSources = implode(",", $menuNames);
				$sql = "DELETE FROM #__jmap" .
					   "\n WHERE " .  $this->_db->quoteName('type') . ' = ' . $this->_db->quote('menu') .
					   "\n AND " .  $this->_db->quoteName('name') . " NOT IN (" . $implodedValidMenuSources . ")";
				$this->_db->setQuery($sql);
				if(!$this->_db->execute()) {
					throw new JMapException(JText::_('COM_JMAP_ERRORSYNC_DELETE'), 'notice');
				}
			} else { // Synced resources, controllo solo se il title/name unique p.key è cambiato
				$currentMenuKeys = array_map('strtolower', array_keys($currentMenus));
				$currentMenuSourcesKeys = array_map('strtolower', array_keys($currentMenuSources));
				asort($currentMenuKeys, SORT_STRING);
				asort($currentMenuSourcesKeys, SORT_STRING);
				$currentMenuKeys = array_values($currentMenuKeys);
				$currentMenuSourcesKeys = array_values($currentMenuSourcesKeys);
				
				// P.key variata, si necessita un update per il sync mantain 
				if($currentMenuKeys !== $currentMenuSourcesKeys) {
					$menuNames = array();
					foreach ($currentMenus as $currentMenuName=>$menuObject) {
						$menuNames[] = $this->_db->quote($currentMenuName);
					}
					$implodedValidMenuSources = implode(",", $menuNames);
					$sql = "DELETE FROM #__jmap" .
							"\n WHERE " .  $this->_db->quoteName('type') . ' = ' . $this->_db->quote('menu') .
							"\n AND " .  $this->_db->quoteName('name') . " NOT IN (" . $implodedValidMenuSources . ")";
					$this->_db->setQuery($sql);
					if(!$this->_db->execute()) {
						throw new JMapException(JText::_('COM_JMAP_ERRORSYNC_DELETE'), 'notice');
					}
					
					// Se non esiste un array key con il name presente in #__menu_types
					$chunksQuery = array();
					foreach ($currentMenus as $key=>$menu) {
						if(!array_key_exists($menu->title, $currentMenuSources)) {
							$chunksQuery[] = "(" .
									$this->_db->quote('menu') . ","  .
									$this->_db->quote($menu->title) . ","  .
									$this->_db->quote($menu->description) . ", 0, 1)";
					
						}
					}
					$sql = "INSERT INTO #__jmap (" .
							$this->_db->quoteName('type') . ", " .
							$this->_db->quoteName('name') . ", " .
							$this->_db->quoteName('description') . ", " .
							$this->_db->quoteName('published') . ", " .
							$this->_db->quoteName('ordering') .
							") VALUES " . implode(",\n", $chunksQuery) .
							"\n ON DUPLICATE KEY UPDATE " .$this->_db->quoteName('type') . " = " . $this->_db->quote('menu');;
					
					$this->_db->setQuery($sql);
					if(!$this->_db->execute()) {
						throw new JMapException(JText::_('COM_JMAP_ERRORSYNC_INSERT'), 'notice');
					}
						
					// Reorder post insert
					JTable::addIncludePath ( JPATH_ADMINISTRATOR . '/components/com_jmap/tables' );
					$table = JTable::getInstance ( 'Sources', 'Table' );
					if (! $table->reorder ()) {
						throw new JMapException(JText::_('COM_JMAP_ERRORSYNC_REORDER'), 'notice');
					}
				}
			}
		} catch(JMapException $e) {
			$this->setError($e);
			return false;
		}  catch(Exception $e) {
			$jmapException = new JMapException($e->getMessage(), 'error');
			$this->setError($jmapException);
			return false;
		}
		 
		return true;
	} 
	
	/**
	 * Get by remote server informations for new updates of this extension
	 *
	 * @access public
	 * @return mixed An object json decoded from server if update information retrieved correctly otherwise false
	 */
	public function getUpdates(JMapHttp $httpClient) {
		// Updates server remote URI
		$option = $this->getState('option');
		if(!$option) {
			return false;
		}
		$url = SERVER_REMOTE_URI . $option . UPDATES_FORMAT;
	
		// Try to get informations
		try {
			$response = $httpClient->get($url)->body;
			if($response) {
				$decodedUpdateInfos = json_decode($response);
			}
			return $decodedUpdateInfos;
		} catch(JMapException $e) {
			return false;
		}  catch(Exception $e) {
			return false;
		}
	}
	
	/**
	 * Class constructor
	 *
	 * @access public
	 * @param $config array
	 * @return Object&
	 */
	public function __construct($config = array()) {
		parent::__construct ( $config );
		$componentParams = JComponentHelper::getParams($this->option);
		$this->setState('cparams', $componentParams);
	}
}