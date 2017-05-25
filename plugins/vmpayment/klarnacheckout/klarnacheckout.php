<?php

defined('_JEXEC') or die('Restricted access');

/**
 * @author Valérie Isaksen
 * @version $Id: klarnacheckout.php 7927 2014-05-15 07:29:17Z alatak $
 * @package VirtueMart
 * @subpackage payment
 * @copyright Copyright (C) 2004-Copyright (C) 2004-2015 Virtuemart Team. All rights reserved.   - All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
 *
 * http://virtuemart.net
 */
if (!class_exists('vmPSPlugin')) {
	require(JPATH_VM_PLUGINS . DS . 'vmpsplugin.php');
}

if (!defined('JPATH_VMKLARNACHEKOUTCHEKOUTPLUGIN')) {
	define('JPATH_VMKLARNACHEKOUTCHEKOUTPLUGIN', VMPATH_ROOT . DS . 'plugins' . DS . 'vmpayment' . DS . 'klarnacheckout');
}
if (!defined('VMKLARNACHEKOUTPLUGINWEBROOT')) {
	define('VMKLARNACHEKOUTPLUGINWEBROOT', 'plugins/vmpayment/klarnacheckout');
}
if (!defined('VMKLARNACHEKOUTPLUGINWEBASSETS')) {
	define('VMKLARNACHEKOUTPLUGINWEBASSETS', JURI::root() . VMKLARNACHEKOUTPLUGINWEBROOT . '/klarnacheckout/assets');
}
if (!defined('VMKLARNACHEKOUTPLUGINWEBASSETS')) {
	define('VMKLARNACHEKOUTPLUGINWEBASSETS', JURI::root() . VMKLARNACHEKOUTPLUGINWEBROOT . '/klarnacheckout/assets');
}
if (!class_exists('Klarna')) {
	require(VMPATH_ROOT . DS . 'plugins' . DS . 'vmpayment' . DS . 'klarna' . DS . 'klarna' . DS . 'api' . DS . 'klarna.php');
}
if (!class_exists('Klarna')) {
	require(VMPATH_ROOT . DS . 'plugins' . DS . 'vmpayment' . DS . 'klarna' . DS . 'klarna' . DS . 'api' . DS . 'klarna.php');
}
class plgVmPaymentKlarnaCheckout extends vmPSPlugin {
	const RELEASE = 'VM 3.0.8';
	protected $currency_code_3;
	protected $currency_id;
	protected $country_code_2;
	protected $country_code_3;
	protected $locale;
	protected $sharedsecret;
	protected $merchantid;
	protected $mode;
	protected $ssl;
	var $method;


	function __construct(& $subject, $config) {

		parent::__construct($subject, $config);

		$this->_loggable = TRUE;
		$this->tableFields = array_keys($this->getTableSQLFields());
		$this->_tablepkey = 'id'; //virtuemart_sofort_id';
		$this->_tableId = 'id'; //'virtuemart_sofort_id';
		$varsToPush = $this->getVarsToPush();
		$this->setConfigParameterable($this->_configTableFieldName, $varsToPush);
		plgVmPaymentKlarnaCheckout::includeKlarnaFiles();
		$this->loadJLangThis('plg_vmpayment_klarna');

	}

	/**
	 * @return string
	 */
	public function getVmPluginCreateTableSQL() {

		return $this->createTableSQL('Payment KlarnaCheckout Table');
	}

	/**
	 * @return array
	 */
	function getTableSQLFields() {

		$SQLfields = array(
			'id' => 'int(11) UNSIGNED NOT NULL AUTO_INCREMENT',
			'virtuemart_order_id' => 'int(1) UNSIGNED',
			'order_number' => 'char(64)',
			'virtuemart_paymentmethod_id' => 'mediumint(1) UNSIGNED',
			'payment_name' => 'varchar(1000)',
			'action' => 'varchar(20)',
			'cardId' => 'varchar(20)',
			'klarna_status' => 'varchar(20)',
			'format'                        => 'varchar(5)',
			'data'                        => 'mediumtext',
		);
		return $SQLfields;
	}

	/**
	 * This shows the plugin for choosing in the payment list of the checkout process.
	 *
	 * @author Valerie Cartan Isaksen
	 */
	function plgVmDisplayListFEPayment(VirtueMartCart $cart, $selected = 0, &$htmlIn) {

		if ($this->getPluginMethods($cart->vendorId) === 0) {
			if (empty($this->_name)) {
				$app = JFactory::getApplication();
				$app->enqueueMessage(vmText::_('COM_VIRTUEMART_CART_NO_' . strtoupper($this->_psType)));
				return false;
			} else {
				return false;
			}
		}
		$htmla = array();
		$html = '';
		$logo='';
		VmConfig::loadJLang('com_virtuemart');
		$currency = CurrencyDisplay::getInstance();
		$showallform = true;

		foreach ($this->methods as $method) {
			if ($this->checkConditions($cart, $method, $cart->cartPrices)) {
				$methodSalesPrice = $this->calculateSalesPrice($cart, $method, $cart->cartPrices);

				if (empty($method->payment_logos)) {
					$logo = '<img src="https://cdn.klarna.com/public/images/SE/logos/v1/basic/SE_basic_logo_std_blue-black.png?width=100&" />';
				}
				$payment_cost = '';
				if ($methodSalesPrice) {
					$payment_cost = $currency->priceDisplay($methodSalesPrice);
				}
				if ($selected == $method->virtuemart_paymentmethod_id) {
					$checked = 'checked="checked"';
				} else {
					$checked = '';
				}
				if ($cart->virtuemart_paymentmethod_id == $method->virtuemart_paymentmethod_id) {
					$showallform = false;
				}
				$html = $this->renderByLayout('display_payment', array(
					'plugin' => $method,
					'checked' => $checked,
					'payment_logo' => $logo,
					'payment_cost' => $payment_cost,
					'showallform' => $showallform
				));

				$htmla[] = $html;
			}
		}


		if ($showallform) {
			$js = '
	jQuery(document).ready(function( $ ) {
		      $("#checkoutForm").show();
		      $(".billto-shipto").show();
		      $("#com-form-login").show();

	});
	';
			vmJsApi::addJScript('vm.showallform', $js);
		}


		if (!empty($htmla)) {
			$htmlIn[] = $htmla;
		}

		return true;
	}


