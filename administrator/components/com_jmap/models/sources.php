<?php
// namespace administrator\components\com_jmap\models;
/**
 * @package JMAP::SOURCES::administrator::components::com_jmap
 * @subpackage models
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
jimport('joomla.filesystem.folder');

/**
 * Sources model responsibilities
 *
 * @package JMAP::SOURCES::administrator::components::com_jmap
 * @subpackage models
 * @since 1.0
 */
interface IJMapModelSources {
	
	/**
	 * Storing entity by ORM table
	 * 
	 * @access public
	 * @return mixed
	 */
	public function storeEntity($forceRegenerate = false, $wizard = false, $wizardModel = null);
	
	/**
	 * Try to load frontend multilevel cats manifest for this component data source
	 * To have option for multi categorization a data source needs 3 requirements:
	 * 1)Have a valid manifest.json for frontend rendering
	 * 2)Have a sqlquery managed field for cat title use
	 * 3)Have a maintable not related to categories itself
	 *
	 * @access public
	 * @param Object $record
	 * @return boolean
	 */
	public function getHasManifest($record);
	
	/**
	 * Try to load route helper manifest for this component data source
	 * If a manifest is available to execute the routing helper by JSitemap
	 * show the option accordingly in the data source edit
	 *
	 * @access public
	 * @param Object $record
	 * @return boolean
	 */
	public function getHasRouteManifest($record);
	
	/**
	 * Try to check if the component table has entity ruled by create date,
	 * and if yes filtering by latest months can be shown and afterwards applied at runtime
	 *
	 * @param Object $record
	 * @return boolean
	 */
	public function gethasCreatedDate($record);
}

/**
 * Sources model concrete implementation <<testable_behavior>>
 *
 * @package JMAP::SOURCES::administrator::components::com_jmap
 * @subpackage models
 * @since 1.0
 */
class JMapModelSources extends JMapModel implements IJMapModelSources {
	/**
	 * Records query result set
	 *
	 * @access private
	 * @var Object[]
	 */
	private $records;
	
	/**
	 * Load table fields on demand
	 *
	 * @access private
	 * @return array
	 */
	private function loadTables() {
		// Tables select list
		$tableOptions = array();
		$queryTables = "SHOW TABLES";
		$this->_db->setQuery($queryTables);
		try {
			$elements = $this->_db->loadColumn();
			if($this->_db->getErrorNum()) {
				throw new JMapException($this->_db->getErrorMsg(), 'notice');
			}
		}
		catch (JMapException $e) {
			$this->app->enqueueMessage($e->getMessage(), $e->getErrorLevel());
			$elements = array();
		} catch (Exception $e) {
			$jmapException = new JMapException($e->getMessage(), 'notice');
			$this->app->enqueueMessage($jmapException->getMessage(), $jmapException->getErrorLevel());
			$elements = array();
		}
		if(is_array($elements)) {
			$options = array();
			$tablePrefix = $this->_db->getPrefix();
			$tableOptions[] = JHTML::_('select.option', null, JText::_('COM_JMAP_SELECTTABLE'));
			foreach ($elements as $element) {
				// Reset table name by prefix
				$userElement = str_ireplace($tablePrefix, '', $element);
				$element = str_ireplace($tablePrefix, '#__', $element);
				$tableOptions[] = JHTML::_('select.option', $element, $userElement);
			}
		}
		return $tableOptions;
	}
	
	/**
	 * Load table fields on demand
	 * 
	 * @access private
	 * @param Object $record
	 * @param string $tableName
	 * @return array
	 */
	private function loadTableFields($record, $tableName) {
		// Option init
		$fieldsOptions = array();
		$fieldsOptions[] = JHTML::_('select.option', null, JText::_('COM_JMAP_SELECTFIELD'));
		if(isset($record->sqlquery_managed->$tableName) && $record->sqlquery_managed->$tableName) {
			$queryFields = 	"SHOW COLUMNS " .
							"\n FROM " . $this->_db->quoteName($record->sqlquery_managed->$tableName);
			$this->_db->setQuery($queryFields);
			try {
				$elements = $this->_db->loadColumn();
				if($this->_db->getErrorNum()) {
					throw new JMapException($this->_db->getErrorMsg(), 'notice');
				}
			}
			catch (JMapException $e) {
				$this->app->enqueueMessage($e->getMessage(), $e->getErrorLevel());
				$elements = array();
			} catch (Exception $e) {
				$jmapException = new JMapException($e->getMessage(), 'notice');
				$this->app->enqueueMessage($jmapException->getMessage(), $jmapException->getErrorLevel());
				$elements = array();
			}
			if(is_array($elements) && count($elements)) {
				foreach ($elements as $element) {
					$fieldsOptions[] = JHTML::_('select.option', $element, $element);
				}
			}
		}
		return $fieldsOptions;
	}
	
	/**
	 * Convert additional fields to SQL string format
	 * 
	 * @access private
	 * @param array& $selectConditions
	 * @param Object $chunksObject
	 * @param string $tableName
	 * @param string $fieldName
	 * @return array
	 */
	 private function convertAdditionalFields(&$selectConditions, $chunksObject, $tableName, $fieldName) {
	 	// Additional fields conversion
		if(!empty($chunksObject->$fieldName)) {
			$additionalParams = explode(PHP_EOL, $chunksObject->$fieldName);
			foreach ($additionalParams as $param) {
				$subCkunks = explode(" ", preg_replace('!\s+!', ' ', $param));
				// Case with AS
				if (count($subCkunks) > 1) {
					$firstSubChunk = $this->_db->quoteName($chunksObject->$tableName) . "." . $this->_db->quoteName($subCkunks[0]);
					$secondSubChunk = $this->_db->quoteName($subCkunks[2]);
					$asAdditionalParam = $subCkunks[2] && $subCkunks[1] === 'AS' ? ' ' . $subCkunks[1] . ' ' . $this->_db->quoteName($subCkunks[2]) : null;
					$selectConditions[] = $firstSubChunk . $asAdditionalParam;
				} else { // Case without AS so consider only first subchunk
					$firstSubChunk = $this->_db->quoteName($chunksObject->$tableName) . "." . $this->_db->quoteName($subCkunks[0]);
					$selectConditions[] = $firstSubChunk;
				}
			}
		}
		
		return $selectConditions;
	}
	
	/**
	 * Build list entities query
	 *
	 * @access protected
	 * @return string
	 */
	protected function buildListQuery() {
		// WHERE
		$where = array ();
		$whereString = null;
		$orderString = null;
		// STATE FILTER
		if ($filter_state = $this->state->get ( 'state' )) {
			if ($filter_state == 'P') {
				$where [] = 's.published = 1';
			} else if ($filter_state == 'U') {
				$where [] = 's.published = 0';
			}
		}
		
		// TYPE FILTER
		if ($this->state->get ( 'type' )) {
			$where [] = "s.type = " . $this->_db->quote($this->state->get ( 'type' ));
		}
		
		// TEXT FILTER
		if ($this->state->get ( 'searchword' )) {
			$where [] = "s.name LIKE " . $this->_db->quote("%" . $this->state->get ( 'searchword' ) . "%");
		}
		
		if (count ( $where )) {
			$whereString = "\n WHERE " . implode ( "\n AND ", $where );
		}
		
		// ORDERBY
		if ($this->state->get ( 'order' )) {
			$orderString = "\n ORDER BY " . $this->state->get ( 'order' ) . " ";
		}
		
		// ORDERDIR
		if ($this->state->get ( 'order_dir' )) {
			$orderString .= $this->state->get ( 'order_dir' );
		}
		
		$query = "SELECT s.*, u.name AS editor" . 
				 "\n FROM #__jmap AS s" .
				 "\n LEFT JOIN #__users AS u" .
				 "\n ON s.checked_out = u.id" .
				 $whereString . $orderString;
		return $query;
	}
	
