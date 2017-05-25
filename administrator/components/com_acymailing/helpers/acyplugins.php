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

class acypluginsHelper{

	public $wraped = false;

	function getFormattedResult($elements,$parameter){
		if(count($elements)<2) return implode('',$elements);

		$beforeAll = array();
		$beforeAll['table'] = '<table cellspacing="0" cellpadding="0" border="0" width="100%" class="elementstable">'."\n";
		$beforeAll['ul'] = '<ul class="elementsul">'."\n";
		$beforeAll['br'] = '';

		$beforeBlock = array();
		$beforeBlock['table'] = '<tr class="elementstable_tr numrow{rownum}">'."\n";
		$beforeBlock['ul'] = '';
		$beforeBlock['br'] = '';

		$beforeOne = array();
		$beforeOne['table'] = '<td valign="top" width="{equalwidth}" class="elementstable_td numcol{numcol}" >'."\n";
		$beforeOne['ul'] = '<li class="elementsul_li numrow{rownum}">'."\n";
		$beforeOne['br'] = '';

		$afterOne = array();
		$afterOne['table'] = '</td>'."\n";
		$afterOne['ul'] = '</li>'."\n";
		$afterOne['br'] = '<br />'."\n";

		$afterBlock =  array();
		$afterBlock['table'] = '</tr>'."\n";
		$afterBlock['ul'] = '';
		$afterBlock['br'] = '';

		$afterAll = array();
		$afterAll['table'] = '</table>'."\n";
		$afterAll['ul'] = '</ul>'."\n";
		$afterAll['br'] = '';


		$type = 'table';
		$cols = 1;
		if(!empty($parameter->displaytype)) $type = $parameter->displaytype;
		if(!empty($parameter->cols)) $cols = $parameter->cols;

		$string = $beforeAll[$type];
		$a = 0;
		$numrow = 1;
		foreach($elements as $oneElement){
			if($a == $cols){
				$string .= $afterBlock[$type];
				$a = 0;
			}
			if($a == 0){
				$string .= str_replace('{rownum}',$numrow,$beforeBlock[$type]);
				$numrow++;
			}
			$string .= str_replace('{numcol}',$a+1,$beforeOne[$type]).$oneElement.$afterOne[$type];
			$a++;
		}
		while($cols > $a){
			$string .= str_replace('{numcol}',$a+1,$beforeOne[$type]).$afterOne[$type];
			$a++;
		}

		$string .= $afterBlock[$type];
		$string .= $afterAll[$type];

		$equalwidth = intval(100/$cols).'%';

		$string = str_replace(array('{equalwidth}'),array($equalwidth),$string);

		return $string;
	}

	function formatString(&$replaceme,$mytag){
		if(!empty($mytag->part)){
			$parts = explode(' ',$replaceme);
			if($mytag->part == 'last'){
				$replaceme = count($parts)>1 ? end($parts) : '';
			}else{
				$replaceme = reset($parts);
			}

		}

		if(!empty($mytag->type)){
			if(empty($mytag->format)) $mytag->format = JText::_('DATE_FORMAT_LC3');
			if($mytag->type == 'date'){
				$replaceme = acymailing_getDate(acymailing_getTime($replaceme),$mytag->format);
			}elseif($mytag->type == 'time'){
				$replaceme = acymailing_getDate($replaceme,$mytag->format);
			}elseif($mytag->type == 'diff'){
				try{
					$date = $replaceme;
					if(is_numeric($date)) $date = acymailing_getDate($replaceme,'%Y-%m-%d %H:%M:%S');
					$dateObj = new DateTime($date);
					$nowObj = new DateTime();
					$diff = $dateObj->diff($nowObj);
					$replaceme = $diff->format($mytag->format);
				}catch(Exception $e){
					$replaceme = 'Error using the "diff" parameter in your tag. Please make sure the DateTime() and diff() functions are available on your server.';
				}
			}
		}

		if(!empty($mytag->lower) || !empty($mytag->lowercase)) $replaceme = function_exists('mb_strtolower') ? mb_strtolower($replaceme,'UTF-8') : strtolower($replaceme);
		if(!empty($mytag->upper) || !empty($mytag->uppercase)) $replaceme = function_exists('mb_strtoupper') ? mb_strtoupper($replaceme,'UTF-8') : strtoupper($replaceme);
		if(!empty($mytag->ucwords)) $replaceme = ucwords($replaceme);
		if(!empty($mytag->ucfirst)) $replaceme = ucfirst($replaceme);
		if(isset($mytag->rtrim)) $replaceme = empty($mytag->rtrim) ? rtrim($replaceme) : rtrim($replaceme,$mytag->rtrim);
		if(!empty($mytag->urlencode)) $replaceme = urlencode($replaceme);


		if(!empty($mytag->maxheight) || !empty($mytag->maxwidth)){
			$pictureHelper = acymailing_get('helper.acypict');
			$pictureHelper->maxHeight = empty($mytag->maxheight) ? 999 : $mytag->maxheight;
			$pictureHelper->maxWidth = empty($mytag->maxwidth) ? 999 : $mytag->maxwidth;
			$replaceme = $pictureHelper->resizePictures($replaceme);
		}
	}

