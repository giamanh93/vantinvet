<?php
/**
 * @package	AcyMailing for Joomla!
 * @version	4.9.2
 * @author	acyba.com
 * @copyright	(C) 2009-2015 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php

class SubscriberController extends acymailingController{

	var $allowedInfo = array();
	var $aclCat = 'subscriber';



	function choose(){
		if(!$this->isAllowed('subscriber','view')) return;
		JRequest::setVar( 'layout', 'choose'  );
		return parent::display();
	}

	function export(){
		if(!$this->isAllowed('subscriber','export')) return;
		$app = JFactory::getApplication();
		$cids = JRequest::getVar('cid');
		$selectedList = JRequest::getInt('filter_lists');
		$_SESSION['acymailing'] = array();
		if(!empty($cids) || !empty($selectedList)){
			if(!empty($cids)) $_SESSION['acymailing']['exportusers'] = $cids;
			else $_SESSION['acymailing']['exportlist'] = $selectedList;
			$this->setRedirect(acymailing_completeLink(($app->isAdmin() ? '' : 'front').'data&task=export&sessionvalues=1',false,true));
		}else{
			$this->setRedirect(acymailing_completeLink(($app->isAdmin() ? '' : 'front').'data&task=export',false,true));
		}
	}

	function store(){
		if(!$this->isAllowed('subscriber','manage')) return;
		JRequest::checkToken() or die( 'Invalid Token' );
		$app = JFactory::getApplication();

		$subscriberClass = acymailing_get('class.subscriber');
		$subscriberClass->sendConf = false;
		$subscriberClass->sendNotif = false;
		$subscriberClass->sendWelcome = false;
		$subscriberClass->allowModif = true;
		$subscriberClass->checkAccess = false;
		$subscriberClass->triggerFilterBE = true;
		$subscriberClass->checkVisitor = false;

		$status = $subscriberClass->saveForm();
		if($status){
			$app->enqueueMessage(JText::_( 'JOOMEXT_SUCC_SAVED' ), 'message');
		}else{
			$app->enqueueMessage(JText::_( 'ERROR_SAVING' ), 'error');
			if(!empty($subscriberClass->errors)){
				foreach($subscriberClass->errors as $oneError){
					$app->enqueueMessage($oneError, 'error');
				}
			}
		}
	}

	function remove(){
		if(!$this->isAllowed('subscriber','delete')) return;
		JRequest::checkToken() or die( 'Invalid Token' );

		$subscriberIds = JRequest::getVar( 'cid', array(), '', 'array' );

		$subscriberObject = acymailing_get('class.subscriber');
		$num = $subscriberObject->delete($subscriberIds);

		$app = JFactory::getApplication();
		$app->enqueueMessage(JText::sprintf('SUCC_DELETE_ELEMENTS',$num), 'message');

		JRequest::setVar( 'layout', 'listing'  );
		return parent::display();
	}

	function getSubscribersByEmail(){
		$app = JFactory::getApplication();
		$NameSearched = JRequest::getString('search', '');
		if(empty($NameSearched) || !$app->isAdmin() || !$this->isAllowed('subscriber','view')) exit;

		$db = JFactory::getDBO();
		$NameSearched = '\'%'.acymailing_getEscaped($NameSearched,true).'%\'';
		$db->setQuery('SELECT name, email FROM #__acymailing_subscriber WHERE email LIKE '.$NameSearched.' OR name LIKE '.$NameSearched.' ORDER BY email ASC LIMIT 30');
		$users = $db->loadObjectList();
		if(empty($users)) exit;

		echo '<table style="width:100%;">';
		foreach($users as $oneUser){
			echo '<tr class="row_user" onclick="setUser(\''.str_replace("'","\'",$oneUser->email).'\');"><td>'.htmlspecialchars($oneUser->name, ENT_COMPAT, 'UTF-8').'</td><td>'.htmlspecialchars($oneUser->email, ENT_COMPAT, 'UTF-8').'</td></tr>';
		}
		echo '</table>';
		exit;
	}
}