	/**
	 * Building raw query with REQUEST chunks query managed
	 *
	 * @access protected
	 * @param string $chunksString
	 * @param Object& $tableObject
	 * @param boolean $wizardClient
	 * @param Object& $wizardModel
	 * @return boolean
	 */
	protected function buildRawQuery($chunksString, &$tableObject, $wizardClient = false, &$wizardModel = null) {
		// Decode chunkString in a plain stdClass object and a JRegistry object
		if($chunksString) {
			$chunksObject = json_decode($chunksString);
			$chunksRegistry = JRegistry::getInstance('sqlquery_managed');
			$chunksRegistry->loadObject($chunksObject);
		}

		// Decode params string in a JRegistry object for params evaluation
		if(isset($tableObject->params)) {
			$paramsRegistry = JRegistry::getInstance('params');
			$paramsRegistry->loadString($tableObject->params);
		}

		if(is_object($chunksObject)) {
			// Init array containers
			$mainTable = $chunksObject->table_maintable;
			$selectConditions = array();
			$joinConditions = array();
			$whereConditions = array();
			$orderbyConditions = array();
			$groupbyConditions = array();
			
			// Get required maintable table fields, if not valid throw exception
			$columnsQuery = "SHOW COLUMNS FROM " . $this->_db->quoteName($chunksObject->table_maintable);
			$this->_db->setQuery($columnsQuery);
			if(!$tableFields = $this->_db->loadColumn()) {
				throw new JMapException(sprintf(JText::_('COM_JMAP_ERROR_BUILDING_QUERY_NOEXTENSION_MAINTABLE_INSTALLED_DETECTED'), $chunksObject->table_maintable), 'error');
				return false;
			}
			
			//  ****SELECT FIELDS PROCESSING****
			// Explicit required
			$asTitleField = !empty($chunksObject->titlefield_as) ? " AS " . $this->_db->quoteName($chunksObject->titlefield_as) : null;
			$selectConditions[] = $this->_db->quoteName($mainTable) . "." . $this->_db->quoteName($chunksObject->titlefield) . $asTitleField;
			
			// Access field supported
			if(in_array('modified', $tableFields)) {
				$selectConditions[] = $this->_db->quoteName($mainTable) . "." . $this->_db->quoteName('modified');
			}

			// Explicit required
			if(!empty($tableFields) && isset($chunksObject->use_alias) && !!$chunksObject->use_alias && in_array('alias', $tableFields)) {
				$asIdField = !empty($chunksObject->idfield_as) ? " AS " . $this->_db->quoteName($chunksObject->idfield_as) : " AS " . $this->_db->quoteName($chunksObject->id);
				$selectConditions[] = "CONCAT_WS(':', " . 
									  $this->_db->quoteName($mainTable) . "." . $this->_db->quoteName($chunksObject->id) . ", " .
									  $this->_db->quoteName($mainTable) . "." . $this->_db->quoteName('alias'). ")" . $asIdField;
			} else {
				$asIdField = !empty($chunksObject->idfield_as) ? " AS " . $this->_db->quoteName($chunksObject->idfield_as) : null;
				$selectConditions[] = $this->_db->quoteName($mainTable) . "." . $this->_db->quoteName($chunksObject->id) . $asIdField;
			}
				
			// Explicit optional
			if(!empty($chunksObject->catid)) {
				// If flagged as use cat alias from 1° JOIN TABLE, should be valid conditions: First JOIN table MUST be the same as maintable and ON same field used as catid
				if(isset($chunksObject->use_catalias) && 
					!!$chunksObject->use_catalias &&
					!empty($chunksObject->table_joinfrom_jointable1) &&
					!empty($chunksObject->field_joinfrom_jointable1) &&
					$chunksObject->table_maintable == $chunksObject->table_joinfrom_jointable1 &&
					$chunksObject->catid == $chunksObject->field_joinfrom_jointable1) {
						// Get fields in jointable1
						$columnsQuery = "SHOW COLUMNS FROM " . $this->_db->quoteName($chunksObject->table_joinwith_jointable1);
						$this->_db->setQuery($columnsQuery);
						if(!$joinTableFields = $this->_db->loadColumn()) {
							throw new JMapException(JText::_('COM_JMAP_ERROR_BUILDING_QUERY_RETRIEVING_JOINTABLE1_FIELDS'), 'error');
							return false;
						}
						// If jointable1 contains alias field
						if(in_array('alias', $joinTableFields)) {
							$asCatidField = !empty($chunksObject->catidfield_as) ? " AS " . $this->_db->quoteName($chunksObject->catidfield_as) : " AS " . $this->_db->quoteName($chunksObject->catid);;
							$selectConditions[] = "CONCAT_WS(':', " . 
									  $this->_db->quoteName($mainTable) . "." . $this->_db->quoteName($chunksObject->catid) . ", " .
									  $this->_db->quoteName($chunksObject->table_joinwith_jointable1) . "." . $this->_db->quoteName('alias'). ")" . $asCatidField;
						}
				} else {
					$asCatidField = !empty($chunksObject->catidfield_as) ? " AS " . $this->_db->quoteName($chunksObject->catidfield_as) : null;
					$selectConditions[] = $this->_db->quoteName($mainTable) . "." . $this->_db->quoteName($chunksObject->catid) . $asCatidField;
				}
			}
			// Implicit optional
			$this->convertAdditionalFields ( $selectConditions, $chunksObject, 'table_maintable' ,'additionalparams_maintable' );

			// ****JOIN TABLES PROCESSING****
			for($jt=1,$maxJoin=3;$jt<=$maxJoin;$jt++) {
				// If not set continue cycle and avoid exceptions
				if(!isset($chunksObject->{'table_joinwith_jointable'.$jt})) {
					continue;
				}
				// Main base condition: 4 fields all compiled otherwise continue
				if(	empty($chunksObject->{'table_joinfrom_jointable'.$jt}) ||
				   	empty($chunksObject->{'table_joinwith_jointable'.$jt}) ||
				   	empty($chunksObject->{'field_joinfrom_jointable'.$jt}) ||
				   	empty($chunksObject->{'field_joinwith_jointable'.$jt})) {
					continue;
				}
				
				// Main JOIN WITH table name
				$joinTable = $chunksObject->{'table_joinwith_jointable'.$jt};
				//  ****SELECT FIELDS PROCESSING****
				if(!empty($chunksObject->{'field_select_jointable'.$jt})) {
					$fieldAsJointable = !empty($chunksObject->{'field_as_jointable'.$jt}) ? " AS " . $this->_db->quoteName($chunksObject->{'field_as_jointable'.$jt}) : null;
					$selectConditions[] = $this->_db->quoteName($joinTable) . "." . $this->_db->quoteName($chunksObject->{'field_select_jointable'.$jt}) . $fieldAsJointable;
				}
				// Implicit optional
				$this->convertAdditionalFields ( $selectConditions, $chunksObject, 'table_joinwith_jointable'.$jt ,'additionalparams_jointable'.$jt );

				//  ****WHERE FIELDS PROCESSING****
				for($wjt=1,$maxWhere=3;$wjt<=$maxWhere;$wjt++) {
					if(!empty($chunksObject->{'where'.$wjt.'_jointable'.$jt})) {
						// Manage IN clause by searching for multiple IDs
						if(strpos($chunksObject->{'where'.$wjt.'_value_jointable'.$jt}, ',')) {
							$jtoperatorPrefix = null;
							if($chunksObject->{'where'.$wjt.'_operator_jointable'.$jt} == '!=') {
								$jtoperatorPrefix = ' NOT';
							}
							$jtoperator = "$jtoperatorPrefix IN ";
							$whereConditions[] = $this->_db->quoteName($joinTable) . "." . $this->_db->quoteName($chunksObject->{'where'.$wjt.'_jointable'.$jt}) . $jtoperator . "(" . $chunksObject->{'where'.$wjt.'_value_jointable'.$jt} . ")";
						} else {
							$jtoperator = !empty($chunksObject->{'where'.$wjt.'_operator_jointable'.$jt}) ? " " . $chunksObject->{'where'.$wjt.'_operator_jointable'.$jt} . " " : " = ";
							$whereConditions[] = $this->_db->quoteName($joinTable) . "." . $this->_db->quoteName($chunksObject->{'where'.$wjt.'_jointable'.$jt}) . $jtoperator . $this->_db->quote($chunksObject->{'where'.$wjt.'_value_jointable'.$jt});
						}
					}
				}
				
				// ****JOIN TABLES INNERPROCESSING****
				$joinType = !empty($chunksObject->{'jointype_jointable'.$jt}) ? $chunksObject->{'jointype_jointable'.$jt} . " JOIN" : "JOIN";
				$joinConditions[] = $joinType . " " . $this->_db->quoteName($joinTable) . " ON " . 
									$this->_db->quoteName($chunksObject->{'table_joinfrom_jointable'.$jt}) . "." . $this->_db->quoteName($chunksObject->{'field_joinfrom_jointable'.$jt}) . " = " .
									$this->_db->quoteName($chunksObject->{'table_joinwith_jointable'.$jt}) . "." . $this->_db->quoteName($chunksObject->{'field_joinwith_jointable'.$jt});
				
				//  ****ORDER BY FIELDS PROCESSING****
				if(!empty($chunksObject->{'orderby_jointable'.$jt})) {
					$orderbyDirectionJointable = !empty($chunksObject->{'orderby_direction_jointable'.$jt}) ? " " . $chunksObject->{'orderby_direction_jointable'.$jt} : null;
					array_unshift($orderbyConditions, $this->_db->quoteName($chunksObject->{'table_joinwith_jointable'.$jt}) . "." . $this->_db->quoteName($chunksObject->{'orderby_jointable'.$jt}) . $orderbyDirectionJointable);
				}
					
				//  ****GROUP BY FIELDS PROCESSING****
				if(!empty($chunksObject->{'groupby_jointable'.$jt})) {
					$groupbyConditions[] = $this->_db->quoteName($joinTable) . "." . $this->_db->quoteName($chunksObject->{'groupby_jointable'.$jt});
				}
			}
			
			//  ****WHERE FIELDS PROCESSING****
			// *AUTO WHERE PART* injected fields
			if(is_array($tableFields) && count($tableFields)) {
				// Published field supported
				if(in_array('published', $tableFields)) {
					$whereConditions[] = $this->_db->quoteName($mainTable) . "." . $this->_db->quoteName('published') . " = " . $this->_db->quote(1);
				} elseif(in_array('state', $tableFields)) { // State field supported fallback
					$whereConditions[] = $this->_db->quoteName($mainTable) . "." . $this->_db->quoteName('state') . " = " . $this->_db->quote(1);
				}
			
				// Access field supported
				if(in_array('access', $tableFields)) {
					$whereConditions[] = $this->_db->quoteName($mainTable) . "." . $this->_db->quoteName('access') . " IN {aid}" ;
				}
				
				// Created field supported, set placeholder to limit items to recent months
				if($paramsRegistry->get('created_date', null) && in_array('created', $tableFields)) {
					$latestMonths = $paramsRegistry->get('created_date');
					$whereConditions[] = $this->_db->quoteName($mainTable) . "." . $this->_db->quoteName('created') . " > {" . $latestMonths . "months}" ;
				}
				
				// Language field supported
				if(in_array('language', $tableFields)) {
					$whereConditions[] =  " (" . $this->_db->quoteName($mainTable) . "." . $this->_db->quoteName('language') . " = " . $this->_db->quote('*') . 
										 " OR " . $this->_db->quoteName($mainTable) . "." . $this->_db->quoteName('language')  . " = {langtag})";
				}
			}
			// *EXPLICIT WHERE PART*
			for($wmt=1,$maxWhere=3;$wmt<=$maxWhere;$wmt++) {
				if(!empty($chunksObject->{'where'.$wmt.'_maintable'})) {
					// Manage IN clause by searching for multiple IDs
					if(strpos($chunksObject->{'where'.$wmt.'_value_maintable'}, ',')) {
						$operatorPrefix = null;
						if($chunksObject->{'where'.$wmt.'_operator_maintable'} == '!=') {
							$operatorPrefix = ' NOT';
						}
						$operator = "$operatorPrefix IN ";
						$whereConditions[] = $this->_db->quoteName($mainTable) . "." . $this->_db->quoteName($chunksObject->{'where'.$wmt.'_maintable'}) . $operator . "(" . $chunksObject->{'where'.$wmt.'_value_maintable'} . ")";
					} else {
						$operator = !empty($chunksObject->{'where'.$wmt.'_operator_maintable'}) ? " " . $chunksObject->{'where'.$wmt.'_operator_maintable'} . " " : " = ";
						$whereConditions[] = $this->_db->quoteName($mainTable) . "." . $this->_db->quoteName($chunksObject->{'where'.$wmt.'_maintable'}) . $operator . $this->_db->quote($chunksObject->{'where'.$wmt.'_value_maintable'});
					}
				}
			}
			
			//  ****ORDER BY FIELDS PROCESSING****
			if(!empty($chunksObject->{'orderby_maintable'})) {
				$orderbyDirectionMaintable = !empty($chunksObject->{'orderby_direction_maintable'}) ? " " . $chunksObject->{'orderby_direction_maintable'} : null;
				array_push($orderbyConditions, $this->_db->quoteName($chunksObject->{'table_maintable'}) . "." . $this->_db->quoteName($chunksObject->{'orderby_maintable'}) . $orderbyDirectionMaintable);
			}	
			
			//  ****GROUP BY FIELDS PROCESSING****
			if(!empty($chunksObject->{'groupby_maintable'})) {
				$groupbyConditions[] = $this->_db->quoteName($chunksObject->{'table_maintable'}) . "." . $this->_db->quoteName($chunksObject->{'groupby_maintable'});
			}
			
			// ****START BUILD CONCATENATE FINAL QUERY STRING****
			// *SELECT* STATEMENT BUILD
			$finalQueryString = "SELECT \n " . implode(", \n ", $selectConditions);
			
			// *FROM* STATEMENT BUILD		
			$finalQueryString .= "\n FROM " . $this->_db->quoteName($chunksObject->table_maintable);
			
			// *JOIN* STATEMENT BUILD
			if(count($joinConditions)) {
				$finalQueryString .= "\n " . implode("\n ", $joinConditions);
			}
			
			// *WHERE* STATEMENT BUILD
			if(count($whereConditions)) {
				$finalQueryString .=  "\n WHERE \n " . implode("\n AND ", $whereConditions);
			}
			
			// *GROUP BY BY* STATEMENT BUILD
			if(count($groupbyConditions)) {
				$finalQueryString .=  "\n GROUP BY \n " . implode(", \n ", $groupbyConditions);
			}
			
			// *ORDER BY* STATEMENT BUILD
			if(count($orderbyConditions)) {
				$finalQueryString .=  "\n ORDER BY \n " . implode(", \n ", $orderbyConditions);
			}
			
		} else {
			throw new JMapException(JText::_('COM_JMAP_ERROR_MANIFEST_FORMAT'), 'error');
			return false;
		}
	
		// Call wizard model substitutions here if is valid object $wizardModel and not wizard client
		if(!$wizardClient && is_object($wizardModel)) {
			$finalQueryString = $wizardModel->getSubstitutionsOnDemand($finalQueryString);
		}
		
		// All well done so final assignment to table object referenced for later store
		$tableObject->sqlquery = $finalQueryString;
		return true;
	}
	