	function getCartItems($cart) {
		vmdebug('getProductItems', $cart->cartPrices);
		//self::includeKlarnaFiles();
		$i = 0;


		foreach ($cart->products as $pkey => $product) {

			$items[$i]['reference'] = !empty($product->sku) ? $product->sku : $product->virtuemart_product_id;
			$items[$i]['name'] = $product->product_name;
			$items[$i]['quantity'] = (int)$product->quantity;
			$price = !empty($product->prices['basePriceWithTax']) ? $product->prices['basePriceWithTax'] : $product->prices['basePriceVariant'];

			$itemInPaymentCurrency = vmPSPlugin::getAmountInCurrency($price, $this->_currentMethod->payment_currency);
			$items[$i]['unit_price'] = round($itemInPaymentCurrency['value'] * 100, 0);
			//$items[$i]['discount_rate'] = $discountRate;
			// Bug indoc: discount is not supported
			//$items[$i]['discount'] = abs($cart->cartPrices[$pkey]['discountAmount']*100);
			$tax_rate = round($this->getVatTaxProduct($cart->cartPrices[$pkey]['VatTax']));
			$items[$i]['tax_rate'] = $tax_rate * 100;
			//$this->debugLog($unitPriceCentsInPaymentCurrency, 'getCartItems', 'debug');
			//$this->debugLog($cart->cartPrices[$pkey], 'getCartItems Products', 'debug');
			$this->debugLog($items[$i], 'getCartItems', 'debug');
			$i++;
			// ADD A DISCOUNT AS A NEGATIVE VALUE FOR THAT PRODUCT
			if ($cart->cartPrices[$pkey]['discountAmount'] != 0.0) {
				$items[$i]['reference'] = $items[$i - 1]['reference'];
				$items[$i]['name'] = $items[$i - 1]['name'] . ' (' . vmText::_('VMPAYMENT_KLARNACHECKOUT_PRODUCTDISCOUNT') . ')';
				$items[$i]['quantity'] = (int)$product->quantity;
				$discount_tax_percent = 0.0;
				$discountInPaymentCurrency = vmPSPlugin::getAmountInCurrency(abs($cart->cartPrices[$pkey]['discountAmount']), $this->_currentMethod->payment_currency);
				$discountAmount = -abs(round($discountInPaymentCurrency['value'] * 100, 0));
				if ($cart->cartPrices[$pkey]['discountAmount'] > 0.0) {
					$items[$i]['tax_rate'] = $items[$i - 1]['tax_rate'];
				} else {
					$items[$i]['tax_rate'] = 0.0;
					$tax_rate = 0.0;
				}
				$items[$i]['unit_price'] = round($discountAmount * (1 + ($tax_rate * 0.01)), 0);

				$this->debugLog($items[$i], 'getCartItems', 'debug');
				$i++;
			}
		}
		if ($cart->cartPrices['salesPriceCoupon']) {
			$items[$i]['reference'] = 'COUPON';
			$items[$i]['name'] = 'Coupon discount';
			$items[$i]['quantity'] = 1;
			$couponInPaymentCurrency = vmPSPlugin::getAmountInCurrency($cart->cartPrices['salesPriceCoupon'], $this->_currentMethod->payment_currency);
			$items[$i]['unit_price'] = round($couponInPaymentCurrency['value'] * 100, 0);
			$items[$i]['tax_rate'] = 0;
			$this->debugLog($cart->cartPrices['salesPriceCoupon'], 'getCartItems Coupon', 'debug');
			$this->debugLog($items[$i], 'getCartItems', 'debug');
			$i++;
		}
		if ($cart->cartPrices['salesPriceShipment']) {
			$items[$i]['reference'] = 'SHIPPING';
			$items[$i]['name'] = 'Shipping Fee';
			$items[$i]['quantity'] = 1;
			$shipmentInPaymentCurrency = vmPSPlugin::getAmountInCurrency($cart->cartPrices['salesPriceShipment'], $this->_currentMethod->payment_currency);
			$items[$i]['unit_price'] = round($shipmentInPaymentCurrency['value'] * 100, 0);
			$items[$i]['tax_rate'] = $this->getTaxShipment($cart->cartPrices['shipment_calc_id']);
			$this->debugLog($cart->cartPrices['salesPriceShipment'], 'getCartItems Shipment', 'debug');
			$this->debugLog($items[$i], 'getCartItems', 'debug');
		}
		$currency = CurrencyDisplay::getInstance($cart->paymentCurrency);

		return $items;

	}


	function getTaxShipment($shipment_calc_id) {
		// TO DO add shipmentTaxRate in the cart
		// assuming there is only one rule +%
		$db = JFactory::getDBO();
		$q = 'SELECT * FROM #__virtuemart_calcs WHERE `virtuemart_calc_id`="' . $shipment_calc_id . '" ';
		$db->setQuery($q);
		$taxrule = $db->loadObject();
		if ($taxrule->calc_value_mathop != "+%") {
			VmError('KlarnaCheckout getTaxShipment: expecting math operation to be +% but is ' . $taxrule->calc_value_mathop);
		}
		return $taxrule->calc_value * 100;

	}

	function getVatTaxProduct($vatTax) {
		$countRules = count($vatTax);
		if ($countRules == 0) {
			return 0;
		}
		if ($countRules > 1) {
			VmError('KlarnaCheckout: More then one VATax for the product:' . $countRules);
		}
		$tax = current($vatTax);
		if ($tax[2] != "+%") {
			VmError('KlarnaCheckout: expecting math operation to be +% but is ' . $tax[2]);
		}
		return $tax[1];

	}

	function plgVmOnCheckoutAdvertise($cart, &$payment_advertise) {


		if ($this->getPluginMethods($cart->vendorId) === 0) {
			return FALSE;
		}
		$virtuemart_paymentmethod_id = 0;
		foreach ($this->methods as $method) {
			if ($cart->virtuemart_paymentmethod_id == $method->virtuemart_paymentmethod_id) {
				$virtuemart_paymentmethod_id = $method->virtuemart_paymentmethod_id;
			}
		}
		if ($virtuemart_paymentmethod_id == 0 or empty($cart->products)) {
			return;
		}
		if (!($this->_currentMethod = $this->getVmPluginMethod($virtuemart_paymentmethod_id))) {
			return NULL; // Another method was selected, do nothing
		}

		// Check if it is the same payment_method_id as the previous one.
		$session = JFactory::getSession();
		$klarna_paymentmethod_id_active = $session->get('klarna_paymentmethod_id_active', '', 'vm');
		if ($klarna_paymentmethod_id_active != $cart->virtuemart_paymentmethod_id) {
			$session->clear('klarna_checkout', 'vm');
			$session->clear('klarna_paymentmethod_id_active', 'vm');
		}

		if (! $this->initKlarnaParams() ) {
			return;
		}
		$message = '';
		$snippet = '';
		$hide_BTST = true;
		if ($cart->virtuemart_shipmentmethod_id == 0) {
			$message = vmText::sprintf('VMPAYMENT_KLARNACHECKOUT_SELECT_SHIPMENT_FIRST', $this->_currentMethod->payment_name);
		} else {
			$session = JFactory::getSession();
			require_once 'klarnacheckout/library/Checkout.php';

			$cartIdInTable = $this->storeCartInTable($cart);
			if ($this->_currentMethod->server == 'beta') {
				Klarna_Checkout_Order::$baseUri = 'https://checkout.testdrive.klarna.com/checkout/orders';
			} else {
				Klarna_Checkout_Order::$baseUri = 'https://checkout.klarna.com/checkout/orders';
			}
			Klarna_Checkout_Order::$contentType = "application/vnd.klarna.checkout.aggregated-order-v2+json";

			$klarna_checkout = $session->get('klarna_checkout', '', 'vm');
			$connector = Klarna_Checkout_Connector::create($this->_currentMethod->sharedsecret);

			$klarnaOrder = null;
			if (!empty($klarna_checkout)) {
				// Resume session
				$klarnaOrder = new Klarna_Checkout_Order($connector, $klarna_checkout);
				try {
					$klarnaOrder->fetch();

					$update['cart']['items'] = array();
					$update['cart']['items'] = $this->getCartItems($cart);
					if (!empty($cart->BT['email'])) {
						$update['shipping_address']['email'] = $cart->BT['email'];
						$hide_BTST = false;
						$address = (($cart->ST == 0) ? $cart->BT : $cart->ST);
						if (isset($address['zip']) and !empty($address['zip'])) {
							$update['shipping_address']['postal_code'] = $cart->BT['zip'];
						}
					}


					$klarnaOrder->update($update);
					$this->debugLog($update, 'plgVmOnCheckoutAdvertise update', 'debug');

				} catch (Exception $e) {
					// Reset session
					$klarnaOrder = null;
					//unset($_SESSION['klarna_checkout']);
					$session->clear('klarna_checkout', 'vm');
					$session->clear('klarna_paymentmethod_id_active', 'vm');
				}
			}
			if ($klarnaOrder == null) {
				// Start new session
				$create['purchase_country'] = $this->country_code_2;
				$create['purchase_currency'] = $this->currency_code_3;
				$create['locale'] = $this->_currentMethod->locale;
				$create['merchant']['id'] = $this->_currentMethod->merchantid;
				$create['merchant']['terms_uri'] = $this->getTermsURI($cart->vendorId);
				$create['merchant']['checkout_uri'] = JURI::root() . 'index.php?option=com_virtuemart&view=cart';
				$create['merchant']['confirmation_uri'] = JURI::root() . 'index.php?option=com_virtuemart&view=pluginresponse&task=pluginresponsereceived&t&pm=' . $virtuemart_paymentmethod_id . '&cartId=' . $cartIdInTable . '&klarna_order={checkout.order.uri}';
				// You can not receive push notification on non publicly available uri
				$create['merchant']['push_uri'] = JURI::root() . 'index.php?option=com_virtuemart&view=pluginresponse&task=pluginnotification&tmpl=component&pm=' . $virtuemart_paymentmethod_id . '&cartId=' . $cartIdInTable . '&klarna_order={checkout.order.uri}';
				if (!empty($cart->BT['email'])) {
					$create['shipping_address']['email'] = $cart->BT['email'];
					$hide_BTST = false;
					$address = (($cart->ST == 0) ? $cart->BT : $cart->ST);
					if (isset($address['zip']) and !empty($address['zip'])) {
						$create['shipping_address']['postal_code'] = $cart->BT['zip'];
					}
				}

				$create['cart']['items'] = $this->getCartItems($cart);
				try {
					$klarnaOrder = new Klarna_Checkout_Order($connector);
					$klarnaOrder->create($create);
					$klarnaOrder->fetch();
					$this->debugLog($create, 'plgVmOnCheckoutAdvertise create', 'debug');

				} catch (Exception $e) {
					$session->clear('klarna_checkout', 'vm');
					$session->clear('klarna_paymentmethod_id_active', 'vm');
					$admin_msg = $e->getMessage();
					vmError($admin_msg, vmText::sprintf('VMPAYMENT_KLARNACHECKOUT_ERROR_OCCURRED', $method->payment_name));
					$this->debugLog($admin_msg, 'plgVmOnCheckoutAdvertise', 'error');
					$this->debugLog($create, 'plgVmOnCheckoutAdvertise', 'error');

					return NULL;
				}


			}


			$session->set('klarna_checkout', $klarnaOrder->getLocation(), 'vm');
			$session->set('klarna_paymentmethod_id_active', $virtuemart_paymentmethod_id, 'vm');
// Display checkout
			$snippet = $klarnaOrder['gui']['snippet'];

// DESKTOP: Width of containing block shall be at least 750px
// MOBILE: Width of containing block shall be 100% of browser window (No
// padding or margin)
		}
		$payment_advertise[] = $this->renderByLayout('cart_advertisement', array(
			'snippet' => $snippet,
			'message' => $message,
			'hide_BTST' => $hide_BTST,
		));


	}

