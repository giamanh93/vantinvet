/* @copyright Copyright (C) 2008 - 2014 breakdesigns.net . All rights reserved.|* @license GNU General Public License version 2 or later;*/
window.onpopstate=function(e){if(e.state!=null)location.href=document.location};var customFilters={eventsAssigned:new Array,uriLocationState:{page:"Results"},counterHist:0,assignEvents:function(e){if(this.eventsAssigned[e]==false||this.eventsAssigned[e]==null){if(customFiltersProp[e].results_trigger=="btn"||customFiltersProp[e].results_loading_mode=="ajax"){document.id("cf_wrapp_all_"+e).addEvent("click:relay(a)",function(t){t.stop();if(customFiltersProp[e].category_flt_parent_link==false){if(this.hasClass("cf_parentOpt"))return false}var n=this.get("href");customFilters.listen(t,this,e,n)});document.id("cf_wrapp_all_"+e).addEvent("click:relay(input[type=checkbox],input[type=radio])",function(t){var n="";var r=document.id(this.get("id")+"_a");if(r)var n=r.get("href");customFilters.listen(t,this,e,n)});document.id("cf_wrapp_all_"+e).addEvent("change:relay(select[class=cf_flt],input[type=text][class!=cf_smart_search],input[type=search][class!=cf_smart_search])",function(t){t.stop();customFilters.listen(t,this,e)})}if(customFiltersProp[e].results_loading_mode=="ajax"&&customFiltersProp[e].results_trigger=="btn"){document.id("cf_wrapp_all_"+e).addEvent("click:relay(input[type=submit],button[type=submit])",function(t){t.preventDefault();customFilters.loadResults(e)})}document.id("cf_wrapp_all_"+e).addEvent("click:relay(button[class=cf_search_button btn])",function(t){t.stop();var n="";var r="";var i="";var s="";var o=this.getProperty("id");var u=o.substr(0,o.indexOf("_button"));var a=document.id(u+"_url").value;var n=a;var f=a.indexOf("?");var l=document.id(u+"_0");var c=document.id(u+"_1");if(l&&c){var h=l.value;var p=c.value;var d=l.name;var v=c.name}else{var h=document.id(u+"_0").value;d=document.id(u+"_0").name}if(f!=-1)var m="&";else var m="?";if(h){r=d+"="+h}if(p){i=v+"="+p}if(r&&!i)s+=m+r;else if(!r&&i)s+=m+i;else s+=m+r+"&"+i;if(s)var g=a+s;if(g){if(customFiltersProp[e].results_loading_mode=="ajax")customFilters.listen(t,this,e,g);else window.top.location.href=g}});this.eventsAssigned[e]=true}},listen:function(e,t,n,r){if(!n)return;if(customFiltersProp[n].results_loading_mode=="ajax"&&customFiltersProp[n].results_trigger!="btn"){if(typeof customFiltersProp[n].mod_type!="undefined"&&customFiltersProp[n].mod_type=="filtering"){query_value="";var i=document.id("q_"+n+"_0");if(i){query_value=i.value;if(typeof t.id!="undefined"&&t.id=="q_"+n+"_clear")query_value="";this.updateSearchModules(query_value)}}this.loadResults(n,r)}if(customFiltersProp[n].loadOtherFilteringModules){query_value="";var i=document.id("cf-searchmod-input_"+n);if(typeof i!="undefined"){query_value=i.value;var s=this.getFilteringModules();for(var o=0;o<s.length;o++){this.updateFilteringModules(s[o],query_value);this.loadModule(e,t,s[o],r)}}}if(customFiltersProp[n].loadModule)this.loadModule(e,t,n,r)},getFilteringModules:function(){var e=$$(".cf_wrapp_all");var t=new Array;for(var n=0;n<e.length;n++){var r=e[n].id;if(r)parseInt(t.push(r.substring(13)))}return t},updateFilteringModules:function(e,t){var n=document.id("cf_form_"+e);if(n!=null){n.getElement("input[name=q]").value=t}},updateSearchModules:function(e){var t=$$(".cf-form-search");for(var n=0;n<t.length;n++){t[n].getElement("input[name=q]").value=e}},loadModule:function(e,t,n,r){var i=document.id("cf_form_"+n);var s=document.id("cf_wrapp_all_"+n);var o=document.id("cf_ajax_loader_"+n);var u=customFiltersProp[n].use_ajax_spinner;var a="";if(u==true&&typeof e!="undefined"){var f=i.getPosition();if(typeof e.page=="undefined")var l=e.pageY;else var l=e.page.y;var a=l-f.y}if(r){var c=new URI(r);c.setData("view","module");c.setData("tmpl","component");c.setData("format","raw");c.setData("module_id",n);var h=(new Request.HTML({url:c,noCache:true,onRequest:function(){if(u==true){var e=s.getSize();o.addClass("cf_ajax_loader");if(a!="undefined")o.setStyle("background-position","center "+a+"px");o.setStyle("height",e.y+"px");o.setStyle("width",e.x+"px")}},onComplete:function(){if(t.getProperty("class")=="cf_resetAll_link"){if(s.getTop()<window.scrollY){var e=(new Fx.Scroll(window)).toElement(s)}}},method:"post",update:s})).post()}else{var p=new Form.Request(i,s,{extraData:{view:"module",tmpl:"component",format:"raw",module_id:n,Itemid:""},onSend:function(){if(u==true){var e=s.getSize();o.addClass("cf_ajax_loader");if(a!="undefined")o.setStyle("background-position","center "+a+"px");o.setStyle("height",e.y+"px");o.setStyle("width",e.x+"px")}}});p.send()}},loadResults:function(module_id,url){var cfForm=document.id("cf_form_"+module_id);var targetSelector=customFiltersProp[module_id].results_wrapper;var ajaxOverlay=document.id("cf_res_ajax_loader");var target=document.id(targetSelector);var baseURL=customFiltersProp[module_id].base_url+"index.php?";if(url)var uriObj=new URI(url);else{baseURL=cfForm.action;if(baseURL.indexOf("?")==-1)baseURL+="?";else baseURL+="&";var uriObj=new URI(baseURL)}var request=new Request.HTML({url:uriObj,link:"cancel",onRequest:function(){if(customFiltersProp[module_id].use_results_ajax_spinner){var e=target.getSize();ajaxOverlay.setStyle("display","block");ajaxOverlay.setStyle("height",e.y+"px");ajaxOverlay.setStyle("width",e.x+"px")}},onSuccess:function(responseTree,responseElements,responseHTML,responseJavaScript){ajaxOverlay.setStyle("display","none");var resultsElements=responseElements.filter("#"+targetSelector);if(resultsElements){target.innerHTML=resultsElements[0].innerHTML;eval(responseJavaScript);if(typeof Virtuemart!="undefined"){Virtuemart.product(jQuery("form.product"))}}if(!url){if(document.id("cf_apply_button_"+module_id)!=null){document.id("cf_apply_button_"+module_id).blur();if(target.getTop()<window.scrollY){var myFX=(new Fx.Scroll(window)).toElement(target)}}}},onCancel:function(){},onFailure:function(e){ajaxOverlay.setStyle("display","none")}});if(url){request.post();customFilters.setWindowState(url)}else{var myUrl=cfForm.toQueryString();myUrl=myUrl.cleanQueryString();myUrl=baseURL+myUrl;customFilters.setWindowState(myUrl);request.post(cfForm)}},setWindowState:function(e){this.counterHist++;var t=window.history.state;if(window.history.pushState&&window.history.replaceState){window.history.pushState({page:this.counterHist},"Search Results",e)}},addEventTree:function(e){var t="virtuemart_category_id";if(customFiltersProp[e].parent_link==false){document.id("cf_wrapp_all_"+e).addEvent("click:relay(.cf_parentOpt)",function(n,r){n.stop();var i=r.getProperty("class");var s=i.split(" ");var o=s.length;var u;if(r.hasClass("cf_unexpand")){r.removeClass("cf_unexpand");r.addClass("cf_expand")}else if(r.hasClass("cf_expand")){r.removeClass("cf_expand");r.addClass("cf_unexpand")}for(var a=0;a<o;a++){if(s[a].indexOf("tree")>=0)u=s[a]}var f=r.getProperty("id");f=parseInt(f.slice(f.indexOf("_elid")+5));if(u){u+="-"+f;var l=document.id("cf_list_"+t+"_"+e).getElements(".li-"+u);if(l[0].hasClass("cf_invisible"))var c=false;else var c=true;for(var a=0;a<l.length;a++){if(c==false){l[a].removeClass("cf_invisible")}else{var h=document.id("cf_list_"+t+"_"+e).getElements("li[class*="+u+"]");for(var p=0;p<h.length;p++){h[p].addClass("cf_invisible");if(h[p].hasClass("cf_parentLi")){h[p].getElement("a").removeClass("cf_expand");h[p].getElement("a").addClass("cf_unexpand")}}}}}customFilters.setWrapperHeight(t,e);return false})}},setWrapperHeight:function(e,t){var n=document.id("cf_wrapper_inner_"+e+"_"+t);var r=n.getParent();r.setStyle("height",n.offsetHeight+"px")},addEventsRangeInputs:function(e,t){var n=e+"_"+t;var r=document.id(n+"_0");var i=document.id(n+"_1");if(r&&i){customFilters.validateRangeFlt(t,e);var s=document.id(n+"_slider");r.addEvent("keyup",function(n){var r=customFilters.validateRangeFlt(t,e);if(s!=null)customFilters.setSliderValues(t,e,r,"min")});i.addEvent("keyup",function(n){var r=customFilters.validateRangeFlt(t,e);if(s!=null)customFilters.setSliderValues(t,e,r,"max")});if(customFiltersProp[t].results_trigger=="btn"){r.addEvent("change",function(n){var i=customFilters.validateRangeFlt(t,e);if(i)customFilters.listen(r,t)});i.addEvent("change",function(n){var r=customFilters.validateRangeFlt(t,e);if(r)customFilters.listen(i,t)})}}},createToggle:function(e,t){var n=new Fx.Slide("cf_wrapper_inner_"+e,{duration:200,wrapper:false,resetHeight:false});var r=Cookie.read(e)?Cookie.read(e):t;n[r]();customFilters.setHeaderClass(e,r);document.id("cfhead_"+e).addEvent("click",function(t){t.stop();var r=this;var i=n;i.toggle();if(i.open)mystate="hide";else mystate="show";customFilters.setHeaderClass(e,mystate);var s=Cookie.write(e,mystate)})},setHeaderClass:function(e,t){var n="headexpand_"+e;var r=document.id(n);if(t=="hide"){r.removeClass("headexpand_show");r.addClass("headexpand_hide")}else{r.removeClass("headexpand_hide");r.addClass("headexpand_show")}},validateRangeFlt:function(e,t){var t=t+"_"+e;var n=document.id(t+"_0");var r=document.id(t+"_1");if(customFiltersProp[e].results_trigger!="btn")var i=document.id(t+"_button");var s=n.value.replace(",",".");var o=s.match(/^[+-]?\d+(\.\d*)?$/);var u=r.value.replace(",",".");var a=u.match(/^[+-]?\d+(\.\d*)?$/);if(o&&u.length==0||a&&s.length==0||o&&a){if(s.length>0&&u.length>0&&parseFloat(s)>parseFloat(u)){if(i)i.setProperty("disabled","disabled");this.displayMsgPriceFlt("",t);return false}else{if(i)i.removeProperty("disabled");this.displayMsgPriceFlt("",t);var f=new Array(s,u);return f}}else{if(i)i.setProperty("disabled","disabled");if(u.length>0||s.length>0){this.displayMsgPriceFlt(Joomla.JText._("MOD_CF_FILTERING_INVALID_CHARACTER"),t)}else this.displayMsgPriceFlt("",t)}return false},displayMsgPriceFlt:function(e,t){var n=document.id(t+"_message");if(e){n.setStyle("display","block");n.innerHTML=e}else{n.setStyle("display","none")}},setSliderValues:function(module_id,filter,valid,minOrMax){var flt_key=filter+"_"+module_id;var sliderObj=eval(flt_key+"_sliderObj");if(valid!==false){var min_val=parseInt(valid[0]);if(isNaN(min_val))min_val=parseInt(customFiltersProp[module_id].slider_min_value);var max_val=parseInt(valid[1]);if(isNaN(max_val))max_val=parseInt(customFiltersProp[module_id].slider_max_value);sliderObj.setMin(min_val);sliderObj.setMax(max_val)}else{if(minOrMax=="min")sliderObj.setMin(parseInt(customFiltersProp[module_id].slider_min_value));else if(minOrMax=="max")sliderObj.setMax(parseInt(customFiltersProp[module_id].slider_max_value))}}};var CfElementFilter=new Class({Implements:[Options,Events],options:{module_id:null,isexpanable_tree:false,filter_key:"",cache:true,caseSensitive:false,ignoreKeys:[13,27,32,37,38,39,40],matchAnywhere:true,optionClass:".cf_option",property:"text",trigger:"keyup",onHide:"",onComplete:"",onStart:function(){this.elements.addClass("cf_hide")},onShow:function(e){e.removeClass("cf_hide")},onMatchText:function(e){var t=this.observeElement.value;var n=this.options.caseSensitive?"":"i";var r=new RegExp(t,n);var i=e.getElements(this.options.optionClass);var s=i[0].get(this.options.property);var o=s.toLowerCase();var u=t.toLowerCase();var a=o.indexOf(u);var f=s.substr(a,t.length);var l=s.replace(r,'<span class="cf_match">'+f+"</span>");i[0].set("html",l)}},initialize:function(e,t,n){this.setOptions(n);this.observeElement=document.id(e);this.elements=$$(t);this.matches=this.elements;this.misses=[];this.listen()},listen:function(){this.observeElement.addEvent(this.options.trigger,function(e){if(this.observeElement.value.length){if(!this.options.ignoreKeys.contains(e.code)){this.fireEvent("start");this.findMatches(this.options.cache?this.matches:this.elements);this.fireEvent("complete")}}else{this.elements.removeClass("cf_hide");this.clearHtmlFromText(this.elements);if(this.options.isexpanable_tree)customFilters.setWrapperHeight(this.options.filter_key,this.options.module_id);this.findMatches(this.elements,false);var t=this.elements.getElements(".cf_invisible");t.each(function(e){e.setStyle("display","")})}}.bind(this))},findMatches:function(e,t){var n=this.observeElement.value;var r=this.options.matchAnywhere?n:"^"+n;var i=this.options.caseSensitive?"":"i";var s=new RegExp(r,i);var o=[];e.each(function(e){var n=t==undefined?s.test(e.get(this.options.property)):t;var r=e.getProperty("class").contains("cf_invisible"," ");if(n){if(r){e.setStyle("display","block")}this.fireEvent("matchText",[e]);this.fireEvent("show",[e])}else{if(r){e.setStyle("display","")}if(e.retrieve("showing")){this.fireEvent("hide",[e])}e.store("showing",false)}return true;return false}.bind(this));return o},clearHtmlFromText:function(e){e.each(function(e){var t=e.getElements(this.options.optionClass);var n=t[0].get(this.options.property);t[0].set("html",n)}.bind(this))}})