	/**
	 * Main get data method
	 *
	 * @access public
	 * @return Object[]
	 */
	public function getData() {
		// Build query
		$query = $this->buildListQuery ();
		$this->_db->setQuery ( $query, $this->getState ( 'limitstart' ), $this->getState ( 'limit' ) );
		try {
			$result = $this->_db->loadObjectList ();
			if($this->_db->getErrorNum()) {
				throw new JMapException(JText::_('COM_JMAP_ERROR_RETRIEVING_DATASOURCES') . $this->_db->getErrorMsg(), 'error');
			}
		} catch (JMapException $e) {
			$this->app->enqueueMessage($e->getMessage(), $e->getErrorLevel());
			$result = array();
		} catch (Exception $e) {
			$jmapException = new JMapException($e->getMessage(), 'error');
			$this->app->enqueueMessage($jmapException->getMessage(), $jmapException->getErrorLevel());
			$result = array();
		}
		return $result;
	}
	
	/**
	 * Method to get a form object.
	 *
	 * @param array $data
	 *        	the form.
	 * @param boolean $loadData
	 *        	the form is to load its own data (default case), false if not.
	 *
	 * @return mixed JForm object on success, false on failure
	 * @since 1.6
	 */
	public function getFormFields($record) {
		// Avoid forms error on new records
		if(!$record->id) {
			return false;
		}
		jimport ( 'joomla.form.form' );
		$pluginName = strtolower($record->name);

		// Try to generate the form and bind parameters data between JRegistry and SimpleXMLElement nodes
		try {
			// Add plugin form path
			JForm::addFormPath ( JPATH_COMPONENT_ADMINISTRATOR . '/plugins/' . $pluginName );
	
			// Get the form object instance
			$form = JForm::getInstance( 'com_jmap.plugin.' . $pluginName, $pluginName, array (
					'control' => 'params',
					'load_data' => false
			), false, '/datasource' );

			if (empty ( $form )) {
				return false;
			}

			// Get and bind plugin config parameters
			$registryParams = JRegistry::getInstance('com_jmap.plugin.' . $pluginName);
			$registryParams->loadString($record->params);
			$form->bind($registryParams);

			// Load the language file for the plugin
			// Manage partial language translations
			$jLang = JFactory::getLanguage();
			$jLang->load($pluginName, JPATH_COMPONENT_ADMINISTRATOR . '/plugins/' . $pluginName, 'en-GB', true, true);
			if($jLang->getTag() != 'en-GB') {
				$jLang->load($pluginName, JPATH_COMPONENT_ADMINISTRATOR . '/plugins/' . $pluginName, null, true, false);
			}
		} catch (JMapException $e) {
			$this->app->enqueueMessage($e->getMessage(), $e->getErrorLevel());
			$elements = array();
		} catch (Exception $e) {
			$jmapException = new JMapException($e->getMessage(), 'notice');
			$this->app->enqueueMessage($jmapException->getMessage(), $jmapException->getErrorLevel());
			$elements = array();
		}

		return $form;
	}
	