	/**
	 * cf https://docs.klarna.com/en/rest-api#supported_locales
	 * @param $method
	 */
	function initKlarnaParams() {


		$return = true;
		$db = JFactory::getDBO();
		$q = 'SELECT ' . $db->escape('country_2_code') . '  , ' . $db->escape('country_3_code') . ' FROM `#__virtuemart_countries` WHERE virtuemart_country_id = ' . (int)$this->_currentMethod->purchase_country;
		$db->setQuery($q);
		$country = $db->loadObject();
		if (!$country) {
			vmError('Klarna Checkout: No country has been found with country id=' . $this->_currentMethod->purchase_country, vmText::sprintf('VMPAYMENT_KLARNACHECKOUT_ERROR_OCCURRED', $this->_currentMethod->payment_name));
			$this->debugLog('No country has been found with country id=' . $this->_currentMethod->purchase_country, 'initKlarnaParams', 'error');

			$return = false;
		}
		$this->country_code_2 = $country->country_2_code;
		$this->country_code_3 = $country->country_3_code;

		$this->getPaymentCurrency($this->_currentMethod);
		$this->currency_code_3 = shopFunctions::getCurrencyByID($this->_currentMethod->payment_currency, 'currency_code_3');
		if (!$this->currency_code_3) {
			vmError('Klarna Checkout: No currency has been found with currency id=' . $this->_currentMethod->payment_currency, vmText::sprintf('VMPAYMENT_KLARNACHECKOUT_ERROR_OCCURRED', $this->_currentMethod->payment_name));
			$this->debugLog('No currency has been found with currency id=' . $this->_currentMethod->payment_currency, 'initKlarnaParams', 'error');

			$return = false;
		}
		if (empty($this->_currentMethod->sharedsecret) or empty($this->_currentMethod->merchantid)) {
			vmError('Klarna Checkout: Missing mandatory values merchant id=' . $this->_currentMethod->merchantid . ' shared secret=' . $this->_currentMethod->sharedsecret, vmText::sprintf('VMPAYMENT_KLARNACHECKOUT_ERROR_OCCURRED', $this->_currentMethod->payment_name));
			$this->debugLog('Missing mandatory values merchant id=' . $this->_currentMethod->merchantid . ' shared secret=' . $this->_currentMethod->sharedsecret, 'initKlarnaParams', 'error');

			$return = false;
		}

		if ($this->_currentMethod->server == 'beta') {
			$this->mode = Klarna::BETA;
		} else {
			$this->mode = Klarna::LIVE;
		}
		$this->ssl = KlarnaHandler::getKlarnaSSL($this->mode);

		return $return;
	}


	function getTermsURI($vendorId) {

		return JURI::root() . 'index.php?option=com_virtuemart&view=vendor&layout=tos&virtuemart_vendor_id=' . $vendorId . '&lang=' . vRequest::getCmd('lang', '');;

	}

	/** Insert or Update the cart content in the  table
	 * will be used by the push notification to retrieve the cart and save the order
	 * @param $cart
	 */

	function storeCartInTable($cart, $cartId = 0, $dbValues = array()) {

		if (empty($dbValues)) {
			$dbValues['order_number'] = '';
			$dbValues['payment_name'] = '';
			$dbValues['virtuemart_paymentmethod_id'] = $cart->virtuemart_paymentmethod_id;
			$dbValues['action'] = 'storeCart';
			$dbValues['klarna_status'] = 'pre-purchase';
		}

		if (empty($cartId)) {
			$session = JFactory::getSession();
			$cartIdInTable = $session->get('cartId', 0, 'vm');
			$dbValues['data'] = json_encode($cart);
			//$dbValues['data'] = ($cart);
			$preload = false;
		} else {
			$cartIdInTable = $cartId;
			//$dbValues['data'] = $this->getCartFromTable($cartId, true);
			$dbValues['data'] = json_encode($cart);
			$preload = true;
		}
		$dbValues['format'] = 'json';
		$dbValues ['id'] = $cartIdInTable;
		$this->debugLog($dbValues, 'storePSPluginInternalData storeCartInTable', 'debug');

		//$values = $this->storePSPluginInternalData($dbValues, $this->_tablepkey, $preload);
		$values = $this->storePluginInternalData($dbValues, $this->_tablepkey, 0, $preload);

		if (empty($cartId)) {
			$session->set('cartId', $values ['id'], 'vm');
		}
		return $values ['id'];

	}

	/** get  the cart saved in the cart table
	 *  used by the push notification to retrieve the cart and save the order
	 * @param $cart
	 */

	function getDataPaymentFromTable($cartId) {

		$db = JFactory::getDBO();
		$q = 'SELECT * FROM `' . $this->_tablename . '` ' . 'WHERE `id` = ' . $cartId . ' AND `action` = "storeCart"';

		$db->setQuery($q);
		$result = $db->loadObject();

		return $result;


	}

	/** get  the cart saved in the cart table
	 *  used by the push notification to retrieve the cart and save the order
	 * @param $cart
	 */

	function getCartFromTable($cartId, $encoded = false) {

		$db = JFactory::getDBO();
		$q = 'SELECT * FROM `' . $this->_tablename . '` ' . 'WHERE `id` = ' . $cartId . ' AND `action` = "storeCart"';

		$db->setQuery($q);
		$result = $db->loadObject();

		if ($encoded) {
			$data = $result->data;
		} else {
			$data = (object)json_decode( $result->data ,true);
		}

		return $data;


	}

	/**
	 * get  the cart saved in the cart table
	 *  used by the push notification to retrieve the cart and save the order
	 */

	function clearCartFromTable() {

	}



	protected function renderPluginName($method, $where = 'checkout') {

		$payment_logo = "";

		if ($method->payment_logos) {
			$payment_logo = '<img src="https://cdn.klarna.com/public/images/SE/logos/v1/basic/SE_basic_logo_std_blue-black.png?width=100&" /> ';

		}
		$payment_name = $method->payment_name;
		$html = $this->renderByLayout('render_pluginname', array(
			'where' => $where,
			'logo' => $payment_logo,
			'payment_name' => $payment_name,
			'payment_description' => $method->payment_desc,
		));

		return $html;
	}

	/**
	 * This is for checking the input data of the payment method within the checkout
	 *
	 * @author Valerie Cartan Isaksen
	 */
	function plgVmOnCheckoutCheckDataPayment(VirtueMartCart $cart) {

		if (!$this->selectedThisByMethodId($cart->virtuemart_paymentmethod_id)) {
			return NULL; // Another method was selected, do nothing
		}

		if (!($this->_currentMethod = $this->getVmPluginMethod($cart->virtuemart_paymentmethod_id))) {
			return FALSE;
		}

		return true;
	}


