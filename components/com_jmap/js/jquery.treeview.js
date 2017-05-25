(function(e) {
	e.extend(e.fn, {
		swapClass : function(e, t) {
			var n = this.filter("." + e);
			this.filter("." + t).removeClass(t).addClass(e);
			n.removeClass(e).addClass(t);
			return this
		},
		replaceClass : function(e, t) {
			return this.filter("." + e).removeClass(e).addClass(t).end()
		},
		hoverClass : function(t) {
			t = t || "hover";
			return this.hover(function() {
				e(this).addClass(t)
			}, function() {
				e(this).removeClass(t)
			})
		},
		heightToggle : function(e, speed, t) {
			e ? this.animate({
				height : "toggle"
			}, speed, t) : this.each(function() {
				jQuery(this)[jQuery(this).is(":hidden") ? "show" : "hide"]();
				if (t)
					t.apply(this, arguments)
			})
		},
		heightHide : function(e, t) {
			if (e) {
				this.animate({
					height : "hide"
				}, e, t)
			} else {
				this.hide();
				if (t)
					this.each(t)
			}
		},
		prepareBranches : function(e, parentWrappedSet) {
			if (!e.prerendered) {
				this.filter(":last-child:not(ul)").addClass(t.last);

				if(e.collapsed) {
					var branchSelector = '';
					var negativeBranchSelector = new Array();
					jQuery.each(e.contentCollapsed, function(sourceHash, sourceCollapsed){
						if(sourceCollapsed == 2) {
							if(sourceHash.indexOf('com_content') != -1) {
								branchSelector += ":not(ul[data-hash*='" + sourceHash + "'])";
							} else {
								branchSelector += ":not(ul[data-hash='" + sourceHash + "'])";
								negativeBranchSelector.push("ul[data-hash='" + sourceHash + "']");
							}
						}
					});
					if(branchSelector) {
						var filteredUL = parentWrappedSet.filter(branchSelector);
						if(negativeBranchSelector.length) {
							filteredUL.each(function(index, filteredULElement){
								if(!jQuery(filteredULElement).parents(negativeBranchSelector.join()).length) {
									jQuery(filteredULElement).find(">li>ul").hide();			
								}
							});
						} else {
							filteredUL.find(">li>ul").hide();
						}
					} else {
						this.filter(":not(." + t.open + ")").find(">ul").hide();
					}
				}
				
				if(!e.collapsed) {
					var branchSelector = new Array();
					jQuery.each(e.contentCollapsed, function(sourceHash, sourceCollapsed){
						if(sourceCollapsed == 1) {
							if(sourceHash.indexOf('com_content') != -1) {
								branchSelector.push("ul[data-hash*='" + sourceHash + "']");
							} else {
								branchSelector.push("ul[data-hash='" + sourceHash + "']");
							}
						}
					});
					if(branchSelector.length) {
						var filteredUL = parentWrappedSet.filter(branchSelector.join());
						filteredUL.find("li ul").hide();
					}
				}
			}
			return this.filter(":has(>ul)")
		},
		applyClasses : function(n, r) {
			this.filter(":not(.noexpandable):has(>ul):not(:has(>a))").find(">span").off("click.treeview").on("click.treeview", function(t) {
				if (this == t.target)
					r.apply(e(this).next())
			}).add(e("a", this)).hoverClass();
			if (!n.prerendered) {
				this.filter(":not(.noexpandable):has(>ul:hidden)").addClass(t.expandable).replaceClass(t.last, t.lastExpandable);
				this.filter(":not(.noexpandable)").not(":has(>ul:hidden)").addClass(t.collapsable).replaceClass(t.last, t.lastCollapsable);
				var i = this.not(".noexpandable").find("div." + t.hitarea);
				if (!i.length)
					i = this.not(".noexpandable").prepend('<div class="' + t.hitarea + '"/>').find("div." + t.hitarea);
				i.removeClass().addClass(t.hitarea).each(function() {
					var t = "";
					e.each(e(this).parent().attr("class").split(" "), function() {
						t += this + "-hitarea "
					});
					e(this).addClass(t)
				})
			}
			this.find("div." + t.hitarea).off("click.treeview").on("click.treeview", r);
		},
		treeview : function(n) {
			var parentWrappedSet = this;
			function r(n, r) {
				function s(r) {
					return function() {
						i.apply(e("div." + t.hitarea, n).filter(function() {
							return r ? e(this).parent("." + r).length : true
						}));
						return false
					}
				}
				e("a:eq(0)", r).click(s(t.collapsable));
				e("a:eq(1)", r).click(s(t.expandable));
				e("a:eq(2)", r).click(s())
			}
			function i() {
				e(this).parent().find(">.hitarea").swapClass(t.collapsableHitarea, t.expandableHitarea).swapClass(t.lastCollapsableHitarea, t.lastExpandableHitarea).end()
						.swapClass(t.collapsable, t.expandable).swapClass(t.lastCollapsable, t.lastExpandable).find(">ul").heightToggle(n.animated, n.animateSpeed, n.toggle);
				if (n.unique) {
					e(this).parent().siblings().find(">.hitarea").replaceClass(t.collapsableHitarea, t.expandableHitarea).replaceClass(t.lastCollapsableHitarea, t.lastExpandableHitarea).end()
							.replaceClass(t.collapsable, t.expandable).replaceClass(t.lastCollapsable, t.lastExpandable).find(">ul").heightHide(n.animated, n.toggle)
				}
			}
			function s() {
				function t(e) {
					return e ? 1 : 0
				}
				var r = [];
				a.each(function(t, n) {
					r[t] = e(n).is(":has(>ul:visible)") ? 1 : 0
				});
				e.cookie(n.cookieId, r.join(""), n.cookieOptions)
			}
			function o() {
				var t = e.cookie(n.cookieId);
				if (t) {
					var r = t.split("");
					a.each(function(t, n) {
						e(n).find(">ul")[parseInt(r[t]) ? "show" : "hide"]()
					})
				}
			}
			n = e.extend({
				cookieId : "treeview"
			}, n);
			if (n.toggle) {
				var u = n.toggle;
				n.toggle = function() {
					return u.apply(e(this).parent()[0], arguments)
				}
			}
			this.data("toggler", i);
			this.addClass("treeview");
			var a = this.find("li").prepareBranches(n, parentWrappedSet);
			switch (n.persist) {
			case "cookie":
				var f = n.toggle;
				n.toggle = function() {
					s();
					if (f) {
						f.apply(this, arguments)
					}
				};
				o();
				break;
			case "location":
				var l = this.find("a").filter(function() {
					return this.href.toLowerCase() == location.href.toLowerCase()
				});
				if (l.length) {
					var c = l.addClass("selected").parents("ul, li").add(l.next()).show();
					if (n.prerendered) {
						c.filter("li").swapClass(t.collapsable, t.expandable).swapClass(t.lastCollapsable, t.lastExpandable).find(">.hitarea").swapClass(t.collapsableHitarea, t.expandableHitarea)
									  .swapClass(t.lastCollapsableHitarea, t.lastExpandableHitarea)
					}
				}
				break;
			case "none":
				break
			}
			a.applyClasses(n, i);
			if (n.control) {
				r(this, n.control);
				e(n.control).show()
			}
			var h = e("li>span.folder");
			e.each(h, function(t, n) {
				var r = e(n).text();
				var i = r.replace(/^\s+|\s+$/g, "");
				if (i.length == 0) {
					e(n).parent("li").css("list-style-type", "none");
					if (e(n).css("background-image") === "none") {
						e(n).hide()
					}
				}
			});
			return this
		}
	});
	e.treeview = {};
	var t = e.treeview.classes = {
		open : "open",
		closed : "closed",
		expandable : "expandable",
		expandableHitarea : "expandable-hitarea",
		lastExpandableHitarea : "lastExpandable-hitarea",
		collapsable : "collapsable",
		collapsableHitarea : "collapsable-hitarea",
		lastCollapsableHitarea : "lastCollapsable-hitarea",
		lastCollapsable : "lastCollapsable",
		lastExpandable : "lastExpandable",
		last : "last",
		hitarea : "hitarea"
	}
})(jQuery);