	/**
	 * Return select lists used as filter for listEntities
	 *
	 * @access public 
	 * @return array
	 */
	public function getFilters() {
		$filters ['state'] = JHTML::_ ( 'grid.state', $this->getState ( 'state' ) );
		
		$datasourceTypes = array();
		$datasourceTypes[] = JHTML::_('select.option', null, JText::_('COM_JMAP_ALL_DATASOURCE'));
		$datasourceTypes[] = JHTML::_('select.option', 'user', JText::_('COM_JMAP_USER_DATASOURCE'));
		$datasourceTypes[] = JHTML::_('select.option', 'menu', JText::_('COM_JMAP_MENU_DATASOURCE'));
		$datasourceTypes[] = JHTML::_('select.option', 'content', JText::_('COM_JMAP_CONTENT_DATASOURCE'));
		$datasourceTypes[] = JHTML::_('select.option', 'plugin', JText::_('COM_JMAP_PLUGIN_DATASOURCE'));
		$datasourceTypes[] = JHTML::_('select.option', 'links', JText::_('COM_JMAP_LINKS_DATASOURCE'));
		$filters ['type'] = JHTML::_ ( 'select.genericlist', $datasourceTypes, 'filter_type', 'onchange="Joomla.submitform();"', 'value', 'text', $this->getState ( 'type' ));
		
		return $filters;
	}
	
