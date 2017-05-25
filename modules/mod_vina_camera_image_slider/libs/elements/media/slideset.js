/*
# ------------------------------------------------------------------------
# TCVN Rotating Image Module for Joomla 2.5
# ------------------------------------------------------------------------
# Copyright(C) 2008-2012 www.Thecoders.vn. All Rights Reserved.
# @license http://www.gnu.org/licenseses/gpl-3.0.html GNU/GPL
# Author: Thecoders.vn
# Websites: http://Thecoders.com
# ------------------------------------------------------------------------
*/

var TCVNSlideSet = (function($){
    var func = {
        init: function(countset){
            this.countset = countset;
        },
        addNewSet: function(){
            var seft = this;
            this.countset = this.countset + 1;
            $('#tcvnSlideSet #tcvnSlideSet-container')
            .append(
                '<div id="Slide-'+ this.countset+ '" class="tcvnSlideSet-item">' +
                    '<div class="tcvnSlideSet-title">' +
                        '<div class="tcvnSlideSet-handle"><span class="ui-icon ui-icon-arrow-4"></span></div>' +
                        '<div onclick="TCVNSlideSet.toggle(this)">Slide-' + this.countset + '</div>' +
                        '<div class="tcvnSlideSet-title-control">' +
                            '<a class="ui-state-default ui-corner-all tcvnSlideSet-button" href="javascript:void(0)" onclick="TCVNSlideSet.removeItem(this)" title="Remove"><span class="ui-icon ui-icon-close"></span></a>' +
                        '</div>' +
                    '</div>' +
                    '<div>' +
                        '<div class="tcvnSlideSet-inputs">' +
                            '<div class="tcvnSlideSet-maininfo">' +
                                '<div class="tcvnSlideSet-input">' +
                                    '<label>Slide Name</label>' +
                                    '<input  class="tcvnSlideSet-name" value="Slide item '+ this.countset +'"/>' +
                                '</div>' +
                                '<div class="clr"></div>' +
                                '<div class="tcvnSlideSet-input">' +
                                    '<label>Image Url</label>' +
                                    '<input class="tcvnSlideSet-img"/>' +
                                '</div>' +
                                '<div class="clr"></div>' +
                                '<div class="tcvnSlideSet-input">' +
                                    '<label>Image Caption</label>' +
                                    '<textarea class="tcvnSlideSet-text" style="width:99%; height:100px">' +
                                        '<div class="camera_caption moveFromTop"> \n' +
                                        '<h3>Display caption anywhere!</h3>\n' +
										'<p>The text of your caption.</p>\n' +
										'</div>' +
									'</textarea>' +
                                '</div>' +
                                '<div class="clr"></div>' +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                '</div>' 
            );
            $('#Slide-'+this.countset).find(".tcvnSlideSet-easing").chosen();
        },
        toggle: function(el){
            $(el).parent().next().slideToggle(200);
            $(el).parent().next().find(".tcvnSlideSet-easing").chosen();

        },
        generateSlideSetValue: function(){
            var data = {};
            data['src'] = $('select#tcvnSlideSet-select-source').val();
            data['list'] = new Array();
            $.each($('#tcvnSlideSet-container .tcvnSlideSet-item'), function(i, item){
                data['list'][i] = {};
                data['list'][i].name = $(item).find('input.tcvnSlideSet-name').val();
                data['list'][i].img = $(item).find('input.tcvnSlideSet-img').val();
                data['list'][i].text = $(item).find('textarea.tcvnSlideSet-text').val();
                data['list'][i].easing = $(item).find('select.tcvnSlideSet-easing').val();
            });

            data['dir'] = {};
            data['dir'].path = $('input#tcvnSlideSet-path').val();
            data['dir'].ext = $('input#tcvnSlideSet-ext').val();

            data['art'] = {};
            data['art'].src = $('#tcvnSlideSet-select-src-art').val();
            data['art'].art = $('#tcvnSlideSet-select-art').val();
            data['art'].cat = $('#tcvnSlideSet-select-cat').val();
            data['art'].sort = $('#tcvnSlideSet-select-sort').val();
            data['art'].dir = $('#tcvnSlideSet-select-sort-dir').val();
            data['art'].sort = $('#tcvnSlideSet-select-sort').val();
            data['art'].showtitle = $('#tcvnSlideSet-select-showtitle').val();
            data['art'].showintro = $('#tcvnSlideSet-select-showintro').val();
            data['art'].intro_chars = $('#tcvnSlideSet-intro_chars').val();
            data['art'].intro_endchar = $('#tcvnSlideSet-intro_endchar').val();
            data['art'].showreadmore = $('#tcvnSlideSet-select-showreadmore').val();
            data['art'].readmore_text = $('#tcvnSlideSet-readmore_text').val();
            $('#jform_params_slides').val(JSON.stringify(data));
        },
        removeItem: function(el){
            $(el).parent().parent().parent().stop(true,true).fadeOut('200', function(){$(this).remove()});
        },

        getEasingOption: function(){
            return  '<option value="jswing">jswing</option>' +
                '<option value="def">def</option>' +
                '<option value="easeInQuad">easeInQuad</option>' +
                '<option value="easeOutQuad">easeOutQuad</option>' +
                '<option value="easeInOutQuad">easeInOutQuad</option>' +
                '<option value="easeInCubic">easeInCubic</option>' +
                '<option value="easeOutCubic">easeOutCubic</option>' +
                '<option value="easeInOutCubic">easeInOutCubic</option>' +
                '<option value="easeInQuart">easeInQuart</option>' +
                '<option value="easeOutQuart">easeOutQuart</option>' +
                '<option value="easeInOutQuart">easeInOutQuart</option>' +
                '<option value="easeInQuint">easeInQuint</option>' +
                '<option value="easeOutQuint" selected>easeOutQuint</option>' +
                '<option value="easeInOutQuint">easeInOutQuint</option>' +
                '<option value="easeInSine">easeInSine</option>' +
                '<option value="easeOutSine">easeOutSine</option>' +
                '<option value="easeInOutSine">easeInOutSine</option>' +
                '<option value="easeInExpo">easeInExpo</option>' +
                '<option value="easeOutExpo">easeOutExpo</option>' +
                '<option value="easeInOutExpo">easeInOutExpo</option>' +
                '<option value="easeInCirc">easeInCirc</option>' +
                '<option value="easeOutCirc">easeOutCirc</option>' +
                '<option value="easeInOutCirc">easeInOutCirc</option>' +
                '<option value="easeInElastic">easeInElastic</option>' +
                '<option value="easeOutElastic">easeOutElastic</option>' +
                '<option value="easeInOutElastic">easeInOutElastic</option>' +
                '<option value="easeInBack">easeInBack</option>' +
                '<option value="easeOutBack">easeOutBack</option>' +
                '<option value="easeInOutBack">easeInOutBack</option>' +
                '<option value="easeInBounce">easeInBounce</option>' +
                '<option value="easeOutBounce">easeOutBounce</option>' +
                '<option value="easeInOutBounce">easeInOutBounce</option>';
        }
    }   
                	
   	return func;
                	
})(jQuery);