jQuery(function($) {
	if(typeof(jmapExpandContentTree) === 'undefined') { jmapExpandContentTree = '{}'; }
	var defaultOptions = {
			persist : jmapExpandLocation,
			collapsed : !jmapExpandAllTree,
			contentCollapsed : JSON.parse(jmapExpandContentTree),
			unique : false,
			animated: jmapAnimated,
			animateSpeed: jmapAnimateSpeed
		};
	$("ul.jmap_filetree").treeview(defaultOptions);
	
	$(function(){
		var recursiveBackground = function(parentElement) {
			var parentBgColor = $(parentElement).css('background-color');
			if((parentBgColor == 'rgba(0, 0, 0, 0)' || parentBgColor == 'transparent') && parentElement.length) {
				recursiveBackground(parentElement.parent());
			} else {
				$('#jmap_sitemap div.jmapcolumn>ul>li>div.lastCollapsable-hitarea').css('background-color', parentBgColor);
				$('#jmap_sitemap div.jmapcolumn>ul>li>div.lastExpandable-hitarea').css('background-color', parentBgColor);
				$('#jmap_sitemap div.jmapcolumn>ul.treeview>li>ul:last-child>li:last-child li.last').addClass('jmap_last_before');
				$('#jmap_sitemap div.jmapcolumn>ul.treeview>li>ul:last-child>li:last-child.last').addClass('jmap_last_before');
				$('#jmap_sitemap div.jmapcolumn>ul.treeview>li>ul:last-child>li.expandable:last-child').addClass('jmap_last_before');
				$('#jmap_sitemap div.jmapcolumn>ul.treeview>li>ul>li.expandable:last-child li.expandable').addClass('jmap_last_before');

				$("<style type='text/css'>li.jmap_last_before.expandable:before,li.jmap_last_before.last:before{ background-color:" + parentBgColor +";</style>").appendTo("head");
			}
		}
		if($('#jmap_sitemap').data('template') == 'mindmap') {
			recursiveBackground($('#jmap_sitemap').parent());
			
			if(jmapDraggableSitemap) {
				var tmp_handler = function(){};
				$('div.jmapcolumn>ul').draggable({
					opacity: .8,
					addClasses: false,
					zIndex: 100,
					distance: 10,
					start : function(event,ui){
						try{
							tmp_handler = $._data( $('span', event.target)[0], "events" ).click[0].handler;
						} catch(e){}
						$('span', this).off('.treeview');
					},
					stop : function(event,ui){
						setTimeout(function(){
							$('span', event.target).on("click.treeview", tmp_handler)
						}, 300);
						
						try {
							if(document.elementFromPoint) {
								var elementOnCoordinates = document.elementFromPoint(ui.offset.left - 1, ui.offset.top - 1);
								var parentBgColor = 'rgba(0, 0, 0, 0)';
								var recursiveElementOnCoordinatesBackground = function(parentElement) {
									parentBgColor = parentElement.css('background-color');
									if((parentBgColor == 'rgba(0, 0, 0, 0)' || parentBgColor == 'transparent') && parentElement.length) {
										recursiveElementOnCoordinatesBackground(parentElement.parent());
									}
									return parentBgColor;
								}
								
								var elementOnCoordinatesBgColor = recursiveElementOnCoordinatesBackground($(elementOnCoordinates));
								var uniqueID = 'repositioned' + Math.floor((Math.random() * 100) + 1);
								$(event.target).attr('id', uniqueID);
								$('#' + uniqueID + '>li:first-child>div.lastCollapsable-hitarea').css('background-color', elementOnCoordinatesBgColor);
								$('#' + uniqueID + '>li:first-child>div.lastExpandable-hitarea').css('background-color', elementOnCoordinatesBgColor);
								$("<style type='text/css'>#" + uniqueID + " li.jmap_last_before.expandable:before,#" + uniqueID + " li.jmap_last_before.last:before{ background-color:" + elementOnCoordinatesBgColor +";</style>").appendTo("head");
							}
						} catch(e){}
					}
				});
			}
		}
	});
	
	if(jmapExpandFirstLevel) {
		$('div.jmapcolumn>ul>li.expandable.lastExpandable>div.hitarea').trigger('click');
	}
	
	if(!$.isEmptyObject(jmapLinkableCatsSources)) {
		$.each(jmapLinkableCatsSources, function(linkableList, linkableMode){
			var dataSourcePromise = $.Deferred(function(defer) {
				setTimeout(function(){
					var ulCategoryList = $('ul[data-hash=' + linkableList + ']').get(0);
					if(!ulCategoryList) return;
					var ulCategoryListLinks = $('a', $(ulCategoryList));
					if(!ulCategoryListLinks.length) return;
					defer.resolve(ulCategoryList, ulCategoryListLinks, linkableList, linkableMode);
				}, 0);
			}).promise();
			
			dataSourcePromise.then(function(ulCategoryList, ulCategoryListLinks, linkableList, linkableMode) {
				if(linkableMode == 'yeshide') {
					$('ul[data-hash=' + linkableList + ']').hide();
				}
				var struct = {};
				$.each(ulCategoryListLinks, function(index, link){
					var href = $(link).attr('href');
					var pkey = $(link).text();
					struct[pkey] = href;
				});
				var target = $(ulCategoryListLinks.get(0)).attr('target');
				var targetString = target ? 'target="' + target + '"' : 'target="_self"';
				var ulLinkableLists = $('ul[data-hash="' + linkableList + '\.items"]');
				$.each(ulLinkableLists, function(index, singleLinkableList){
					var spansToReplace = $('ul.jmap_filetree span.folder', singleLinkableList);
					$.each(spansToReplace, function(k, spanElem){
						var spanElemPKey = $(spanElem).text();
						if(struct[spanElemPKey]) {
							$(spanElem).text('');
							$(spanElem).append('<a ' + targetString + ' href="' + struct[spanElemPKey] + '">' + spanElemPKey + '</a>');
						}
					});
				});
			});
		});
	}
	
	if(!$.isEmptyObject(jmapMergeMenuTree)) {
		$.each(jmapMergeMenuTree, function(mergebleListIdentifier, linkableMode) {
			var dataSourcePromise = $.Deferred(function(defer) {
				if(mergebleListIdentifier == 'com_content') {return;}
				setTimeout(function(){
					var ulCategoryList = $('ul[data-hash="' + mergebleListIdentifier + '"]');
					if(!ulCategoryList.length) return;
					if(linkableMode == 'yeshide') {
						$('ul[data-hash="' + mergebleListIdentifier + '"]').hide();
					}
					
					var ulCategoryListUl = $('ul[data-hash^="' + mergebleListIdentifier + '"]', $(ulCategoryList));
					if(!ulCategoryListUl.length) return;
					
					defer.resolve(ulCategoryListUl, mergebleListIdentifier, linkableMode);
				}, 0);
			}).promise();
			
			dataSourcePromise.then(function(ulCategoryListUl, mergebleListIdentifier, linkableMode) {
				var struct = {};
				$.each(ulCategoryListUl, function(index, listUl){
					var dataHash = $(listUl).data('hash');
					var pkey = $('a', listUl).text();
					struct[pkey] = dataHash;
				});
				
				var ulItemsAnchors = $('ul[data-hash="' + mergebleListIdentifier + '\.items"] span.folder');
				$.each(ulItemsAnchors, function(index, ulItemsAnchor){
					var anchorElemPKey = $(ulItemsAnchor).text();
					if(struct[anchorElemPKey]) {
						var targetUlToAppend = $(ulItemsAnchor).parents('ul.jmap_filetree').get(0);
						var clonedDomElement = $(targetUlToAppend).clone(true, true).css('display', 'block');
						$('ul.jmap_filetree li[data-hash="' + struct[anchorElemPKey] + '"]').append(clonedDomElement);
					}
				});
				
				if(linkableMode == 'yeshide') {
					$('ul[data-hash="' + mergebleListIdentifier + '\.items"]').hide();
				}
			});
			
			var dataSourceContentPromise = $.Deferred(function(defer) {
				if(mergebleListIdentifier != 'com_content') {return;}
				setTimeout(function(){
					var ulCategoryContentList = $('ul.jmap_filetree[data-hash^="' + mergebleListIdentifier + '"]');
					if(!ulCategoryContentList.length) return;
					if(linkableMode == 'yeshide') {
						$('ul.jmap_filetree[data-hash^="' + mergebleListIdentifier + '"]').hide();
					}
					
					defer.resolve(ulCategoryContentList, mergebleListIdentifier, linkableMode);
				}, 0);
			}).promise();
			
			dataSourceContentPromise.then(function(ulCategoryContentList, mergebleListIdentifier, linkableMode) {
				$.each(ulCategoryContentList, function(index, ulCategoryContent) {
					var sourceHash = $(ulCategoryContent).data('hash');
					var clonedDomElement = $(ulCategoryContent).clone(true, true).css('display', 'block');
					$('ul.jmap_filetree li[data-hash="' + sourceHash + '"]').append(clonedDomElement);
				});
			});
		});
	}
	
	if(jmapMergeAliasMenu) {
		var dataSourceMenuAliasPromise = $.Deferred(function(defer) {
			setTimeout(function(){
				var ulMenuMergeAliasList = $('ul.jmap_filetree_menu li[data-merge]');
				if(!ulMenuMergeAliasList.length) return;
				defer.resolve(ulMenuMergeAliasList);
			}, 0);
		}).promise();
		
		dataSourceMenuAliasPromise.then(function(ulMenuMergeAliasList) {
			var allMenuLinksToEvaluate = $('ul.jmap_filetree_menu li:not([data-merge]) a').filter(function(){
				return $(this).parents('li[data-merge]').length ? false : true;
			});
			$.each(allMenuLinksToEvaluate, function(index, menuLinkToEvaluate) {
				var hrefLink = $(menuLinkToEvaluate).attr('href');
				$.each(ulMenuMergeAliasList, function(k, originalMergeSource){
					var $originalMergeSource = $(originalMergeSource);
					var currentDataMerge = $(originalMergeSource).data('merge');
					var currentElementMerge = $('ul', $originalMergeSource).get(0);
					if(currentElementMerge && currentDataMerge == hrefLink) {
						var clonedDomElement = $(currentElementMerge).clone(true, true).css('display', 'block');
						var parentsUL = $originalMergeSource.parents('ul:not(.jmap_filetree_menu )');
						var directLIParent = $($originalMergeSource.parents('li.collapsable').get(0));
						$originalMergeSource.remove()
						if(!$('li', parentsUL).length) {
							parentsUL.parents('ul.jmap_filetree_menu').remove();
						}
						if(!$('li', directLIParent).length) {
							directLIParent.removeClass('collapsable lastCollapsable');
						}
						$(menuLinkToEvaluate).after(clonedDomElement);
						$(menuLinkToEvaluate).before('<div class="hitarea collapsable-hitarea"></div>');
						$($(menuLinkToEvaluate).parents('ul').get(0)).treeview(defaultOptions);
					}
				});
			});
		});
	}
});