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

class EditorController extends acymailingController{

	function __construct($config = array())
	{
		parent::__construct($config);
		JHTML::_('behavior.tooltip');
		JRequest::setVar('tmpl','component');
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.path');

		$this->registerDefaultTask('browse');
	}

 	function browse(){
		$this->_setCss();
		$this->_setJs();
		$this->_displayHTML();
	}

	private function _setCss(){
		if(JRequest::getVar('inpopup','')=='true'){
			$height_acy_media_browser_table=420;
			$height_acy_media_browser_list=310;
			$width_acy_media_browser_actions=393;
			$width_acy_media_browser_hidden_elements=395;
			$height_acy_media_browser_image_details=415;
			$width_acy_media_browser_buttons_block=365;
			$width_acy_media_browser_url_input=60;
		}else{
			$height_acy_media_browser_table=540;
			$height_acy_media_browser_list=450;
			$width_acy_media_browser_actions=522;
			$width_acy_media_browser_hidden_elements=522;
			$height_acy_media_browser_image_details=550;
			$width_acy_media_browser_buttons_block=492;
			$width_acy_media_browser_url_input=70;
		}

		$css="
			#acy_media_browser_table{
				height:".$height_acy_media_browser_table."px;
				width:100%;
				margin: 0px;
				border: 1px solid rgb(233, 233, 233);
				box-shadow: 4px 4px 4px -4px rgba(0, 0, 0, 0.1);
			}

