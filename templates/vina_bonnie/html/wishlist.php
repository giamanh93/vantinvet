<?php 
/**
 * Favorites Template Page for Favorites Component
 * 
 * @package    Favorites & Wishlist
 * @subpackage com_wishlist
 * @license  GNU/GPL v2
 * @Copyright (C) 2013 2KWeb Solutions. All rights reserved.
 * This program is distributed under the terms of the GNU General Public License
 *
 */
 
//Load the com_favorite language file
$language 		= JFactory::getLanguage();
$language_tag 	= $language->getTag();
JFactory::getLanguage()->load('com_wishlist', JPATH_SITE, $language_tag, true);

//Loading the Component Stylesheet
//JHtml::stylesheet('components/com_wishlist/template.css', array(), false);

//Loading Font Awesome
JHtml::stylesheet('http://netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css', array(), false);

//Loading Template Options
$fav_params 			= JComponentHelper::getParams( 'com_wishlist' );
$tmpl_favlist_style 	= $fav_params->get('tmpl_favlist_style');
$tmpl_favbtn_image 		= $fav_params->get('tmpl_favbtn_image');
$notorderable_enabled 	= $fav_params->get('tmpl_notorderable_enabled');
$qty_enabled 			= $fav_params->get('tmpl_qty_enabled');
$guest_enabled 			= $fav_params->get('tmpl_guest_enabled');
$likestats_enabled 		= $fav_params->get('tmpl_favstat_enabled');
$favorites_maxnum 		= $fav_params->get('tmpl_favorites_maxnum');
			
/* FAVORITES & WISHLIST ENTRY */
if(!isset($_COOKIE['virtuemart_wish_session']))
{
	$session = JFactory::getSession();
	setcookie('virtuemart_wish_session',$session->getId(),2592000 + time(),'/');
	$_COOKIE['virtuemart_wish_session'] = $session->getId();
}

$db 		= JFactory::getDBO();
$view 		= JRequest::getString('view',  "");
//$itemid 	= JRequest::getInt('Itemid',  1);
$user 		= JFactory::getUser();
$user_id 	= $user->guest ? $_COOKIE['virtuemart_wish_session'] : $user->id;

$product_orderable = false;

if ($view == "category" || $view=='products' || is_object($product))
{
	$product_id 		= $product->virtuemart_product_id;
	$category_id 		= $product->virtuemart_category_id;
	$product_orderable 	= $product->orderable;
}
else if ($view == "productdetails")
{
	$product_id 		= $this->product->virtuemart_product_id;
	$category_id 		= $this->product->virtuemart_category_id;
	$product_orderable 	= $this->product->orderable;
}

