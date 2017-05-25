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
include(ACYMAILING_BACK.'views'.DS.'newsletter'.DS.'view.html.php');

class NotificationViewNotification extends NewsletterViewNewsletter
{
	var $type = 'joomlanotification';
	var $ctrl = 'notification';
	var $nameListing = 'JOOMLA_NOTIFICATIONS';
	var $nameForm = 'JOOMLA_NOTIFICATIONS';
	var $doc = 'joomlanotification';
	var $icon = 'joomlanotification';


	function listing(){
		$app = JFactory::getApplication();
		$config = acymailing_config();

		if(!class_exists('plgSystemAcymailingClassMail')){
			$app->enqueueMessage('AcyMailing can customize some Joomla messages. If you want to do this, please first <a href="index.php?option=com_acymailing&ctrl=cpanel">enable the plugin acymailingclassmail (Override Joomla mailing system plugin)</a>', 'notice');
		}

		$pageInfo = new stdClass();
		$pageInfo->filter = new stdClass();
		$pageInfo->filter->order = new stdClass();

		$paramBase = ACYMAILING_COMPONENT.'.'.$this->getName();
		$pageInfo->filter->order->value = $app->getUserStateFromRequest( $paramBase.".filter_order", 'filter_order',	'mailid','cmd' );
		$pageInfo->filter->order->dir	= $app->getUserStateFromRequest( $paramBase.".filter_order_Dir", 'filter_order_Dir',	'desc',	'word' );
		if(strtolower($pageInfo->filter->order->dir) !== 'desc') $pageInfo->filter->order->dir = 'asc';

		$db = JFactory::getDBO();
		$query = 'SELECT mailid, subject, alias, fromname, published, fromname, fromemail, replyname, replyemail FROM #__acymailing_mail WHERE `type` = '.$db->Quote($this->type);

		if(!empty($pageInfo->filter->order->value)){
			$query .= ' ORDER BY '.$pageInfo->filter->order->value.' '.$pageInfo->filter->order->dir;
		}

		$db->setQuery($query);
		$rows = $db->loadObjectList();

			acymailing_setTitle(JText::_($this->nameListing),$this->icon,$this->ctrl);
		$bar = JToolBar::getInstance('toolbar');
		JToolBarHelper::custom('preview', 'acypreview', '', JText::_('ACY_PREVIEW'), true);
		JToolBarHelper::editList();
		JToolBarHelper::deleteList(JText::_('ACY_VALIDDELETEITEMS'));

		JToolBarHelper::divider();
		$bar->appendButton( 'Pophelp',$this->doc);
		$bar->appendButton( 'Link', 'acymailing', JText::_('ACY_CPANEL'), acymailing_completeLink('dashboard') );

		$toggleClass = acymailing_get('helper.toggle');
		$this->assignRef('toggleClass',$toggleClass);
		$this->assignRef('pageInfo',$pageInfo);
		$this->assign('config', $config);
		$this->assign('rows', $rows);
	}

	function form(){
		JHTML::_('behavior.modal','a.modal');
		return parent::form();
	}

	function preview(){
		JHTML::_('behavior.modal','a.modal');
		return parent::preview();
	}

}