	function replaceVideos($text)
	{
		$text = preg_replace('#\[embed=videolink][^}]*youtube[^=]*=([^"/}]*)[^}]*}\[/embed]#i', '<a href="http://www.youtube.com/watch?v=$1"><img src="http://img.youtube.com/vi/$1/0.jpg"/></a>', $text);
		$text = preg_replace('#<video[^>]*youtube\.com/embed/([^"/]*)[^>]*>[^>]*</video>#i', '<a href="http://www.youtube.com/watch?v=$1"><img src="http://img.youtube.com/vi/$1/0.jpg"/></a>', $text);
		$text = preg_replace('#{JoooidContent[^}]*youtube[^}]*id"[^"]*"([^}"]*)"[^}]*}#i', '<a href="http://www.youtube.com/watch?v=$1"><img src="http://img.youtube.com/vi/$1/0.jpg"/></a>', $text);

		$text = preg_replace('#\[embed=videolink][^}]*video":"([^"]*)[^}]*}\[/embed]#i', '<a href="$1"><img src="'.ACYMAILING_IMAGES.'/video.png"/></a>', $text);
		$text = preg_replace('#<video[^>]*src="([^"]*)"[^>]*>[^>]*</video>#i', '<a href="$1"><img src="'.ACYMAILING_IMAGES.'/video.png"/></a>', $text);
		return $text;
	}

	function removeJS($text){
		$text = preg_replace("#(onmouseout|onmouseover|onclick|onfocus|onload|onblur) *= *\"(?:(?!\").)*\"#iU",'',$text);
		$text = preg_replace("#< *script(?:(?!< */ *script *>).)*< */ *script *>#isU",'',$text);
		return $text;
	}

	private function _convertbase64pictures(&$html){
		if(!preg_match_all('#<img[^>]*src=("data:image/([^;]{1,5});base64[^"]*")([^>]*)>#Uis',$html,$resultspictures)) return;

		jimport('joomla.filesystem.file');

		$dest = ACYMAILING_MEDIA.'resized'.DS;
		acymailing_createDir($dest);
		foreach($resultspictures[2] as $i => $extension){
			$pictname =  md5($resultspictures[1][$i]).'.'.$extension;
			$picturl = ACYMAILING_LIVE.'media/'.ACYMAILING_COMPONENT.'/resized/'.$pictname;
			$pictPath = $dest.$pictname;
			$pictCode = trim($resultspictures[1][$i],'"');
			if(file_exists($pictPath)){
				$html = str_replace($pictCode,$picturl,$html);
				continue;
			}

			$getfunction = '';
			switch($extension){
				case 'gif':
					$getfunction = 'ImageCreateFromGIF';
					break;
				case 'jpg':
				case 'jpeg':
					$getfunction = 'ImageCreateFromJPEG';
					break;
				case 'png':
					$getfunction = 'ImageCreateFromPNG';
					break;
			}

			if(empty($getfunction) || !function_exists($getfunction)) continue;

			$img = $getfunction($pictCode);

			if(in_array($extension,array('gif','png'))){
				imagealphablending($img, false);
				imagesavealpha($img,true);
			}

			ob_start();
			switch($extension){
				case 'gif':
					$status = imagegif($img);
					break;
				case 'jpg':
				case 'jpeg':
					$status = imagejpeg($img,null,100);
					break;
				case 'png':
					$status = imagepng($img,null,0);
					break;
			}
			$imageContent = ob_get_clean();
			$status = $status && JFile::write($pictPath,$imageContent);

			if(!$status) continue;
			$html = str_replace($pictCode,$picturl,$html);
		}
	}

