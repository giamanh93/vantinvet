<?php
/**
 * @package		mod_qlform
 * @copyright	Copyright (C) 2013 ql.de All rights reserved.
 * @author 		Mareike Riegel mareike.riegel@ql.de
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
// no direct access
defined('_JEXEC') or die;
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.tooltip');
?>

<div class="qlform<?php echo $moduleclass_sfx; ?>">
<?php
if (1==$params->get('stylesActive',0))require JModuleHelper::getLayoutPath('mod_qlform', 'default_styles');
if (1==$emailcloak) echo '{emailcloak=off}'; /*very important; disables email cloaking in email inputs!!!!*/
require JModuleHelper::getLayoutPath('mod_qlform', 'default_copyright');

if ((1==$messageType OR 3==$messageType) AND isset($messages)) require JModuleHelper::getLayoutPath('mod_qlform', 'default_message');
if (0==$params->get('hideform') OR (1==$params->get('hideform') AND  (!isset($validated) OR (isset($validated) AND 0==$validated)))) 
{
	if (1==$showpretext) require JModuleHelper::getLayoutPath('mod_qlform', 'default_pretext');
	if (is_object($form)) require JModuleHelper::getLayoutPath('mod_qlform', 'default_form'.ucwords($params->get('stylesHtmltemplate','htmlpure')));
}
if (1==$params->get('backbool') AND isset($validated)) require JModuleHelper::getLayoutPath('mod_qlform', 'default_back');
if (1==$params->get('authorbool')) require JModuleHelper::getLayoutPath('mod_qlform', 'default_author');
?>
</div>