	/**
	 * @return bool|null
	 */
	/**
	 * @param $html
	 * @return bool|null|string
	 */
	function plgVmOnPaymentResponseReceived(&$html) {

		if (!class_exists('VirtueMartCart')) {
			require(VMPATH_SITE . DS . 'helpers' . DS . 'cart.php');
		}
		if (!class_exists('shopFunctionsF')) {
			require(VMPATH_SITE . DS . 'helpers' . DS . 'shopfunctionsf.php');
		}
		if (!class_exists('VirtueMartModelOrders')) {
			require(VMPATH_ADMIN . DS . 'models' . DS . 'orders.php');
		}
		require_once 'klarnacheckout/library/Checkout.php';
		$virtuemart_paymentmethod_id = vRequest::getInt('pm', 0);
		if (!($this->_currentMethod = $this->getVmPluginMethod($virtuemart_paymentmethod_id))) {
			return NULL; // Another method was selected, do nothing
		}
		if (!$this->selectedThisElement($this->_currentMethod->payment_element)) {
			return NULL;
		}
		$session = JFactory::getSession();
		Klarna_Checkout_Order::$contentType = "application/vnd.klarna.checkout.aggregated-order-v2+json";
		$this->initKlarnaParams($this->_currentMethod);
		$connector = Klarna_Checkout_Connector::create($this->_currentMethod->sharedsecret);

		//$checkoutId = $_SESSION['klarna_checkout'];
		$checkoutId = $session->get('klarna_checkout', 0, 'vm');
		if (empty($checkoutId)) {
			vmError('Missing klarna_checkout in session', 'Missing klarna_checkout in session', vmText::sprintf('VMPAYMENT_KLARNACHECKOUT_ERROR_OCCURRED', $this->_currentMethod->payment_name));
			$this->debugLog('Missing klarna_checkout in session', 'plgVmOnPaymentResponseReceived', 'error');

			return NULL;
		}
		$order = new Klarna_Checkout_Order($connector, $checkoutId);
		$order->fetch();

		if ($order['status'] == 'checkout_incomplete') {
			$app = JFactory::getApplication();
			$app->redirect(JRoute::_('index.php?option=com_virtuemart&view=cart', false), vmText::_('VMPAYMENT_KLARNACHECKOUT_INCOMPLETE'));
		}

		$snippet = $order['gui']['snippet'];
// DESKTOP: Width of containing block shall be at least 750px
// MOBILE: Width of containing block shall be 100% of browser window (No
// padding or margin)
		//$html .= var_export($order->_data);

		$html = $this->renderByLayout('response_received', array(
			'snippet' => $snippet,
		));


		//unset($_SESSION['klarna_checkout']);
		$session->clear('klarna_checkout', 'vm');
		$session->clear('cartId', 'vm');

		// let's do


		//We delete the old stuff
		// get the correct cart / session
		$cart = VirtueMartCart::getCart();
		$cart->emptyCart();
		return TRUE;
	}


	/*
		 *   plgVmOnPaymentNotification() - This event is fired by Offline Payment. It can be used to validate the payment data as entered by the user.
		 * Return:
		 * Parameters:
		 *  None
		 *  @author Valerie Isaksen
		 */

	/**
	 * @return bool|null
	 */
	function plgVmOnPaymentNotification() {

		$virtuemart_paymentmethod_id = vRequest::getInt('pm', '');
		$checkoutId = vRequest::getString('klarna_order', '');
		$cartId = vRequest::getString('cartId', '');


		if (empty($virtuemart_paymentmethod_id) or !$this->selectedThisByMethodId($virtuemart_paymentmethod_id) or empty($checkoutId) or empty($cartId)) {
			return NULL;
		}
		if (!($this->_currentMethod = $this->getVmPluginMethod($virtuemart_paymentmethod_id))) {
			return NULL; // Another method was selected, do nothing
		}

		if (!($cartDataFromTable = $this->getCartFromTable($cartId))) {
			$this->debugLog('No cart with this  Id=' . $cartId, 'plgVmOnPaymentNotification', 'error');
			return NULL; // No cart with this  Id
		}
		$this->debugLog($cartDataFromTable, 'plgVmOnPaymentNotification getCartFromTable', 'debug');

		require_once 'klarnacheckout/library/Checkout.php';
		Klarna_Checkout_Order::$contentType = "application/vnd.klarna.checkout.aggregated-order-v2+json";

		$this->initKlarnaParams();
		$connector = Klarna_Checkout_Connector::create($this->_currentMethod->sharedsecret);
		$klarna_order = new Klarna_Checkout_Order($connector, $checkoutId);
		$klarna_order->fetch();
		$this->debugLog(var_export($klarna_order, true), 'plgVmOnPaymentNotificationafter fetch', 'debug');
		if ($klarna_order['status'] != "checkout_complete") {
			$this->debugLog($klarna_order, 'plgVmOnPaymentNotification Klarna_Checkout_Order', 'error');
			return NULL;
		}
		// At this point make sure the order is created in your system and send a
		// confirmation email to the customer

		$vmOrderNumber = $this->createVmOrder($klarna_order, $cartDataFromTable, (int)$cartId);
		// update Order status
		$update['status'] = 'created';
		$update['merchant_reference'] = array(
			'orderid1' => $vmOrderNumber
		);

		$klarna_order->update($update);
		if (!class_exists('VirtueMartModelOrders')) {
			require(VMPATH_ADMIN . DS . 'models' . DS . 'orders.php');
		}
		$values['virtuemart_order_id'] = VirtueMartModelOrders::getOrderIdByOrderNumber($vmOrderNumber);
		$dbValues = array(
			'virtuemart_order_id' => VirtueMartModelOrders::getOrderIdByOrderNumber($vmOrderNumber),
			'order_number' => $vmOrderNumber,
			'virtuemart_paymentmethod_id' => $this->_currentMethod->virtuemart_paymentmethod_id,
			'payment_name'                => $this->renderPluginName($this->_currentMethod, 'create_order'),
			'action'                      => 'update',
			'klarna_status'               => $update['status'],
			'format'                      => 'json',
			'data'                        => json_encode($update)

		);
		$this->debugLog($dbValues, 'plgVmOnPaymentNotification update', 'debug');
		//$this->storePSPluginInternalData($dbValues );
		$return = $this->storePluginInternalData($dbValues, 0, 0, false);
		$this->debugLog($return, 'plgVmOnPaymentNotification RETURN', 'debug');


	}

	/**
	 * Create the VM order with the saved cart, and the users infos from klarna
	 * @param $klarna_order return data from Klarna
	 * @param $cartData cart unserialized saved in the table
	 * @param $method
	 * @param $cartId pkey of the cart saved in the table
	 *
	 */
	function createVmOrder($klarna_order, $cartData, $cartId) {

		if (!class_exists('VirtueMartCart')) {
			require(VMPATH_SITE . DS . 'helpers' . DS . 'cart.php');
		}
		if (!class_exists('shopFunctionsF')) {
			require(VMPATH_SITE . DS . 'helpers' . DS . 'shopfunctionsf.php');
		}
		if (!class_exists('VirtueMartModelOrders')) {
			require(VMPATH_ADMIN . DS . 'models' . DS . 'orders.php');
		}

		if (!class_exists('VirtueMartCart')) {
			require(VMPATH_SITE . DS . 'helpers' . DS . 'cart.php');
		}

		$cartData->_confirmDone = true;
		$cartData->_dataValidated = true;
		$this->debugLog($cartData, 'getCart $cartData', 'debug');

		$cart = VirtueMartCart::getCart(false, array(), $cartData);
		$this->debugLog($cart, 'getCart', 'debug');

		$this->updateBTSTAddressInCart($cart, $klarna_order);
		$cart->prepareCartData();
		$orderId = $cart->confirmedOrder();
		$this->debugLog($orderId, 'createVmOrder', 'debug');

		if (!class_exists('VirtueMartModelOrders')) {
			require(VMPATH_ADMIN . DS . 'models' . DS . 'orders.php');
		}
		$modelOrder = VmModel::getModel('orders');
		$order_number = VirtueMartModelOrders::getOrderNumber($orderId);
		$history = array();
		$history['customer_notified'] = 1;
		$history['order_status'] = $this->method->status_checkout_complete;
		$history['comments'] = vmText::sprintf('VMPAYMENT_KLARNACHECKOUT_PAYMENT_STATUS_CHECKOUT_COMPLETE', $order_number);
		$modelOrder->updateStatusForOneOrder($orderId, $history, TRUE);
		$this->debugLog('', 'AFTER updateStatusForOneOrder', 'debug');

		$klarna_data = $this->getKlarnaData($klarna_order);

		//$this->debugLog('plgVmOnPaymentNotification KLARNA DATA ' . var_export($klarna_data, true), 'message');
		$order_number = VirtueMartModelOrders::getOrderNumber($orderId);

		$dbValues = array(
			'virtuemart_order_id' => $orderId,
			'order_number' => $order_number,
			'virtuemart_paymentmethod_id' => $this->method->virtuemart_paymentmethod_id,
			'payment_name' => $this->renderPluginName($this->method, 'create_order'),
			'action' => 'createOrder',
			'klarna_status' => $klarna_order['status'],
			//'data'                        => ($klarna_data),
			'format'                        => 'json',
			'data'                        => json_encode($klarna_data),

		);
		$this->debugLog($dbValues, 'storePSPluginInternalData createVmOrder', 'debug');

		$this->storePSPluginInternalData($dbValues);

		$dbValues = array(
			'virtuemart_order_id' => $orderId,
			'order_number' => VirtueMartModelOrders::getOrderNumber($orderId),
			'virtuemart_paymentmethod_id' => $this->method->virtuemart_paymentmethod_id,
			'payment_name'                => 'Klarna Checkout',
			'action'                      => 'storeCart',
			'format'                        => 'json',
			'klarna_status'               => 'pre-purchase',
		);
		$this->storeCartInTable($cartData, $cartId, $dbValues);
		return $order_number;

	}