	private function _lineheightfix(&$html){
		$pregreplace = array();
		$pregreplace['#<tr([^>"]*>([^<]*<td[^>]*>[ \n\s]*<img[^>]*>[ \n\s]*</ *td[^>]*>[ \n\s]*)*</ *tr)#Uis'] = '<tr style="line-height: 0px;" $1';
		$pregreplace['#<td(((?!style|>).)*>[ \n\s]*(<a[^>]*>)?[ \n\s]*<img[^>]*>[ \n\s]*(</a[^>]*>)?[ \n\s]*</ *td)#Uis'] = '<td style="line-height: 0px;" $1';

		$pregreplace['#<xml>.*</xml>#Uis'] = '';
		$newbody = preg_replace(array_keys($pregreplace),$pregreplace,$html);
		if(!empty($newbody)) $html = $newbody;
	}

	private function _removecontenttags(&$html){
		$pregreplace = array();
		$pregreplace['#{tab[ =][^}]*}#is'] = '';
		$pregreplace['#{/tabs}#is'] = '';
		$pregreplace['#{jcomments\s+(on|off|lock)}#is'] = '';
		$newbody = preg_replace(array_keys($pregreplace),$pregreplace,$html);
		if(!empty($newbody)) $html = $newbody;
	}

	function cleanHtml(&$html){

		$this->_lineheightfix($html);
		$this->_removecontenttags($html);
		$this->_convertbase64pictures($html);
		$this->cleanEditorCode($html);
	}

	public function fixPictureDim(&$html){
		if(!preg_match_all('#(<img)([^>]*>)#i',$html,$results)) return;

		static $replace = array();
		foreach($results[0] as $num => $oneResult){
			if(isset($replace[$oneResult])) continue;

			if(strpos($oneResult,'width=') || strpos($oneResult,'height=')) continue;
			if(preg_match('#[^a-z_\-]width *:([0-9 ]{1,8})#i',$oneResult,$res) || preg_match('#[^a-z_\-]height *:([0-9 ]{1,8})#i',$oneResult,$res)) continue;

			if(!preg_match('#src="([^"]*)"#i',$oneResult,$url)) continue;

			$imageUrl = $url[1];

			$replace[$oneResult] = $oneResult;

			$base = str_replace(array('http://www.','https://www.','http://','https://'),'',ACYMAILING_LIVE);
			$replacements = array('https://www.'.$base,'http://www.'.$base,'https://'.$base,'http://'.$base);
			$localpict = false;
			foreach($replacements as $oneReplacement){
				if(strpos($imageUrl,$oneReplacement) === false) continue;
				$imageUrl = str_replace(array($oneReplacement,'/'),array(ACYMAILING_ROOT,DS),urldecode($imageUrl));
				$localpict = true;
				break;
			}

			if(!$localpict) continue;

			$dim = @getimagesize($imageUrl);
			if(!$dim) continue;
			if(empty($dim[0]) || empty($dim[1])) continue;

			$replace[$oneResult] = str_replace('<img','<img width="'.$dim[0].'" height="'.$dim[1].'"',$oneResult);

		}

		if(empty($replace)) return;

		$html = str_replace(array_keys($replace),$replace,$html);
	}

	private function cleanEditorCode(&$html){
		if(!strpos($html,'cke_edition_en_cours')) return;

		$html = preg_replace('#<div[^>]*cke_edition_en_cours.*$#Uis','',$html);

	}

