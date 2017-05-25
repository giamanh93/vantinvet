<?php
// namespace administrator\components\com_jmap\models;
/**
 * @package JMAP::DATASETS::administrator::components::com_jmap
 * @subpackage models
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );

/**
 * Datasets links model concrete implementation <<testable_behavior>>
 *
 * @package JMAP::DATASETS::administrator::components::com_jmap
 * @subpackage models
 * @since 2.0
 */
class JMapModelDatasets extends JMapModel {
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

		// TEXT FILTER
		if ($this->state->get ( 'searchword' )) {
			$where [] = "(s.name LIKE " . $this->_db->quote("%" . $this->state->get ( 'searchword' ) . "%") . ")";
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
				 "\n FROM #__jmap_datasets AS s" .
				 "\n LEFT JOIN #__users AS u" .
				 "\n ON s.checked_out = u.id" . 
				 $whereString . $orderString;
		return $query;
	}

	/**
	 * Main get data methods
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
				throw new JMapException(JText::_('COM_JMAP_ERROR_RETRIEVING_DATASETS') . $this->_db->getErrorMsg(), 'error');
			}
			
			// Attach names for included data sources
			if(count($result)) {
				foreach ($result as &$row) {
					$subQuery = "SELECT" .
								"\n " . $this->_db->quoteName('name') .
								"\n FROM " . $this->_db->quoteName('#__jmap') .
								"\n WHERE " . $this->_db->quoteName('id') . ' IN ( ' . preg_replace('/\[|\]/i', '', $row->sources) . ' )';
					$subQueryResults = $this->_db->setQuery($subQuery)->loadColumn();
					$row->sourcesNames = $subQueryResults;
					if($this->_db->getErrorNum()) {
						throw new JMapException(JText::_('COM_JMAP_ERROR_RETRIEVING_DATASETS') . $this->_db->getErrorMsg(), 'error');
					}
				}
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
	 * Return select lists used as filter for editEntity
	 *
	 * @access public
	 * @param Object $record
	 * @return array
	 */
	public function getLists($record = null) {
		$lists = parent::getLists($record);

		$lists['sources'] = array(); 

		// Select all published data sources
		$query = $this->_db->getQuery(true);
		$query->select($this->_db->quoteName('id'));
		$query->select($this->_db->quoteName('name'));
		$query->from($this->_db->quoteName('#__jmap'));
		$query->where($this->_db->quoteName('published') . ' = 1');
		$query->order($this->_db->quoteName('ordering'));
		
		$this->_db->setQuery($query);
		$lists['sources'] = $this->_db->loadObjectList();
		
		return $lists;
	}
}