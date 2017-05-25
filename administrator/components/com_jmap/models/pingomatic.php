<?php
// namespace administrator\components\com_jmap\models;
/**
 * @package JMAP::PINGOMATIC::administrator::components::com_jmap
 * @subpackage models
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );

/**
 * Pingomatic links model concrete implementation <<testable_behavior>>
 *
 * @package JMAP::PINGOMATIC::administrator::components::com_jmap
 * @subpackage models
 * @since 2.0
 */
class JMapModelPingomatic extends JMapModel {
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
			$where [] = "(s.title LIKE " . $this->_db->quote("%" . $this->state->get ( 'searchword' ) . "%") . ") OR" .
						"(s.blogurl LIKE " . $this->_db->quote("%" . $this->state->get ( 'searchword' ) . "%") . ") OR" .
						"(s.rssurl LIKE " . $this->_db->quote("%" . $this->state->get ( 'searchword' ) . "%") . ")";
		}
		
		if($this->state->get('fromPeriod')) {
			$where[] = "\n s.lastping > " . $this->_db->quote(($this->state->get('fromPeriod')));
		}
		
		if($this->state->get('toPeriod')) {
			$where[] = "\n s.lastping < " . $this->_db->quote(date('Y-m-d', strtotime("+1 day", strtotime($this->state->get('toPeriod')))));
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
				 "\n FROM #__jmap_pingomatic AS s" .
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
				throw new JMapException(JText::_('COM_JMAP_ERROR_RETRIEVING_PINGOMATIC_LINKS') . $this->_db->getErrorMsg(), 'error');
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
	 * @param Object& $record
	 * @return array
	 */
	public function getLists($record = null) {
		$lists = array ();
		// Common services
		$lists ['chk_google'] = JHTML::_ ( 'select.booleanlist', 'chk_google', null, $record->services->get('chk_google', 1));
		$lists ['chk_weblogscom'] = JHTML::_ ( 'select.booleanlist', 'chk_weblogscom', null, $record->services->get('chk_weblogscom', 1));
		$lists ['chk_blogs'] = JHTML::_ ( 'select.booleanlist', 'chk_blogs', null, $record->services->get('chk_blogs', 1));
		$lists ['chk_feedburner'] = JHTML::_ ( 'select.booleanlist', 'chk_feedburner', null, $record->services->get('chk_feedburner', 1));
		$lists ['chk_newsgator'] = JHTML::_ ( 'select.booleanlist', 'chk_newsgator', null, $record->services->get('chk_newsgator', 1));
		$lists ['chk_myyahoo'] = JHTML::_ ( 'select.booleanlist', 'chk_myyahoo', null, $record->services->get('chk_myyahoo', 1));
		$lists ['chk_pubsubcom'] = JHTML::_ ( 'select.booleanlist', 'chk_pubsubcom', null, $record->services->get('chk_pubsubcom', 1));
		$lists ['chk_blogdigger'] = JHTML::_ ( 'select.booleanlist', 'chk_blogdigger', null, $record->services->get('chk_blogdigger', 1));
		$lists ['chk_weblogalot'] = JHTML::_ ( 'select.booleanlist', 'chk_weblogalot', null, $record->services->get('chk_weblogalot', 1));
		$lists ['chk_newsisfree'] = JHTML::_ ( 'select.booleanlist', 'chk_newsisfree', null, $record->services->get('chk_newsisfree', 1));
		$lists ['chk_topicexchange'] = JHTML::_ ( 'select.booleanlist', 'chk_topicexchange', null, $record->services->get('chk_topicexchange', 1));
		$lists ['chk_tailrank'] = JHTML::_ ( 'select.booleanlist', 'chk_tailrank', null, $record->services->get('chk_tailrank', 1));
		$lists ['chk_skygrid'] = JHTML::_ ( 'select.booleanlist', 'chk_skygrid', null, $record->services->get('chk_skygrid', 1));
		$lists ['chk_collecta'] = JHTML::_ ( 'select.booleanlist', 'chk_collecta', null, $record->services->get('chk_collecta', 1));
		$lists ['chk_superfeedr'] = JHTML::_ ( 'select.booleanlist', 'chk_superfeedr', null, $record->services->get('chk_superfeedr', 1));
	
		$lists ['chk_audioweblogs'] = JHTML::_ ( 'select.booleanlist', 'chk_audioweblogs', null, $record->services->get('chk_audioweblogs', null));
		$lists ['chk_rubhub'] = JHTML::_ ( 'select.booleanlist', 'chk_rubhub', null, $record->services->get('chk_rubhub', null));
		$lists ['chk_a2b'] = JHTML::_ ( 'select.booleanlist', 'chk_a2b', null, $record->services->get('chk_a2b', null));
		$lists ['chk_blogshares'] = JHTML::_ ( 'select.booleanlist', 'chk_blogshares', null, $record->services->get('chk_blogshares', null));
	
		return $lists;
	}

	/**
	 * Get by remote Pingomatic server stats flash object, parse it
	 * and get rid of undesired tags
	 *
	 * @access public
	 * @return mixed HTML code only for object/embed tags
	 */
	public function getPingomaticStats(JMapHttp $httpClient) {
		// Detect uri scheme
		$instance = JUri::getInstance();
		$this->urischeme = $instance->isSSL() ? 'https' : 'http';
		
		// Get stats from remote URI
		$url = $this->urischeme . '://pingomatic.com/stats/';
	
		// Try to get informations
		try {
			// Fake a user agent to avoid Pingomatic empty response
			$response = $httpClient->get($url, array('User-Agent' => 'Mozilla/5.0'))->body;
			if($response) {
				// make links from relative to absolute
				$replacedResponse = preg_replace('/href="\//i', 'href="' . $this->urischeme . '://pingomatic.com/', $response);
				$replacedResponse = preg_replace('/src="\//i', 'src="' . $this->urischeme . '://pingomatic.com/', $replacedResponse);
				$replacedResponse = preg_replace('/charts.swf/i', $this->urischeme . '://pingomatic.com/stats/charts.swf', $replacedResponse);
				
				// Keep only object/embed tags
				$replacedResponse = strip_tags($replacedResponse, '<object>,<embed>');
				preg_match('/(<object)([a-zA-Z0-9=\-\/\.,<>\?_\&#:;"\s]*)(<\/object>)/im', $replacedResponse, $matches);
				if(isset($matches[0]) && $matches[0]) {
					return $matches[0];
				}
			}
		} catch(JMapException $e) {
			return null;
		} catch (Exception $e) {
			return null;
		}
	}
}