	function replaceTags(&$email, &$tags, $html = false){
		if(empty($tags)) return;

		$htmlVars = array('body');
		$textVars = array('altbody');
		$lineVars = array('subject','From','FromName','ReplyTo','ReplyName','bcc','cc','fromname','fromemail','replyname','replyemail','params');

		$variables = array_merge($htmlVars,$textVars,$lineVars);

		if($html){
			if(empty($this->mailerHelper)) $this->mailerHelper = acymailing_get('helper.mailer');

			$textreplace = array();
			$linereplace = array();
			foreach($tags as $i => &$params){
				if(isset($textreplace[$i])) continue;
				$textreplace[$i] = $this->mailerHelper->textVersion($params,true);
				$linereplace[$i] = strip_tags(preg_replace('#</tr>[^<]*<tr[^>]*>#Uis',' | ',$params));
			}

			$htmlKeys = array_keys($tags);
			$lineKeys = array_keys($linereplace);
			$textKeys = array_keys($textreplace);
		}else{
			$textreplace = &$tags;
			$linereplace = &$tags;
			$htmlKeys = array_keys($tags);
			$lineKeys = &$htmlKeys;
			$textKeys = &$htmlKeys;
		}

		foreach($variables as &$var){
			if(empty($email->$var)) continue;

			if(is_array($email->$var)){
				foreach($email->$var as $i => &$arrayField){
					if(empty($arrayField)) continue;

					if(is_array($arrayField)){
						foreach($arrayField as $a => &$oneval){
							if(in_array($var, $htmlVars)) $oneval = str_replace($htmlKeys,$tags,$oneval);
							elseif(in_array($var, $lineVars)) $oneval = str_replace($lineKeys,$linereplace,$oneval);
							else $oneval = str_replace($textKeys,$textreplace,$oneval);
						}
					}else{
						if(in_array($var, $htmlVars)) $arrayField = str_replace($htmlKeys,$tags,$arrayField);
						elseif(in_array($var, $lineVars)) $arrayField = str_replace($lineKeys,$linereplace,$arrayField);
						else $arrayField = str_replace($textKeys,$textreplace,$arrayField);
					}
				}
			}else{
				if(in_array($var, $htmlVars)) $email->$var = str_replace($htmlKeys,$tags,$email->$var);
				elseif(in_array($var, $lineVars)) $email->$var = str_replace($lineKeys,$linereplace,$email->$var);
				else $email->$var = str_replace($textKeys,$textreplace,$email->$var);
			}
		}
	}

	function extractTags(&$email,$tagfamily){
		$results = array();

		$match = '#(?:{|%7B)'.$tagfamily.':(.*)(?:}|%7D)#Ui';
		$variables = array('subject','body','altbody','From','FromName','ReplyTo','ReplyName','bcc','cc','fromname','fromemail','replyname','replyemail','params');
		$found = false;
		foreach($variables as &$var){
			if(empty($email->$var)) continue;
			if(is_array($email->$var)){
				foreach($email->$var as $i => &$arrayField){
					if(empty($arrayField)) continue;
					if(is_array($arrayField)){
						foreach($arrayField as $a => &$oneval){
							$found = preg_match_all($match,$oneval,$results[$var.$i.'-'.$a]) || $found;
							if(empty($results[$var.$i.'-'.$a][0])) unset($results[$var.$i.'-'.$a]);
						}
					}else{
						$found = preg_match_all($match,$arrayField,$results[$var.$i]) || $found;
						if(empty($results[$var.$i][0])) unset($results[$var.$i]);
					}
				}
			}else{
				$found = preg_match_all($match,$email->$var,$results[$var]) || $found;
				if(empty($results[$var][0])) unset($results[$var]);
			}
		}

		if(!$found) return array();

		$tags = array();
		foreach($results as $var => $allresults){
			foreach($allresults[0] as $i => $oneTag){
				if(isset($tags[$oneTag])) continue;
				$tags[$oneTag] = $this->extractTag($allresults[1][$i]);
			}
		}

		return $tags;
	}

	function extractTag($oneTag){
		$arguments = explode('|',strip_tags($oneTag));
		$tag = new stdClass();
		$tag->id = $arguments[0];
		$tag->default = '';
		for($i=1,$a=count($arguments);$i<$a;$i++){
			$args = explode(':',$arguments[$i]);
			$arg0 = trim($args[0]);
			if(empty($arg0)) continue;
			if(isset($args[1])){
				$tag->$arg0 = $args[1];
				if(isset($args[2])) $tag->$args[0] .= ':'.$args[2];
			}else{
				$tag->$arg0 = true;
			}
		}
		return $tag;
	}