			#acy_media_browser_path_dropdown{
				float:left;
				margin-left:15px;
				margin-top:15px;
				width:65%;
			}

			#acy_media_browser_global_create_folder{
				width:28%;
				float:right;
				margin-top:15px;
				margin-right:10px;
			}

			#acy_media_browser_create_folder{
				width:100%;
			}

			#create_folder_btn{
				margin-top:0px;
			}

			#acy_media_browser_area_create_folder{
				position:absolute;
				z-index:10;
				margin-top:5px;
				border:1px solid #e9e9e9;
				height:0px;
				width:150px;
				background-color:#f6f6f6;
			}

			#subFolderName{
				width:80%;
				margin-left:7px;
				margin-top:5px;
			}

			#acy_media_browser_area_create_folder .btn{
				float:right;
				margin-right:5px
			}

			#acy_media_browser_message{
				height:450px;
				overflow:auto;
				margin:0px;
				padding:5px;
				border-bottom: 1px solid rgb(233, 233, 233);
			}

			#acy_media_browser_list{
				height:".$height_acy_media_browser_list."px;
				overflow-x:hidden;
				margin:0px;
					padding:5px;
				border-bottom: 1px solid rgb(233, 233, 233);
			}

			.acy_media_browser_image_size{
				color: #AAAAAA;
			}

			#acy_media_browser_actions{
				text-align:center;
					box-shadow: 0px -4px 4px -4px rgba(0, 0, 0, 0.3);
					width:".$width_acy_media_browser_actions."px;
				overflow:hidden;
			}

			#acy_media_browser_containing_block{
				height: 70px;
					width:1044px;
			}

			#acy_media_browser_buttons_block{
				padding:22px 15px 0px;
				width: ".$width_acy_media_browser_buttons_block."px;
				float:left;
				display:inline-block;
			}

			#acy_media_browser_hidden_elements{
				float:left;
					width:".$width_acy_media_browser_hidden_elements."px;
					display:inline-block;
			}

			#acy_media_browser_url_input{
				width:".$width_acy_media_browser_url_input."%;
				margin:0px;
			}

			#acy_media_browser_insert_message{
				margin-top:5px;
			}

			#acy_media_browser_image_details_row{
				width:35%;
				vertical-align:top;
				background-color: rgb(246, 246, 246);
				border: 1px solid rgb(233, 233, 233);
				font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif;
				font-size: 13px;
				line-height: 18px;
				color: rgb(102, 102, 102);
			}

			#acy_media_browser_image_details{
				position: relative;
				width: 85%;
				overflow-x:hidden;
				height: ".$height_acy_media_browser_image_details."px;
				padding: 15px;
			}

			#acy_media_browser_image_selected_info{
				width:230px;
				float:left;
				margin-bottom:10px;
			}

			#acy_media_browser_image_selected_details label{
				font-weight: bold;
			}

			#acy_media_browser_image_selected_details input {
				margin-bottom: 7px;
				}

			#acy_media_browser_image_selected_details select {
				margin-bottom: 7px;
				}

			.alert{
					padding: 8px 35px 8px 14px;
					margin-bottom: 18px;
					text-shadow: 0px 1px 0px rgba(255, 255, 255, 0.5);
					background-color: rgb(252, 248, 227);
					border: 1px solid rgb(251, 238, 213);
					border-radius: 4px;
			}

			.alert-error{
				background-color: rgb(242, 222, 222);
				border-color: rgb(238, 211, 215);
				color: rgb(185, 74, 72);
			}

			.alert-success {
					background-color: rgb(223, 240, 216);
					border-color: rgb(214, 233, 198);
					color: rgb(70, 136, 71);
			}


		";
		if(!ACYMAILING_J30){
			$css = $css."#acy_media_browser_area_create_folder .btn{
					margin-top:30px;
					margin-right:20px;
				}
				#subFolderName{
					margin-left:13px;
				}
			";
		}
		echo '<style>'.$css.'</style>';
	}

	private function _setJs(){
		$doc = JFactory::getDocument();
		$config =& acymailing_config();
		$websiteurl = rtrim(JURI::root(),'/').'/';


		$doc->addScript($websiteurl.'media/com_acymailing/js/jquery/jquery-1.9.1.min.js?v='.@filemtime(ACYMAILING_ROOT.'media'.DS.'com_acymailing'.DS.'js'.DS.'jquery'.DS.'jquery-1.9.1.min.js'));

		$imageZone = JRequest::getVar( 'image_zone', array(), '', 'array' );
		if(empty($imageZone)){
			$getAdditionalTags = "
					var selectedImageWidth = document.getElementById('acy_media_browser_image_width').value;
					var selectedImageHeight = document.getElementById('acy_media_browser_image_height').value;
					var selectedImageAlign = document.getElementById('acy_media_browser_image_align').value;
					var selectedImageBorder = document.getElementById('acy_media_browser_image_border').value;
					var selectedImageMargin = document.getElementById('acy_media_browser_image_margin').value;

					var width = ''; var height =''; var align=''; var border = ''; var margin = '';
					if(selectedImageWidth>0) width =  ' width:' + selectedImageWidth + 'px; ';
					if(selectedImageHeight>0) height = ' height:' +  selectedImageHeight + 'px; ';
					if(selectedImageAlign) align = 'float:' + selectedImageAlign + ';';
					if(selectedImageWidth>0 && selectedImageAlign.trim()=='center') align = 'margin:auto;';
					if(selectedImageBorder) border = ' border:' +  selectedImageBorder + '; ';
					if(selectedImageBorder>0 ) border = ' border: solid ' +  selectedImageBorder + 'px; ';
					if(selectedImageMargin>0) margin = ' margin:' +  selectedImageMargin + 'px; ';
					else if(selectedImageMargin) margin = ' margin:' +  selectedImageMargin + '; ';
					var imgSize = ' height =\"' + selectedImageHeight + '\" width = \"' + selectedImageWidth + '\"';
							";
			$sizeAndAlignTags = " style=\"' + height + width + align + border + margin +'\" ";

			$insertImage = "window.parent.insertImageTag(tag, previousSelection);";
		}else{
			$getAdditionalTags = "var selectedImageRef = document.getElementById('acy_media_browser_image_target').value; ";
			$sizeAndAlignTags = "";
			$insertImage = "window.parent.jInsertEditorText(tag, this.editor);";
		}

		if(JRequest::getVar('inpopup','')=='true'){
			$imgMaxHeight = 150;
			$slideValue = -395;
		}else{
			$imgMaxHeight = 190;
			$slideValue = -522;
		}

			$js="
				var previousSelection = window.parent.getPreviousSelection();

				function checkSelected(imageZone) {

					if(imageZone){
						var editor = window.parent.CKEDITOR.editor;

						o = this._getUriObject(window.self.location.href);
						q = new Hash(this._getQueryObject(o.query));
						zone = decodeURIComponent(q.get('e_name'));

						var html = window.parent.getSelectedHTML(zone);
						var parsedSelection = jQuery.parseHTML(html);

						if(!parsedSelection)
							return false;

						if(parsedSelection[0].tagName == 'A'){
							var parsedImage = jQuery.parseHTML(parsedSelection[0].innerHTML);
							parsedImage = parsedImage[0];

							if(parsedSelection[0].href)
									document.getElementById('acy_media_browser_image_target').value =  parsedSelection[0].href;
						}else if(parsedSelection[0].tagName == 'IMG'){
							var parsedImage = parsedSelection[0];
						}

						if(!parsedImage) return false;

						var name = parsedImage.src.substr(parsedImage.src.lastIndexOf('/') + 1);
						if(parsedImage.src.substring(0,4)=='http'){
							var imageUrl =  parsedImage.src;
						}else{
							var imageUrl =  '".ACYMAILING_LIVE."' + parsedImage.src;
						}
						var width = parsedImage.width;
						var height = parsedImage.height;
						displayImageFromUrl(imageUrl, 'success', name, width, height);
						if(parsedImage.alt)
							document.getElementById('acy_media_browser_image_title').value =  parsedImage.alt;
					}else{
						var editor =  window.parent.editor;
						var sel = editor.getSelection();
						var ranges = sel.getRanges();
						var el = new window.parent.CKEDITOR.dom.element('div');
						for (var i = 0, len = ranges.length; i < len; ++i) {
								el.append(ranges[i].cloneContents());
						}

						if(el.getFirst() && el.getFirst().getName() == 'a'){
							var selection = el.getFirst().getHtml();
							var selectedImageRef = el.getFirst().getAttribute('href');
						} else{
							var selection = el.getHtml();
						}

						var parsedSelection = jQuery.parseHTML(selection);

						if(!parsedSelection)
							return false;

						if(parsedSelection[0].tagName == 'IMG'){
							var name = parsedSelection[0].src.substr(parsedSelection[0].src.lastIndexOf('/') + 1);
							var width = parsedSelection[0].width;
							var height = parsedSelection[0].height;
							if($(selection).attr('src').substring(0,4)=='http'){
								var imageUrl =  $(selection).attr('src');
							}else{
								var imageUrl =  '".ACYMAILING_LIVE."' + $(selection).attr('src');
							}
							displayImageFromUrl(imageUrl, 'success', name, width, height);

							if(parsedSelection[0].alt)
								document.getElementById('acy_media_browser_image_title').value =  parsedSelection[0].alt;
							if(parsedSelection[0].style.width)
								document.getElementById('acy_media_browser_image_width').value =  parsedSelection[0].style.width.slice(0,-2);
							if(parsedSelection[0].style.height)
								document.getElementById('acy_media_browser_image_height').value =  parsedSelection[0].style.height.slice(0,-2);
							if(parsedSelection[0].style.cssFloat)
								document.getElementById('acy_media_browser_image_align').value =  parsedSelection[0].style.cssFloat;
							if(parsedSelection[0].style.margin)
								document.getElementById('acy_media_browser_image_margin').value =  parsedSelection[0].style.margin;
							if(parsedSelection[0].style.border)
								document.getElementById('acy_media_browser_image_border').value =  parsedSelection[0].style.border;
							if(parsedSelection[0].className)
								document.getElementById('acy_media_browser_image_class').value =  parsedSelection[0].className;
							if(selectedImageRef)
								document.getElementById('acy_media_browser_image_linkhref').value = selectedImageRef;
						}
					}
				}

				function displayImageFromUrl(url, result, name, width, height, fromUrl){
					if(result=='success'){
							var infos = '<div style=\"width:100%; display:block: height:1px; float:left; margin-top:10px;\"></div>';
							document.getElementById('acy_media_browser_image_selected').innerHTML='<img id=\"acy_media_browser_selected_image\" src=\"' + url + '\"  style=\"border: 1px solid rgb(233, 233, 233); float:left; margin-right:15px; max-width: 230px; max-height:".$imgMaxHeight."px;\"></img>'+infos;
							document.getElementById('acy_media_browser_image_selected').style.display=\"\";
							if(!name){ var name = url.substr(url.lastIndexOf('/') + 1); }
							if(width){
								document.getElementById('acy_media_browser_image_selected_info').innerHTML='<div><span id=\"acy_media_browser_image_selected_name\" style=\"font-weight:bold;\"> '+name+'</span><br />'+width+'x'+height+'<br />';
								var widthField = document.getElementById('acy_media_browser_image_width');
								var heightField = document.getElementById('acy_media_browser_image_height');
								if(widthField) widthField.value = width;
								if(heightField) heightField.value = height;
							}
							document.getElementById('acy_media_browser_image_selected_info').style.display=\"\";
							if(fromUrl){
								document.getElementById('acy_media_browser_insert_message').innerHTML='<span style=\"color:green;\">".str_replace("'","\'",JText::_('IMAGE_FOUND'))."</span>';
							}
					}else{
							document.getElementById('acy_media_browser_image_selected').innerHTML=\"\";
							document.getElementById('acy_media_browser_image_selected').style.display=\"none\";
							document.getElementById('acy_media_browser_image_selected_info').innerHTML=\"\";
							if(fromUrl){
								if(result='error'){
									document.getElementById('acy_media_browser_insert_message').innerHTML='<span style=\"color:red;\">".str_replace("'","\'",JText::_('IMAGE_NOT_FOUND'))."</span>';
								}else if(result='timeout'){
									document.getElementById('acy_media_browser_insert_message').innerHTML='<span style=\"color:red;\">".str_replace("'","\'",JText::_('IMAGE_TIMEOUT'))."</span>';
								}
							}
					}
				}


				function calculateSize(newHeight, newWidth){
					if((newHeight == '' && newWidth == '') || (newHeight == '' && newWidth == 0) || (newHeight == 0 && newWidth == '')) return;
					var img = document.getElementById('acy_media_browser_selected_image');
					if(!img) return;

					if(newHeight == 0)
						document.getElementById('acy_media_browser_image_height').value =  parseInt(img.naturalHeight * (newWidth / img.naturalWidth));

					if(newWidth == 0)
						document.getElementById('acy_media_browser_image_width').value =  parseInt(img.naturalWidth * (newHeight / img.naturalHeight));
				}


				function testImage(url, callback, timeout) {
					timeout = timeout || 5000;
						var timedOut = false, timer;
						var img = new Image();
						img.onerror = img.onabort = function() {
								if (!timedOut) {
										clearTimeout(timer);
										callback(url, \"error\", '', '', '',true);
								}
						};
						img.onload = function() {
								if (!timedOut) {
										clearTimeout(timer);
										callback(url, \"success\",'','', '',true);
								}
						};
						img.src = url;
						timer = setTimeout(function() {
								timedOut = true;
								callback(url, \"timeout\", '', '', '', true);
						}, timeout);
				}

				function displayAppropriateField(id){
					if(id==\"import_from_url_btn\"){
							document.getElementById('upload_image').style.display=\"none\";
							document.getElementById('import_from_url').style.display=\"\";
							jQuery('#acy_media_browser_containing_block').stop().animate({marginLeft: '".$slideValue."px' }, 400);
					}else if(id==\"upload_image_btn\"){
							document.getElementById('upload_image').style.display=\"\";
							document.getElementById('import_from_url').style.display=\"none\";
							jQuery('#acy_media_browser_containing_block').stop().animate({marginLeft: '".$slideValue."px' }, 400);
					}else if(id == \"create_folder_btn\"){
						if(document.getElementById('acy_media_browser_area_create_folder').style.display == \"none\"){
							document.getElementById('acy_media_browser_area_create_folder').style.display = \"\";
							jQuery('#acy_media_browser_area_create_folder').stop().animate({height: '85px'},400);
						}else{
							document.getElementById('acy_media_browser_area_create_folder').style.display = \"none\";
							jQuery('#acy_media_browser_area_create_folder').stop().animate({height: '0px'},400);
						}
					}else{
							jQuery('#acy_media_browser_containing_block').stop().animate({marginLeft: '-0px' }, 400);
					}
				}

				function toggleImageInfo(id, action){
					if(action==\"display\"){
							document.getElementById('acy_media_browser_image_info_'+id+'').style.display = \"\";
					}else{
							document.getElementById('acy_media_browser_image_info_'+id+'').style.display = \"none\";
					}
				}

				function _getQueryObject(q) {
					var vars = q.split(/[&;]/);
					var rs = {};
					if (vars.length) vars.each(function(val) {
							var keys = val.split('=');
							if (keys.length && keys.length == 2) rs[encodeURIComponent(keys[0])] = encodeURIComponent(keys[1]);
					});
					return rs;
				}

				function _getUriObject(u){
					var bits = u.match(/^(?:([^:\/?#.]+):)?(?:\/\/)?(([^:\/?#]*)(?::(\d*))?)((\/(?:[^?#](?![^?#\/]*\.[^?#\/.]+(?:[\?#]|$)))*\/?)?([^?#\/]*))?(?:\?([^#]*))?(?:#(.*))?/);
					return (bits)
						? bits.associate(['uri', 'scheme', 'authority', 'domain', 'port', 'path', 'directory', 'file', 'query', 'fragment'])
						: null;
				}

				function validateImage(){
					var urlInput = document.getElementById('acy_media_browser_url_input').value;
					var urlImageName = urlInput.substr(urlInput.lastIndexOf('/') + 1);
					var selectedImageName = '';
					if(document.getElementById('acy_media_browser_image_selected_name'))
					var selectedImageName = document.getElementById('acy_media_browser_image_selected_name').innerHTML;

					var selectedImageAlt = document.getElementById('acy_media_browser_image_title').value;
					var selectedImageRef = '';
					if(document.getElementById('acy_media_browser_image_linkhref'))
						var selectedImageRef = document.getElementById('acy_media_browser_image_linkhref').value;
					var selectedImageUrl = document.getElementById('acy_media_browser_selected_image').src;
					var imgSize = '';
					var selectedImageClass = '';
					if(document.getElementById('acy_media_browser_image_class'))
						var selectedImageClass = document.getElementById('acy_media_browser_image_class').value;

					".$getAdditionalTags."

					o = this._getUriObject(window.self.location.href);
					q = new Hash(this._getQueryObject(o.query));
					this.editor = decodeURIComponent(q.get('e_name'));

					var dropdown = document.getElementById('acy_media_browser_files_path');
					var path = dropdown.options[dropdown.selectedIndex].text;
					var base = ' ".ACYMAILING_LIVE." ';

					if(urlInput!='http://' && selectedImageName.trim()==urlImageName.trim()){
							var tag = '<img ' + imgSize + ' src=\"' + urlInput + '\" alt=\"' + selectedImageAlt + '\" ".$sizeAndAlignTags." class=\"' + selectedImageClass + '\" />';
					}else{
							var tag = '<img ' + imgSize + ' src=\"' + selectedImageUrl + '\" alt=\"' + selectedImageAlt + '\" ".$sizeAndAlignTags." class=\"' + selectedImageClass + '\" />';
					}

					if(selectedImageRef){
							tag = '<a href=\"' + selectedImageRef + '\">' + tag + '</a>';
					}

					".$insertImage."
					return false;
				}

				function changeFolder(folderName){
					var url = window.location.href;
					if (url.indexOf('?') > -1){
							var lastParam = url.substring(url.lastIndexOf('&') + 1);
							if(lastParam == 'task=createFolder')url = url.replace(lastParam,'task=browse&e_name=ACY_NAME_AREA');
							lastParam = lastParam.split('=');
							if(lastParam=='selected_folder')
								url = url.replace(lastParam, 'selected_folder='+folderName);
							else
								url += '&selected_folder='+folderName;
					}else{
							 url += '?selected_folder='+folderName;
					}
					window.location.href = url;
				}

			";

			$doc->addScriptDeclaration($js);
		}

	private function _displayHTML(){
		$config =& acymailing_config();
		$app = JFactory::getApplication();

		$mediaFolders = explode(',', $config->get('mediafolder','media/com_acymailing/upload'));
		$mediaFolders = $this->_generateSpecificFolders($mediaFolders);

		$receivedFolder = $app->getUserStateFromRequest( ACYMAILING_COMPONENT.".acyeditor.selected_folder", 'selected_folder','','string' );
		$defaultFolder = reset($mediaFolders);

		if(!empty($receivedFolder)){
			$allowed = false;
			foreach($mediaFolders as $oneMedia){
				if(preg_match('#^'.preg_quote($oneMedia).'[a-z_0-9\-/]*$#i',$receivedFolder)){
					$allowed = true;
					break;
				}
			}
			if($allowed){
				$defaultFolder=$receivedFolder;
			}else{
				acymailing_display('You are not allowed to access this folder','error');
			}
		}

		$uploadPath = JPath::clean(ACYMAILING_ROOT.trim(str_replace('/',DS,trim($defaultFolder)),DS));

		$uploadedImage = JRequest::getVar( 'uploadedImage', array(), 'files', 'array' );
		if(!empty($uploadedImage)){
			if(!empty($uploadedImage['name'])){
				if($this->_importImage($uploadedImage, $uploadPath)) $uploadMessage='success';
				else $uploadMessage='error';
			} else{
				$uploadMessage = 'error';
				$this->message = JText::_('BROWSE_FILE');
			}
		}

			?>

			<div id="acy_media_browser" >
				<!-- <br style="font-size:1px"/> -->
				<table id="acy_media_browser_table" style="height:420px;">
					<tr>
						<td style="width:65%; vertical-align:top;">
							<?php

							$folders = $this->_generateArborescence($mediaFolders);

							foreach($folders as $folder){
								$this->values[] = JHTML::_('select.option', $folder, $folder);
							}

							echo '<div style="display:inline-block;width:100%;">';
							echo '<form method="post" action="index.php?option=com_acymailing&ctrl='.(JFactory::getApplication()->isAdmin()?'':'front').'editor&task=createFolder" >';
							echo '<div id="acy_media_browser_path_dropdown" >';
							echo JHTML::_('select.genericlist',   $this->values, 'acy_media_browser_files_path', 'class="inputbox chzn-done" size="1" onchange="changeFolder(this.value)" style="width:350px" ', 'value', 'text', $defaultFolder).'<br />';
							echo '</div>';

							echo '<div id="acy_media_browser_global_create_folder" >';

							echo '<div id="acy_media_browser_create_folder" >';
							echo '<button id="create_folder_btn" class="btn" onclick="displayAppropriateField(this.id)" type="button" style="width:100%;" >'.JText::_('CREATE_FOLDER').'</button>';
							echo '</div>';

							echo '<div id="acy_media_browser_area_create_folder" style=\'display:none;\'>';
							echo '<input id="subFolderName" name="subFolderName" type="text" placeholder="'.JText::_('FOLDER_NAME').'" name="text" required="required" />';
							echo '<input type="submit" class="btn btn-primary" value="'.JText::_('ACY_APPLY').'" />';
							echo '</div>';

							echo '</div>';
							echo JHTML::_( 'form.token' );
							echo '</form>';
							echo '</div>';


							acymailing_createDir($uploadPath);

							$files = JFolder::files($uploadPath);

							echo '<ul id="acy_media_browser_list">';

							if(!empty($uploadMessage)){
								 if($uploadMessage=='success') acymailing_display($this->message);
								 else if($uploadMessage=='error') acymailing_display($this->message, 'error');
							}

							$images = array();
							$imagesFound=false;
							foreach($files as $k => $file){
								if(strrpos($file, '.') === false)
									continue;

								$ext = strtolower(substr($file, strrpos($file, '.') + 1));
								$extensions = array('jpg', 'jpeg', 'png', 'gif');
								if(!in_array($ext, $extensions))
									continue;

								$imagesFound = true;
								$images[] = $file;
								$imageSize = getimagesize($uploadPath.DS.$file);
								?>
								<li id="acy_media_browser_images_<?php echo $k; ?>" style="position: relative; height: 135px; width:135px; display:inline-block; margin:14px; margin-top:7px; box-shadow: 0px 2px 2px 2px rgba(0, 0, 0, 0.2);"  onmouseover="toggleImageInfo(<?php echo $k; ?>, 'display')" onmouseout="toggleImageInfo(<?php echo $k; ?>, 'hide')" >
									<img id="acy_media_browser_image_<?php echo $k; ?>" src="<?php echo ACYMAILING_LIVE.$defaultFolder.'/'.$file; ?>" height="135" width="135" style="height: 135px; width:135px;"></img>
									<a href="#" onclick="displayImageFromUrl('<?php echo ACYMAILING_LIVE.$defaultFolder.'/'.$file; ?>', 'success', '<?php echo $file; ?>', '<?php echo $imageSize[0]; ?>', '<?php echo $imageSize[1]; ?>'); return false;" >
											<div id="acy_media_browser_image_info_<?php echo $k; ?>" style="box-shadow: 0px 3px 3px 3px rgba(0, 0, 0, 0.3); padding-top:40px; text-align:center; vertical-align:middle; color: white; position:absolute; top:0px; left:0px; bottom:0px; right:0px; display:none; background-color: rgba(0,0,0,0.5);">
												<?php echo $file; ?><br />
												<span class="acy_media_browser_image_size" ><?php echo $imageSize[0].'x'.$imageSize[1]; ?> - <?php echo round((filesize($uploadPath.DS.$file)* 0.0009765625),2).' ko'; ?><br /></span>
											</div>
									</a>
								</li>
								<?php
							}

							if(!$imagesFound){
								acymailing_display(JText::_( 'NO_FILE_FOUND' ),'warning');
							}
							?>

							</ul>

							<!-- Here we give the possibility to import a file or specify and url -->
							<div id="acy_media_browser_actions" >
								<div id="acy_media_browser_containing_block">
									<div id="acy_media_browser_buttons_block" >
										<button type="button"  class="btn" id="upload_image_btn" onclick="displayAppropriateField(this.id)"> <?php echo JText::_('UPLOAD_NEW_IMAGE'); ?></button>
										<?php echo JText::_('ACY_OR'); ?>
										<button type="button"  class="btn" id="import_from_url_btn" onclick="displayAppropriateField(this.id)"> <?php echo JText::_('INSERT_IMAGE_FROM_URL'); ?> </button>
									</div>
									<div id="acy_media_browser_hidden_elements">
										<div id="upload_image" style="position: relative; padding-top:5px;	display:none; text-align: center;">
											<form method="post"  name="adminForm" id="adminForm" enctype="multipart/form-data" style="margin:0px; margin-top:3px;" >
												<input type="file" style="width:auto;" name="uploadedImage" /><br />
												<input type="hidden" name="task" value="browse" />
												<input type="hidden" name="selected_folder" value="<?php echo htmlspecialchars($defaultFolder, ENT_COMPAT, 'UTF-8'); ?>" />
												<?php echo JHTML::_( 'form.token' ); ?>
											</form>
											<button class="btn btn-primary" type="button" onclick="submitbutton();"> <?php echo JText::_('IMPORT'); ?> </button>
											<span style="position:absolute; top:5px; left:5px;" id="acy_back_from_upload" onclick="displayAppropriateField(this.id)" ><a href="javascript:void(0);">&#8592 <?php echo JText::_('MEDIA_BACK'); ?></a></span>
										</div>
										<div id="import_from_url" style="padding-top:9px; position:relative; ">
											<input type="text" id="acy_media_browser_url_input" class="inputbox" oninput="testImage(this.value, displayImageFromUrl)" value="http://" />
											<?php  ?>
											<div id="acy_media_browser_insert_message"></div>
											<span style="position:absolute; top:5px; left:5px;" id="acy_back_from_url" onclick="displayAppropriateField(this.id)" ><a href="javascript:void(0);">&#8592 <?php echo JText::_('MEDIA_BACK'); ?></a></span>
										</div>
									</div>
								</div>
							</div>
						</td>
						<!-- IMAGE INFORMATION -->
						<td id="acy_media_browser_image_details_row" >
							<div id="acy_media_browser_image_details"  >
								<div id="acy_media_browser_image_selected" style=" max-width:230px; max-height:190px; display:none;	margin:auto; margin-bottom:10px;"></div>
								<div id="acy_media_browser_image_selected_info" style=""></div>
								<div id="acy_media_browser_image_selected_details" >
									<label for="acy_media_browser_image_title" style="float:left;"><?php echo JText::_( 'ACY_TITLE' ); ?></label>
									<input type="text" id="acy_media_browser_image_title" class="inputbox" style="width:100%"  value="" />
									<?php $imageZone = JRequest::getVar( 'image_zone', array(), '', 'array' );
									if(!empty($imageZone)){ ?>
										<label for="acy_media_browser_image_target"><?php echo JText::_( 'ACY_LINK' ); ?></label>
										<input type="text" id="acy_media_browser_image_target" placeholder="<?php echo ACYMAILING_LIVE; ?>..." class="inputbox" style="width:100%"  value="" />
									<?php }else{ ?>
										<label for="acy_media_browser_image_width" style="display:inline;"><?php echo JText::_( 'CAPTCHA_WIDTH' ); ?></label>	<input type="text" id="acy_media_browser_image_width"  style="width:23%;"  value="" oninput="calculateSize(0, this.value)" />
										<br /><label for="acy_media_browser_image_height" style="display:inline;"><?php echo JText::_( 'CAPTCHA_HEIGHT' ); ?></label>	<input type="text" id="acy_media_browser_image_height"  style="width:22%;"  value="" oninput="calculateSize(this.value, 0)" />
										<br /><label for="acy_media_browser_image_align" style="display:inline;"><?php echo JText::_( 'ALIGNMENT' ); ?></label>
										<select id="acy_media_browser_image_align" class="chzn-done" style="width:50%">
											<option value=""><?php echo JText::_( 'NOT_SET' ); ?></option>
											<option value="left"><?php echo JText::_( 'ACY_LEFT' ); ?></option>
											<option value="right"><?php echo JText::_( 'ACY_RIGHT' ); ?></option>
										</select><br />
										<label for="acy_media_browser_image_margin" style="display:inline;"><?php echo JText::_( 'ACY_MARGIN' ); ?></label>	<input type="text" style="width:23%;"  id="acy_media_browser_image_margin"  value="" /><br />
										<label for="acy_media_browser_image_border" style="display:inline;"><?php echo JText::_( 'ACY_BORDER' ); ?></label>	<input type="text" style="width:23%;"  id="acy_media_browser_image_border"  value="" /><br />
										<label for="acy_media_browser_image_class" style="display:inline;"><?php echo JText::_( 'ACY_CLASS' ); ?></label>	<input type="text" style="width:50%;"  id="acy_media_browser_image_class"  value="" />
										<input type="hidden" id="acy_media_browser_image_linkhref"  value="" />
									<?php } ?>
								</div>
								<button class="btn btn-primary" type="button" onclick="validateImage();window.parent.SqueezeBox.close();" style=" position:absolute; bottom:6px; right:6px; "><?php echo JText::_('INSERT'); ?> </button>
							 </div>
						</td>
					</tr>
			 	</table>
			</div>
		<?php

		$imageZone = JRequest::getVar( 'image_zone', array(), '', 'array' );
		if($imageZone){
			echo '<script>checkSelected(true);</script>';
		}else{
			echo '<script>checkSelected();</script>';
		}
		if(isset($uploadMessage) && $uploadMessage=='success'){
			$imageSize = getimagesize(ACYMAILING_LIVE.rtrim($defaultFolder, '/').'/'.$this->imageName);
			echo '<script> displayImageFromUrl(\''.ACYMAILING_LIVE.rtrim($defaultFolder, '/').'/'.$this->imageName.'\',\'success\', \''.$this->imageName.'\','.$imageSize[0].','.$imageSize[1].');</script>';
		}
	}

	private function _generateArborescence($folders){
		$folderList = array();
		foreach($folders as $folder){
			$folderPath = JPath::clean(ACYMAILING_ROOT.trim(str_replace('/',DS,trim($folder)),DS));
			if(!file_exists($folderPath)) acymailing_createDir($folderPath);
			$subFolders = JFolder::listFolderTree($folderPath,'',15);
			$folderList[] = trim($folder,'/ ');
			foreach($subFolders as $oneFolder){
				$subFolder = str_replace(ACYMAILING_ROOT,'',$oneFolder['relname']);
				$subFolder = str_replace(DS,'/',$subFolder);
				$folderList[] = ltrim($subFolder, '/');
			}
		}

		return array_unique($folderList);
	}

	private function _generateSpecificFolders($folders){

		$my = JFactory::getUser();
		$db = JFactory::getDBO();
		$newFolders = array();

		foreach($folders as $k => $folder){
			if(strpos($folder,'{userid}') !== false){
				if(empty($my->id)){
					$folders[$k] = str_replace('{userid}', '', $folders[$k]);
				}else{
					$folders[$k] = str_replace('{userid}', $my->id, $folders[$k]);
				}
			}

			if(strpos($folder,'{groupid}') !== false || strpos($folder,'{groupname}') !== false){
				if(!ACYMAILING_J16){
					$groups = array($my->gid);
				}else{
					jimport('joomla.access.access');
					$groups = JAccess::getGroupsByUser($my->id,false);
				}

				if(empty($groups)){
					$folders[$k] = str_replace(array('{groupid}','{groupname}'), '', $folders[$k]);
				}else{
					JArrayHelper::toInteger($groups);

					if(ACYMAILING_J16){
						$db->setQuery('SELECT id, title FROM #__usergroups WHERE id IN ('.implode(',', $groups).')');
						$completeGroups = $db->loadObjectList();
					}else{
						$groupObject = new stdClass();
						$groupObject->id = $my->gid;
						$groupObject->title = $my->usertype;
						$completeGroups = array($groupObject);
					}

					foreach($completeGroups as $group){
						$newFolders[] = str_replace(array('{groupid}', '{groupname}'), array($group->id, strtolower(str_replace(' ', '_', $group->title))), $folders[$k]);
					}

					$folders[$k] = '';
				}
			}
		}

		$folders = array_merge($folders, $newFolders);
		$folders = array_filter( $folders );
		sort($folders);

		return $folders;
	}

		private function _importImage($image, $uploadPath){
			JRequest::checkToken() or die( 'Invalid Token' );

			$app = JFactory::getApplication();
			$config =& acymailing_config();
			$additionalMsg='';

			if($image["error"] > 0){
				$this->message = "Error Uploading code: ".htmlspecialchars($image["error"], ENT_COMPAT, 'UTF-8')."<br />";
				return false;
			}

			if(!preg_match('#\.(jpg|jpeg|png|gif)$#Ui',$image["name"],$extension)){
				$ext = substr($image["name"], strrpos($image["name"], '.')+1);
				$this->message = JText::sprintf( 'ACCEPTED_TYPE', htmlspecialchars($ext, ENT_COMPAT, 'UTF-8'), 'jpg, jpeg, png, gif' );
				return false;
			}

			$image["name"] = preg_replace('#[^a-z0-9]#i','_',strtolower(substr($image["name"], 0,strrpos($image["name"], '.')))).'.'.$extension[1];

			$imageSize = getimagesize($image['tmp_name']);
			if(empty($imageSize)){
				$this->message = 'Invalid image';
				return false;
			}

			if(file_exists($uploadPath.DS.$image["name"])){
				$i = 1;
				$nameFile = preg_replace("/\\.[^.\\s]{3,4}$/", "", $image["name"]);
				$ext = substr($image["name"], strrpos($image["name"], '.')+1);
				while(file_exists($uploadPath. DS . $nameFile.'_'.$i.'.'.$ext)){
					$i++;
				}

				$image["name"] = $nameFile.'_'.$i.'.'.$ext;
				$additionalMsg = '<br />'.JText::sprintf('FILE_RENAMED', $image["name"]);
			}

			if(!JFile::upload($image["tmp_name"],rtrim($uploadPath,DS).DS. $image["name"])){
				if(!move_uploaded_file($image["tmp_name"],	rtrim($uploadPath,DS).DS. $image["name"])){
					$this->message = JText::sprintf( 'FAIL_UPLOAD','<b><i>'.htmlspecialchars($image["tmp_name"], ENT_COMPAT, 'UTF-8').'</i></b>','<b><i>'.htmlspecialchars(rtrim($uploadPath,DS).DS.$image["name"], ENT_COMPAT, 'UTF-8').'</i></b>');
					return false;
				}
			}

			if($imageSize[0]>1000){
				$pictureHelper = acymailing_get('helper.acypict');
				if($pictureHelper->available()){
					$pictureHelper->maxHeight=9999;
					$pictureHelper->maxWidth=700;
					$pictureHelper->destination =  $uploadPath;
					$thumb = $pictureHelper->generateThumbnail(rtrim($uploadPath,DS).DS. $image["name"], $image["name"]);
					$resize = JFile::move($thumb['file'],$uploadPath.DS.$image["name"]);
					if($thumb) $additionalMsg .='<br />'.JText::_( 'IMAGE_RESIZED' );
				}
			}
			$this->message = '<strong>'.JText::_( 'SUCCESS_FILE_UPLOAD' ).'</strong>'.$additionalMsg;
			$this->imageName = $image["name"];
			return true;
		}

		public function createFolder(){
			JRequest::checkToken() or die( 'Invalid Token to create folder' );
			$folderName = str_replace(array('.','-'),array('','_'),strtolower(JRequest::getCmd('subFolderName')));
			if(empty($folderName)){
				$this->browse();
				return false;
			}
			$app = JFactory::getApplication();
			$config = acymailing_config();

			$directoryPath = JRequest::getString('acy_media_browser_files_path').'/'.$folderName;

			$mediaFolders = explode(',', $config->get('mediafolder','media/com_acymailing/upload'));
			$mediaFolders = $this->_generateSpecificFolders($mediaFolders);
			$allowed = false;
			foreach($mediaFolders as $oneMedia){
				if(preg_match('#^'.preg_quote($oneMedia).'[a-z_0-9\-/]*$#i',$directoryPath)){
					$allowed = true;
					break;
				}
			}
			if(!$allowed){
				$app->enqueueMessage('You are not allowed to create this folder','error');
				$this->browse();
				return false;
			}

			$directoryPath = str_replace('/',DS,$directoryPath);

			if(JFolder::exists(ACYMAILING_ROOT.$directoryPath)){
				$app->enqueueMessage(JText::_('FOLDER_ALREADY_EXISTS'),'warning');
				$this->browse();
				return false;
			}
			if(!JFolder::create(ACYMAILING_ROOT.$directoryPath)){
				$app->enqueueMessage(JText::sprintf('WRITABLE_FOLDER',substr(ACYMAILING_ROOT.$directoryPath,0,strrpos(ACYMAILING_ROOT.$directoryPath,DS)),'error'));
				$this->browse();
				return false;
			}
			JRequest::setVar('selected_folder',JRequest::getString('acy_media_browser_files_path').'/'.$folderName);
			$this->browse();
		}
}
