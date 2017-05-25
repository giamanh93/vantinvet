<?php
// namespace administrator\components\com_jmap\framework\http;
/**
 * @package JMAP::FRAMEWORK::components::com_jmap
 * @subpackage framework
 * @subpackage http
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('_JEXEC') or die('Restricted access');

/**
 * HTTP transport class interface.
 *
 * @package JMAP::FRAMEWORK::components::com_jmap
 * @subpackage framework
 * @subpackage http
 * @since 1.0
 */
interface JMapHttpTransport {

	/**
	 * Send a request to the server and return a JMapHttpResponse object with the response.
	 *
	 * @param   string   $method     The HTTP method for sending the request.
	 * @param   JUri     $uri        The URI to the resource to request.
	 * @param   mixed    $data       Either an associative array or a string to be sent with the request.
	 * @param   array    $headers    An array of request headers to send with the request.
	 * @param   integer  $timeout    Read timeout in seconds.
	 * @param   string   $userAgent  The optional user agent string to send with the request.
	 *
	 * @return  JMapHttpResponse
	 *
	 * @since   11.3
	 */
	public function request($method, JUri $uri, $data = null, array $headers = null, $timeout = null, $userAgent = null);
}
