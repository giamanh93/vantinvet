<?php
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

//set variables, usually set by shopfunctionsf::getLoginForm in case this layout is differently used
if (!isset( $this->show )) $this->show = TRUE;
if (!isset( $this->from_cart )) $this->from_cart = FALSE;
if (!isset( $this->order )) $this->order = FALSE ;

if (empty($this->url)){ $url = vmURI::getCleanUrl();}
else{ $url = $this->url;}

$user = JFactory::getUser();

if ($this->show and $user->id == 0  ) {
JHtml::_('behavior.formvalidation');
JHtml::_ ( 'behavior.modal' );
//$uri = JFactory::getURI();
//$url = $uri->toString(array('path', 'query', 'fragment'));
//Extra login stuff, systems like openId and plugins HERE
if (JPluginHelper::isEnabled('authentication', 'openid')) {
	$lang = JFactory::getLanguage();
	$lang->load('plg_authentication_openid', JPATH_ADMINISTRATOR);
	$langScript = '
		//<![CDATA[
		'.'var JLanguage = {};' .
						' JLanguage.WHAT_IS_OPENID = \'' . vmText::_('WHAT_IS_OPENID') . '\';' .
						' JLanguage.LOGIN_WITH_OPENID = \'' . vmText::_('LOGIN_WITH_OPENID') . '\';' .
						' JLanguage.NORMAL_LOGIN = \'' . vmText::_('NORMAL_LOGIN') . '\';' .
						' var comlogin = 1;
		//]]>
			';
	vmJsApi::addJScript('login_openid',$langScript);
	JHtml::_('script', 'openid.js');
}

$html = '';
JPluginHelper::importPlugin('vmpayment');
$dispatcher = JDispatcher::getInstance();
$returnValues = $dispatcher->trigger('plgVmDisplayLogin', array($this, &$html, $this->from_cart));

if (is_array($html)) {
	foreach ($html as $login) {
		echo $login.'<br />';
	}
}
else {
	echo $html;
}

//end plugins section

//anonymous order section
if ($this->order  ) { ?>
	<div class="order-view">
	<h2><?php echo vmText::_('COM_VIRTUEMART_ORDER_ANONYMOUS') ?></h2>
	<form action="<?php echo JRoute::_( 'index.php', 1, $this->useSSL); ?>" method="post" name="com-login" >
		<div class="width30 floatleft" id="com-form-order-number">
			<label for="order_number"><?php echo vmText::_('COM_VIRTUEMART_ORDER_NUMBER') ?></label><br />
			<input type="text" id="order_number" name="order_number" class="inputbox" size="18" alt="order_number" />
		</div>
		<div class="width30 floatleft" id="com-form-order-pass">
			<label for="order_pass"><?php echo vmText::_('COM_VIRTUEMART_ORDER_PASS') ?></label><br />
			<input type="text" id="order_pass" name="order_pass" class="inputbox" size="18" alt="order_pass" value="p_"/>
		</div>
		<div class="width30 floatleft" id="com-form-order-submit">
			<input type="submit" name="Submitbuton" class="button" value="<?php echo vmText::_('COM_VIRTUEMART_ORDER_BUTTON_VIEW') ?>" />
		</div>
		<div class="clr"></div>
		<input type="hidden" name="option" value="com_virtuemart" />
		<input type="hidden" name="view" value="orders" />
		<input type="hidden" name="layout" value="details" />
		<input type="hidden" name="return" value="" />
	</form>
	</div>
<?php } ?>

<?php // XXX style CSS id com-form-login ?>
<div class = "vmshop-cart-account cart-account-login">	
	<div class = "login-users">				
		<form id="form-login" action="<?php echo JRoute::_('index.php', $this->useXHTML, $this->useSSL); ?>" method="post" name="com-login" >
			<fieldset class="userdata content">				
				<h2><?php echo JText::_('VINA_VIRTUEMART_HAVE_ACCOUNT'); ?></h2>
				<p><?php echo vmText::_('COM_VIRTUEMART_ORDER_CONNECT_FORM').':'; ?></p>
				<div class="row-fluid">
					<div class="span3 first " id="com-form-login-username">
						<input type="text" name="username" class="inputbox" size="18" value="<?php echo vmText::_('COM_VIRTUEMART_USERNAME'); ?>" onblur="if(this.value=='') this.value='<?php echo addslashes(vmText::_('COM_VIRTUEMART_USERNAME')); ?>';" onfocus="if(this.value=='<?php echo addslashes(vmText::_('COM_VIRTUEMART_USERNAME')); ?>') this.value='';" />
					</div>
					<div class="span3" id="com-form-login-password">
						<input id="modlgn-passwd" type="password" name="password" class="inputbox" size="18" value="<?php echo vmText::_('COM_VIRTUEMART_PASSWORD'); ?>" onblur="if(this.value=='') this.value='<?php echo addslashes(vmText::_('COM_VIRTUEMART_PASSWORD')); ?>';" onfocus="if(this.value=='<?php echo addslashes(vmText::_('COM_VIRTUEMART_PASSWORD')); ?>') this.value='';" />
					</div>														
					<div class="span4 " id="com-form-login-login">
						<input type="submit" name="Submit" class="btn-login default width30" value="<?php echo vmText::_('COM_VIRTUEMART_LOGIN'); ?>" />					
					</div>
				</div>
			</fieldset>
			
			<div class="clr"></div>
			<div class="row-fluid">
				<div class="span3 first">										
					<a href="<?php echo JRoute::_('index.php?option=com_users&view=remind'); ?>" rel="nofollow">
					<?php echo vmText::_('COM_VIRTUEMART_ORDER_FORGOT_YOUR_USERNAME'); ?></a>
				</div>
				<div class="span3 last">
					<a href="<?php echo JRoute::_('index.php?option=com_users&view=reset'); ?>" rel="nofollow">
					<?php echo vmText::_('COM_VIRTUEMART_ORDER_FORGOT_YOUR_PASSWORD'); ?></a>
				</div>	
				<div class="span4 " id="com-form-login-remember">
					<?php if (JPluginHelper::isEnabled('system', 'remember')) : ?>
					<label for="remember"><?php echo $remember_me = vmText::_('JGLOBAL_REMEMBER_ME') ?></label>
					<input type="checkbox" id="remember" name="remember" class="inputbox" value="yes" />							
					<?php endif; ?>	
				</div>
			</div>
			<div class="clr"></div>
			
			<input type="hidden" name="task" value="user.login" />
			<input type="hidden" name="option" value="com_users" />
			<input type="hidden" name="return" value="<?php echo base64_encode($url) ?>" />
			<?php echo JHtml::_('form.token'); ?>
		</form>	
		<p class="new-customer"><?php echo JText::_('VINA_VIRTUEMART_NEW_CUSTOMER');?>
			<a class="link-registration" href="<?php echo JRoute::_('index.php?option=com_virtuemart&view=user&task=edit', FALSE); ?>" target="_blank" ><span><?php echo JText::_('VINA_VIRTUEMART_REGISTRATION'); ?></span></a>
		</p>		
	</div>
</div>
<!-- Logout -->
<?php } else if ( $user->id ) { ?>
	<div class="logout-users">
		<form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="login" id="form-login">
			<?php echo vmText::sprintf( 'COM_VIRTUEMART_HINAME', $user->name ); ?>					
			<input type="submit" name="Submit" class="btn-logout button" value="<?php echo vmText::_( 'COM_VIRTUEMART_BUTTON_LOGOUT'); ?>" />
			<input type="hidden" name="option" value="com_users" />
			<input type="hidden" name="task" value="user.logout" />
			<?php echo JHtml::_('form.token'); ?>
			<input type="hidden" name="return" value="<?php echo base64_encode($url) ?>" />
		</form>
	</div>
<?php } ?>