	function updateBTSTAddressInCart($cart, $klarna_order) {

		 $this->updateAddressInCart($cart, $klarna_order['billing_address'], 'BT');
		 $this->updateAddressInCart($cart, $klarna_order['shipping_address'], 'ST');


	}

	function updateAddressInCart($cart, $klarna_address, $address_type) {


		if ($address_type == 'BT') {
			$prefix = '';
			$vmAddress = $cart->BT;
		} else {
			$prefix = 'shipto_';
			$vmAddress = $cart->ST;
		}

		// Update the Shipping Address to what is specified in the register.
		$update_data = array(
			$prefix . 'address_type_name' => 'klarnacheckout',
			$prefix . 'company' => $klarna_address['company_name'],
			$prefix . 'first_name' => $klarna_address['given_name'],
			$prefix . 'last_name' => $klarna_address['family_name'],
			$prefix . 'address_1' => $klarna_address['street_address'],
			$prefix . 'zip' => $klarna_address['postal_code'],
			$prefix . 'city' => $klarna_address['city'],
			$prefix . 'virtuemart_country_id' => shopFunctions::getCountryIDByName($klarna_address['country']),
			$prefix . 'state' => '',
			$prefix . 'phone_1' => $klarna_address['phone'],
			'address_type' => $address_type
		);
		if ($address_type == 'BT') {
			$update_data ['email'] = $klarna_address['email'];
		}

		if (!empty($st)) {
			$update_data = array_merge($vmAddress, $update_data);
		}
		$cart->saveAddressInCart($update_data, $update_data['address_type'], FALSE);
	}

	function getKlarnaData($klarna_order) {
		$push_params = $this->getKlarnaDisplayParams();
		foreach ($push_params as $key => $value) {
			$klarna_data[$key] = $klarna_order[$key];
		}

		return $klarna_data;

	}

	function getKlarnaDisplayParams() {
		return array(
			'id' => 'debug',
			'purchase_country' => 'display',
			'purchase_currency' => 'display',
			'locale' => 'debug',
			'status' => 'display',
			'reference' => 'display',
			'reservation' => 'display',
			'started_at' => 'debug',
			'completed_at' => 'debug',
			'last_modified_at' => 'debug',
			'expires_at' => 'debug',
			'cart' => 'debug',
			'customer' => 'debug',
			'shipping_address' => 'debug',
			'billing_address' => 'debug',
			'options' => 'debug',
			'merchant' => 'debug',
		);
	}

	/**
	 * @param $virtuemart_paymentmethod_id
	 * @param $paymentCurrencyId
	 * @return bool|null
	 */
	function plgVmgetEmailCurrency($virtuemart_paymentmethod_id, $virtuemart_order_id, &$emailCurrencyId) {

		if (!($this->method = $this->getVmPluginMethod($virtuemart_paymentmethod_id))) {
			return NULL; // Another method was selected, do nothing
		}
		if (!$this->selectedThisElement($this->method->payment_element)) {
			return FALSE;
		}
		if (!($payments = $this->getDatasByOrderId($virtuemart_order_id))) {
			return '';
		}
		if (empty($payments[0]->email_currency)) {
			$vendorId = 1; //VirtueMartModelVendor::getLoggedVendor();
			$db = JFactory::getDBO();
			$q = 'SELECT   `vendor_currency` FROM `#__virtuemart_vendors` WHERE `virtuemart_vendor_id`=' . $vendorId;
			$db->setQuery($q);
			$emailCurrencyId = $db->loadResult();
		} else {
			$emailCurrencyId = $payments[0]->email_currency;
		}

	}

	/**
	 * @param $virtuemart_paymentmethod_id
	 * @param $paymentCurrencyId
	 * @return bool|null
	 */
	function plgVmgetPaymentCurrency($virtuemart_paymentmethod_id, &$paymentCurrencyId) {

		if (!($this->method = $this->getVmPluginMethod($virtuemart_paymentmethod_id))) {
			return NULL; // Another method was selected, do nothing
		}
		if (!$this->selectedThisElement($this->method->payment_element)) {
			return FALSE;
		}
		$this->getPaymentCurrency($this->method);
		$paymentCurrencyId = $this->method->payment_currency;
	}

	/**
	 * Display stored payment data for an order
	 * @param  int $virtuemart_order_id
	 * @param  int $payment_method_id
	 * @see components/com_virtuemart/helpers/vmPSPlugin::plgVmOnShowOrderBEPayment()
	 */
	function plgVmOnShowOrderBEPayment($virtuemart_order_id, $payment_method_id) {
		if (!$this->selectedThisByMethodId($payment_method_id)) {
			return NULL; // Another method was selected, do nothing
		}
		if (!($this->_currentMethod = $this->getVmPluginMethod($payment_method_id))) {
			return NULL; // Another method was selected, do nothing
		}
		if (!($payments = $this->getDatasByOrderId($virtuemart_order_id))) {
			// JError::raiseWarning(500, $db->getErrorMsg());
			return '';
		}

		$html = '<table class="adminlist table">' . "\n";
		$html .= $this->getHtmlHeaderBE();
		$first = TRUE;
		if ($this->method->debug) {
			$html .= '<tr class="row1"><td></td><td align="left">' . vmText::_('VMPAYMENT_KLARNACHECKOUT_ORDER_BE_WARNING') . '</td></tr>';

		}
		foreach ($payments as $payment) {
			$display_action = 'onShowOrderBE_' . $payment->action;
			$row_html = $this->$display_action($payment);
			if ($row_html) {
				$html .= '<tr class="row1"><td><strong>' . vmText::_('VMPAYMENT_KLARNACHECKOUT_DATE') . '</strong></td><td align="left"><strong>' . $payment->created_on . '</strong></td></tr>';
				$html .= $row_html;
			}
		}
		$html .= '</table>' . "\n";
		return $html;

	}

	function onShowOrderBE_activate($payment) {
		if (!class_exists('VirtueMartModelOrders')) {
			require(VMPATH_ADMIN . DS . 'models' . DS . 'orders.php');
		}
		$html = $this->getHtmlRowBE(vmText::_('VMPAYMENT_KLARNACHECKOUT_STATUS'), $payment->klarna_status);
		$activate_data =  $this-> getStoredData($payment);

		$html .= $this->getHtmlRowBE(vmText::_('VMPAYMENT_KLARNACHECKOUT_INVOICE_NUMBER'), $activate_data->InvoiceNumber);
		if (!empty($activate_data->InvoicePdf)) {

			$virtuemart_order_id = VirtueMartModelOrders::getOrderIdByOrderNumber($payment->order_number);
			$invoicePdfLink = $this->getInvoicePdfLink($virtuemart_order_id);
			$value = '<a target="_blank" href="' . $invoicePdfLink . '">' . vmText::_('VMPAYMENT_KLARNACHECKOUT_VIEW_INVOICE') . '</a>';

			$html .= $this->getHtmlRowBE("", $value);
		}
		return $html;
	}

	function onShowOrderBE_update($payment) {
		$html = $this->getHtmlRowBE(vmText::_('VMPAYMENT_KLARNACHECKOUT_STATUS'), $payment->klarna_status);

		return $html;

	}