	function wrapText($text, $tag){

		$this->wraped = false;

		if(!empty($tag->wrap)) $tag->wrap = intval($tag->wrap);
		if(empty($tag->wrap)) return $text;

		$allowedTags = array();
		$allowedTags[] = 'b';
		$allowedTags[] = 'strong';
		$allowedTags[] = 'i';
		$allowedTags[] = 'em';
		$allowedTags[] = 'a';

		$aloneAllowedTags = array();
		$aloneAllowedTags[] = 'br';
		$aloneAllowedTags[] = 'img';

		$newText = preg_replace('/<p[^>]*>/i', '<br />', $text);
		$newText = preg_replace('/<div[^>]*>/i', '<br />', $newText);
		$newText = strip_tags($newText,'<'.implode('><', array_merge($allowedTags, $aloneAllowedTags)).'>');

		$newText = preg_replace('/^(\s|\n|(<br[^>]*>))+/i', '', trim($newText));
		$newText = preg_replace('/(\s|\n|(<br[^>]*>))+$/i', '', trim($newText));

		$newText = str_replace(array('&lt', '&gt'), array('<', '>'), $newText);

		$numChar = strlen($newText);

		$numCharStrip = strlen(strip_tags($newText));

		if($numCharStrip <= $tag->wrap) return $newText;

		$this->wraped = true;

		$open = array();

		$write = true;

		$countStripChar = 0;

		for($i=0 ; $i<$numChar ; $i++){
			if($newText[$i] == '<'){
				foreach($allowedTags as $oneAllowedTag){
					if($numChar >= ($i+strlen($oneAllowedTag)+1) && substr($newText, $i, strlen($oneAllowedTag)+1) == '<'.$oneAllowedTag && (in_array($newText[$i+strlen($oneAllowedTag)+1], array(' ', '>')))){
						$write = false;
						$open[] = '</'.$oneAllowedTag.'>';
					}

					if($numChar >= ($i+strlen($oneAllowedTag)+2) && substr($newText, $i, strlen($oneAllowedTag)+2) == '</'.$oneAllowedTag){
						if(end($open) == '</'.$oneAllowedTag.'>') array_pop($open);
					}
				}

				foreach($aloneAllowedTags as $oneAllowedTag){
					if($numChar >= ($i+strlen($oneAllowedTag)+1) && substr($newText, $i, strlen($oneAllowedTag)+1) == '<'.$oneAllowedTag && (in_array($newText[$i+strlen($oneAllowedTag)+1], array(' ', '/', '>')))){
						$write = false;
					}
				}
			}

			if($write) $countStripChar++;

			if($newText[$i] == ">") $write = true;

			if($newText[$i] == " " && $countStripChar >= $tag->wrap && $write){
				$newText = substr($newText,0,$i).'...';

				$open = array_reverse($open);
				$newText = $newText.implode('', $open);

				break;
			}
		}

		$newText = preg_replace('/^(\s|\n|(<br[^>]*>))+/i', '', trim($newText));
		$newText = preg_replace('/(\s|\n|(<br[^>]*>))+$/i', '', trim($newText));

		return $newText;
	}

