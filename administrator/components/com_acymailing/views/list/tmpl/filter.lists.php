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
	if(count($this->lists) > 10){
?>
	<script language="javascript" type="text/javascript">
	<!--
		function acymailing_searchAList(){
			var filter = document.getElementById("acymailing_searchList").value.toLowerCase();
			for(i=0 ; i<<?php echo count($this->lists); ?> ; i++){
				itemName = document.getElementById("listName_"+i).innerHTML.toLowerCase();
				itemId = document.getElementById("listId_"+i).innerHTML;
				if(document.getElementById(<?php echo $currentPage == 'newsletter' ? 'itemId+"listmail' : '"exportlists"+itemId+"_'; ?>1").checked || itemName.indexOf(filter)>-1){
					document.getElementById("acylist_"+i).style.display = "table-row";
				}else{
					document.getElementById("acylist_"+i).style.display = "none";
				}
			}
		}
	//-->
	</script>
	<div style="margin-bottom:10px;"><input onkeyup="acymailing_searchAList();" type="text" style="width: 200px;max-width:100%;margin-bottom:5px;" placeholder="<?php echo JText::_('ACY_SEARCH'); ?>" id="acymailing_searchList"></div>
<?php }

	$k = 0;
	$i = 0;

	$orderedList = array();
	$listsPerCategory = array();
	$languages = array();
	foreach($this->lists as $row){
		$orderedList[$row->category][$row->listid] = $row;
		$listsPerCategory[$row->category][$row->listid] = $row->listid;
		if(count($this->lists) < 4) continue;

		$languages['all'][$row->listid] = $row->listid;
		if($row->languages == 'all') continue;
		$lang = explode(',',trim($row->languages,','));
		foreach($lang as $oneLang){
			$languages[strtolower($oneLang)][$row->listid] = $row->listid;
		}
	}
	ksort($orderedList);
	$allCats = array_keys($orderedList);
	$this->lists = array();
	foreach($orderedList as $oneCategory){
		$this->lists = array_merge($this->lists, $oneCategory);
	}

	if($currentPage == 'newsletter'){
		echo '<table id="receiverstable" class="adminlist table table-striped table-hover" cellpadding="1" width="100%">
			<thead>
				<tr>
					<th class="title">'.JText::_('LIST_NAME').'</th>
					<th class="title">'.JText::_('LIST_RECEIVE').'</th>
				</tr>
			</thead>
			<tbody>';
	}else{
		echo '<table class="adminlist table table-striped" cellpadding="1"><tbody>';
	}

	foreach($this->lists as $row){
		if(empty($row->category)) $row->category = JText::_('ACY_NO_CATEGORY');
		if(count($allCats) > 1 && (empty($currentCatgeory) || $row->category != $currentCatgeory)){
			$currentCatgeory = $row->category; ?>
			<tr class="<?php echo "row$k"; ?>">
				<td colspan="2">
					<a href="#" onclick="checkCats('<?php echo htmlspecialchars(str_replace("'", "\'", $row->category == JText::_('ACY_NO_CATEGORY') ? -1 : $row->category), ENT_QUOTES, "UTF-8"); ?>');"><strong><?php echo htmlspecialchars($row->category, ENT_QUOTES, "UTF-8"); ?></strong></a>
				</td>
			</tr>
	<?php }
		if($currentPage == 'newsletter'){
			$filter_list = JRequest::getInt( 'filter_list');
			if(empty($filter_list)) $filter_list=JRequest::getInt('listid');
			$selectedLists = explode(',',JRequest::getString('listids'));
			$checked = (bool) ($row->mailid || (empty($row->mailid) && empty($this->mail->mailid) && $filter_list == $row->listid) || (empty($this->mail->mailid) && count($this->lists) == 1) || (in_array($row->listid,$selectedLists)));
		}else{
			$checked = ((empty($this->exportlist) && in_array($row->listid,$this->selectedlists)) || (!empty($this->exportlist) && $this->exportlist == $row->listid)) ? 1 : 0;
		}
		$classList = $checked ? 'acy_list_checked' : 'acy_list_unchecked';
	?>
		<tr id="acylist_<?php echo $i; ?>" class="<?php echo "row$k $classList"; ?>">
			<td style="display:none;" id="listId_<?php echo $i; ?>"><?php echo $row->listid;?></td>
			<td style="display:none;" id="listName_<?php echo $i; ?>"><?php echo $row->name;?></td>
			<td>
				<?php
					echo '<div class="roundsubscrib rounddisp" style="background-color:'.$row->color.'"></div>';
					$text = '<b>'.JText::_('ACY_ID').' : </b>'.$row->listid;
					$text .= '<br />'.$row->description;
					echo acymailing_tooltip($text, $row->name, 'tooltip.png', $row->name);
				?>
			</td>
			<td align="center" nowrap="nowrap" style="text-align:center">
				<?php echo JHTML::_('acyselect.booleanlist', ($currentPage == 'newsletter' ? "data[listmail]" : "exportlists")."[".$row->listid."]" , '',$checked,JText::_('JOOMEXT_YES'),JText::_('JOOMEXT_NO'),$currentPage == 'newsletter' ? $row->listid.'listmail' : 'exportlists'.$row->listid.'_'); ?>
			</td>
		</tr>
	<?php
		$k = 1-$k;
		$i++;
	}
	if(count($this->lists)>3){ ?>
		<tr>
			<td/>
			<td align="center" nowrap="nowrap">
				<script language="javascript" type="text/javascript">
				<!--
					var selectedLists = new Array();
					<?php
						foreach($languages as $val => $listids){
							echo "selectedLists['$val'] = new Array('".implode("','",$listids)."'); ";
						}
					?>
					function updateStatus(selection){
						<?php
							if($currentPage == 'newsletter'){
								$listidAll = "selectedLists['all'][i]+'listmail";
								$listidSelection = "selectedLists[selection][i]+'listmail";
							}else{
								$listidAll = "'exportlists'+selectedLists['all'][i]+'_";
								$listidSelection = "'exportlists'+selectedLists[selection][i]+'_";
							}
						?>
						for(var i=0; i < selectedLists['all'].length; i++){
							if(searchParent(window.document.getElementById(<?php echo $listidAll; ?>0'), 'tr').style.display == 'none') continue;
							<?php if(ACYMAILING_J30) echo "jQuery('input[id='+".$listidAll."0]').next().click();"; ?>
							window.document.getElementById(<?php echo $listidAll; ?>0').checked = true;
						}
						if(!selectedLists[selection]) return;
						for(var i=0; i < selectedLists[selection].length; i++){
							if(searchParent(window.document.getElementById(<?php echo $listidSelection; ?>1'), 'tr').style.display == 'none') continue;
							<?php if(ACYMAILING_J30) echo "jQuery('input[id='+".$listidSelection."1]').next().click();"; ?>
							window.document.getElementById(<?php echo $listidSelection; ?>1').checked = true;
						}
					}
				-->
				</script>
				<?php
					$selectList = array();
					$selectList[] = JHTML::_('select.option', 'none',JText::_('ACY_NONE'));
					foreach($languages as $oneLang => $values){
						if($oneLang == 'all') continue;
						$selectList[] = JHTML::_('select.option', $oneLang,ucfirst($oneLang));
					}
					$selectList[] = JHTML::_('select.option', 'all',JText::_('ACY_ALL'));
					echo JHTML::_('acyselect.radiolist', $selectList, "selectlists" , 'onclick="updateStatus(this.value);"', 'value', 'text');
				?>
			</td>
		</tr>
	<?php } ?>
	</tbody>
</table>

<script language="javascript" type="text/javascript">
<!--
	function searchParent(elem, tag){
		tag = tag.toUpperCase();
		do {
			if (elem.nodeName === tag) {
				return elem;
			}
		} while (elem = elem.parentNode);
		return null;
	}

	var listsCats = new Array();

	<?php
	foreach($listsPerCategory as $val => $listids){
		if(empty($val)) $val = '-1';
		echo "listsCats['".str_replace("'", "\'", $val)."'] = new Array('".implode("','",$listids)."'); ";
	}
	$listCatsSelection = $currentPage == 'newsletter' ? 'listsCats[selection][i]+"listmail' : '"exportlists"+listsCats[selection][i]+"_';
	?>
	function checkCats(selection){
		if(!listsCats[selection]) return;
		var unselect = true;
		for(var i=0; i < listsCats[selection].length; i++){
			if(searchParent(window.document.getElementById(<?php echo $listCatsSelection; ?>0"), 'tr').style.display == 'none') continue;
			if(window.document.getElementById(<?php echo $listCatsSelection; ?>1").checked == true) continue;
			unselect = false;
			break;
		}
		for(var i=0; i < listsCats[selection].length; i++){
			if(searchParent(window.document.getElementById(<?php echo $listCatsSelection; ?>0"), 'tr').style.display == 'none') continue;
			if(unselect){
				<?php if(ACYMAILING_J30) echo 'jQuery("input[id="+'.$listCatsSelection.'0]").next().click();'; ?>
				window.document.getElementById(<?php echo $listCatsSelection; ?>0").checked = true;
			}else{
				<?php if(ACYMAILING_J30) echo 'jQuery("input[id="+'.$listCatsSelection.'1]").next().click();'; ?>
				window.document.getElementById(<?php echo $listCatsSelection; ?>1").checked = true;
			}
		}
	}
-->
</script>
<?php