	function onShowOrderBE_cancelReservation($payment) {

		return $this->getHtmlRowBE(vmText::_('VMPAYMENT_KLARNACHECKOUT_STATUS'), $payment->klarna_status);
	}

	/**
	 * @param $type
	 * @param $name
	 * @param $render
	 */
	function plgVmOnSelfCallBE($type, $name, &$render) {
		if ($name != $this->_name || $type != 'vmpayment') {
			return FALSE;
		}
		// fetches PClasses From XML file
		$call = vRequest::getWord('call');
		$this->$call();
		// 	jexit();
	}

	function onShowOrderBE_createOrder($payment) {

		if ($this->method->debug) {
			$show_fields = array("display", "debug");
		} else {
			$show_fields = array("display");
		}
		if (empty($payment->data)) {
			$html = "<tr>\n<td class='key' >" . vmText::_('id') . "</td>\n <td align='left'>" . 'ERROR NO DATA' . "</td>\n</tr>\n";

		} else {
			$klarna_order = $this-> getStoredData($payment);
			//$klarna_order =  ($payment->data);
			$push_params = $this->getKlarnaDisplayParams();
			$html = '';
			$lang = JFactory::getLanguage();
			foreach ($push_params as $key => $value) {
				if (in_array($value, $show_fields)) {
					$display_value = isset($klarna_order->$key) ? $klarna_order->$key : "???";
					$text_key = strtoupper('VMPAYMENT_KLARNACHECKOUT_' . $key);
					if ($lang->hasKey($text_key)) {
						$text = vmText::_('VMPAYMENT_KLARNACHECKOUT_' . $key);
					} else {
						$text = $key;
					}
					if (!is_array($display_value)) {
						$html .= "<tr>\n<td class='key' >" . $text . "</td>\n <td align='left'>" . $display_value . "</td>\n</tr>\n";
					} else {
						$html .= "<tr>\n<td class='key' ><strong>" . $text . "</strong></td>\n <td align='left'></td>\n</tr>\n";

						foreach ($klarna_order[$key] as $order_key => $order_value) {
							$text_key = strtoupper('VMPAYMENT_KLARNACHECKOUT_' . $order_key);

							if ($lang->hasKey($text_key)) {
								$text = vmText::_('VMPAYMENT_KLARNACHECKOUT_' . $order_key);
							} else {
								$text = $order_key;
							}
							if (!is_array($order_value)) {
								$display_order_value = isset($klarna_order[$key][$order_key]) ? $klarna_order[$key][$order_key] : "????";
								$html .= "<tr>\n<td class='key' >" . $text . "</td>\n <td align='left'>" . $display_order_value . "</td>\n</tr>\n";
							} else {
								$html .= "<tr>\n<td class='key' >" . $text . "</td>\n <td align='left'><pre>" . var_export($klarna_order[$key][$order_key], true) . "</pre></td>\n</tr>\n";
							}
						}
					}

				}
			}
		}

		return $html;

	}


	function getStoredData($payment) {
		if ($payment->format='json') {
			$data = (object)json_decode($payment->data, true);
		} else {
			$data =   unserialize($payment->data);
		}
		return $data;
	}
	/**
	 * Can be usefull for debugging
	 * @param $payment
	 * @return string
	 */
	function onShowOrderBE_storeCart($payment) {
		$html = '';

		if ($this->method->debug) {
			if (!class_exists('VirtueMartCart')) {
				require(VMPATH_SITE . DS . 'helpers' . DS . 'cart.php');
			}
			$cart = VirtueMartCart::getCart(false, array(), $payment->data);
			$html = "<tr>\n<td class='key'>" . vmText::_('storeCart') . "</td>\n <td align='left'><pre>" . var_export($cart->products, true) . "</pre></td>\n</tr>\n";
		}
		return $html;

	}


	/**
	 * Check if the payment conditions are fulfilled for this payment method
	 *
	 * @author: Valerie Isaksen
	 *
	 * @param $cart_prices: cart prices
	 * @param $payment
	 * @return true: if the conditions are fulfilled, false otherwise
	 *
	 */
	protected function checkConditions($cart, $method, $cart_prices) {

		$this->convert($method);

		$address = $cart->BT;

		$amount = $cart_prices['salesPrice'];
		$amount_cond = ($amount >= $method->min_amount AND $amount <= $method->max_amount
			OR
			($method->min_amount <= $amount AND ($method->max_amount == 0)));

		$countries = array();
		if (!empty($method->purchase_country)) {
			if (!is_array($method->purchase_country)) {
				$countries[0] = $method->purchase_country;
			} else {
				$countries = $method->purchase_country;
			}
		}
		// probably did not gave his BT:ST address
		if (!is_array($address)) {
			$address = array();
			$address['virtuemart_country_id'] = 0;
		}

		if (!isset($address['virtuemart_country_id'])) {
			$address['virtuemart_country_id'] = 0;
		}
		if ((!empty($address) or $address['virtuemart_country_id'] != 0) and in_array($address['virtuemart_country_id'], $countries) || count($countries) == 0) {
			if ($amount_cond) {
				return TRUE;
			}
		} elseif (empty($address) or $address['virtuemart_country_id'] == 0) {
			if ($amount_cond) {
				return TRUE;
			}
		}

		return FALSE;
	}

	/**
	 * @param $method
	 */
	function convert($method) {

		$method->min_amount = (float)$method->min_amount;
		$method->max_amount = (float)$method->max_amount;
	}

	/**
	 * We must reimplement this triggers for joomla 1.7
	 */

	/**
	 * Create the table for this plugin if it does not yet exist.
	 * This functions checks if the called plugin is active one.
	 * When yes it is calling the standard method to create the tables
	 *
	 * @author Valérie Isaksen
	 *
	 */
	function plgVmOnStoreInstallPaymentPluginTable($jplugin_id) {

		return $this->onStoreInstallPluginTable($jplugin_id);
	}


	/**
	 * This event is fired after the payment method has been selected. It can be used to store
	 * additional payment info in the cart.
	 *
	 * @author Valérie isaksen
	 *
	 * @param VirtueMartCart $cart: the actual cart
	 * @return null if the payment was not selected, true if the data is valid, error message if the data is not vlaid
	 *
	 */
	public function plgVmOnSelectCheckPayment(VirtueMartCart $cart, &$msg) {

		if (!$this->selectedThisByMethodId($cart->virtuemart_paymentmethod_id)) {
			return NULL; // Another method was selected, do nothing
		}

		if (!($this->_currentMethod = $this->getVmPluginMethod($cart->virtuemart_paymentmethod_id))) {
			return FALSE;
		}


		return true;
	}



	/**
	 * @param VirtueMartCart $cart
	 * @param array $cart_prices
	 * @param                $cart_prices_name
	 * @return bool|null
	 */

	public function plgVmOnSelectedCalculatePricePayment(VirtueMartCart $cart, array &$cart_prices, &$cart_prices_name) {

		return $this->onSelectedCalculatePrice($cart, $cart_prices, $cart_prices_name);
	}


	/**
	 * plgVmOnCheckAutomaticSelectedPayment
	 * Checks how many plugins are available. If only one, the user will not have the choice. Enter edit_xxx page
	 * The plugin must check first if it is the correct type
	 *
	 * @author Valerie Isaksen
	 * @param VirtueMartCart cart: the cart object
	 * @return null if no plugin was found, 0 if more then one plugin was found,  virtuemart_xxx_id if only one plugin is found
	 *
	 */
	function plgVmOnCheckAutomaticSelectedPayment(VirtueMartCart $cart, array $cart_prices = array(), &$paymentCounter) {

		return $this->onCheckAutomaticSelected($cart, $cart_prices, $paymentCounter);

	}

	/**
	 * This method is fired when showing the order details in the frontend.
	 * It displays the method-specific data.
	 *
	 * @param integer $order_id The order ID
	 * @return mixed Null for methods that aren't active, text (HTML) otherwise
	 * @author Valerie Isaksen
	 */
	public function plgVmOnShowOrderFEPayment($virtuemart_order_id, $virtuemart_paymentmethod_id, &$payment_name) {

		$this->onShowOrderFE($virtuemart_order_id, $virtuemart_paymentmethod_id, $payment_name);
	}


