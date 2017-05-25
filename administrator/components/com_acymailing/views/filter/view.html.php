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

class FilterViewFilter extends acymailingView
{

	var $chosen = false;

	function display($tpl = null)
	{
		$function = $this->getLayout();
		if(method_exists($this,$function)) $this->$function();

		parent::display($tpl);
	}

	function form(){
		$db = JFactory::getDBO();
		$config = acymailing_config();

		if(JRequest::getVar('task') == 'filterDisplayUsers'){
			$action = array();
			$action['type'] = array('displayUsers');
			$action[] = array('displayUsers' => array());

			$filterClass = acymailing_get('class.filter');
			$filterClass->subid = JRequest::getString('subid');
			$filterClass->execute(JRequest::getVar('filter'), $action);

			if(!empty($filterClass->report)){
				$this->assignRef('filteredUsers',$filterClass->report[0]);
			}
		}


		$filid = acymailing_getCID('filid');

		$filterClass = acymailing_get('class.filter');
		$testFilter = JRequest::getVar('filter');
		if(!empty($filid) && empty($testFilter)){
			$filter = $filterClass->get($filid);
		}else{
			$filter = new stdClass();
			$filter->action = JRequest::getVar('action');
			$filter->filter = JRequest::getVar('filter');
			$filter->published = 1;
		}

		JPluginHelper::importPlugin('acymailing');
		$this->dispatcher = JDispatcher::getInstance();

		$typesFilters = array();
		$typesActions = array();

		$outputFilters = implode('',$this->dispatcher->trigger('onAcyDisplayFilters',array(&$typesFilters,'massactions')));
		$outputActions = implode('',$this->dispatcher->trigger('onAcyDisplayActions',array(&$typesActions)));

		$typevaluesFilters = array();
		$typevaluesActions = array();
		$typevaluesFilters[] = JHTML::_('select.option', '',JText::_('FILTER_SELECT'));
		$typevaluesActions[] = JHTML::_('select.option', '',JText::_('ACTION_SELECT'));
		$doc = JFactory::getDocument();
		foreach($typesFilters as $oneType => $oneName){
			$typevaluesFilters[] = JHTML::_('select.option', $oneType,$oneName);
		}
		foreach($typesActions as $oneType => $oneName){
			$typevaluesActions[] = JHTML::_('select.option', $oneType,$oneName);
		}

		$js = "function updateAction(actionNum){
				currentActionType =window.document.getElementById('actiontype'+actionNum).value;
				if(!currentActionType){
					window.document.getElementById('actionarea_'+actionNum).innerHTML = '';
					return;
				}
				actionArea = 'action__num__'+currentActionType;
				window.document.getElementById('actionarea_'+actionNum).innerHTML = window.document.getElementById(actionArea).innerHTML.replace(/__num__/g,actionNum);
				if(typeof(window['onAcyDisplayAction_'+currentActionType]) == 'function') {
					try{ window['onAcyDisplayAction_'+currentActionType](actionNum); }catch(e){alert('Error in the onAcyDisplayAction_'+currentActionType+' function : '+e); }
				}

			}";

		$js .= "var numActions = 0;
				function addAction(){
					var newdiv = document.createElement('div');
					newdiv.id = 'action'+numActions;
					newdiv.className = 'plugarea';
					newdiv.innerHTML = document.getElementById('actions_original').innerHTML.replace(/__num__/g, numActions);
					document.getElementById('allactions').appendChild(newdiv); updateAction(numActions); numActions++;
				}";

		$js .= "window.addEvent('domready', function(){ addAcyFilter(); addAction(); });";

