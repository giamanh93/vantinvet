<?php
/**
 * @package	AcyMailing for Joomla!
 * @version	4.9.2
 * @author	acyba.com
 * @copyright	(C) 2009-2015 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><table style="width:100%;"><tr><td><h1><?php echo $this->mail->subject ?></h1></td>
<td style="text-align:center;width:32px;"><a href="#" onclick="if(document.getElementById('iframepreview')){document.getElementById('iframepreview').contentWindow.focus();document.getElementById('iframepreview').contentWindow.print();}else{window.print();}return false;"><span class="icon-32-acyprint" style="width:32px;height:32px;display:block;" title="<?php echo JText::_('ACY_PRINT',true); ?>"></span><?php echo JText::_('ACY_PRINT'); ?></a></td></tr></table>
<div class="newsletter_body" id="newsletter_preview_area">
<?php echo $this->mail->sendHTML ? $this->mail->body : nl2br($this->mail->altbody); ?>
</div>