	/**
	 * Return select lists used as filter for editEntity
	 *
	 * @access public
	 * @param Object $record
	 * @return array
	 */
	public function getLists($record = null) {
		$lists = array ();
		// Grid states
		$lists ['published'] = JHTML::_ ( 'select.booleanlist', 'published', null, $record->published );
		
		// Components select list
		$queryComponent = "SELECT DISTINCT " . $this->_db->quoteName('element') . " AS value, SUBSTRING(" . $this->_db->quoteName('element') . ", 5) AS text" .
						  "\n FROM #__extensions" .
						  "\n WHERE (" . $this->_db->quoteName('protected') . " = 0" .
						  "\n OR " . $this->_db->quoteName('element') . " = " . $this->_db->quote('com_content') . // Exception for custom content sources
						  "\n OR " . $this->_db->quoteName('element') . " = " . $this->_db->quote('com_tags') . // Exception for custom tags sources
						  "\n OR " . $this->_db->quoteName('element') . " = " . $this->_db->quote('com_docman') . ")" . // Exception for docman 2 protected
		 				  "\n AND ". $this->_db->quoteName('type') . " = " . $this->_db->quote('component');
		$this->_db->setQuery($queryComponent);
		try {
			$elements = $this->_db->loadObjectList();
			if($this->_db->getErrorNum()) {
				throw new JMapException($this->_db->getErrorMsg(), 'notice');
			}
		} catch (JMapException $e) {
			$this->app->enqueueMessage($e->getMessage(), $e->getErrorLevel());
			$elements = array();
		} catch (Exception $e) {
			$jmapException = new JMapException($e->getMessage(), 'notice');
			$this->app->enqueueMessage($jmapException->getMessage(), $jmapException->getErrorLevel());
			$elements = array();
		}
		
		array_unshift($elements, JHTML::_('select.option', null, JText::_('COM_JMAP_SELECTCOMPONENT')));
		$lists ['components'] = JHTML::_ ( 'select.genericlist', $elements, 'sqlquery_managed[option]', 'data-validation="required"', 'value', 'text', @$record->sqlquery_managed->option);
		
		
		// Tables select list
		$tableOptions = $this->loadTables();
		// Maintable select list
		$lists ['tablesMaintable'] = JHTML::_ ( 'select.genericlist', $tableOptions, 'sqlquery_managed[table_maintable]', 'class="table_maintable" data-validation="required" data-bind="table_maintable"', 'value', 'text', @$record->sqlquery_managed->table_maintable);
		
		$fieldsOptions = $this->loadTableFields($record, 'table_maintable');
		$lists ['fieldsTitle'] = JHTML::_ ( 'select.genericlist', $fieldsOptions, 'sqlquery_managed[titlefield]', 'class="table_maintable" data-bind="field_maintable" data-validation="required"', 'value', 'text', @$record->sqlquery_managed->titlefield);
		$lists ['fieldsID'] = JHTML::_ ( 'select.genericlist', $fieldsOptions, 'sqlquery_managed[id]', 'class="table_maintable" data-bind="field_maintable" data-validation="required"', 'value', 'text', @$record->sqlquery_managed->id);
		$lists ['fieldsCatid'] = JHTML::_ ( 'select.genericlist', $fieldsOptions, 'sqlquery_managed[catid]', 'class="table_maintable" data-bind="field_maintable"', 'value', 'text', @$record->sqlquery_managed->catid);
		$lists ['where1Maintable'] = JHTML::_ ( 'select.genericlist', $fieldsOptions, 'sqlquery_managed[where1_maintable]', 'class="table_maintable" data-bind="field_maintable"', 'value', 'text', @$record->sqlquery_managed->where1_maintable);
		$lists ['where2Maintable'] = JHTML::_ ( 'select.genericlist', $fieldsOptions, 'sqlquery_managed[where2_maintable]', 'class="table_maintable" data-bind="field_maintable""', 'value', 'text', @$record->sqlquery_managed->where2_maintable);
		$lists ['where3Maintable'] = JHTML::_ ( 'select.genericlist', $fieldsOptions, 'sqlquery_managed[where3_maintable]', 'class="table_maintable" data-bind="field_maintable"', 'value', 'text', @$record->sqlquery_managed->where3_maintable);
		$lists ['orderByMaintable'] = JHTML::_ ( 'select.genericlist', $fieldsOptions, 'sqlquery_managed[orderby_maintable]', 'class="table_maintable" data-bind="field_maintable"', 'value', 'text', @$record->sqlquery_managed->orderby_maintable);

		// Operators select list
		$operatorOptions = array();
		$operatorOptions[] = JHTML::_('select.option', null, '=');
		$arrayOperators = array ('!=', '>', '<', '>=', '<=');
		foreach ($arrayOperators as $operator) {
			$operatorOptions[] = JHTML::_('select.option', $operator, $operator);
		}
		for($wmo=1,$maxOperators=3;$wmo<=$maxOperators;$wmo++) {
			$lists ['where'.$wmo.'MaintableOperators'] = JHTML::_ ( 'select.genericlist', $operatorOptions, 'sqlquery_managed[where'.$wmo.'_operator_maintable]', 'class="where_condition_operator"', 'value', 'text', @$record->sqlquery_managed->{'where'.$wmo.'_operator_maintable'});
		}
		
		// Order BY direction selectlist
		$directionOptions = array();
		$directionOptions[] = JHTML::_('select.option', null, JText::_('COM_JMAP_SELECTFIELD'));
		$directionOptions[] = JHTML::_('select.option', 'ASC', 'Ascending');
		$directionOptions[] = JHTML::_('select.option', 'DESC', 'Descending');
		$lists ['orderByDirectionMaintable'] = JHTML::_ ( 'select.genericlist', $directionOptions, 'sqlquery_managed[orderby_direction_maintable]', 'class="right_select intermediate"', 'value', 'text', @$record->sqlquery_managed->orderby_direction_maintable);
		$lists ['groupByMaintable'] = JHTML::_ ( 'select.genericlist', $fieldsOptions, 'sqlquery_managed[groupby_maintable]', 'class="table_maintable" data-bind="field_maintable"', 'value', 'text', @$record->sqlquery_managed->groupby_maintable);
		
		
		// Cycle for JoinTable #n elements
		$joinOptions = array();
		$joinOptions[] = JHTML::_('select.option', null, JText::_('COM_JMAP_DEFAULT_JOIN'));
		$joinOptions[] = JHTML::_('select.option', 'LEFT', JText::_('COM_JMAP_LEFT_JOIN'));
		$joinOptions[] = JHTML::_('select.option', 'RIGHT', JText::_('COM_JMAP_RIGHT_JOIN'));
		for($jt=1,$maxJoin=3;$jt<=$maxJoin;$jt++) {
			// Tables select list
			$fieldsJoinFromTableOptions = $this->loadTableFields($record, 'table_joinfrom_jointable'.$jt);
			$fieldsJoinWithTableOptions = $this->loadTableFields($record, 'table_joinwith_jointable'.$jt);
			
			// JoinFromJointables select list
			$lists ['tablesJoinFromJointable'.$jt] = JHTML::_ ( 'select.genericlist', $tableOptions, 'sqlquery_managed[table_joinfrom_jointable'.$jt.']', 'class="table_joinfrom" data-bind="table_joinfrom_jointable'.$jt.'"', 'value', 'text', @$record->sqlquery_managed->{'table_joinfrom_jointable'.$jt});
			// JoinWithJointables select list
			$lists ['tablesJoinWithJointable'.$jt] = JHTML::_ ( 'select.genericlist', $tableOptions, 'sqlquery_managed[table_joinwith_jointable'.$jt.']', 'class="table_joinwith" data-bind="table_joinwith_jointable'.$jt.'"', 'value', 'text', @$record->sqlquery_managed->{'table_joinwith_jointable'.$jt});
			// Join type Jointables
			$lists ['jointypeJointable'.$jt] = JHTML::_ ( 'select.genericlist', $joinOptions, 'sqlquery_managed[jointype_jointable'.$jt.']', 'class="intermediate_small"', 'value', 'text', @$record->sqlquery_managed->{'jointype_jointable'.$jt});
			
			$lists ['fieldsJoinFromJointable'.$jt] = JHTML::_ ( 'select.genericlist', $fieldsJoinFromTableOptions, 'sqlquery_managed[field_joinfrom_jointable'.$jt.']', 'class="field_joinfrom" data-bind="field_joinfrom_jointable'.$jt.'"', 'value', 'text', @$record->sqlquery_managed->{'field_joinfrom_jointable'.$jt});
			$lists ['fieldsJoinWithJointable'.$jt] = JHTML::_ ( 'select.genericlist', $fieldsJoinWithTableOptions, 'sqlquery_managed[field_joinwith_jointable'.$jt.']', 'data-bind="field_joinwith_jointable'.$jt.'" class="field_joinwith right_select"', 'value', 'text', @$record->sqlquery_managed->{'field_joinwith_jointable'.$jt});
			$lists ['fieldsSelectJointable'.$jt] = JHTML::_ ( 'select.genericlist', $fieldsJoinWithTableOptions, 'sqlquery_managed[field_select_jointable'.$jt.']', 'data-bind="field_joinwith_jointable'.$jt.'" class="field_joinwith right_select"', 'value', 'text', @$record->sqlquery_managed->{'field_select_jointable'.$jt});
			
			$lists ['where1Jointable'.$jt] = JHTML::_ ( 'select.genericlist', $fieldsJoinWithTableOptions, 'sqlquery_managed[where1_jointable'.$jt.']', 'class="field_joinwith" data-bind="field_joinwith_jointable'.$jt.'"', 'value', 'text', @$record->sqlquery_managed->{'where1_jointable'.$jt});
			$lists ['where2Jointable'.$jt] = JHTML::_ ( 'select.genericlist', $fieldsJoinWithTableOptions, 'sqlquery_managed[where2_jointable'.$jt.']', 'class="field_joinwith" data-bind="field_joinwith_jointable'.$jt.'"', 'value', 'text', @$record->sqlquery_managed->{'where2_jointable'.$jt});
			$lists ['where3Jointable'.$jt] = JHTML::_ ( 'select.genericlist', $fieldsJoinWithTableOptions, 'sqlquery_managed[where3_jointable'.$jt.']', 'class="field_joinwith" data-bind="field_joinwith_jointable'.$jt.'"', 'value', 'text', @$record->sqlquery_managed->{'where3_jointable'.$jt});
			
			$lists ['where1Jointable'.$jt.'Operators'] = JHTML::_ ( 'select.genericlist', $operatorOptions, 'sqlquery_managed[where1_operator_jointable'.$jt.']', 'class="where_condition_operator"', 'value', 'text', @$record->sqlquery_managed->{'where1_operator_jointable'.$jt});
			$lists ['where2Jointable'.$jt.'Operators'] = JHTML::_ ( 'select.genericlist', $operatorOptions, 'sqlquery_managed[where2_operator_jointable'.$jt.']', 'class="where_condition_operator"', 'value', 'text', @$record->sqlquery_managed->{'where2_operator_jointable'.$jt});
			$lists ['where3Jointable'.$jt.'Operators'] = JHTML::_ ( 'select.genericlist', $operatorOptions, 'sqlquery_managed[where3_operator_jointable'.$jt.']', 'class="where_condition_operator"', 'value', 'text', @$record->sqlquery_managed->{'where3_operator_jointable'.$jt});
				
			$lists ['orderByJointable'.$jt] = JHTML::_ ( 'select.genericlist', $fieldsJoinWithTableOptions, 'sqlquery_managed[orderby_jointable'.$jt.']', 'class="field_joinwith" data-bind="field_joinwith_jointable'.$jt.'"', 'value', 'text', @$record->sqlquery_managed->{'orderby_jointable'.$jt});
			$lists ['orderByDirectionJointable'.$jt] = JHTML::_ ( 'select.genericlist', $directionOptions, 'sqlquery_managed[orderby_direction_jointable'.$jt.']', 'class="right_select intermediate"', 'value', 'text', @$record->sqlquery_managed->{'orderby_direction_jointable'.$jt});
			$lists ['groupByJointable'.$jt] = JHTML::_ ( 'select.genericlist', $fieldsJoinWithTableOptions, 'sqlquery_managed[groupby_jointable'.$jt.']', 'class="field_joinwith" data-bind="field_joinwith_jointable'.$jt.'"', 'value', 'text', @$record->sqlquery_managed->{'groupby_jointable'.$jt});
		}
		
		// Priority select list
		$options = array();
		$options[] = JHTML::_('select.option', null, JText::_('COM_JMAP_SELECTPRIORITY')); 
		$arrayPriorities = array ('0.1'=>'10%', '0.2'=>'20%', '0.3'=>'30%', '0.4'=>'40%', '0.5'=>'50%', '0.6'=>'60%', '0.7'=>'70%', '0.8'=>'80%', '0.9'=>'90%', '1.0'=>'100%');
		foreach ($arrayPriorities as $value=>$text) {
			$options[] = JHTML::_('select.option', $value, $text);
		}
		$lists ['priority'] = JHTML::_ ( 'select.genericlist', $options, 'params[priority]', 'style="width:200px"', 'value', 'text', $record->params->get('priority', '0.5'));
		$lists ['priorities'] = JHTML::_ ( 'select.genericlist', $options, 'params[priorities]', 'style="width:200px" size=15', 'value', 'text', null, 'priorities');
		
		// Change frequency select list
		$options = array();
		$options[] = JHTML::_('select.option', null, JText::_('COM_JMAP_SELECTCHANGEFREQ'));
		$arrayPriority = array ('always', 'hourly', 'daily', 'weekly', 'monthly', 'yearly', 'never');
		foreach ($arrayPriority as $priority) {
			$options[] = JHTML::_('select.option', $priority, $priority);
		}
		$lists ['changefreq'] = JHTML::_ ( 'select.genericlist', $options, 'params[changefreq]', 'style="width:200px";', 'value', 'text', $record->params->get('changefreq', 'daily'));
		
		// Lazy Loading dependency - Use J Element simbolic override for menu multiselect generation specific to single data source menu
		if($record->type == 'menu') {
			$selections = JMapHtmlMenu::getMenuItems($record->name);
			$lists['exclusion']	= JHTML::_('select.genericlist', $selections, 'params[exclusion][]', 'class="inputbox" size="15" multiple="multiple"', 'value', 'text', $record->params->get('exclusion', array()), 'exclusion' );
			
			$selectionsWithPriority = JMapHtmlMenu::getMenuItems($record->name, true);
			$lists['menu_priorities']	= JHTML::_('select.genericlist', $selectionsWithPriority, 'params[menu_priorities]', array('option.attr'=>'style', 'list.attr'=>'data-type="MenuPriorities" class="inputbox" size="15"'));
		}
		
		// Lazy Loading dependency - Use J Element simbolic override for menu multiselect generation specific to single data source menu
		if($record->type == 'content') {
			// Get categories exclusion multiselect
			$categoryOptions = JMapHtmlCategories::getCategories();
			$lists['catexclusion']	= JHTML::_('select.genericlist', $categoryOptions, 'params[catexclusion][]', 'class="inputbox" size="15" multiple="multiple"', 'value', 'text', $record->params->get('catexclusion', array()), 'catexclusion' );
		
			// Get articles exclusion multiselect
			if($this->getComponentParams()->get('enable_articles_exclusions', 1)) {
				$articleOptions = JMapHtmlArticles::getArticles();
				$lists['articleexclusion']	= JHTML::_('select.genericlist', $articleOptions, 'params[articleexclusion][]', 'class="inputbox" size="15" multiple="multiple"', 'value', 'text', $record->params->get('articleexclusion', array()), 'articleexclusion' );
			}

			// Get articles categories priorities
			$selectionsCatsWithPriority = JMapHtmlCatspriorities::getCategories();
			$lists['cats_priorities']	= JHTML::_('select.genericlist', $selectionsCatsWithPriority, 'params[cats_priorities]', array('option.attr'=>'style', 'list.attr'=>'data-type="CatsPriorities" class="inputbox" size="15"'));
		}
		
		// Check if data source extension has valid GNews inclusion support
		if($this->getHasGnewsSupport($record)) {
			// Get Google News sitemap genres multiselect
			$genresArray = array (''=>'COM_JMAP_NONE',
								  'Blog'=>'COM_JMAP_BLOG',
								  'PressRelease'=>'COM_JMAP_PRESSRELEASE',
								  'Satire'=>'COM_JMAP_SATIRE',
								  'OpEd'=>'COM_JMAP_OPED',
								  'Opinion'=>'COM_JMAP_OPINION',
								  'UserGenerated'=>'COM_JMAP_USERGENERATED');
			foreach ($genresArray as $genre=>$translation) {
				$genresOptions[] = JHTML::_('select.option', $genre, JText::_($translation));
			}
			$lists['gnews_genres'] = JHTML::_('select.genericlist', $genresOptions, 'params[gnews_genres][]', 'multiple="multiple" size="8"', 'value', 'text', $record->params->get('gnews_genres', $this->getComponentParams()->get('gnews_genres', array('Blog'))), 'params_gnews_genres');
		}
		
		// Lazy Loading dependency - Use J Element simbolic override for menu multiselect generation specific to single data source menu
		if($record->type == 'user') {
			$sefItemid = JMapHtmlMenu::getMenuItems();
			array_unshift($sefItemid, JHTML::_('select.option', '0', JText::_('COM_JMAP_NOMENUSELECTED')));
			$lists['sef_itemid'] = JHTML::_('select.genericlist', $sefItemid, 'params[sef_itemid]', 'class="inputbox" size="15"', 'value', 'text', $record->params->get('sef_itemid', null), 'sef_itemid' );
		}

		// Configure a language dropdown if the links source is requested
		if($record->type == 'links' || $record->type == 'user') {
			// Check if multilanguage dropdown is always active
			if($this->getComponentParams()->get('showalways_language_dropdown', false)) {
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
			$languageOptions = JMapHtmlLanguages::getAvailableLanguageOptions(true);
			if(count($languageOptions) >= 2 && $languageFilterPluginEnabled) {
				$currentSelectedLanguage = $record->params->get('datasource_language', null);
				$lists['languages']	= JHTML::_('select.genericlist',   $languageOptions, 'params[datasource_language]', 'class="inputbox" style="width: 200px"', 'value', 'text', $currentSelectedLanguage, 'datasource_language' );
				// Append a flag button image if the language is a specific one
				if($currentSelectedLanguage && $currentSelectedLanguage != '*') {
					$lists['languages'] .= '<img id="language_flag_image" src="' . JUri::root(false) . 'media/mod_languages/images/' . $currentSelectedLanguage . '.gif" alt="language_flag" />';
				}
			}
		}
		
		return $lists;
	}
	
	/**
	 * Try to load frontend multilevel cats manifest for this component data source
	 * To have option for multi categorization a data source needs 3 requirements:
	 * 1)Have a valid manifest.json for frontend rendering
	 * 2)Have a sqlquery managed field for cat title use
	 * 3)Have a maintable not related to categories itself
	 * 
	 * @access public
	 * @param Object $record
	 * @return boolean 
	 */
	public function getHasManifest($record) {
		if(!$record->id || $record->type != 'user') {
			return false;
		}
		
		// Check if a valid field has been chosen and activated for category titles
		$hasCategoryUseByTitle = false;
		for($i=1,$k=4;$i<$k;$i++) {
			if(isset($record->sqlquery_managed->{'use_category_title_jointable'.$i}) && $record->sqlquery_managed->{'use_category_title_jointable'.$i}) {
				$hasCategoryUseByTitle = true;
				break;
			}
		}
		
		// Check if data source is elated to categories entities itself
		$hasCategoryUseByCatsItself = false;
		if(preg_match('/categor|cats|catg/i', $record->sqlquery_managed->table_maintable)) {
			$hasCategoryUseByCatsItself = true;
		}
		
		if(!$hasCategoryUseByTitle && !$hasCategoryUseByCatsItself) {
			return false;
		}

		// Load configuration manifest file
		$fileName = JPATH_COMPONENT_SITE . '/manifests/' . $record->sqlquery_managed->option . '.json';
		
		// Check if file exists and is valid manifest
		if(file_exists($fileName)) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Check if the data source has a items categorization
	 * determined by use as title setting
	 *
	 * @access public
	 * @param Object $record
	 * @return boolean
	 */
	public function getHasCategoryByTitle($record) {
		if(!$record->id || $record->type != 'user') {
			return false;
		}
	
		if(preg_match('/categor|cats|catg/i', $record->sqlquery_managed->table_maintable)) {
			return false;
		}

		// Check if a valid field has been chosen and activated for category titles
		for($i=1,$k=4;$i<$k;$i++) {
			if(isset($record->sqlquery_managed->{'use_category_title_jointable'.$i}) && $record->sqlquery_managed->{'use_category_title_jointable'.$i}) {
				return true;
			}
		}

		return false;
	}
	
	/**
	 * Check if the data source is for type category itself
	 *
	 * @access public
	 * @param Object $record
	 * @return boolean
	 */
	public function getIsCategorySource($record) {
		if(!$record->id || $record->type != 'user') {
			return false;
		}
	
		if(preg_match('/categor|cats|catg/i', $record->sqlquery_managed->table_maintable)) {
			return true;
		}
	
		return false;
	}
	
	/**
	 * Check if the extension data source has support for valid GNews sitemap XML
	 *
	 * @access public
	 * @param Object $record
	 * @return boolean
	 */
	public function getHasGnewsSupport($record) {
		static $hasGNewsSupport;

		// Return already evaluated support
		if(!is_null($hasGNewsSupport)) {
			return $hasGNewsSupport;
		}

		// Content source always supported by default
		if($record->type == 'content' || $record->type == 'plugin' || $record->type == 'links') {
			$hasGNewsSupport = true;
			return $hasGNewsSupport;
		}

		// Required a valid 'user' data source, no menu or content already evaluated
		if(!isset($record->sqlquery_managed->table_maintable)) {
			$hasGNewsSupport = false;
			return $hasGNewsSupport;
		}

		// Check if valid GNews for type 'user'
		$supportedExtensionTables = array('#__k2_items', '#__zoo_item', '#__easyblog_post', '#__mt_links');
		$hasGNewsSupport = in_array($record->sqlquery_managed->table_maintable, $supportedExtensionTables);

		return $hasGNewsSupport;
	}
	
	/**
	 * Check if the extension data source has support for valid RSS feed
	 *
	 * @access public
	 * @param Object $record
	 * @return boolean
	 */
	public function getHasRSSSupport($record) {
		static $hasRSSSupport;
	
		// Return already evaluated support
		if(!is_null($hasRSSSupport)) {
			return $hasRSSSupport;
		}
	
		// Content source always supported by default
		if($record->type == 'content') {
			$hasRSSSupport = true;
			return $hasRSSSupport;
		}
	
		// Required a valid 'user' data source, no menu or content already evaluated
		if(!isset($record->sqlquery_managed->table_maintable)) {
			$hasRSSSupport = false;
			return $hasRSSSupport;
		}

		// Supporting by default virtuemart products
		if(stripos($record->sqlquery_managed->table_maintable, 'virtuemart_products')) {
			$hasRSSSupport = true;
			return $hasRSSSupport;
		}

		// Load configuration manifest file
		$fileName = JPATH_COMPONENT_SITE . '/manifests/rss.json';

		// Check if file exists and is valid manifest
		if(file_exists($fileName)) {
			// Load the manifest serialized file and assign to local variable
			$manifest = file_get_contents($fileName);
			$supportedExtensionTables = json_decode($manifest);
		}

		// Check if valid GNews for type 'user'
		$hasRSSSupport = property_exists($supportedExtensionTables, $record->sqlquery_managed->table_maintable);
	
		return $hasRSSSupport;
	}
	
	/**
	 * Try to load route helper manifest for this component data source
	 * If a manifest is available to execute the routing helper by JSitemap
	 * show the option accordingly in the data source edit
	 *
	 * @access public
	 * @param Object $record
	 * @return boolean
	 */
	public function getHasRouteManifest($record) {
		if(!$record->id || $record->type != 'user') {
			return false;
		}

		// Load configuration manifest file
		$fileName = JPATH_COMPONENT_ADMINISTRATOR . '/framework/route/manifests/' . $record->sqlquery_managed->option . '.json';

		// Check if file exists and is valid manifest
		if(file_exists($fileName)) {
			return true;
		}

		return false;
	}
	
	/**
	 * Try to check if the component table has entity ruled by create date,
	 * and if yes filtering by latest months can be shown and afterwards applied at runtime
	 *
	 * @param Object $record
	 * @return boolean
	 */
	public function getHasCreatedDate($record) {
		// Always true for content type data source
		if($record->type == 'content') {
			return true;
		}

		// Available only for content and user data source that supports created field not newly created
		if(!$record->id || $record->type == 'menu' || $record->type == 'plugin' || $record->type == 'links') {
			return false;
		}

		if(strpos($record->sqlquery_managed->table_maintable, 'categor')) {
			return false;
		}

		if(!isset($record->sqlquery_managed->table_maintable)) {
			return false;
		}

		// Build query
		$query = "SHOW COLUMNS FROM " . $this->_db->quoteName($record->sqlquery_managed->table_maintable);
		$this->_db->setQuery ( $query );

		try {
			$tableFields = $this->_db->loadColumn ();
			if ($this->_db->getErrorNum ()) {
				throw new JMapException ( JText::sprintf ( 'COM_JMAP_ERROR_RETRIEVING_CREATEDATE_INFO', $this->_db->getErrorMsg () ), 'notice' );
			}
			// Search in fields array we have found the create date reserved field
			if(in_array('created', $tableFields)) {
				return true;
			}
		} catch ( JMapException $e ) {
			$this->app->enqueueMessage ( $e->getMessage (), $e->getErrorLevel () );
			return false;
		} catch ( Exception $e ) {
			$jmapException = new JMapException ( $e->getMessage (), 'error' );
			$this->app->enqueueMessage ( $jmapException->getMessage (), $jmapException->getErrorLevel () );
			return false;
		}

		return false;
	}
	
	/**
	 * Storing entity by ORM table
	 * 
	 * @access public
	 * @param boolean $forceRegenerate
	 * @param boolean $wizard
	 * @param Object $wizardModel
	 * @return mixed
	 */
	public function storeEntity($forceRegenerate = false, $wizard = false, $wizardModel = null) {
		$table = $this->getTable();
		try {
			if (!$table->bind ($this->requestArray[$this->requestName], true)) {
				throw new JMapException($table->getError (), 'error');
			}
	
			if (!$table->check ( )) {
				throw new JMapException($table->getError (), 'error');
			}
	
			// Delegate creazione raw query se type=user e new
			if($table->type == 'user' && (!$table->id || $forceRegenerate)) {
				if(!$this->buildRawQuery($table->sqlquery_managed, $table, $wizard, $wizardModel)) {
					throw new JMapException(JText::_('COM_JMAP_ERROR_BUILDING_QUERY'), 'error');
				}
			}
			
			if (! $table->store (false)) {
				throw new JMapException($table->getError (), 'error');
			} 
			$table->reorder(); 
		} catch(JMapException $e) {
			$this->setError($e);
			// Rethrow exception if wizard mode to bubble it to wizard controller
			if($wizard) {
				throw $e;
			}
			return false;
		} catch (Exception $e) {
			$jmapException = new JMapException($e->getMessage(), 'error');
			$this->setError($jmapException);
			// Rethrow exception if wizard mode to bubble it to wizard controller
			if($wizard) {
				throw $jmapException;
			}
			return false;
		}
			
		return $table;
	}
	
	/**
	 * Copy existing entity
	 *
	 * @param int $id
	 * @access public
	 * @return boolean
	 */
	public function copyEntity($ids) { 
		if(is_array($ids) && count($ids)) {
			$table = $this->getTable();
			try {
				foreach ( $ids as $id ) {
					if ($table->load ( ( int ) $id )) {
						$table->id = 0;
						// Don't rename plugin type data source! The name is the unique identifier
						if($table->type != 'plugin') {
							$table->name = JText::_ ( 'COM_JMAP_COPYOF' ) . $table->name;
						}
						$table->published = 0;
						$table->sqlquery_managed = json_encode($table->sqlquery_managed);
						$table->params = $table->params->toString();
						if (! $table->store ()) {
							throw new JMapException($table->getError (), 'error');
						} 
					} else {
						throw new JMapException($table->getError (), 'error');
					}
				}	
				$table->reorder();
			} catch (JMapException $e) {
				$this->setError($e);
				return false;
			} catch (Exception $e) {
				$jmapException = new JMapException($e->getMessage(), 'error');
				$this->setError($jmapException);
				return false;
			}
		}
		return true;
	}
	
	/**
	 * Delete entity
	 *
	 * @param array $ids
	 * @access public
	 * @return boolean
	 */
	public function deleteEntity($ids) {
		$table = $this->getTable ();

		// Ciclo su ogni entity da cancellare
		if (is_array ( $ids ) && count ( $ids )) {
			foreach ( $ids as $id ) {
				try {
					// Load always to identify if the type is plugin with filesystem resources
					$table->load($id);

					// Evaluate if the data source is of type plugin and delete filesystem resources if no more data sources are used
					if($table->type === 'plugin') {
						$otherResourcesQuery = "SELECT COUNT(*)" .
											   "\n FROM " . $this->_db->quoteName('#__jmap') .
											   "\n WHERE" .
											   "\n " . $this->_db->quoteName('type') . " = " . $this->_db->quote('plugin') .
											   "\n AND " . $this->_db->quoteName('name') . " = " . $this->_db->quote($table->name) .
											   "\n AND " . $this->_db->quoteName('id') . " != " . (int)$table->id;
						$otherResourcesCount = $this->_db->setQuery($otherResourcesQuery)->loadResult();
						if(!$otherResourcesCount) {
							$folderPluginName = strtolower($table->name);
							$folderPluginPath = JPATH_COMPONENT_ADMINISTRATOR . '/plugins/' . $folderPluginName;
							if(JFolder::exists($folderPluginPath)) {
								$deleted = JFolder::delete($folderPluginPath);
							}
						}
					}

					if (! $table->delete ( $id )) {
						throw new JMapException ( $table->getError (), 'error' );
					}
					$table->reorder ();
				} catch ( JMapException $e ) {
					$this->setError ( $e );
					return false;
				} catch ( Exception $e ) {
					$jmapException = new JMapException ( $e->getMessage (), 'error' );
					$this->setError ( $jmapException );
					return false;
				}
			}
		}

		return true;
	}
	
	/**
	 * Change entities ordering
	 *
	 * @access public
	 * @param int $idEntity        	
	 * @param int $direction        	
	 * @return boolean
	 */
	public function changeOrder($idEntity, $direction) {
		if (isset ( $idEntity )) {
			try {
				$table =  $this->getTable ();
				$table->load ( ( int ) $idEntity );
				if (! $table->move ( $direction )) {
					throw new JMapException($table->getError (), 'notice');
				}
			} catch(JMapException $e) {
				$this->setError($e);
				return false;
			} catch (Exception $e) {
				$jmapException = new JMapException($e->getMessage(), 'notice');
				$this->setError($jmapException);
				return false;
			}
		}
		return true;
	}
	
	/**
	 * Method to move and reorder
	 *
	 * @access public
	 * @param array $cid        	
	 * @param array $order  
	 * @return boolean on success
	 * @since 1.5
	 */
	public function saveOrder($cid = array(), $order) {
		if (is_array ( $cid ) && count ( $cid )) {
			try {
				$table =  $this->getTable ();
				// update ordering values
				for($i = 0; $i < count ( $cid ); $i ++) {
					$table->load ( ( int ) $cid [$i] );
					if ($table->ordering != $order [$i]) {
						$table->ordering = $order [$i];
						if (! $table->store ()) {
							throw new JMapException($table->getError (), 'notice');
						}
					}
				}
			} catch(JMapException $e) {
				$this->setError($e);
				return false;
			} catch (Exception $e) {
				$jmapException = new JMapException($e->getMessage(), 'notice');
				$this->setError($jmapException);
				return false;
			}
			// All went well
			$table->reorder ();
		}
		return true;
	}
	
	/**
	 * Publishing state changer for entities
	 *
	 * @access public
	 * @param int $idEntity        	
	 * @param string $state        	
	 * @return boolean
	 */
	public function publishEntities($idEntity, $state) {
		// Table load
		$table = $this->getTable ();
		if (isset ( $idEntity )) {
			try {
				if (! $table->load($idEntity)) {
					throw new JMapException($table->getError (), 'notice');
				}
				switch ($state) {
					case 'unpublish' :
						$table->published = 0;
						break;
					
					case 'publish' :
						$table->published = 1;
						break;
				}
				
				if (! $table->store ( true )) {
					throw new JMapException($table->getError (), 'notice');
				}
			} catch(JMapException $e) {
				$this->setError($e);
				return false;
			} catch (Exception $e) {
				$jmapException = new JMapException($e->getMessage(), 'notice');
				$this->setError($jmapException);
				return false;
			}
		}
		return true;
	}
}