		if(!ACYMAILING_J16){
			$js .= 	'function submitbutton(pressbutton){
						if (pressbutton != \'save\') {
							submitform( pressbutton );
							return;
						}';
		}else{
			$js .= 	'Joomla.submitbutton = function(pressbutton) {
						if (pressbutton != \'save\') {
							Joomla.submitform(pressbutton,document.adminForm);
							return;
						}';
		}
		if(ACYMAILING_J30) {
			$js .= 	"if(window.document.getElementById('filterinfo').style.display == 'none'){
						window.document.getElementById('filterinfo').style.display = 'block';
						try{allspans = window.document.getElementById('toolbar-save').getElementsByTagName(\"span\"); allspans[0].className = 'icon-apply';}catch(err){}
						try{alli = window.document.getElementById('toolbar-save').getElementsByTagName(\"i\"); alli[0].className = 'icon-apply';}catch(err){}
						return false;}
					if(window.document.getElementById('title').value.length < 2){alert('".JText::_('ENTER_TITLE',true)."'); return false;}";
		} else{
			$js .= 	"if(window.document.getElementById('filterinfo').style.display == 'none'){
						window.document.getElementById('filterinfo').style.display = 'block';
						try{allspans = window.document.getElementById('toolbar-save').getElementsByTagName(\"span\"); allspans[0].className = 'icon-32-apply';}catch(err){}
						return false;}
					if(window.document.getElementById('title').value.length < 2){alert('".JText::_('ENTER_TITLE',true)."'); return false;}";
		}
		if(!ACYMAILING_J16){
			$js .= 	"submitform( pressbutton );} ";
		}else{ $js .= 	"Joomla.submitform(pressbutton,document.adminForm);}; "; }

		$doc->addScriptDeclaration( $js );

		$filterClass->addJSFilterFunctions();

		$js = '';
		$data = array('addAction' => 'action','addAcyFilter' => 'filter');
		foreach($data as $jsFunction => $datatype){
			if(empty($filter->$datatype)) continue;
			foreach($filter->{$datatype}['type'] as $num => $oneType){
				if(empty($oneType)) continue;
				$js .= "while(!document.getElementById('".$datatype."type$num')){".$jsFunction."();}
						document.getElementById('".$datatype."type$num').value= '$oneType';
						update".ucfirst($datatype)."($num);";
				if(empty($filter->{$datatype}[$num][$oneType])) continue;
				foreach($filter->{$datatype}[$num][$oneType] as $key => $value){
					if(is_array($value)){
						$js .= "try{";
						foreach($value as $subkey => $subval){
							$js .= "document.adminForm.elements['".$datatype."[$num][$oneType][$key][$subkey]'].value = '".addslashes(str_replace(array("\n","\r"),' ',$subval))."';";
							$js .= "if(document.adminForm.elements['".$datatype."[$num][$oneType][$key][$subkey]'].type && document.adminForm.elements['".$datatype."[$num][$oneType][$key][$subkey]'].type == 'checkbox'){ document.adminForm.elements['".$datatype."[$num][$oneType][$key][$subkey]'].checked = 'checked'; }";
						}
						$js .= "}catch(e){}";
					}
					$myVal = is_array($value)?implode(',',$value):$value;
					$js .= "try{";
					$js .= "document.adminForm.elements['".$datatype."[$num][$oneType][$key]'].value = '".addslashes(str_replace(array("\n","\r"),' ',$myVal))."';";
					$js .= "if(document.adminForm.elements['".$datatype."[$num][$oneType][$key]'].type && document.adminForm.elements['".$datatype."[$num][$oneType][$key]'].type == 'checkbox'){ document.adminForm.elements['".$datatype."[$num][$oneType][$key]'].checked = 'checked'; }";
					$js .= "}catch(e){}";
				}

				$js .= "\n"." if(typeof(onAcyDisplay".ucfirst($datatype)."_".$oneType.") == 'function'){
					try{ onAcyDisplay".ucfirst($datatype)."_".$oneType."($num); }catch(e){alert('Error in the onAcyDisplay".ucfirst($datatype)."_".$oneType." function : '+e); }
				}";

				if($datatype == 'filter') $js.= " countresults($num);";
			}
		}

		$listid = JRequest::getInt('listid');
		if(!empty($listid)){
			$js .= "document.getElementById('actiontype0').value = 'list'; updateAction(0); document.adminForm.elements['action[0][list][selectedlist]'].value = '".$listid."';";

		}

		$doc->addScriptDeclaration( "window.addEvent('domready', function(){ $js });" );

		$triggers = array();
		$triggers['daycron'] = JText::_('AUTO_CRON_FILTER');
		$nextDate = $config->get('cron_plugins_next');

		$listHours = array();
		$listMinutess = array();
		for($i=0; $i<24; $i++){ $listHours[] = JHTML::_('select.option', $i, ($i<10?'0'.$i:$i)); }
		$hours = JHTML::_('select.genericlist', $listHours, 'triggerhours', 'class="inputbox" size="1" style="width:50px;"', 'value', 'text', acymailing_getDate($nextDate, 'H'));
		for($i=0; $i<60; $i+=5){ $listMinutess[] = JHTML::_('select.option', $i, ($i<10?'0'.$i:$i)); }
		$defaultMin = floor(acymailing_getDate($nextDate, 'i')/5)*5;
		$minutes = JHTML::_('select.genericlist', $listMinutess, 'triggerminutes', 'class="inputbox" size="1" style="width:50px;"', 'value', 'text', $defaultMin);
		$this->assign('hours', $hours);
		$this->assign('minutes', $minutes);

		$this->assign('nextDate', !empty($nextDate)?' ('.JText::_('NEXT_RUN').' : '.acymailing_getDate($nextDate,'%d %B %Y  %H:%M').')':'');

		$triggers['allcron'] = JText::_('ACY_EACH_TIME');
		$triggers['subcreate'] = JText::_('ON_USER_CREATE');
		$triggers['subchange'] = JText::_('ON_USER_CHANGE');
		$this->dispatcher->trigger('onAcyDisplayTriggers',array(&$triggers));

		$name = empty($filter->name) ? '' : ' : '.$filter->name;
		acymailing_setTitle(JText::_('ACY_FILTER').$name,'filter','filter&task=edit&filid='.$filid);

		$bar = JToolBar::getInstance('toolbar');
		JToolBarHelper::custom('filterDisplayUsers', 'acyusers', '',JText::_('FILTER_VIEW_USERS'), false);
		JToolBarHelper::divider();
		$bar->appendButton( 'Confirm', JText::_('PROCESS_CONFIRMATION'), 'process', JText::_('PROCESS'), 'process', false, false );
		JToolBarHelper::divider();
		if(acymailing_level(3)){
			JToolBarHelper::custom('save', 'save', '',JText::_('ACY_SAVE'), false);
			if(!empty($filter->filid)) $bar->appendButton( 'Link', 'new', JText::_('ACY_NEW'), acymailing_completeLink('filter&task=edit&filid=0') );
		}
		$bar->appendButton( 'Link', 'cancel', JText::_('ACY_CLOSE'), acymailing_completeLink('dashboard') );
		JToolBarHelper::divider();
		$bar->appendButton( 'Pophelp','filter');

		$subid = JRequest::getString('subid');
		if(!empty($subid)){
			$subArray = explode(',',trim($subid,','));
			JArrayHelper::toInteger($subArray);

			$db->setQuery('SELECT `name`,`email` FROM `#__acymailing_subscriber` WHERE `subid` IN ('.implode(',',$subArray).')');
			$users = $db->loadObjectList();
			if(!empty($users)){
				$this->assignRef('users',$users);
				$this->assignRef('subid',$subid);
			}

		}

		$this->assignRef('typevaluesFilters',$typevaluesFilters);
		$this->assignRef('typevaluesActions',$typevaluesActions);
		$this->assignRef('outputFilters',$outputFilters);
		$this->assignRef('outputActions',$outputActions);
		$this->assignRef('filter',$filter);

		$this->assignRef('triggers',$triggers);
		if(JRequest::getCmd('tmpl') == 'component'){
			$doc->addStyleSheet( ACYMAILING_CSS.'frontendedition.css?v='.filemtime(ACYMAILING_MEDIA.'css'.DS.'frontendedition.css'));
		}

		if(acymailing_level(3) AND JRequest::getCmd('tmpl') != 'component'){
			$db->setQuery('SELECT * FROM #__acymailing_filter ORDER BY `published` DESC, `filid` DESC');
			$filters = $db->loadObjectList();

			$toggleClass = acymailing_get('helper.toggle');
			$this->assignRef('toggleClass',$toggleClass);
			$this->assignRef('filters',$filters);
		}
	}
}
