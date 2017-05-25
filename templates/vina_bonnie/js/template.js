/**
 * @package sample data Jshopping
 * @author VinaGecko.com http://VinaGecko.com
 * @copyright Copyright (C) 2014 www.VinaGecko.com
 * @license http://www.gnu.org/licenseses/gpl-3.0.html GNU/GPL
*/

var $jq=jQuery.noConflict();

$jq(window).scroll(function () {
  if ($jq(this).scrollTop() > 150) {
   $jq('#sp-main-menu-wrapper').addClass("nav-container-fix");
  } else {
   $jq('#sp-main-menu-wrapper').removeClass("nav-container-fix");
  }
});

/* FC - Mini Cart Effect */
function slideEffectMiniCart() {
	$jq('.block-mini-cart').mouseenter(function() {
		$jq(this).find(".mini-cart-content").stop(true, true).slideDown();
	});
	//hide submenus on exit
	$jq('.block-mini-cart').mouseleave(function() {
		$jq(this).find(".mini-cart-content").stop(true, true).slideUp();
	});
}
	
$jq(document).ready(function($){

	var galleryBlock = $("ul.gallery");
	
	/* Off-Canvas Menu Block */
	$sidebaroffcanvas = $(".sidebar-offcanvas");
	$sidebaroffcanvas.height($(window).height());
	
	galleryBlock.parents('.row-fluid').addClass('visiable-gallery');
	
	$sidebaroffcanvas = $(".sidebar-offcanvas");
	$sidebaroffcanvas.height($(window).height());
	
	/*if(/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
		$('body').swipe({
			swipeLeft: function(event, phase, direction, distance) {
				$('.row-offcanvas').removeClass('active');
			},
			swipeRight: function(event, phase, direction, distance) {
				$('.row-offcanvas').addClass('active');
			}
		});
	}*/
	
	$('#product-details-tab a').click(function (e) {
	  e.preventDefault();
	  $(this).tab('show');
	})
	
	$('[data-toggle=offcanvas]').click(function () {
		$('.row-offcanvas').toggleClass('active');
	});	
	
	/* Multi Language Effect */			
	$('.mod-languages').hover(function() {
		$('.mod-languages .btn-group').addClass("open").parent().addClass("nav-hover");		
	}, function(){
		$('.mod-languages .btn-group').removeClass("open").parent().removeClass("nav-hover");		
	});
	
	/* Mini Cart Effect */
	slideEffectMiniCart();
	
	/* Tooltip */
	$('.tooltip, [rel="tooltip"], .vm-product-details-inner .icons a').tooltip();
	
	/* Goto Top */
	$(window).scroll(function(event) {	
		if ($(this).scrollTop() > 300) {
			$('.sp-totop').fadeIn();
			$('.sp-totop').css({"visibility": "visible"});
		} else {
			$('.sp-totop').fadeOut();
		}
	});

	$('.sp-totop').click(function(){
		$('html').animate({
			scrollTop: 0
		}, 500);
	});
	$('.sp-totop').parents('.row-fluid').addClass('visiable-gallery');
	$(window).resize(function(){	
		$(this).load();
	});
}); 