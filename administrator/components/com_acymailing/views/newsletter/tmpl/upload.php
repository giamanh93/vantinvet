<?php
/**
 * @package	AcyMailing for Joomla!
 * @version	4.9.2
 * @author	acyba.com
 * @copyright	(C) 2009-2015 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div id="acy_content">
<form action="index.php?tmpl=component&amp;option=<?php echo ACYMAILING_COMPONENT ?>" method="post" name="adminForm"  id="adminForm" autocomplete="off" enctype="multipart/form-data">
	<fieldset>
		<div class="acyheader icon-48-acytemplate" style="float: left;"><?php echo JText::_('NEWSLETTER'); ?></div>
		<div class="toolbar" id="toolbar" style="float: right;">
			<table><tr>
			<td><a onclick="javascript:submitbutton('douploadnewsletter'); return false;" href="#" ><span class="icon-32-save" title="<?php echo JText::_('IMPORT',true); ?>"></span><?php echo JText::_('IMPORT'); ?></a></td>
			</tr></table>
		</div>
	</fieldset>
	<div id="iframedoc"></div>
	<div style="text-align:center;padding-top:20px;"><input type="file" style="width:auto" name="uploadedfile" />
	<?php echo '<br />'.(JText::sprintf('MAX_UPLOAD',(acymailing_bytes(ini_get('upload_max_filesize')) > acymailing_bytes(ini_get('post_max_size'))) ? ini_get('post_max_size') : ini_get('upload_max_filesize'))); ?></div>
	<input type="hidden" name="option" value="<?php echo ACYMAILING_COMPONENT; ?>" />
	<input type="hidden" name="task" value="upload" />
	<input type="hidden" name="ctrl" value="newsletter" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
</div>
