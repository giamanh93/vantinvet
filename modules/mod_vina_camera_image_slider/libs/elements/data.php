<?php
/*
# ------------------------------------------------------------------------
# Vina Camera Image Slider for Joomla 3
# ------------------------------------------------------------------------
# Copyright(C) 2014 www.VinaGecko.com. All Rights Reserved.
# @license http://www.gnu.org/licenseses/gpl-3.0.html GNU/GPL
# Author: VinaGecko.com
# Websites: http://vinagecko.com
# Forum:    http://vinagecko.com/forum/
# ------------------------------------------------------------------------
*/

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.form.formfield');

class JFormFieldData extends JFormField
{
    protected $type = 'data';
    
    protected function getLabel()
    {   
        return false;
    }

    protected function getInput()
    {
        $doc = JFactory::getDocument();
        $j30 = 1;
		
        if(version_compare(JVERSION,'3.0') < 0)
		{
            $j30 = 0;
            $doc->addScript(JURI::root().'modules/mod_vina_camera_image_slider/assets/js/jquery-1.8.3.min.js', 'text/javascript');
            $doc->addScript(JURI::root().'modules/mod_vina_camera_image_slider/assets/js/chosen.jquery.min.js', 'text/javascript');
            $doc->addStyleSheet(JURI::root().'modules/mod_vina_camera_image_slider/assets/css/chosen.css', 'text/css');
        }

        $doc->addScript('http://ajax.googleapis.com/ajax/libs/jqueryui/1.9.1/jquery-ui.min.js', 'text/javascript');
        $doc->addScript(JURI::root().'modules/mod_vina_camera_image_slider/libs/elements/media/slideset.js', 'text/javascript');
        $doc->addScript(JURI::root().'modules/mod_vina_camera_image_slider/libs/elements/media/json2.js', 'text/javascript');

        $doc->addStyleSheet('http://ajax.googleapis.com/ajax/libs/jqueryui/1.9.0/themes/cupertino/jquery-ui.css', 'text/css');
        $doc->addStyleSheet(JURI::root().'modules/mod_vina_camera_image_slider/libs/elements/media/slideset.css', 'text/css');

        if($this->value){
            $slide = json_decode($this->value);
        }else{
            $default = new JObject();
            $default->src = 'list';
            $default->list = array();
			$captions = array(
			'<div class="camera_caption fadeFromRight" style="left:0px; top: 20%; width:430px;"> 
	<h3>Free Joomla! 3 Extensions by VinaGecko.com</h3>
</div>', 
			'<div class="camera_caption moveFromLeft" style="left:0px; top: 65%; width:380px;"> 
	<h3>Vina Camera Image Slider</h3>
	<p>The best responsive image slideshow module for Joomla 3!</p>
</div>',
			'<div class="camera_caption fadeFromLeft" style="left:0px; top: 50%; width:380px;"> 
	<h3>Responsive Image Slideshow</h3>
	<p>Respond perfectly for all device resolutions like mobiles, tablets and the browser size of your site visitor.</p>
</div>',
			'<div class="camera_caption moveFromBottom" style="left:0px; top: 0; width:380px;"> 
	<p>Vina Camera Image Slider support unlimited image. Very easy to add new, remove and order image.</p>
</div>',
			'<div class="camera_caption moveFromLeft" style="right:0px; top:45%; width:380px;"> 
	<h3>Thumbnail Images Navigation</h3>
	<p>Vina Camera Image Slider will auto create thumbnail images and make it work as a navigation.</p>
</div>',
			'<div class="camera_caption moveFromTop"> 
	<h3>Display caption anywhere!</h3>
	<p>You can display caption anywhere! Just need change the left, top, right or bottom.</p>
</div>');
            for($i=0;$i<6;$i++){
                $default->list[$i]->name = 'Slide item '.($i+1);
                $default->list[$i]->img = 'modules/mod_vina_camera_image_slider/samples/image-'.($i+1).'.jpg';
                $default->list[$i]->text = $captions[$i];
                $default->list[$i]->easing = 'easeOutQuint';
            }
            $slide = $default;
        }
        ob_start();
        ?>
    <style type="text/css">
        <?php if($j30){?>
            .tcvnSlideSet-easing{
                 width: 200px;
             }
        <?php }?>
    </style>
    <div class="clr"></div>
    <div id="tcvnSlideSet">
        <div id="tcvnSlideSet-source">
            <div style="float: left;line-height: 24px;">Get source from</div>
            <div style="float: right">
                <select id="tcvnSlideSet-select-source" style="width: 200%">
                    <option value="list">From List</option>
                </select>
            </div>
            <div class="clr"></div>
        </div>
        <div class="clr"></div>
        <div id="tcvnSlideSet-dir">
            <div class="tcvnSlideSet-item">
                <div class="tcvnSlideSet-input">
                    <label>Path</label>
                    <input id="tcvnSlideSet-path" value="<?php if(isset($slide->dir->path)) echo $slide->dir->path; else echo 'modules/mod_vina_camera_image_slider/samples/'?>"/>
                </div>
                <div class="tcvnSlideSet-input" title="Ex:.jpg,.png,.gif.">
                    <label>Allow Image Extension</label>
                    <input id="tcvnSlideSet-ext" value="<?php if(isset($slide->dir->ext)) echo $slide->dir->ext; else echo '.jpg,.png'?>"/>
                </div>
            </div>
        </div>
        <div class="clr"></div>
        <div id="tcvnSlideSet-art">
            <div class="tcvnSlideSet-item">
                <div class="tcvnSlideSet-input">
                    <label>From</label>
                    <select id="tcvnSlideSet-select-src-art" class="chzn-select">
                        <option value="art">Articles</option>
                        <option value="cat" <?php if(isset($slide->art->src) && $slide->art->src=='cat') echo 'selected';?>>Categories</option>
                    </select>
                    <div class="clr"></div>
                </div>
                <div class="tcvnSlideSet-input">
                    <label>Articles</label>
                    <select id="tcvnSlideSet-select-art" data-placeholder="Articles" class="chzn-select" multiple="multiple">
                        <?php echo $this->getArticles($slide->art->art);?>
                    </select>
                    <div class="clr"></div>
                </div>
                <div class="tcvnSlideSet-input">
                    <label>Categories</label>
                    <select id="tcvnSlideSet-select-cat" data-placeholder="Categories" class="chzn-select" multiple="multiple">
                        <?php echo $this->getCategories($slide->art->cat);?>
                    </select>
                    <div class="clr"></div>
                </div>
                <div class="tcvnSlideSet-input">
                    <label>Sort By</label>
                    <select id="tcvnSlideSet-select-sort" class="chzn-select">
                        <option value="id">ID</option>
                        <option value="created" <?php if($slide->art->sort=='created') echo 'selected';?>>Created date</option>
                        <option value="modified" <?php if($slide->art->sort=='modified') echo 'selected';?>>Modified date</option>
                        <option value="ordering" <?php if($slide->art->sort=='ordering') echo 'selected';?>>Ordering</option>
                        <option value="hits" <?php if($slide->art->sort=='hits') echo 'selected';?>>Hits</option>
                    </select>
                    <div class="clr"></div>
                </div>
                <div class="tcvnSlideSet-input">
                    <label>Sort Direction</label>
                    <select id="tcvnSlideSet-select-sort-dir" class="chzn-select">
                        <option value="asc">Ascending</option>
                        <option value="desc" <?php if($slide->art->dir=='desc') echo 'selected';?>>Descending</option>
                    </select>
                    <div class="clr"></div>
                </div>
                <div class="clr"></div>
                <div class="tcvnSlideSet-input">
                    <label>Show Article Title</label>
                    <select id="tcvnSlideSet-select-showtitle" class="chzn-select">
                        <option value="1">Show</option>
                        <option value="0" <?php if($slide->art->showtitle==0) echo 'selected';?>>Hide</option>
                    </select>
                    <div class="clr"></div>
                </div>
                <div class="clr"></div>
                <div class="tcvnSlideSet-input">
                    <label>Show Introtext</label>
                    <select id="tcvnSlideSet-select-showintro" class="chzn-select">
                        <option value="1">Show</option>
                        <option value="0" <?php if($slide->art->showintro==0) echo 'selected';?>>Hide</option>
                    </select>
                    <div class="clr"></div>
                </div>
                <div class="clr"></div>
                <div class="tcvnSlideSet-input">
                    <label>Introtext Limit Chars</label>
                    <input id="tcvnSlideSet-intro_chars" value="<?php if($slide->art->intro_chars!='') echo $slide->art->intro_chars; else echo '100'?>"/>
                </div>
                <div class="clr"></div>
                <div class="tcvnSlideSet-input">
                    <label>Introtext End Char</label>
                    <input id="tcvnSlideSet-intro_endchar" value="<?php if($slide->art->intro_endchar!='') echo $slide->art->intro_endchar;?>"/>
                </div>
                <div class="clr"></div>
                <div class="tcvnSlideSet-input">
                    <label>Show Readmore</label>
                    <select id="tcvnSlideSet-select-showreadmore" class="chzn-select">
                        <option value="1">Show</option>
                        <option value="0" <?php if($slide->art->showreadmore==0) echo 'selected';?>>Hide</option>
                    </select>
                    <div class="clr"></div>
                </div>
                <div class="clr"></div>
                <div class="tcvnSlideSet-input">
                    <label>Readmore Text</label>
                    <input id="tcvnSlideSet-readmore_text" value="<?php if($slide->art->readmore_text!='') echo $slide->art->readmore_text; else echo 'Read More &rsaquo;'?>"/>
                </div>
                <div class="clr"></div>
            </div>
        </div>
        <div class="clr"></div>
        <div id="tcvnSlideSet-list">
            <div id="tcvnSlideSet-container">
                <?php
                if($slide->list){
                    for($i=0;$i<count($slide->list);$i++){
                        $set = $slide->list[$i];
                        ?>
                        <div id="<?php echo 'Slide-'.($i+1);?>" class="tcvnSlideSet-item">

                            <div class="tcvnSlideSet-title">
                                <div class="tcvnSlideSet-handle"><span class="ui-icon ui-icon-arrow-4"></span></div>
                                <div onclick="TCVNSlideSet.toggle(this)"><?php echo $set->name;?></div>
                                <div class="tcvnSlideSet-title-control">
                                    <a class="ui-state-default ui-corner-all tcvnSlideSet-button" href="javascript:void(0)" onclick="TCVNSlideSet.removeItem(this)" title="Remove"><span class="ui-icon ui-icon-close"></span></a>
                                </div>
                            </div>
                            <div style="display: none;">
                                <div class="tcvnSlideSet-inputs">
                                    <div class="tcvnSlideSet-maininfo">
                                        <div class="tcvnSlideSet-input">
                                            <label>Set Name</label>
                                            <input class="tcvnSlideSet-name" value="<?php echo $set->name;?>"/>
                                        </div>
                                        <div class="clr"></div>
                                        <div class="tcvnSlideSet-input">
                                            <label>Image Url</label>
                                            <input class="tcvnSlideSet-img" value="<?php echo $set->img;?>"/>
                                        </div>
                                        <div class="clr"></div>
                                        <div class="tcvnSlideSet-input">
                                            <label>Image Caption</label>
                                            <textarea class="tcvnSlideSet-text"><?php echo $set->text;?></textarea>
                                        </div>
                                        <div class="clr"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                }
                ?>
                <div class="clr"></div>
            </div>
            <div class="clr"></div>
            <div class="tcvnSlideSet-control">
                <a class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-icon-primary" href="javascript:void(0)" onclick="TCVNSlideSet.addNewSet()">
                    <span class="ui-button-icon-primary ui-icon ui-icon-plusthick"></span>
                    <span class="ui-button-text tcvnSlideSet-buttontext">Add</span>
                </a>
            </div>
        </div>
        <div class="clr"></div>
    </div>
    <script>
        jQuery(function($){
            $("#tcvnSlideSet-select-source").chosen();
            <?php if($j30){?>
                $('.chzn-container-multi').css('width','100%');
                $('.chzn-container-multi .chzn-drop').css('width','99.8%');
            <?php }?>
            $('.chzn-select').chosen();
            TCVNSlideSet.init(<?php if(isset($slide->list)) echo count($slide->list); else echo '0';?>);
            $('#tcvnSlideSet-select-source').change(function(){
                var type = $(this).val();
                if(type=='art'){
                    $('#tcvnSlideSet-list').hide();
                    $('#tcvnSlideSet-dir').hide();
                    $('#tcvnSlideSet-art').show();
                }else if(type=='dir'){
                    $('#tcvnSlideSet-list').hide();
                    $('#tcvnSlideSet-dir').show();
                    $('#tcvnSlideSet-art').hide();
                }else{
                    $('#tcvnSlideSet-list').show();
                    $('#tcvnSlideSet-dir').hide();
                    $('#tcvnSlideSet-art').hide();
                }
            });

            $('#tcvnSlideSet-select-source').trigger('change');

            Joomla.submitbutton = function(task)
            {
                if (task == 'module.cancel' || document.formvalidator.isValid(document.getElementById('module-form'))) {
                    if(task == 'module.apply' || task == 'module.save') {
                        TCVNSlideSet.generateSlideSetValue();
                    }
                    Joomla.submitform(task, document.getElementById('module-form'));
                    if (self != top) {
                        window.top.setTimeout('window.parent.SqueezeBox.close()', 1000);
                    }
                } else {
                    alert('<?php echo JText::_('JGLOBAL_VALIDATION_FORM_FAILED');?>');
                }
            }

            $('#tcvnSlideSet-container').sortable({ handle: ".tcvnSlideSet-handle" });
        })
    </script>
    <?php

        return ob_get_clean().'<input style="display:none" name="'.$this->name.'" id="'.$this->id.'"/>';
    }

    protected function getEasingOption($value){
        ob_start()?>
        <option value="jswing" <?php if($value == 'jswing') echo 'selected="selected"';?>>jswing</option>
        <option value="def" <?php if($value == 'def') echo 'selected="selected"';?>>def</option>
        <option value="easeInQuad" <?php if($value == 'easeInQuad') echo 'selected="selected"';?>>easeInQuad</option>
        <option value="easeOutQuad" <?php if($value == 'easeOutQuad') echo 'selected="selected"';?>>easeOutQuad</option>
        <option value="easeInOutQuad" <?php if($value == 'easeInOutQuad') echo 'selected="selected"';?>>easeInOutQuad</option>
        <option value="easeInCubic" <?php if($value == 'easeInCubic') echo 'selected="selected"';?>>easeInCubic</option>
        <option value="easeOutCubic" <?php if($value == 'easeOutCubic') echo 'selected="selected"';?>>easeOutCubic</option>
        <option value="easeInOutCubic" <?php if($value == 'easeInOutCubic') echo 'selected="selected"';?>>easeInOutCubic</option>
        <option value="easeInQuart" <?php if($value == 'easeInQuart') echo 'selected="selected"';?>>easeInQuart</option>
        <option value="easeOutQuart" <?php if($value == 'easeOutQuart') echo 'selected="selected"';?>>easeOutQuart</option>
        <option value="easeInOutQuart" <?php if($value == 'easeInOutQuart') echo 'selected="selected"';?>>easeInOutQuart</option>
        <option value="easeInQuint" <?php if($value == 'easeInQuint') echo 'selected="selected"';?>>easeInQuint</option>
        <option value="easeOutQuint" <?php if($value == 'easeOutQuint') echo 'selected="selected"';?>>easeOutQuint</option>
        <option value="easeInOutQuint" <?php if($value == 'easeInOutQuint') echo 'selected="selected"';?>>easeInOutQuint</option>
        <option value="easeInSine" <?php if($value == 'easeInSine') echo 'selected="selected"';?>>easeInSine</option>
        <option value="easeOutSine" <?php if($value == 'easeOutSine') echo 'selected="selected"';?>>easeOutSine</option>
        <option value="easeInOutSine" <?php if($value == 'easeInOutSine') echo 'selected="selected"';?>>easeInOutSine</option>
        <option value="easeInExpo" <?php if($value == 'easeInExpo') echo 'selected="selected"';?>>easeInExpo</option>
        <option value="easeOutExpo" <?php if($value == 'easeOutExpo') echo 'selected="selected"';?>>easeOutExpo</option>
        <option value="easeInOutExpo" <?php if($value == 'easeInOutExpo') echo 'selected="selected"';?>>easeInOutExpo</option>
        <option value="easeInCirc" <?php if($value == 'easeInCirc') echo 'selected="selected"';?>>easeInCirc</option>
        <option value="easeOutCirc" <?php if($value == 'easeOutCirc') echo 'selected="selected"';?>>easeOutCirc</option>
        <option value="easeInOutCirc" <?php if($value == 'easeInOutCirc') echo 'selected="selected"';?>>easeInOutCirc</option>
        <option value="easeInElastic" <?php if($value == 'easeInElastic') echo 'selected="selected"';?>>easeInElastic</option>
        <option value="easeOutElastic" <?php if($value == 'easeOutElastic') echo 'selected="selected"';?>>easeOutElastic</option>
        <option value="easeInOutElastic" <?php if($value == 'easeInOutElastic') echo 'selected="selected"';?>>easeInOutElastic</option>
        <option value="easeInBack" <?php if($value == 'easeInBack') echo 'selected="selected"';?>>easeInBack</option>
        <option value="easeOutBack" <?php if($value == 'easeOutBack') echo 'selected="selected"';?>>easeOutBack</option>
        <option value="easeInOutBack" <?php if($value == 'easeInOutBack') echo 'selected="selected"';?>>easeInOutBack</option>
        <option value="easeInBounce" <?php if($value == 'easeInBounce') echo 'selected="selected"';?>>easeInBounce</option>
        <option value="easeOutBounce" <?php if($value == 'easeOutBounce') echo 'selected="selected"';?>>easeOutBounce</option>
        <option value="easeInOutBounce" <?php if($value == 'easeInOutBounce') echo 'selected="selected"';?>>easeInOutBounce</option>
        <?php
        return ob_get_clean();
    }

    protected function getCategories($values){
        $db = JFactory::getDbo();
        $db->setQuery('SELECT id, title FROM #__categories WHERE extension="com_content" AND published=1 ORDER BY title asc');
        $list = $db->loadObjectList();
        $html = '';
        for($i=0;$i<count($list);$i++){
            if(is_array($values) && in_array($list[$i]->id, $values)){
                $html .= '<option value="'.$list[$i]->id.'" selected>'.$list[$i]->id.'. '.$list[$i]->title.'</option>';
            }else{
                $html .= '<option value="'.$list[$i]->id.'">'.$list[$i]->id.'. '.$list[$i]->title.'</option>';
            }
        }
        return $html;
    }

    protected function getArticles($values){
        $db = JFactory::getDbo();
        $db->setQuery('SELECT id, title FROM #__content  WHERE state=1 ORDER BY title asc');
        $list = $db->loadObjectList();
        $html = '';
        for($i=0;$i<count($list);$i++){
            if(is_array($values) && in_array($list[$i]->id, $values)){
                $html .= '<option value="'.$list[$i]->id.'" selected>'.$list[$i]->id.'. '.$list[$i]->title.'</option>';
            }else{
                $html .= '<option value="'.$list[$i]->id.'">'.$list[$i]->id.'. '.$list[$i]->title.'</option>';
            }
        }
        return $html;
    }
}
