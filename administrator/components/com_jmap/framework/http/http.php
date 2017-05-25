<?php
// namespace administrator\components\com_jmap\framework\http;
/**
 * @package JMAP::FRAMEWORK::administrator::components::com_jmap
 * @subpackage framework
 * @subpackage http
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('_JEXEC') or die('Restricted access');

/**
 * HTTP connector client object interface
 *
 * @package JMAP::FRAMEWORK::administrator::components::com_jmap
 * @subpackage framework
 * @subpackage http
 * @since 1.0
 */
interface IJMapHttp {
	/**
	 * Method to send the GET command to the server.
	 *
	 * @param   string  $url      Path to the resource.
	 * @param   array   $headers  An array of name-value pairs to include in the header of the request.
	 *
	 * @return  JMapHttpResponse
	 *
	 * @since 2.0
	 */
	public function get($url, array $headers = null);
	
	/**
	 * Method to send the POST command to the server.
	 *
	 * @param   string  $url      Path to the resource.
	 * @param   mixed   $data     Either an associative array or a string to be sent with the request.
	 * @param   array   $headers  An array of name-value pairs to include in the header of the request.
	 * @param	int 	$timeout
	 * @param	string 	$useragent
	 *
	 * @return  JMapHttpResponse
	 *
	 * @since 1.0
	 */
	public function post($url, $data, array $headers = null, $timeout = null, $userAgent = null);
}

/**
 * HTTP client class.
 *
 * @package JMAP::FRAMEWORK::administrator::components::com_jmap
 * @subpackage framework
 * @subpackage http
 * @since 1.0
 */
class JMapHttp implements IJMapHttp {
	/**
	 * Number of requests placed
	 * @var    Int 
	 * @access protected
	 */
	protected $numRequests;

	/**
	 * @var    JRegistry  Options for the HTTP client
	 * @access protected
	 */
	protected $options;

	/**
	 * @var    JMapHttpTransport  The HTTP transport object to use in sending HTTP requests.
	 * @access protected
	 */
	protected $transport;

	/**
	 * Component params
	 * @var    Object&
	 * @access protected
	 */
	protected $cParams;
	
	/**
	 * Application object
	 * @var    Object&
	 * @access protected
	 */
	protected $app;

	/**
	 * Constructor.
	 *
	 * @param   JMapHttpTransport  $transport  The HTTP transport object.
	 * @param   $cParams Object& Component configuration
	 *
	 * @since 1.0
	 */
	public function __construct(JMapHttpTransport $transport = null, &$cParams = null) {
		$this->numRequests = 0;
		$this->cParams = $cParams;
		$this->app = JFactory::getApplication();

		$this->transport = isset($transport) ? $transport : new JMapHttpTransportSocket($this->options);
	}

	/**
	 * Method to send the OPTIONS command to the server.
	 *
	 * @param   string  $url      Path to the resource.
	 * @param   array   $headers  An array of name-value pairs to include in the header of the request.
	 *
	 * @return  JMapHttpResponse
	 *
	 * @since 1.0
	 */
	public function options($url, array $headers = null) {
		$this->numRequests++;
		return $this->transport->request('OPTIONS', new JUri($url), null, $headers);
	}

	/**
	 * Method to send the HEAD command to the server.
	 *
	 * @param   string  $url      Path to the resource.
	 * @param   array   $headers  An array of name-value pairs to include in the header of the request.
	 *
	 * @return  JMapHttpResponse
	 *
	 * @since 1.0
	 */
	public function head($url, array $headers = null) {
		$this->numRequests++;
		return $this->transport->request('HEAD', new JUri($url), null, $headers);
	}

	/**
	 * Method to send the GET command to the server.
	 *
	 * @param   string  $url      Path to the resource.
	 * @param   array   $headers  An array of name-value pairs to include in the header of the request.
	 *
	 * @return  JMapHttpResponse
	 *
	 * @since 1.0
	 */
	public function get($url, array $headers = null) {
		$this->numRequests++;
		return $this->transport->request('GET', new JUri($url), null, $headers);
	}

	/**
	 * Method to send the POST command to the server.
	 *
	 * @param   string  $url      Path to the resource.
	 * @param   mixed   $data     Either an associative array or a string to be sent with the request.
	 * @param   array   $headers  An array of name-value pairs to include in the header of the request.
	 *
	 * @return  JMapHttpResponse
	 *
	 * @since 1.0
	 */
	public function post($url, $data, array $headers = null, $timeout = null, $userAgent = null) {
		$this->numRequests++;
		return $this->transport->request('POST', new JUri($url), $data, $headers, $timeout, $userAgent);
	}

	/**
	 * Method to send the PUT command to the server.
	 *
	 * @param   string  $url      Path to the resource.
	 * @param   mixed   $data     Either an associative array or a string to be sent with the request.
	 * @param   array   $headers  An array of name-value pairs to include in the header of the request.
	 *
	 * @return  JMapHttpResponse
	 *
	 * @since 1.0
	 */
	public function put($url, $data, array $headers = null) {
		$this->numRequests++;
		return $this->transport->request('PUT', new JUri($url), $data, $headers);
	}

	/**
	 * Method to send the DELETE command to the server.
	 *
	 * @param   string  $url      Path to the resource.
	 * @param   array   $headers  An array of name-value pairs to include in the header of the request.
	 *
	 * @return  JMapHttpResponse
	 *
	 * @since 1.0
	 */
	public function delete($url, array $headers = null) {
		$this->numRequests++;
		return $this->transport->request('DELETE', new JUri($url), null, $headers);
	}

	/**
	 * Method to send the TRACE command to the server.
	 *
	 * @param   string  $url      Path to the resource.
	 * @param   array   $headers  An array of name-value pairs to include in the header of the request.
	 *
	 * @return  JMapHttpResponse
	 *
	 * @since 1.0
	 */
	public function trace($url, array $headers = null) {
		$this->numRequests++;
		return $this->transport->request('TRACE', new JUri($url), null, $headers);
	}

	/**
	 * Check for remaining requests
	 * 
	 * @access public
	 * @return boolean
	 */
	public function isValidRequest() {
		// If unlimited requests, return always true
		if ($this->cParams->get('max_images_requests', 0) == 0) {
			return true;
		}

		// If limited check if remains count
		$limitRequests = $this->cParams->get('max_images_requests');
		if ($this->numRequests < $limitRequests) {
			return true;
		}

		return false;
	}
}