	function getStandardDisplay($format){
		if(empty($format->columns)){
			$table = '<span>';
			$tableText = '<span style="text-align:justify;">';
			$endTable = '</span><br />';
		}else{
			$table = '<tr><td colspan="'.$format->columns.'">';
			$tableText = '<tr><td colspan="'.$format->columns.'" style="text-align:justify;">';
			$endTable = '</td></tr>';
		}

		if(!empty($format->tag->type) && $format->tag->type == 'title'){
			$h2 = '';
			$endH2 = '';
		}else{
			$h2 = '<h2 class="acymailing_title">';
			$endH2 = '</h2>';
		}

		if(!empty($format->link)){
			$link = '<a href="'.$format->link.'">';
			$endLink = '</a>';
		}else{
			$endLink = '';
		}

		if(empty($format->tag->format) || !in_array($format->tag->format, array('TOP-LEFT', 'TOP-RIGHT', 'LEFT-IMG', 'CENTER-IMG', 'TOP-IMG'))) $format->tag->format = 'DEFAULT';

		if(!empty($format->title)) $format->title = $link.$format->title.$endLink;

		$image = '';
		if(!empty($format->imagePath)){
			$image .= '<img src="'.$format->imagePath.'" style="float:';
			if(in_array($format->tag->format, array('TOP-LEFT','DEFAULT'))) $image .= 'left';
			if($format->tag->format == 'TOP-RIGHT') $image .= 'right';
			$image .= ';" />';
			$image = $link.$image.$endLink;
		}

		if(!empty($format->description)) $format->description = $this->wrapText($format->description, $format->tag);


		$result = '';
		if(in_array($format->tag->format, array('DEFAULT', 'TOP-LEFT', 'TOP-RIGHT'))){
			if(!empty($format->title) && empty($tag->notitle))
				$result .= $table.$h2.$format->title.$endH2.$endTable;

			if(!empty($format->afterTitle) && $format->tag->format == 'DEFAULT')
				$result .= $format->afterTitle;

			if(!empty($image) || !empty($format->description)){
				$result .= $tableText;
				if(!empty($image)) $result .= $image;
				if(!empty($format->description)) $result .= $format->description;
				$result .= $endTable;
			}
		}elseif($format->tag->format == 'LEFT-IMG'){
			if(!empty($image) || !empty($format->title)){
				$result .= $table.$h2;
				if(!empty($image)) $result .= $image;
				if(!empty($format->title)) $result .= $format->title;
				$result .= $endH2.$endTable;
			}

			if(!empty($format->description)) $result .= $tableText.$format->description.$endTable;
		}elseif($format->tag->format == 'CENTER-IMG'){
			if(!empty($format->title)) $result .= $table.$h2.$format->title.$endH2.$endTable;
			if(!empty($image)) $result .= '<tr><td colspan="'.$format->columns.'" align="center">'.$image.$endTable;
			if(!empty($format->description)) $result .= $tableText.$format->description.$endTable;
		}elseif($format->tag->format == 'TOP-IMG'){
			if(!empty($image)) $result .= $table.$image.$endTable;
			if(!empty($format->title)) $result .= $table.$h2.$format->title.$endH2.$endTable;
			if(!empty($format->description)) $result .= $tableText.$format->description.$endTable;
		}

		if(!empty($format->afterTitle) && in_array($format->tag->format, array('TOP-LEFT', 'TOP-RIGHT', 'LEFT-IMG', 'CENTER-IMG', 'TOP-IMG'))) $result .= $format->afterTitle;

		return $result;
	}

	function managePicts($tag, $result){
		if(!isset($tag->pict)) return $result;

		$pictureHelper = acymailing_get('helper.acypict');
		if($tag->pict === 'resized'){
			$app = JFactory::getApplication();
			$pictureHelper->maxHeight = empty($tag->maxheight) ? 150 : $tag->maxheight;
			$pictureHelper->maxWidth = empty($tag->maxwidth) ? 150 : $tag->maxwidth;
			if($pictureHelper->available()){
				$result = $pictureHelper->resizePictures($result);
			}elseif($app->isAdmin()){
				$app->enqueueMessage($pictureHelper->error,'notice');
			}
		}elseif($tag->pict == '0'){
			$result = $pictureHelper->removePictures($result);
		}

		return $result;
	}

	function getOrderingField($values, $ordering, $direction, $function = 'updateTagAuto'){
		$orderingValues = array();
		foreach($values as $value => $title){
			$orderingValues[] = JHTML::_('select.option', $value, JText::_($title));
		}
		$orderingValues[] = JHTML::_('select.option', "rand",JText::_('ACY_RANDOM'));

		$orderingDirections = array();
		$orderingDirections[] = JHTML::_('select.option', "DESC", 'DESC');
		$orderingDirections[] = JHTML::_('select.option', "ASC", 'ASC');

		return JHTML::_('select.genericlist', $orderingValues, 'contentorder' , 'size="1" onchange="'.$function.'();" style="width:100px;"', 'value', 'text', $ordering).' '.JHTML::_('select.genericlist', $orderingDirections, 'contentorderdir' , 'size="1" onchange="'.$function.'();" style="width:80px;"', 'value', 'text', $direction);
	}
}