	/**
	 * This method is fired when showing when priting an Order
	 * It displays the the payment method-specific data.
	 *
	 * @param integer $_virtuemart_order_id The order ID
	 * @param integer $method_id  method used for this order
	 * @return mixed Null when for payment methods that were not selected, text (HTML) otherwise
	 * @author Valerie Isaksen
	 */
	function plgVmonShowOrderPrintPayment($order_number, $method_id) {

		return $this->onShowOrderPrint($order_number, $method_id);
	}

	/**
	 * Triggered by updateStatusForOneOrder
	 * When status= pre delivery, possible action CancelReservation or ChangeReservation
	 * When status=  delivery, possible action ActivateReservation
	 * When status=  post delivery, possible action CreditInvoice, Return Amount, CreditPart
	 *
	 * @param array $order order data
	 * @return mixed, True on success, false on failures (the rest of the save-process will be
	 * skipped!), or null when this method is not actived.
	 */
	public function plgVmOnUpdateOrderPayment(&$order, $old_order_status) {
		// get latest info from DB
		/*if (!$this->selectedThisByMethodId($order->virtuemart_paymentmethod_id)) {
			return NULL; // Another method was selected, do nothing
		}*/

		if (!($this->_currentMethod = $this->getVmPluginMethod($order->virtuemart_paymentmethod_id))) {
			return NULL; // Another method was selected, do nothing
		}

		if (!$this->selectedThisElement($this->_currentMethod -> payment_element)) {
			return NULL;
		}

		if (!($payments = $this->getDatasByOrderId($order->virtuemart_order_id))) {
			vmError(vmText::sprintf('VMPAYMENT_KLARNA_ERROR_NO_DATA', $order->virtuemart_order_id), vmText::sprintf('VMPAYMENT_KLARNACHECKOUT_ERROR_OCCURRED', $this->_currentMethod->payment_name));
			$this->debugLog('No klarna data for this order:' . $order->virtuemart_order_id, 'plgVmOnUpdateOrderPayment', 'error');
			$this->debugLog($payments, 'plgVmOnUpdateOrderPayment', 'debug');
			return NULL;
		}

		//plgVmPaymentKlarnaCheckout::includeKlarnaFiles();
		$new_order_status = $order->order_status;
		$lastPayment = end($payments);
		$klarna_status = $lastPayment->klarna_status;
		$actions = array('activate', 'cancelReservation', 'changeReservation', 'creditInvoice');
		foreach ($actions as $action) {
			$status = 'status_' . $action;

			if ($this->_currentMethod->$status == $new_order_status and $this->authorizedAction($klarna_status, $new_order_status, $old_order_status, $action, $this->_currentMethod)) {
				$this->$action($order, $payments);
				return true;
			}
		}
		// may be it is another new order status unknown?
		// TO DO ... how can we disply that when not in push
		vmError(vmText::sprintf('VMPAYMENT_KLARNACHECKOUT_ACTION_NOT_AUTHORIZED', $new_order_status, $lastPayment->klarna_status), vmText::sprintf('VMPAYMENT_KLARNACHECKOUT_ERROR_OCCURRED', $this->_currentMethod->payment_name));
		$this->debugLog(vmText::sprintf('VMPAYMENT_KLARNACHECKOUT_ACTION_NOT_AUTHORIZED', $action, $lastPayment->klarna_status), 'plgVmOnUpdateOrderPayment', 'error');


		// true means plugin call was successfull
		return true;
	}

	function authorizedAction($klarna_status, $new_order_status, $old_order_status, $action) {
		return true;
		if ($old_order_status == $this->method->status_checkout_complete) {
			$authorize = array(
				'cancelReservation' => $this->method->status_cancelReservation,
				'changeReservation' => $this->method->status_changeReservation,
				'activate' => $this->method->status_activate,
			);
			if (in_array($new_order_status, $authorize)) {
				return TRUE;
			}
		} elseif ($old_order_status == $this->method->status_activate) {
			$authorize = array(
				'creditInvoice' => $this->method->status_creditInvoice,
				'returnAmount' => $this->method->status_returnAmount,
				'creditPart' => $this->method->status_creditPart,
			);
			if (in_array($new_order_status, $authorize)) {
				return TRUE;
			}
		}

		return FALSE;
	}

	/**
	 * The following variables are no longer order specific and should be fixed:
	 * pclass, -1 for all Klarna Checkout orders
	 * pno, null for all Klarna Checkout orders
	 * @param $order
	 * @param $method
	 * @param $payments
	 * @return bool
	 */
	function activate($order, $payments) {
		if (!($rno = $this->getReservationNumber($payments))) {
			return; // error already sent
		}
		// TO DO ASK KLARNA ABOUT KLARNA MODE
		//$mode = KlarnaHandler::getKlarnaMode($method,  $this->getPurchaseCountry($method));
		//$ssl = KlarnaHandler::getKlarnaSSL($mode);
		// Instantiate klarna object.
		$this->initKlarnaParams();
		$klarna = new Klarna_virtuemart();
		$klarna->config($this->_currentMethod->merchantid, $this->_currentMethod->sharedsecret, $this->country_code_3, NULL, $this->currency_code_3, $this->mode, VMKLARNA_PC_TYPE, KlarnaHandler::getKlarna_pc_type(), $this->ssl);
		$modelOrder = VmModel::getModel('orders');

		try {
			$return = $klarna->activate($rno);
			if ($return[0] == 'ok') {
				VmInfo(vmText::sprintf('VMPAYMENT_KLARNACHECKOUT_ACTIVATE_RESERVATION', $rno));
				$vm_invoice_name = '';
				$invoice_number = $return[1];
				$invoiceURL = $this->getInvoice($invoice_number, $vm_invoice_name);

				$history = array();
				$history['customer_notified'] = 0;
				$history['order_status'] = $this->method->status_activate;
				$history['comments'] = vmText::sprintf('VMPAYMENT_KLARNACHECKOUT_PAYMENT_STATUS_ACTIVATE', $rno); // $order['details']['BT']->order_number);
				$modelOrder->updateStatusForOneOrder($order->virtuemart_paymentmethod_id, $history, false);
				$dbValues['order_number'] = $payments[0]->order_number;
				$dbValues['payment_name'] = '';
				$dbValues['virtuemart_paymentmethod_id'] = $payments[0]->virtuemart_paymentmethod_id;
				$dbValues['action'] = 'activate';
				$dbValues['klarna_status'] = 'activate';
				$data["InvoiceNumber"] = $invoice_number;
				$data["InvoicePdf"] = $invoiceURL;
				$dbValues['data'] = json_encode($data);
				$dbValues['format'] = 'json';
				$this->debugLog($dbValues, 'storePSPluginInternalData activate', 'debug');

				$values = $this->storePSPluginInternalData($dbValues, $this->_tablepkey);

			} else {
				VmError('activate returned KO', vmText::sprintf('VMPAYMENT_KLARNACHECKOUT_ERROR_OCCURRED', $this->method->payment_name));
			}

		} catch (Exception $e) {
			VmError($e->getMessage(), vmText::sprintf('VMPAYMENT_KLARNACHECKOUT_ERROR_OCCURRED', $this->method->payment_name));
			$this->debugLog($e->getMessage(), 'activate', 'error');

			return FALSE;
		}


		return true;
	}

	/**
	 *
	 */
	function cancelReservation($order, $payments) {
		$rno = $this->getReservationNumber($payments);
		if (!$rno) {
			return; // error already sent
		}
		$this->initKlarnaParams();
		$klarna = new Klarna_virtuemart();
		$klarna->config($this->_currentMethod->merchantid, $this->_currentMethod->sharedsecret, $this->country_code_3, NULL, $this->currency_code_3, $this->mode, VMKLARNA_PC_TYPE, KlarnaHandler::getKlarna_pc_type(), $this->ssl);
		$modelOrder = VmModel::getModel('orders');

		try {
			$result = $klarna->cancelReservation($rno);
			$info = vmText::sprintf('VMPAYMENT_KLARNACHECKOUT_RESERVATION_CANCELED', $rno);
			VmInfo($info);
			$history = array();
			$history['customer_notified'] = 1;
			//$history['order_status'] = $this->method->checkout_complete;
			$history['comments'] = $info; // $order['details']['BT']->order_number);
			$modelOrder->updateStatusForOneOrder($order->virtuemart_paymentmethod_id, $history, TRUE);

			$dbValues['order_number'] = $payments[0]->order_number;
			$dbValues['payment_name'] = '';
			$dbValues['virtuemart_paymentmethod_id'] = $payments[0]->virtuemart_paymentmethod_id;
			$dbValues['action'] = 'cancelReservation';
			$dbValues['klarna_status'] = 'cancelReservation';
			$dbValues['data'] = $info;
			$this->debugLog($dbValues, 'storePSPluginInternalData cancelReservation', 'debug');

			$values = $this->storePSPluginInternalData($dbValues, $this->_tablepkey);

		} catch (Exception $e) {
			$error = $e->getMessage();
			VmError($e->getMessage(), vmText::sprintf('VMPAYMENT_KLARNACHECKOUT_ERROR_OCCURRED', $this->method->payment_name));
			$this->debugLog($e->getMessage(), 'cancelReservation', 'error');

			return FALSE;
		}


		//$dbValues['data'] = $vm_invoice_name;

		return true;
	}

