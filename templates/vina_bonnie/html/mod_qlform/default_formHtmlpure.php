<?php
/**
 * @package		mod_qlform
 * @copyright	Copyright (C) 2013 ql.de All rights reserved.
 * @author 		Mareike Riegel mareike.riegel@ql.de
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
// no direct access
defined('_JEXEC') or die;
?>
<form method="post" action="<?php echo $action; ?>" id="mod_qlform_<?php echo $module->id;?>" class="form-validate" enctype="multipart/form-data">

    <?php
    if (1==$params->get('addPostToForm') AND isset($array_posts) AND is_array($array_posts)) : foreach ($array_posts as $k=>$v) :?>
        <input type="hidden" name="former[<?php echo $k;?>]" value="<?php echo preg_replace("/\"/","",$v);?>" /><?php
    endforeach; endif; ?>
    <div style="display:none;"><input name="JabBerwOcky" type="text" value="" /></div>
    <input name="formSent" type="hidden" value="1" />
    <?php
    foreach ($form->getFieldsets() as $fieldset):
        $fields = $form->getFieldset($fieldset->name);
        echo '<fieldset id="'.$fieldset->name.'"';
        if (isset($fieldset->class)) echo ' class="'.$fieldset->class.'"';
        echo '>';
        if (isset($fieldset->label) AND ''!=$fieldset->label) echo '<legend id="legend'.$fieldset->name.'">'.$fieldset->label.'</legend>';
		echo '<dl>';
        foreach($fields as $field):
            if ($field->hidden): echo $field->input;
            else: ?>
				<dd class="cluster">
				<dl class="cluster">
                <dt class="<?php echo $field->id;?>">
                    <?php echo preg_replace('~}}~','>',preg_replace('~{{~','<',$field->label)); ?>
                </dt>
                <dd class="<?php echo $field->id;?>">
                    <?php echo $field->input;?>
                </dd>
				</dl>
				</dd>
                <?php endif;
        endforeach;
        echo '</dl></fieldset>';
    endforeach; ?>
    <dl>
        <?php if (1==$showCaptacha) : ?>
        <dt class="captcha">
            <?php if(''!=$params->get('captchalabel')) echo '<span>'.$params->get('captchalabel').'</span><br />';?>
            <img id="captcha" src="<?php echo $image;?>" alt="captcha" /></dt>
        <dd class="captcha"><?php if(''!=$params->get('captchadesc')) echo '<span>'.$params->get('captchadesc').'</span><br />';?><input type="text" name="captcha" value="" /></dd>
        <?php endif; ?>
        <dt class="submit"></dt><dd class="submit"><input class="submit" type="submit" value="<?php echo $submit; ?>" /></dd>
    </dl>
    <?php if (1==$fieldModuleId) : ?>
    <input type="hidden" value="<?php echo $moduleId;?>" name="moduleId" />
    <?php endif; ?>
</form>