if($product_orderable || $notorderable_enabled) {
	$favorite_id = JRequest::getInt('favorite_id',  1);
	$quantity = JRequest::getInt('quantity',  1);
	$mode = JRequest::getString('mode',  "null");
	$q = "SELECT COUNT(*) FROM #__virtuemart_favorites WHERE product_id=".$product_id;
	$db->setQuery($q);
	$total_fav = $db->loadResult();
	$q = "SELECT COUNT(*) FROM #__virtuemart_favorites WHERE user_id ='".$user_id."' AND product_id=".$product_id;
	$db->setQuery($q);
	$result = $db->loadResult();
	$url_fav = JURI::getInstance()->toString();
	$url_favlist = JRoute::_("index.php?option=com_wishlist&view=favoriteslist");
	//generate button to remove from favorites list
	$form_deletefavorite = '<form style="display: inline-block; text-align: center; margin:0px" action="';
	$form_deletefavorite .= $url_fav.'" method="POST" name="deletefavo" id="'. uniqid('deletefavo_') .'">';
	$form_deletefavorite .= '<button class="modns button art-button art-button" title="'.JText::_('VM_REMOVE_FAVORITE').'" >';
	if ($tmpl_favbtn_image) $form_deletefavorite .= '<i class="fa fa-trash-o"></i>';
	$form_deletefavorite .= JText::_('VM_REMOVE_FAVORITE').'</button>';
	$form_deletefavorite .= '<input type="hidden" name="mode" value="fav_del" />';
	$form_deletefavorite .= '<input type="hidden" name="favorite_id" value="'. $product_id .'" />';
	$form_deletefavorite .= '</form>'; 
	$addtofavorites = '';
	if ($result > 0 ){
			$total_fav --;
			if ($product_id == $favorite_id && $mode == "fav_del")
			{
				$Sql = "DELETE FROM #__virtuemart_favorites ";
				$Sql.= "WHERE product_id='$product_id' AND user_id='$user_id'";
				$db->setQuery($Sql);
				$db->query();
				$result = 0;
				JError::raiseNotice( 100, JText::_('VM_FAVORITE_REMOVED'));
			}
			else
			{
				if ($tmpl_favlist_style == "button") $addtofavorites .= $form_deletefavorite;
				else 
				{
					$addtofavorites .= '<a class="jutooltip" title="'.JText::_('VINA_VIRTUEMART_ALL_WISHLIST').'" href="'.$url_favlist.'">';
					$addtofavorites .= '<div class="addtofav_aws_icon_active">';
					if ($tmpl_favlist_style == "button") $addtofavorites .= JText::_('VINA_VIRTUEMART_ALL_WISHLIST');
					else $addtofavorites .= '<i class="fa fa-'.$tmpl_favlist_style.'"></i>';
					$addtofavorites .= '<span>'.JText::_('VINA_VIRTUEMART_ALL_WISHLIST').'</span></div></a>';
				}
			}
	}
	if ($result == 0){
		if($product_id == $favorite_id && $mode == "fav_add") {
				if ($favorites_maxnum != '')
				{
					$Sql = "SELECT COUNT(*) FROM #__virtuemart_favorites where user_id='$user_id'";
					$db->setQuery($Sql);
					$max_fav = $db->loadResult();
				}
				if ($max_fav != null && $max_fav >= $favorites_maxnum) 
				{
					JError::raiseNotice( 100, JText::_('VM_FAVORITE_MAXNUM'));
					//$addtofavorites .= '<a href="'.$url_favlist.'">'.JText::_('VM_MANAGE_FAVORITE_PRODUCTS').'</a>';
				}
				
				else
				{
					$Sql = "INSERT INTO #__virtuemart_favorites ";
					$Sql.= "SET product_id='$product_id', product_qty='$quantity', user_id='$user_id', fav_date=NOW(), isGuest=".$user->guest;
					$db->setQuery($Sql);
					$db->query();
					JFactory::getApplication()->enqueueMessage(JText::_('VM_FAVORITE_ADDED'));
					if ($tmpl_favlist_style == "button") $addtofavorites .= $form_deletefavorite;
					else 
					{
						$addtofavorites .= '<a class="jutooltip" title="'.JText::_('VINA_VIRTUEMART_ALL_WISHLIST').'" href="'.$url_favlist.'">';
						$addtofavorites .= '<div class="addtofav_aws_icon_active">';
						if ($tmpl_favlist_style == "button") $addtofavorites .= JText::_('VM_ALL_FAVORITE_PRODUCTS');
						else $addtofavorites .= '<i class="fa fa-'.$tmpl_favlist_style.'"></i>';
						$addtofavorites .= '<span>'.JText::_('VINA_VIRTUEMART_ALL_WISHLIST').'</span></div></a>';
					}
					
				}
		} 
		else {
			if ($guest_enabled || !$user->guest){
					$addtofavorites .= '
					<form style="display: inline-block; text-align: center; margin:0px" method="post" action="'.$url_fav.'" name="addtofavorites" class="addtofavorites_'.$product_id.'">';
					// Product custom_fields
					if ($qty_enabled)
						$addtofavorites .= '<input id="quantity_'.$product_id
						.'" class="quantity-input" size="1" name="quantity" value="1" />';
					$addtofavorites .= '<button class="jutooltip ';
					if ($tmpl_favlist_style == "button") $addtofavorites .= 'modns button art-button art-button"';
					else $addtofavorites .= 'addtofav_aws_icon"';
					$addtofavorites .= ' value="" name="addtofavorites" title="'.JText::_('VINA_VIRTUEMART_ADD_TO_WISHLIST').'" >';
					if ($tmpl_favlist_style == "button") 
					{
						if ($tmpl_favbtn_image) $addtofavorites .= '<i class="fa fa-star"></i>';
						$addtofavorites .= JText::_('VM_ADD_TO_FAVORITES');
					}
					else $addtofavorites .= '<i class="fa fa-'.$tmpl_favlist_style.'"></i>';
					$addtofavorites .= '<span>'.JText::_('VINA_VIRTUEMART_ADD_TO_WISHLIST').'</span></button>
					<input type="hidden" name="favorite_id" value="'.$product_id.'" />
					<input type="hidden" name="mode" value="fav_add" />
					</form>'; 
				}
			else {
					$redirectUrl = $url_fav.'&mode=fav_add&favorite_id='.$product_id;
					$redirectUrl = urlencode(base64_encode($redirectUrl));
					$redirectUrl = '&return='.$redirectUrl;
					$joomlaLoginUrl = 'index.php?option=com_users&view=login';
					$finalUrl = $joomlaLoginUrl . $redirectUrl;
					$addtofavorites .= '<a class="jutooltip" title="'.JText::_('VINA_VIRTUEMART_ADD_TO_WISHLIST').'" href="'.$finalUrl.'" alt="Login" title="Login">';
					$addtofavorites .= '<button class=';
					if ($tmpl_favlist_style == "button") $addtofavorites .= '"modns button art-button art-button"';
					else $addtofavorites .= '"addtofav_aws_icon"';
					$addtofavorites .= ' value="" name="addtofavorites" title="'.JText::_('VINA_VIRTUEMART_ADD_TO_WISHLIST').'" >';
					if ($tmpl_favlist_style == "button") 
					{
						if ($tmpl_favbtn_image) $addtofavorites .= '<i class="fa fa-star"></i>';
						$addtofavorites .= JText::_('VM_ADD_TO_FAVORITES');
					}
					else $addtofavorites .= '<i class="fa fa-'.$tmpl_favlist_style.'"></i>';
					$addtofavorites .= '<span>'.JText::_('VINA_VIRTUEMART_ADD_TO_WISHLIST').'</span></button></a>';
			}
		}
	}
	if ($total_fav > 0 && $likestats_enabled)
		$addtofavorites .= '<div style="clear:both">'.JText::_('VM_FAV_TOTAL_LIKES').'<b>'.$total_fav.'</b>'.JText::_('VM_FAV_MORE_PEOPLE').'</div>';
	echo $addtofavorites;
}