	function changeReservation() {

	}

	function creditInvoice() {

	}

	function creditPart() {

	}

	function getReservationNumber($payments) {
		foreach ($payments as $payment) {
			if ($payment->klarna_status == "checkout_complete") {
				$klarna_order = (object)json_decode($payment->data, true);
				return $klarna_order->reservation;
			}
		}
		vmError('VMPAYMENT_KLARNACHECKOUT_ERROR_NO_RNO', 'VMPAYMENT_KLARNACHECKOUT_ERROR_NO_RNO');
		return null;
	}

	/**
	 * @param $orderDetails
	 */
	function plgVmOnUserOrder(&$orderDetails) {
		if (!($this->_currentMethod = $this->getVmPluginMethod($orderDetails->virtuemart_paymentmethod_id))) {
			return NULL; // Another method was selected, do nothing
		}
		if (!$this->selectedThisElement($this->_currentMethod->payment_element)) {
			return NULL;
		}
		if (!$orderDetails->virtuemart_order_id) {
			return NULL;
		}
		if (!($payments = $this->getDatasByOrderId($orderDetails->virtuemart_order_id))) {
			return NULL;
		}
		$orderDetails->order_number = $this->getReservationNumber($payments);
		return;
	}

	/**
	 * @param $orderDetails
	 * @param $data
	 * @return null
	 */
	function plgVmOnUserInvoice($orderDetails, &$data) {

		if (!($this->method = $this->getVmPluginMethod($orderDetails['virtuemart_paymentmethod_id']))) {
			return NULL; // Another method was selected, do nothing
		}
		if (!$this->selectedThisElement($this->method->payment_element)) {
			return NULL;
		}

		$data['invoice_number'] = 'reservedByPayment_' . $orderDetails['order_number']; // Never send the invoice via email
	}

	/**
	 * Save updated orderline data to the method specific table
	 *
	 * @param array $_formData Form data
	 * @return mixed, True on success, false on failures (the rest of the save-process will be
	 * skipped!), or null when this method is not actived.
	 */
	public function plgVmOnUpdateOrderLine($_formData) {
		return null;
	}

	/**
	 * plgVmOnEditOrderLineBE
	 * This method is fired when editing the order line details in the backend.
	 * It can be used to add line specific package codes
	 *
	 * @param integer $_orderId The order ID
	 * @param integer $_lineId
	 * @return mixed Null for method that aren't active, text (HTML) otherwise

	public function plgVmOnEditOrderLineBE(  $_orderId, $_lineId) {
	return null;
	}
	 */

	/**
	 * This method is fired when showing the order details in the frontend, for every orderline.
	 * It can be used to display line specific package codes, e.g. with a link to external tracking and
	 * tracing systems
	 *
	 * @param integer $_orderId The order ID
	 * @param integer $_lineId
	 * @return mixed Null for method that aren't active, text (HTML) otherwise

	public function plgVmOnShowOrderLineFE(  $_orderId, $_lineId) {
	return null;
	}
	 */
	function plgVmDeclarePluginParamsPaymentVM3( &$data) {
		return $this->declarePluginParams('payment', $data);
	}

	/**
	 * @param $name
	 * @param $id
	 * @param $table
	 * @return bool
	 */
	function plgVmSetOnTablePluginParamsPayment($name, $id, &$table) {

		return $this->setOnTablePluginParams($name, $id, $table);
	}


	static function   getSuccessUrl($order) {
		return JURI::root() . "index.php?option=com_virtuemart&view=pluginresponse&task=pluginresponsereceived&pm=" . $order['details']['BT']->virtuemart_paymentmethod_id . '&on=' . $order['details']['BT']->order_number . "&Itemid=" . vRequest::getInt('Itemid') . '&lang=' . vRequest::getCmd('lang', '');
	}

	static function   getCancelUrl($order) {
		return JURI::root() . "index.php?option=com_virtuemart&view=pluginresponse&task=pluginUserPaymentCancel&pm=" . $order['details']['BT']->virtuemart_paymentmethod_id . '&on=' . $order['details']['BT']->order_number . '&Itemid=' . vRequest::getInt('Itemid') . '&lang=' . vRequest::getCmd('lang', '');
	}

	static function   getNotificationUrl($order_number) {

		return JURI::root() . "index.php?option=com_virtuemart&view=pluginresponse&task=pluginnotification&on=" . $order_number . '&lang=' . vRequest::getCmd('lang', '');
	}

	/**
	 * @return mixed
	 */
	function _getVendorCurrencyId() {

		if (!class_exists('VirtueMartModelVendor')) {
			require(VMPATH_ADMIN . DS . 'models' . DS . 'vendor.php');
		}
		$vendor_id = 1;
		$vendor_currency = VirtueMartModelVendor::getVendorCurrency($vendor_id);
		return $vendor_currency->virtuemart_currency_id;
	}


	/**
	 *
	 */
	static function includeKlarnaFiles() {


		require(VMPATH_ROOT . DS . 'plugins' . DS . 'vmpayment' . DS . 'klarna' . DS . 'klarna' . DS . 'helpers' . DS . 'define.php');

		if (!class_exists('KlarnaHandler')) {
			require(JPATH_VMKLARNAPLUGIN . DS . 'klarna' . DS . 'helpers' . DS . 'klarnahandler.php');
		}
		if (!class_exists('klarna_virtuemart')) {
			require(JPATH_VMKLARNAPLUGIN . DS . 'klarna' . DS . 'helpers' . DS . 'klarna_virtuemart.php');
		}
		require_once(JPATH_VMKLARNAPLUGIN . DS . 'klarna' . DS . 'api' . DS . 'transport' . DS . 'xmlrpc-3.0.0.beta' . DS . 'lib' . DS . 'xmlrpc.inc');
		require_once(JPATH_VMKLARNAPLUGIN . DS . 'klarna' . DS . 'api' . DS . 'transport' . DS . 'xmlrpc-3.0.0.beta' . DS . 'lib' . DS . 'xmlrpc_wrappers.inc');


	}

	/**
	 * @param $klarna_invoice_pdf
	 * @param $vm_invoice_name
	 * @return bool
	 */
	function getInvoice($invoice_number, &$vm_invoice_name) {


		//$klarna_invoice = explode ('/', $klarna_invoice_pdf);
		if ($this->method->server == 'live') {
			$klarna_invoice_name = "https://online.klarna.com/packslips/" . $invoice_number . '.pdf';
		} else {
			$klarna_invoice_name = "https://online.testdrive.klarna.com/packslips/" . $invoice_number . '.pdf';
		}

		$vm_invoice_name = 'klarna_' . $invoice_number . '.pdf';

		return $klarna_invoice_name;
	}

	/**
	 * @return int|null|string
	 */
	function getInvoicePdfLink($virtuemart_order_id) {

		if (!class_exists('VirtueMartModelOrders')) {
			require(VMPATH_ADMIN . DS . 'models' . DS . 'orders.php');
		}

		if (!class_exists('JFile')) {
			require(JPATH_SITE . DS . 'libraries' . DS . 'joomla' . DS . 'filesystem' . DS . 'file.php');
		}


		if (!($payments = $this->getDatasByOrderId($virtuemart_order_id))) {
			return '';
		}
		foreach ($payments as $payment) {
			if ($payment->klarna_status == 'activate') {
				$data =$this->getStoredData($payment);
				$path = VmConfig::get('forSale_path', 0);
				$path .= 'invoices' . DS;
				$fileName =  $data->InvoicePdf;
				break;
			}
		}


		return $fileName;
	}

}


// No closing tag
