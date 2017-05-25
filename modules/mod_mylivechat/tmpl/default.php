<?php
/**
* @package		Joomla
* @copyright	Copyright (C) 2011 mylivechat.com
* @license		http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
* @Websites:     http://www.mylivechat.com
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
	// no direct access
	defined('_JEXEC') or die;

	// get membership
    $user	=  JFactory::getUser();
?>

<?php

	if(empty($mylivechat_id) || $mylivechat_id=="0")
	{
		echo "<a href='https://www.mylivechat.com/register.aspx' target='_blank'>Sign up MyLiveChat</a>";
	}
	else
	{
	?>
		<div class="mod_mylivechat">
	<?php
		if($mylivechat_displaytype=="inline")
		{
			echo "<script type=\"text/javascript\" src=\"https://www.mylivechat.com/chatinline.aspx?hccid=".$mylivechat_id."\"></script>";
		}
		else if($mylivechat_displaytype=="button")
		{
			echo "<div id=\"MyLiveChatContainer\"></div><script type=\"text/javascript\" src=\"https://www.mylivechat.com/chatbutton.aspx?hccid=".$mylivechat_id."\"></script>";
		}
		else if($mylivechat_displaytype=="box")
		{
			echo "<div id=\"MyLiveChatContainer\"></div><script type=\"text/javascript\" src=\"https://www.mylivechat.com/chatbox.aspx?hccid=".$mylivechat_id."\"></script>";
		}
		else if($mylivechat_displaytype=="widget")
		{
			echo "<script type=\"text/javascript\" src=\"https://www.mylivechat.com/chatwidget.aspx?hccid=".$mylivechat_id."\"></script>";
		}
		else
		{
			echo "<div id=\"MyLiveChatContainer\"></div><script type=\"text/javascript\" src=\"https://www.mylivechat.com/chatlink.aspx?hccid=".$mylivechat_id."\"></script>";
		}
		if($user->guest)
		{
			// no login
		} 
		else if($isIntegrateUser==true)
		{
			if($mylivechat_encrykey==null || strlen($mylivechat_encrykey) == 0)
			{
				echo  "<script type=\"text/javascript\">function MyLiveChat_OnInit() {MyLiveChat_SetUserName('".EncodeJScript($user->name)."');MyLiveChat_SetEmail('".EncodeJScript($user->email)."');}</script>";
			}
			else
			{
				echo  "<script type=\"text/javascript\">function MyLiveChat_OnInit() {MyLiveChat_SetUserName('".EncodeJScript($user->name)."','".GetEncrypt($user->id."",$mylivechat_encrymode,$mylivechat_encrykey)."');MyLiveChat_SetEmail('".EncodeJScript($user->email)."');}</script>";
			}
		}

	?>	
		</div>
	<?php
	}
?>