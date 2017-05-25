<?php
// namespace administrator\components\com_jmap\framework\pinger;
/**
 * @package JMAP::FRAMEWORK::components::com_jmap
 * @subpackage framework
 * @subpackage pinger
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('_JEXEC') or die('Restricted access');

$GLOBALS['xmlrpcI4']='i4';
$GLOBALS['xmlrpcInt']='int';
$GLOBALS['xmlrpcBoolean']='boolean';
$GLOBALS['xmlrpcDouble']='double';
$GLOBALS['xmlrpcString']='string';
$GLOBALS['xmlrpcDateTime']='dateTime.iso8601';
$GLOBALS['xmlrpcBase_64']='base' . '64';
$GLOBALS['xmlrpcArray']='array';
$GLOBALS['xmlrpcStruct']='struct';
$GLOBALS['xmlrpcValue']='undefined';

$GLOBALS['xmlrpcTypes']=array(
	$GLOBALS['xmlrpcI4']       => 1,
	$GLOBALS['xmlrpcInt']      => 1,
	$GLOBALS['xmlrpcBoolean']  => 1,
	$GLOBALS['xmlrpcString']   => 1,
	$GLOBALS['xmlrpcDouble']   => 1,
	$GLOBALS['xmlrpcDateTime'] => 1,
	$GLOBALS['xmlrpcBase_64']   => 1,
	$GLOBALS['xmlrpcArray']    => 2,
	$GLOBALS['xmlrpcStruct']   => 3
);

$GLOBALS['xmlrpc_valid_parents'] = array(
	'VALUE' => array('MEMBER', 'DATA', 'PARAM', 'FAULT'),
	'BOOLEAN' => array('VALUE'),
	'I4' => array('VALUE'),
	'INT' => array('VALUE'),
	'STRING' => array('VALUE'),
	'DOUBLE' => array('VALUE'),
	'DATETIME.ISO8601' => array('VALUE'),
	'BASE' . '64' => array('VALUE'),
	'MEMBER' => array('STRUCT'),
	'NAME' => array('MEMBER'),
	'DATA' => array('ARRAY'),
	'ARRAY' => array('VALUE'),
	'STRUCT' => array('VALUE'),
	'PARAM' => array('PARAMS'),
	'METHODNAME' => array('METHODCALL'),
	'PARAMS' => array('METHODCALL', 'METHODRESPONSE'),
	'FAULT' => array('METHODRESPONSE'),
	'NIL' => array('VALUE') // only used when extension activated
);

$GLOBALS['xmlrpcNull']='null';
$GLOBALS['xmlrpcTypes']['null']=1;
$GLOBALS['xml_iso88591_Entities']=array();
$GLOBALS['xml_iso88591_Entities']['in'] = array();
$GLOBALS['xml_iso88591_Entities']['out'] = array();
for ($i = 0; $i < 32; $i++)
{
	$GLOBALS['xml_iso88591_Entities']['in'][] = chr($i);
	$GLOBALS['xml_iso88591_Entities']['out'][] = '&#'.$i.';';
}
for ($i = 160; $i < 256; $i++)
{
	$GLOBALS['xml_iso88591_Entities']['in'][] = chr($i);
	$GLOBALS['xml_iso88591_Entities']['out'][] = '&#'.$i.';';
}


$GLOBALS['xmlrpcerr'] = array(
'unknown_method'=>1,
'invalid_return'=>2,
'incorrect_params'=>3,
'introspect_unknown'=>4,
'http_error'=>5,
'no_data'=>6,
'no_ssl'=>7,
'curl_fail'=>8,
'invalid_request'=>15,
'no_curl'=>16,
'server_error'=>17,
'multicall_error'=>18,
'multicall_notstruct'=>9,
'multicall_nomethod'=>10,
'multicall_notstring'=>11,
'multicall_recursion'=>12,
'multicall_noparams'=>13,
'multicall_notarray'=>14,

'cannot_decompress'=>103,
'decompress_fail'=>104,
'dechunk_fail'=>105,
'server_cannot_decompress'=>106,
'server_decompress_fail'=>107
);

$GLOBALS['xmlrpcstr'] = array(
'unknown_method'=>'Unknown method',
'invalid_return'=>'Invalid return payload: enable debugging to examine incoming payload',
'incorrect_params'=>'Incorrect parameters passed to method',
'introspect_unknown'=>"Can't introspect: method unknown",
'http_error'=>"Didn't receive 200 OK from remote server.",
'no_data'=>'No data received from server.',
'no_ssl'=>'No SSL support compiled in.',
'curl_fail'=>'CURL error',
'invalid_request'=>'Invalid request payload',
'no_curl'=>'No CURL support compiled in.',
'server_error'=>'Internal server error',
'multicall_error'=>'Received from server invalid multicall response',
'multicall_notstruct'=>'system.multicall expected struct',
'multicall_nomethod'=>'missing methodName',
'multicall_notstring'=>'methodName is not a string',
'multicall_recursion'=>'recursive system.multicall forbidden',
'multicall_noparams'=>'missing params',
'multicall_notarray'=>'params is not an array',
'cannot_decompress'=>'Received from server compressed HTTP and cannot decompress',
'decompress_fail'=>'Received from server invalid compressed HTTP',
'dechunk_fail'=>'Received from server invalid chunked HTTP',
'server_cannot_decompress'=>'Received from client compressed HTTP request and cannot decompress',
'server_decompress_fail'=>'Received from client invalid compressed HTTP request'
);

$GLOBALS['xmlrpc_defencoding']='UTF-8';
$GLOBALS['xmlrpc_internalencoding']='ISO-8859-1';
$GLOBALS['xmlrpcName']='XML-RPC for PHP';
$GLOBALS['xmlrpcVersion']='2.2.2';
$GLOBALS['xmlrpcerruser']=800;
$GLOBALS['xmlrpcerrxml']=100;
$GLOBALS['xmlrpc_backslash']=chr(92).chr(92);
$GLOBALS['xmlrpc_null_extension']=false;
$GLOBALS['_xh']=null;

class jmap_xmlrpc_client
{
		var $path;
		var $server;
		var $port=0;
		var $method='http';
		var $errno;
		var $errstr;
		var $username='';
		var $password='';
		var $authtype=1;
		var $cert='';
		var $certpass='';
		var $cacert='';
		var $cacertdir='';
		var $key='';
		var $keypass='';
		var $verifypeer=true;
		var $verifyhost=1;
		var $no_multicall=false;
		var $proxy='';
		var $proxyport=0;
		var $proxy_user='';
		var $proxy_pass='';
		var $proxy_authtype=1;
		var $cookies=array();
		var $accepted_compression = array();
		var $request_compression = '';
		var $xmlrpc_curl_handle = null;
		/// Wheter to use persistent connections for http 1.1 and https
		var $keepalive = false;
		/// Charset encodings that can be decoded without problems by the client
		var $accepted_charset_encodings = array();
		/// Charset encoding to be used in serializing request. NULL = use ASCII
		var $request_charset_encoding = '';
		var $return_type = 'xmlrpcvals';

		function __construct($path, $server='', $port='', $method='')
		{
			// allow user to specify all params in $path
			if($server == '' and $port == '' and $method == '')
			{
				$parts = parse_url($path);
				$server = $parts['host'];
				$path = isset($parts['path']) ? $parts['path'] : '';
				if(isset($parts['query']))
				{
					$path .= '?'.$parts['query'];
				}
				if(isset($parts['fragment']))
				{
					$path .= '#'.$parts['fragment'];
				}
				if(isset($parts['port']))
				{
					$port = $parts['port'];
				}
				if(isset($parts['scheme']))
				{
					$method = $parts['scheme'];
				}
				if(isset($parts['user']))
				{
					$this->username = $parts['user'];
				}
				if(isset($parts['pass']))
				{
					$this->password = $parts['pass'];
				}
			}
			if($path == '' || $path[0] != '/')
			{
				$this->path='/'.$path;
			}
			else
			{
				$this->path=$path;
			}
			$this->server=$server;
			if($port != '')
			{
				$this->port=$port;
			}
			if($method != '')
			{
				$this->method=$method;
			}

			if(function_exists('gzinflate') || (
				function_exists('curl_init') && (($info = curl_version()) &&
				((is_string($info) && strpos($info, 'zlib') !== null) || isset($info['libz_version'])))
			))
			{
				$this->accepted_compression = array('gzip', 'deflate');
			}

			if(version_compare(phpversion(), '4.3.8') >= 0)
			{
				$this->keepalive = true;
			}

			$this->accepted_charset_encodings = array('UTF-8', 'ISO-8859-1', 'US-ASCII');
		}

		function setCredentials($u, $p, $t=1)
		{
			$this->username=$u;
			$this->password=$p;
			$this->authtype=$t;
		}

		function setCertificate($cert, $certpass)
		{
			$this->cert = $cert;
			$this->certpass = $certpass;
		}

		function setCaCertificate($cacert, $is_dir=false)
		{
			if ($is_dir)
			{
				$this->cacertdir = $cacert;
			}
			else
			{
				$this->cacert = $cacert;
			}
		}

		function setKey($key, $keypass)
		{
			$this->key = $key;
			$this->keypass = $keypass;
		}

		function setSSLVerifyPeer($i)
		{
			$this->verifypeer = $i;
		}

		function setSSLVerifyHost($i)
		{
			$this->verifyhost = $i;
		}

		function setProxy($proxyhost, $proxyport, $proxyusername = '', $proxypassword = '', $proxyauthtype = 1)
		{
			$this->proxy = $proxyhost;
			$this->proxyport = $proxyport;
			$this->proxy_user = $proxyusername;
			$this->proxy_pass = $proxypassword;
			$this->proxy_authtype = $proxyauthtype;
		}

		function setAcceptedCompression($compmethod)
		{
			if ($compmethod == 'any')
				$this->accepted_compression = array('gzip', 'deflate');
			else
				$this->accepted_compression = array($compmethod);
		}

		function setRequestCompression($compmethod)
		{
			$this->request_compression = $compmethod;
		}

		function setCookie($name, $value='', $path='', $domain='', $port=null)
		{
			$this->cookies[$name]['value'] = urlencode($value);
			if ($path || $domain || $port)
			{
				$this->cookies[$name]['path'] = $path;
				$this->cookies[$name]['domain'] = $domain;
				$this->cookies[$name]['port'] = $port;
				$this->cookies[$name]['version'] = 1;
			}
			else
			{
				$this->cookies[$name]['version'] = 0;
			}
		}

		function& send($msg, $timeout=10, $method='')
		{
			// if user deos not specify http protocol, use native method of this client
			// (i.e. method set during call to constructor)
			if($method == '')
			{
				$method = $this->method;
			}

			if(is_array($msg))
			{
				// $msg is an array of jmap_xmlrpcmsg's
				$r = $this->multicall($msg, $timeout, $method);
				return $r;
			}
			elseif(is_string($msg))
			{
				$n = new jmap_xmlrpcmsg('');
				$n->payload = $msg;
				$msg = $n;
			}

			if($method == 'https')
			{
				$r = $this->sendPayloadHTTPS(
					$msg,
					$this->server,
					$this->port,
					$timeout,
					$this->username,
					$this->password,
					$this->authtype,
					$this->cert,
					$this->certpass,
					$this->cacert,
					$this->cacertdir,
					$this->proxy,
					$this->proxyport,
					$this->proxy_user,
					$this->proxy_pass,
					$this->proxy_authtype,
					$this->keepalive,
					$this->key,
					$this->keypass
				);
			}
			elseif($method == 'http11')
			{
				$r = $this->sendPayloadCURL(
					$msg,
					$this->server,
					$this->port,
					$timeout,
					$this->username,
					$this->password,
					$this->authtype,
					null,
					null,
					null,
					null,
					$this->proxy,
					$this->proxyport,
					$this->proxy_user,
					$this->proxy_pass,
					$this->proxy_authtype,
					'http',
					$this->keepalive
				);
			}
			else
			{
				$r = $this->sendPayloadHTTP10(
					$msg,
					$this->server,
					$this->port,
					$timeout,
					$this->username,
					$this->password,
					$this->authtype,
					$this->proxy,
					$this->proxyport,
					$this->proxy_user,
					$this->proxy_pass,
					$this->proxy_authtype
				);
			}

			return $r;
		}

		/**
		* @access private
		*/
		function &sendPayloadHTTP10($msg, $server, $port, $timeout=10,
			$username='', $password='', $authtype=1, $proxyhost='',
			$proxyport=0, $proxyusername='', $proxypassword='', $proxyauthtype=1)
		{
			if($port==0)
			{
				$port=80;
			}

			// Only create the payload if it was not created previously
			if(empty($msg->payload))
			{
				$msg->createPayload($this->request_charset_encoding);
			}

			$payload = $msg->payload;
			// Deflate request body and set appropriate request headers
			if(function_exists('gzdeflate') && ($this->request_compression == 'gzip' || $this->request_compression == 'deflate'))
			{
				if($this->request_compression == 'gzip')
				{
					$a = @gzencode($payload);
					if($a)
					{
						$payload = $a;
						$encoding_hdr = "Content-Encoding: gzip\r\n";
					}
				}
				else
				{
					$a = @gzcompress($payload);
					if($a)
					{
						$payload = $a;
						$encoding_hdr = "Content-Encoding: deflate\r\n";
					}
				}
			}
			else
			{
				$encoding_hdr = '';
			}

			// thanks to Grant Rauscher <grant7@firstworld.net> for this
			$credentials='';
			if($username!='')
			{
				$bas64FunctionNameEncode = 'base'. 64 . '_encode';
				$credentials='Authorization: Basic ' . $bas64FunctionNameEncode($username . ':' . $password) . "\r\n";
			}

			$accepted_encoding = '';
			if(is_array($this->accepted_compression) && count($this->accepted_compression))
			{
				$accepted_encoding = 'Accept-Encoding: ' . implode(', ', $this->accepted_compression) . "\r\n";
			}

			$proxy_credentials = '';
			if($proxyhost)
			{
				if($proxyport == 0)
				{
					$proxyport = 8080;
				}
				$connectserver = $proxyhost;
				$connectport = $proxyport;
				$uri = 'http://'.$server.':'.$port.$this->path;
				if($proxyusername != '')
				{
					$bas64FunctionNameEncode = 'base'. 64 . '_encode';
					$proxy_credentials = 'Proxy-Authorization: Basic ' . $bas64FunctionNameEncode($proxyusername.':'.$proxypassword) . "\r\n";
				}
			}
			else
			{
				$connectserver = $server;
				$connectport = $port;
				$uri = $this->path;
			}

			// Cookie generation, as per rfc2965 (version 1 cookies) or
			// netscape's rules (version 0 cookies)
			$cookieheader='';
			if (count($this->cookies))
			{
				$version = '';
				foreach ($this->cookies as $name => $cookie)
				{
					if ($cookie['version'])
					{
						$version = ' $Version="' . $cookie['version'] . '";';
						$cookieheader .= ' ' . $name . '="' . $cookie['value'] . '";';
						if ($cookie['path'])
							$cookieheader .= ' $Path="' . $cookie['path'] . '";';
						if ($cookie['domain'])
							$cookieheader .= ' $Domain="' . $cookie['domain'] . '";';
						if ($cookie['port'])
							$cookieheader .= ' $Port="' . $cookie['port'] . '";';
					}
					else
					{
						$cookieheader .= ' ' . $name . '=' . $cookie['value'] . ";";
					}
				}
				$cookieheader = 'Cookie:' . $version . substr($cookieheader, 0, -1) . "\r\n";
			}

			$op= 'POST ' . $uri. " HTTP/1.0\r\n" .
				'User-Agent: ' . $GLOBALS['xmlrpcName'] . ' ' . $GLOBALS['xmlrpcVersion'] . "\r\n" .
				'Host: '. $server . ':' . $port . "\r\n" .
				$credentials .
				$proxy_credentials .
				$accepted_encoding .
				$encoding_hdr .
				'Accept-Charset: ' . implode(',', $this->accepted_charset_encodings) . "\r\n" .
				$cookieheader .
				'Content-Type: ' . $msg->content_type . "\r\nContent-Length: " .
				strlen($payload) . "\r\n\r\n" .
				$payload;

			if($timeout>0)
			{
				$fp=@fsockopen($connectserver, $connectport, $this->errno, $this->errstr, $timeout);
			}
			else
			{
				$fp=@fsockopen($connectserver, $connectport, $this->errno, $this->errstr);
			}
			if($fp)
			{
				if($timeout>0 && function_exists('stream_set_timeout'))
				{
					stream_set_timeout($fp, $timeout);
				}
			}
			else
			{
				$this->errstr='Connect error: '.$this->errstr;
				$r=new jmap_xmlrpcresp(0, $GLOBALS['xmlrpcerr']['http_error'], $this->errstr . ' (' . $this->errno . ')');
				return $r;
			}

			if(!fputs($fp, $op, strlen($op)))
			{
    			fclose($fp);
				$this->errstr='Write error';
				$r=new jmap_xmlrpcresp(0, $GLOBALS['xmlrpcerr']['http_error'], $this->errstr);
				return $r;
			}
			else
			{
				// reset errno and errstr on succesful socket connection
				$this->errstr = '';
			}
			$ipd='';
			do
			{
				// shall we check for $data === FALSE?
				// as per the manual, it signals an error
				$ipd.=fread($fp, 32768);
			} while(!feof($fp));
			fclose($fp);
			$r = $msg->parseResponse($ipd, false, $this->return_type);
			return $r;

		}

		/**
		* @access private
		*/
		function &sendPayloadHTTPS($msg, $server, $port, $timeout=10, $username='',
			$password='', $authtype=1, $cert='',$certpass='', $cacert='', $cacertdir='',
			$proxyhost='', $proxyport=0, $proxyusername='', $proxypassword='', $proxyauthtype=1,
			$keepalive=false, $key='', $keypass='')
		{
			$r = $this->sendPayloadCURL($msg, $server, $port, $timeout, $username,
				$password, $authtype, $cert, $certpass, $cacert, $cacertdir, $proxyhost, $proxyport,
				$proxyusername, $proxypassword, $proxyauthtype, 'https', $keepalive, $key, $keypass);
			return $r;
		}

		function &sendPayloadCURL($msg, $server, $port, $timeout=10, $username='',
			$password='', $authtype=1, $cert='', $certpass='', $cacert='', $cacertdir='',
			$proxyhost='', $proxyport=0, $proxyusername='', $proxypassword='', $proxyauthtype=1, $method='https',
			$keepalive=false, $key='', $keypass='')
		{
			if(!function_exists('curl_init'))
			{
				$this->errstr='CURL unavailable on this install';
				$r=new jmap_xmlrpcresp(0, $GLOBALS['xmlrpcerr']['no_curl'], $GLOBALS['xmlrpcstr']['no_curl']);
				return $r;
			}
			if($method == 'https')
			{
				if(($info = curl_version()) &&
					((is_string($info) && strpos($info, 'OpenSSL') === null) || (is_array($info) && !isset($info['ssl_version']))))
				{
					$this->errstr='SSL unavailable on this install';
					$r=new jmap_xmlrpcresp(0, $GLOBALS['xmlrpcerr']['no_ssl'], $GLOBALS['xmlrpcstr']['no_ssl']);
					return $r;
				}
			}

			if($port == 0)
			{
				if($method == 'http')
				{
					$port = 80;
				}
				else
				{
					$port = 443;
				}
			}

			// Only create the payload if it was not created previously
			if(empty($msg->payload))
			{
				$msg->createPayload($this->request_charset_encoding);
			}

			// Deflate request body and set appropriate request headers
			$payload = $msg->payload;
			if(function_exists('gzdeflate') && ($this->request_compression == 'gzip' || $this->request_compression == 'deflate'))
			{
				if($this->request_compression == 'gzip')
				{
					$a = @gzencode($payload);
					if($a)
					{
						$payload = $a;
						$encoding_hdr = 'Content-Encoding: gzip';
					}
				}
				else
				{
					$a = @gzcompress($payload);
					if($a)
					{
						$payload = $a;
						$encoding_hdr = 'Content-Encoding: deflate';
					}
				}
			}
			else
			{
				$encoding_hdr = '';
			}

			if(!$keepalive || !$this->xmlrpc_curl_handle)
			{
				$curl = curl_init($method . '://' . $server . ':' . $port . $this->path);
				if($keepalive)
				{
					$this->xmlrpc_curl_handle = $curl;
				}
			}
			else
			{
				$curl = $this->xmlrpc_curl_handle;
			}

			// results into variable
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

			curl_setopt($curl, CURLOPT_USERAGENT, $GLOBALS['xmlrpcName'].' '.$GLOBALS['xmlrpcVersion']);
			// required for XMLRPC: post the data
			curl_setopt($curl, CURLOPT_POST, 1);
			// the data
			curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);

			// return the header too
			curl_setopt($curl, CURLOPT_HEADER, 1);

			if(is_array($this->accepted_compression) && count($this->accepted_compression))
			{
				//curl_setopt($curl, CURLOPT_ENCODING, implode(',', $this->accepted_compression));
				// empty string means 'any supported by CURL' (shall we catch errors in case CURLOPT_SSLKEY undefined ?)
				if (count($this->accepted_compression) == 1)
				{
					curl_setopt($curl, CURLOPT_ENCODING, $this->accepted_compression[0]);
				}
				else
					curl_setopt($curl, CURLOPT_ENCODING, '');
			}
			// extra headers
			$headers = array('Content-Type: ' . $msg->content_type , 'Accept-Charset: ' . implode(',', $this->accepted_charset_encodings));
			// if no keepalive is wanted, let the server know it in advance
			if(!$keepalive)
			{
				$headers[] = 'Connection: close';
			}
			// request compression header
			if($encoding_hdr)
			{
				$headers[] = $encoding_hdr;
			}

			curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
			// timeout is borked
			if($timeout)
			{
				curl_setopt($curl, CURLOPT_TIMEOUT, $timeout == 1 ? 1 : $timeout - 1);
			}

			if($username && $password)
			{
				curl_setopt($curl, CURLOPT_USERPWD, $username.':'.$password);
				if (defined('CURLOPT_HTTPAUTH'))
				{
					curl_setopt($curl, CURLOPT_HTTPAUTH, $authtype);
				}
			}

			if($method == 'https')
			{
				// set cert file
				if($cert)
				{
					curl_setopt($curl, CURLOPT_SSLCERT, $cert);
				}
				// set cert password
				if($certpass)
				{
					curl_setopt($curl, CURLOPT_SSLCERTPASSWD, $certpass);
				}
				// whether to verify remote host's cert
				curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, $this->verifypeer);
				// set ca certificates file/dir
				if($cacert)
				{
					curl_setopt($curl, CURLOPT_CAINFO, $cacert);
				}
				if($cacertdir)
				{
					curl_setopt($curl, CURLOPT_CAPATH, $cacertdir);
				}
				// set key file (shall we catch errors in case CURLOPT_SSLKEY undefined ?)
				if($key)
				{
					curl_setopt($curl, CURLOPT_SSLKEY, $key);
				}
				// set key password (shall we catch errors in case CURLOPT_SSLKEY undefined ?)
				if($keypass)
				{
					curl_setopt($curl, CURLOPT_SSLKEYPASSWD, $keypass);
				}
				// whether to verify cert's common name (CN); 0 for no, 1 to verify that it exists, and 2 to verify that it matches the hostname used
				curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, $this->verifyhost);
			}

			// proxy info
			if($proxyhost)
			{
				if($proxyport == 0)
				{
					$proxyport = 8080; // NB: even for HTTPS, local connection is on port 8080
				}
				curl_setopt($curl, CURLOPT_PROXY, $proxyhost.':'.$proxyport);
				//curl_setopt($curl, CURLOPT_PROXYPORT,$proxyport);
				if($proxyusername)
				{
					curl_setopt($curl, CURLOPT_PROXYUSERPWD, $proxyusername.':'.$proxypassword);
					if (defined('CURLOPT_PROXYAUTH'))
					{
						curl_setopt($curl, CURLOPT_PROXYAUTH, $proxyauthtype);
					}
				}
			}

			if (count($this->cookies))
			{
				$cookieheader = '';
				foreach ($this->cookies as $name => $cookie)
				{
					$cookieheader .= $name . '=' . $cookie['value'] . '; ';
				}
				curl_setopt($curl, CURLOPT_COOKIE, substr($cookieheader, 0, -2));
			}

			$result = curl_exec($curl);

			if(!$result)
			{
				$this->errstr='no response';
				$resp=new jmap_xmlrpcresp(0, $GLOBALS['xmlrpcerr']['curl_fail'], $GLOBALS['xmlrpcstr']['curl_fail']. ': '. curl_error($curl));
				curl_close($curl);
				if($keepalive)
				{
					$this->xmlrpc_curl_handle = null;
				}
			}
			else
			{
				if(!$keepalive)
				{
					curl_close($curl);
				}
				$resp = $msg->parseResponse($result, true, $this->return_type);
			}
			return $resp;
		}

		function multicall($msgs, $timeout=10, $method='', $fallback=true)
		{
			if ($method == '')
			{
				$method = $this->method;
			}
			if(!$this->no_multicall)
			{
				$results = $this->_try_multicall($msgs, $timeout, $method);
				if(is_array($results))
				{
					// System.multicall succeeded
					return $results;
				}
				else
				{
					if ($fallback)
					{
						// Don't try it next time...
						$this->no_multicall = true;
					}
					else
					{
						if (is_a($results, 'jmap_xmlrpcresp'))
						{
							$result = $results;
						}
						else
						{
							$result = new jmap_xmlrpcresp(0, $GLOBALS['xmlrpcerr']['multicall_error'], $GLOBALS['xmlrpcstr']['multicall_error']);
						}
					}
				}
			}
			else
			{
				$fallback = true;
			}

			$results = array();
			if ($fallback)
			{
				foreach($msgs as $msg)
				{
					$results[] = $this->send($msg, $timeout, $method);
				}
			}
			else
			{
				foreach($msgs as $msg)
				{
					$results[] = $result;
				}
			}
			return $results;
		}

		function _try_multicall($msgs, $timeout, $method)
		{
			// Construct multicall message
			$calls = array();
			foreach($msgs as $msg)
			{
				$call['methodName'] = new jmap_xmlrpcval($msg->method(),'string');
				$numParams = $msg->getNumParams();
				$params = array();
				for($i = 0; $i < $numParams; $i++)
				{
					$params[$i] = $msg->getParam($i);
				}
				$call['params'] = new jmap_xmlrpcval($params, 'array');
				$calls[] = new jmap_xmlrpcval($call, 'struct');
			}
			$multicall = new jmap_xmlrpcmsg('system.multicall');
			$multicall->addParam(new jmap_xmlrpcval($calls, 'array'));

			// Attempt RPC call
			$result = $this->send($multicall, $timeout, $method);

			if($result->faultCode() != 0)
			{
				// call to system.multicall failed
				return $result;
			}

			// Unpack responses.
			$rets = $result->value();

			if ($this->return_type == 'xml')
			{
					return $rets;
			}
			else if ($this->return_type == 'phpvals')
			{
				$rets = $result->value();
				if(!is_array($rets))
				{
					return false;		// bad return type from system.multicall
				}
				$numRets = count($rets);
				if($numRets != count($msgs))
				{
					return false;		// wrong number of return values.
				}

				$response = array();
				for($i = 0; $i < $numRets; $i++)
				{
					$val = $rets[$i];
					if (!is_array($val)) {
						return false;
					}
					switch(count($val))
					{
						case 1:
							if(!isset($val[0]))
							{
								return false;		// Bad value
							}
							$response[$i] = new jmap_xmlrpcresp($val[0], 0, '', 'phpvals');
							break;
						case 2:
							$code = @$val['faultCode'];
							if(!is_int($code))
							{
								return false;
							}
							$str = @$val['faultString'];
							if(!is_string($str))
							{
								return false;
							}
							$response[$i] = new jmap_xmlrpcresp(0, $code, $str);
							break;
						default:
							return false;
					}
				}
				return $response;
			}
			else // return type == 'xmlrpcvals'
			{
				$rets = $result->value();
				if($rets->kindOf() != 'array')
				{
					return false;		// bad return type from system.multicall
				}
				$numRets = $rets->arraysize();
				if($numRets != count($msgs))
				{
					return false;		// wrong number of return values.
				}

				$response = array();
				for($i = 0; $i < $numRets; $i++)
				{
					$val = $rets->arraymem($i);
					switch($val->kindOf())
					{
						case 'array':
							if($val->arraysize() != 1)
							{
								return false;		// Bad value
							}
							// Normal return value
							$response[$i] = new jmap_xmlrpcresp($val->arraymem(0));
							break;
						case 'struct':
							$code = $val->structmem('faultCode');
							if($code->kindOf() != 'scalar' || $code->scalartyp() != 'int')
							{
								return false;
							}
							$str = $val->structmem('faultString');
							if($str->kindOf() != 'scalar' || $str->scalartyp() != 'string')
							{
								return false;
							}
							$response[$i] = new jmap_xmlrpcresp(0, $code->scalarval(), $str->scalarval());
							break;
						default:
							return false;
					}
				}
				return $response;
			}
		}
} // end class jmap_xmlrpc_client

class jmap_xmlrpcresp
{
	var $val = 0;
	var $valtyp;
	var $errno = 0;
	var $errstr = '';
	var $payload;
	var $hdrs = array();
	var $_cooksarray = array();
	var $content_type = 'text/xml';
	var $raw_data = '';

	function __construct($val, $fcode = 0, $fstr = '', $valtyp='')
	{
		if($fcode != 0)
		{
			// error response
			$this->errno = $fcode;
			$this->errstr = $fstr;
			//$this->errstr = htmlspecialchars($fstr); // XXX: encoding probably shouldn't be done here; fix later.
		}
		else
		{
			// successful response
			$this->val = $val;
			if ($valtyp == '')
			{
				// user did not declare type of response value: try to guess it
				if (is_object($this->val) && is_a($this->val, 'jmap_xmlrpcval'))
				{
					$this->valtyp = 'xmlrpcvals';
				}
				else if (is_string($this->val))
				{
					$this->valtyp = 'xml';

				}
				else
				{
					$this->valtyp = 'phpvals';
				}
			}
			else
			{
				// user declares type of resp value: believe him
				$this->valtyp = $valtyp;
			}
		}
	}

	function faultCode()
	{
		return $this->errno;
	}

	function faultString()
	{
		return $this->errstr;
	}

	function value()
	{
		return $this->val;
	}

	function cookies()
	{
		return $this->_cooksarray;
	}

	function serialize($charset_encoding='')
	{
		if ($charset_encoding != '')
			$this->content_type = 'text/xml; charset=' . $charset_encoding;
		else
			$this->content_type = 'text/xml';
		$result = "<methodResponse>\n";
		if($this->errno)
		{
			// G. Giunta 2005/2/13: let non-ASCII response messages be tolerated by clients
			// by xml-encoding non ascii chars
			$result .= "<fault>\n" .
"<value>\n<struct><member><name>faultCode</name>\n<value><int>" . $this->errno .
"</int></value>\n</member>\n<member>\n<name>faultString</name>\n<value><string>" .
jmap_xmlrpc_encode_entitites($this->errstr, $GLOBALS['xmlrpc_internalencoding'], $charset_encoding) . "</string></value>\n</member>\n" .
"</struct>\n</value>\n</fault>";
		}
		else
		{
			if(!is_object($this->val) || !is_a($this->val, 'jmap_xmlrpcval'))
			{
				if (is_string($this->val) && $this->valtyp == 'xml')
				{
					$result .= "<params>\n<param>\n" .
						$this->val .
						"</param>\n</params>";
				}
				else
				{
					die('cannot serialize jmap_xmlrpcresp objects whose content is native php values');
				}
			}
			else
			{
				$result .= "<params>\n<param>\n" .
					$this->val->serialize($charset_encoding) .
					"</param>\n</params>";
			}
		}
		$result .= "\n</methodResponse>";
		$this->payload = $result;
		return $result;
	}
}

class jmap_xmlrpcmsg
{
	var $payload;
	var $methodname;
	var $params=array();
	var $content_type = 'text/xml';

	function __construct($meth, $pars=0)
	{
		$this->methodname=$meth;
		if(is_array($pars) && count($pars)>0)
		{
			for($i=0; $i<count($pars); $i++)
			{
				$this->addParam($pars[$i]);
			}
		}
	}

	function xml_header($charset_encoding='')
	{
		if ($charset_encoding != '')
		{
			return "<?xml version=\"1.0\" encoding=\"$charset_encoding\" ?" . ">\n<methodCall>\n";
		}
		else
		{
			return "<?xml version=\"1.0\"?" . ">\n<methodCall>\n";
		}
	}

	function xml_footer()
	{
		return '</methodCall>';
	}

	function kindOf()
	{
		return 'msg';
	}

	function createPayload($charset_encoding='')
	{
		if ($charset_encoding != '')
			$this->content_type = 'text/xml; charset=' . $charset_encoding;
		else
			$this->content_type = 'text/xml';
		$this->payload=$this->xml_header($charset_encoding);
		$this->payload.='<methodName>' . $this->methodname . "</methodName>\n";
		$this->payload.="<params>\n";
		for($i=0; $i<count($this->params); $i++)
		{
			$p=$this->params[$i];
			$this->payload.="<param>\n" . $p->serialize($charset_encoding) .
			"</param>\n";
		}
		$this->payload.="</params>\n";
		$this->payload.=$this->xml_footer();
	}

	function method($meth='')
	{
		if($meth!='')
		{
			$this->methodname=$meth;
		}
		return $this->methodname;
	}

	function serialize($charset_encoding='')
	{
		$this->createPayload($charset_encoding);
		return $this->payload;
	}

	function addParam($par)
	{
		// add check: do not add to self params which are not xmlrpcvals
		if(is_object($par) && is_a($par, 'jmap_xmlrpcval'))
		{
			$this->params[]=$par;
			return true;
		}
		else
		{
			return false;
		}
	}

	function getParam($i) { return $this->params[$i]; }

	function getNumParams() { return count($this->params); }

	function &parseResponseFile($fp)
	{
		$ipd='';
		while($data=fread($fp, 32768))
		{
			$ipd.=$data;
		}
		//fclose($fp);
		$r = $this->parseResponse($ipd);
		return $r;
	}

	function &parseResponseHeaders(&$data, $headers_processed=false)
	{
			// Support "web-proxy-tunelling" connections for https through proxies
			if(preg_match('/^HTTP\/1\.[0-1] 200 Connection established/', $data))
			{
				$pos = strpos($data,"\r\n\r\n");
				if($pos || is_int($pos))
				{
					$bd = $pos+4;
				}
				else
				{
					$pos = strpos($data,"\n\n");
					if($pos || is_int($pos))
					{
						$bd = $pos+2;
					}
					else
					{
						$bd = 0;
					}
				}
				if ($bd)
				{
					$data = substr($data, $bd);
				}
				else
				{
					$r=new jmap_xmlrpcresp(0, $GLOBALS['xmlrpcerr']['http_error'], $GLOBALS['xmlrpcstr']['http_error']. ' (HTTPS via proxy error, tunnel connection possibly failed)');
					return $r;
				}
			}

			while(preg_match('/^HTTP\/1\.1 1[0-9]{2} /', $data))
			{
				$pos = strpos($data, 'HTTP', 12);
				if(!$pos && !is_int($pos)) // works fine in php 3, 4 and 5
				{
					break;
				}
				$data = substr($data, $pos);
			}
			if(!preg_match('/^HTTP\/[0-9.]+ 200 /', $data))
			{
				$errstr= substr($data, 0, strpos($data, "\n")-1);
				$r=new jmap_xmlrpcresp(0, $GLOBALS['xmlrpcerr']['http_error'], $GLOBALS['xmlrpcstr']['http_error']. ' (' . $errstr . ')');
				return $r;
			}

			$GLOBALS['_xh']['headers'] = array();
			$GLOBALS['_xh']['cookies'] = array();

			$pos = strpos($data,"\r\n\r\n");
			if($pos || is_int($pos))
			{
				$bd = $pos+4;
			}
			else
			{
				$pos = strpos($data,"\n\n");
				if($pos || is_int($pos))
				{
					$bd = $pos+2;
				}
				else
				{
					$bd = 0;
				}
			}
			$ar = preg_split("/\r?\n/", trim(substr($data, 0, $pos)));
			while(list(,$line) = @each($ar))
			{
				// take care of multi-line headers and cookies
				$arr = explode(':',$line,2);
				if(count($arr) > 1)
				{
					$header_name = strtolower(trim($arr[0]));
					if ($header_name == 'set-cookie' || $header_name == 'set-cookie2')
					{
						if ($header_name == 'set-cookie2')
						{
							$cookies = explode(',', $arr[1]);
						}
						else
						{
							$cookies = array($arr[1]);
						}
						foreach ($cookies as $cookie)
						{
							// glue together all received cookies, using a comma to separate them
							// (same as php does with getallheaders())
							if (isset($GLOBALS['_xh']['headers'][$header_name]))
								$GLOBALS['_xh']['headers'][$header_name] .= ', ' . trim($cookie);
							else
								$GLOBALS['_xh']['headers'][$header_name] = trim($cookie);
							$cookie = explode(';', $cookie);
							foreach ($cookie as $pos => $val)
							{
								$val = explode('=', $val, 2);
								$tag = trim($val[0]);
								$val = trim(@$val[1]);
								if ($pos == 0)
								{
									$cookiename = $tag;
									$GLOBALS['_xh']['cookies'][$tag] = array();
									$GLOBALS['_xh']['cookies'][$cookiename]['value'] = urldecode($val);
								}
								else
								{
									if ($tag != 'value')
									{
									  $GLOBALS['_xh']['cookies'][$cookiename][$tag] = $val;
									}
								}
							}
						}
					}
					else
					{
						$GLOBALS['_xh']['headers'][$header_name] = trim($arr[1]);
					}
				}
				elseif(isset($header_name))
				{
					$GLOBALS['_xh']['headers'][$header_name] .= ' ' . trim($line);
				}
			}

			$data = substr($data, $bd);

			if(!$headers_processed)
			{
				// Decode chunked encoding sent by http 1.1 servers
				if(isset($GLOBALS['_xh']['headers']['transfer-encoding']) && $GLOBALS['_xh']['headers']['transfer-encoding'] == 'chunked')
				{
					if(!$data = jmap_decode_chunked($data))
					{
						$r = new jmap_xmlrpcresp(0, $GLOBALS['xmlrpcerr']['dechunk_fail'], $GLOBALS['xmlrpcstr']['dechunk_fail']);
						return $r;
					}
				}

				if(isset($GLOBALS['_xh']['headers']['content-encoding']))
				{
					$GLOBALS['_xh']['headers']['content-encoding'] = str_replace('x-', '', $GLOBALS['_xh']['headers']['content-encoding']);
					if($GLOBALS['_xh']['headers']['content-encoding'] == 'deflate' || $GLOBALS['_xh']['headers']['content-encoding'] == 'gzip')
					{
						// if decoding works, use it. else assume data wasn't gzencoded
						if(function_exists('gzinflate'))
						{
							if($GLOBALS['_xh']['headers']['content-encoding'] == 'deflate' && $degzdata = @gzuncompress($data))
							{
								$data = $degzdata;
							}
							elseif($GLOBALS['_xh']['headers']['content-encoding'] == 'gzip' && $degzdata = @gzinflate(substr($data, 10)))
							{
								$data = $degzdata;
							}
							else
							{
								$r = new jmap_xmlrpcresp(0, $GLOBALS['xmlrpcerr']['decompress_fail'], $GLOBALS['xmlrpcstr']['decompress_fail']);
								return $r;
							}
						}
						else
						{
							$r = new jmap_xmlrpcresp(0, $GLOBALS['xmlrpcerr']['cannot_decompress'], $GLOBALS['xmlrpcstr']['cannot_decompress']);
							return $r;
						}
					}
				}
			} // end of 'if needed, de-chunk, re-inflate response'

			$r = null;
			$r = $r;
			return $r;
	}

	function &parseResponse($data='', $headers_processed=false, $return_type='xmlrpcvals')
	{

		if($data == '')
		{
			$r = new jmap_xmlrpcresp(0, $GLOBALS['xmlrpcerr']['no_data'], $GLOBALS['xmlrpcstr']['no_data']);
			return $r;
		}

		$GLOBALS['_xh']=array();

		$raw_data = $data;
		if(substr($data, 0, 4) == 'HTTP')
		{
			$r = $this->parseResponseHeaders($data, $headers_processed);
			if ($r)
			{
				$r->raw_data = $data;
				return $r;
			}
		}
		else
		{
			$GLOBALS['_xh']['headers'] = array();
			$GLOBALS['_xh']['cookies'] = array();
		}

		// be tolerant of extra whitespace in response body
		$data = trim($data);

		$bd = false;
		$pos = strpos($data, '</methodResponse>');
		while($pos || is_int($pos))
		{
			$bd = $pos+17;
			$pos = strpos($data, '</methodResponse>', $bd);
		}
		if($bd)
		{
			$data = substr($data, 0, $bd);
		}

		// if user wants back raw xml, give it to him
		if ($return_type == 'xml')
		{
			$r = new jmap_xmlrpcresp($data, 0, '', 'xml');
			$r->hdrs = $GLOBALS['_xh']['headers'];
			$r->_cooksarray = $GLOBALS['_xh']['cookies'];
			$r->raw_data = $raw_data;
			return $r;
		}

		$resp_encoding = jmap_guess_encoding(@$GLOBALS['_xh']['headers']['content-type'], $data);

		$GLOBALS['_xh']['ac']='';
		//$GLOBALS['_xh']['qt']=''; //unused...
		$GLOBALS['_xh']['stack'] = array();
		$GLOBALS['_xh']['valuestack'] = array();
		$GLOBALS['_xh']['isf']=0; // 0 = OK, 1 for xmlrpc fault responses, 2 = invalid xmlrpc
		$GLOBALS['_xh']['isf_reason']='';
		$GLOBALS['_xh']['rt']=''; // 'methodcall or 'methodresponse'

		if (!in_array($resp_encoding, array('UTF-8', 'ISO-8859-1', 'US-ASCII')))
		{
			$resp_encoding = $GLOBALS['xmlrpc_defencoding'];
		}
		$parser = xml_parser_create($resp_encoding);
		xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, true);
		if (!in_array($GLOBALS['xmlrpc_internalencoding'], array('UTF-8', 'ISO-8859-1', 'US-ASCII')))
		{
			xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, 'UTF-8');
		}
		else
		{
			xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, $GLOBALS['xmlrpc_internalencoding']);
		}

		if ($return_type == 'phpvals')
		{
			xml_set_element_handler($parser, 'jmap_xmlrpc_se', 'jmap_xmlrpc_ee_fast');
		}
		else
		{
			xml_set_element_handler($parser, 'jmap_xmlrpc_se', 'jmap_xmlrpc_ee');
		}

		xml_set_character_data_handler($parser, 'jmap_xmlrpc_cd');
		xml_set_default_handler($parser, 'jmap_xmlrpc_dh');

		// first error check: xml not well formed
		if(!xml_parse($parser, $data, count($data)))
		{
			// thanks to Peter Kocks <peter.kocks@baygate.com>
			if((xml_get_current_line_number($parser)) == 1)
			{
				$errstr = 'XML error at line 1, check URL';
			}
			else
			{
				$errstr = sprintf('XML error: %s at line %d, column %d',
					xml_error_string(xml_get_error_code($parser)),
					xml_get_current_line_number($parser), xml_get_current_column_number($parser));
			}
			$r=new jmap_xmlrpcresp(0, $GLOBALS['xmlrpcerr']['invalid_return'], $GLOBALS['xmlrpcstr']['invalid_return'].' ('.$errstr.')');
			xml_parser_free($parser);
			$r->hdrs = $GLOBALS['_xh']['headers'];
			$r->_cooksarray = $GLOBALS['_xh']['cookies'];
			$r->raw_data = $raw_data;
			return $r;
		}
		xml_parser_free($parser);
		// second error check: xml well formed but not xml-rpc compliant
		if ($GLOBALS['_xh']['isf'] > 1)
		{
			$r = new jmap_xmlrpcresp(0, $GLOBALS['xmlrpcerr']['invalid_return'],
			$GLOBALS['xmlrpcstr']['invalid_return'] . ' ' . $GLOBALS['_xh']['isf_reason']);
		}
		elseif ($return_type == 'xmlrpcvals' && !is_object($GLOBALS['_xh']['value']))
		{
			$r=new jmap_xmlrpcresp(0, $GLOBALS['xmlrpcerr']['invalid_return'],
				$GLOBALS['xmlrpcstr']['invalid_return']);
		}
		else
		{
			$v = $GLOBALS['_xh']['value'];

			if($GLOBALS['_xh']['isf'])
			{
				if ($return_type == 'xmlrpcvals')
				{
					$errno_v = $v->structmem('faultCode');
					$errstr_v = $v->structmem('faultString');
					$errno = $errno_v->scalarval();
					$errstr = $errstr_v->scalarval();
				}
				else
				{
					$errno = $v['faultCode'];
					$errstr = $v['faultString'];
				}

				if($errno == 0)
				{
					$errno = -1;
				}

				$r = new jmap_xmlrpcresp(0, $errno, $errstr);
			}
			else
			{
				$r=new jmap_xmlrpcresp($v, 0, '', $return_type);
			}
		}

		$r->hdrs = $GLOBALS['_xh']['headers'];
		$r->_cooksarray = $GLOBALS['_xh']['cookies'];
		$r->raw_data = $raw_data;
		return $r;
	}
}

class jmap_xmlrpcval {
	var $me=array();
	var $mytype=0;
	var $_php_class=null;
	function __construct($val=-1, $type='')
	{
		if($val!==-1 || $type!='')
		{
			switch($type)
			{
				case '':
					$this->mytype=1;
					$this->me['string']=$val;
					break;
				case 'i4':
				case 'int':
				case 'double':
				case 'string':
				case 'boolean':
				case 'dateTime.iso8601':
				case 'base' . '64':
				case 'null':
					$this->mytype=1;
					$this->me[$type]=$val;
					break;
				case 'array':
					$this->mytype=2;
					$this->me['array']=$val;
					break;
				case 'struct':
					$this->mytype=3;
					$this->me['struct']=$val;
					break;
			}
		}
	}

	function addScalar($val, $type='string')
	{
		$typeof=@$GLOBALS['xmlrpcTypes'][$type];
		if($typeof!=1)
		{
			return 0;
		}

		if($type==$GLOBALS['xmlrpcBoolean'])
		{
			if(strcasecmp($val,'true')==0 || $val==1 || ($val==true && strcasecmp($val,'false')))
			{
				$val=true;
			}
			else
			{
				$val=false;
			}
		}

		switch($this->mytype)
		{
			case 1:
				return 0;
			case 3:
				return 0;
			case 2:
				$this->me['array'][]=new jmap_xmlrpcval($val, $type);
				return 1;
			default:
				// a scalar, so set the value and remember we're scalar
				$this->me[$type]=$val;
				$this->mytype=$typeof;
				return 1;
		}
	}

	function addArray($vals)
	{
		if($this->mytype==0)
		{
			$this->mytype=$GLOBALS['xmlrpcTypes']['array'];
			$this->me['array']=$vals;
			return 1;
		}
		elseif($this->mytype==2)
		{
			// we're adding to an array here
			$this->me['array'] = array_merge($this->me['array'], $vals);
			return 1;
		}
		else
		{
			return 0;
		}
	}

	function addStruct($vals)
	{
		if($this->mytype==0)
		{
			$this->mytype=$GLOBALS['xmlrpcTypes']['struct'];
			$this->me['struct']=$vals;
			return 1;
		}
		elseif($this->mytype==3)
		{
			// we're adding to a struct here
			$this->me['struct'] = array_merge($this->me['struct'], $vals);
			return 1;
		}
		else
		{
			return 0;
		}
	}

	function kindOf()
	{
		switch($this->mytype)
		{
			case 3:
				return 'struct';
				break;
			case 2:
				return 'array';
				break;
			case 1:
				return 'scalar';
				break;
			default:
				return 'undef';
		}
	}

	function serializedata($typ, $val, $charset_encoding='') 	{
			$rs='';
			switch(@$GLOBALS['xmlrpcTypes'][$typ])
			{
				case 1:
					switch($typ)
					{
						case $GLOBALS['xmlrpcBase_64']:
							$bas64FunctionNameEncode = 'base'. 64 . '_encode';
							$rs.="<${typ}>" . $bas64FunctionNameEncode($val) . "</${typ}>";
							break;
						case $GLOBALS['xmlrpcBoolean']:
							$rs.="<${typ}>" . ($val ? '1' : '0') . "</${typ}>";
							break;
						case $GLOBALS['xmlrpcString']:
							// G. Giunta 2005/2/13: do NOT use htmlentities, since
							// it will produce named html entities, which are invalid xml
							$rs.="<${typ}>" . jmap_xmlrpc_encode_entitites($val, $GLOBALS['xmlrpc_internalencoding'], $charset_encoding). "</${typ}>";
							break;
						case $GLOBALS['xmlrpcInt']:
						case $GLOBALS['xmlrpcI4']:
							$rs.="<${typ}>".(int)$val."</${typ}>";
							break;
						case $GLOBALS['xmlrpcDouble']:
    						$rs.="<${typ}>".preg_replace('/\\.?0+$/','',number_format((double)$val, 128, '.', ''))."</${typ}>";
							break;
						case $GLOBALS['xmlrpcNull']:
							$rs.="<nil/>";
							break;
						default:
							$rs.="<${typ}>${val}</${typ}>";
					}
					break;
				case 3:
					// struct
					if ($this->_php_class)
					{
						$rs.='<struct php_class="' . $this->_php_class . "\">\n";
					}
					else
					{
						$rs.="<struct>\n";
					}
					foreach($val as $key2 => $val2)
					{
						$rs.='<member><name>'.jmap_xmlrpc_encode_entitites($key2, $GLOBALS['xmlrpc_internalencoding'], $charset_encoding)."</name>\n";
						$rs.=$val2->serialize($charset_encoding);
						$rs.="</member>\n";
					}
					$rs.='</struct>';
					break;
				case 2:
					// array
					$rs.="<array>\n<data>\n";
					for($i=0; $i<count($val); $i++)
					{
						$rs.=$val[$i]->serialize($charset_encoding);
					}
					$rs.="</data>\n</array>";
					break;
				default:
					break;
			}
		return $rs;
	}

	function serialize($charset_encoding='')
	{
		reset($this->me);
		list($typ, $val) = each($this->me);
		return '<value>' . $this->serializedata($typ, $val, $charset_encoding) . "</value>\n";
	}

	function structmemexists($m)
	{
		return array_key_exists($m, $this->me['struct']);
	}

	function structmem($m)
	{
		return @$this->me['struct'][$m];
	}

	function structreset()
	{
		reset($this->me['struct']);
	}

	function structeach()
	{
		return each($this->me['struct']);
	}

	function getval()
	{
		reset($this->me);
		list($a,$b)=each($this->me);
		if(is_array($b))
		{
			@reset($b);
			while(list($id,$cont) = @each($b))
			{
				$b[$id] = $cont->scalarval();
			}
		}

		// add support for structures directly encoding php objects
		if(is_object($b))
		{
			$t = get_object_vars($b);
			@reset($t);
			while(list($id,$cont) = @each($t))
			{
				$t[$id] = $cont->scalarval();
			}
			@reset($t);
			while(list($id,$cont) = @each($t))
			{
				@$b->$id = $cont;
			}
		}
		// end contrib
		return $b;
	}

	function scalarval()
	{
		reset($this->me);
		list(,$b)=each($this->me);
		return $b;
	}

	function scalartyp()
	{
		reset($this->me);
		list($a,)=each($this->me);
		if($a==$GLOBALS['xmlrpcI4'])
		{
			$a=$GLOBALS['xmlrpcInt'];
		}
		return $a;
	}

	function arraymem($m)
	{
		return $this->me['array'][$m];
	}

	function arraysize()
	{
		return count($this->me['array']);
	}

	function structsize()
	{
		return count($this->me['struct']);
	}
}

function jmap_iso8601_decode($idate, $utc=0)
{
	$t=0;
	if(preg_match('/([0-9]{4})([0-9]{2})([0-9]{2})T([0-9]{2}):([0-9]{2}):([0-9]{2})/', $idate, $regs))
	{
		if($utc)
		{
			$t=gmmktime($regs[4], $regs[5], $regs[6], $regs[2], $regs[3], $regs[1]);
		}
		else
		{
			$t=mktime($regs[4], $regs[5], $regs[6], $regs[2], $regs[3], $regs[1]);
		}
	}
	return $t;
}

function jmap_php_xmlrpc_decode($xmlrpc_val, $options=array())
{
	switch($xmlrpc_val->kindOf())
	{
		case 'scalar':
			if (in_array('extension_api', $options))
			{
				reset($xmlrpc_val->me);
				list($typ,$val) = each($xmlrpc_val->me);
				switch ($typ)
				{
					case 'dateTime.iso8601':
						$xmlrpc_val->scalar = $val;
						$xmlrpc_val->xmlrpc_type = 'datetime';
						$xmlrpc_val->timestamp = jmap_iso8601_decode($val);
						return $xmlrpc_val;
					case 'base' . '64':
						$xmlrpc_val->scalar = $val;
						$xmlrpc_val->type = $typ;
						return $xmlrpc_val;
					default:
						return $xmlrpc_val->scalarval();
				}
			}
			return $xmlrpc_val->scalarval();
		case 'array':
			$size = $xmlrpc_val->arraysize();
			$arr = array();
			for($i = 0; $i < $size; $i++)
			{
				$arr[] = jmap_php_xmlrpc_decode($xmlrpc_val->arraymem($i), $options);
			}
			return $arr;
		case 'struct':
			$xmlrpc_val->structreset();
			if (in_array('decode_php_objs', $options) && $xmlrpc_val->_php_class != ''
				&& class_exists($xmlrpc_val->_php_class))
			{
				$obj = @new $xmlrpc_val->_php_class;
				while(list($key,$value)=$xmlrpc_val->structeach())
				{
					$obj->$key = jmap_php_xmlrpc_decode($value, $options);
				}
				return $obj;
			}
			else
			{
				$arr = array();
				while(list($key,$value)=$xmlrpc_val->structeach())
				{
					$arr[$key] = jmap_php_xmlrpc_decode($value, $options);
				}
				return $arr;
			}
		case 'msg':
			$paramcount = $xmlrpc_val->getNumParams();
			$arr = array();
			for($i = 0; $i < $paramcount; $i++)
			{
				$arr[] = jmap_php_xmlrpc_decode($xmlrpc_val->getParam($i));
			}
			return $arr;
		}
}

function jmap_php_xmlrpc_encode($php_val, $options=array())
{
	$type = gettype($php_val);
	switch($type)
	{
		case 'string':
			if (in_array('auto_dates', $options) && preg_match('/^[0-9]{8}T[0-9]{2}:[0-9]{2}:[0-9]{2}$/', $php_val))
				$xmlrpc_val = new jmap_xmlrpcval($php_val, $GLOBALS['xmlrpcDateTime']);
			else
				$xmlrpc_val = new jmap_xmlrpcval($php_val, $GLOBALS['xmlrpcString']);
			break;
		case 'integer':
			$xmlrpc_val = new jmap_xmlrpcval($php_val, $GLOBALS['xmlrpcInt']);
			break;
		case 'double':
			$xmlrpc_val = new jmap_xmlrpcval($php_val, $GLOBALS['xmlrpcDouble']);
			break;
			// Add support for encoding/decoding of booleans, since they are supported in PHP
		case 'boolean':
			$xmlrpc_val = new jmap_xmlrpcval($php_val, $GLOBALS['xmlrpcBoolean']);
			break;
		case 'array':
			$j = 0;
			$arr = array();
			$ko = false;
			foreach($php_val as $key => $val)
			{
				$arr[$key] = jmap_php_xmlrpc_encode($val, $options);
				if(!$ko && $key !== $j)
				{
					$ko = true;
				}
				$j++;
			}
			if($ko)
			{
				$xmlrpc_val = new jmap_xmlrpcval($arr, $GLOBALS['xmlrpcStruct']);
			}
			else
			{
				$xmlrpc_val = new jmap_xmlrpcval($arr, $GLOBALS['xmlrpcArray']);
			}
			break;
		case 'object':
			if(is_a($php_val, 'jmap_xmlrpcval'))
			{
				$xmlrpc_val = $php_val;
			}
			else
			{
				$arr = array();
				while(list($k,$v) = each($php_val))
				{
					$arr[$k] = jmap_php_xmlrpc_encode($v, $options);
				}
				$xmlrpc_val = new jmap_xmlrpcval($arr, $GLOBALS['xmlrpcStruct']);
				if (in_array('encode_php_objs', $options))
				{
					// let's save original class name into jmap_xmlrpcval:
					// might be useful later on...
					$xmlrpc_val->_php_class = get_class($php_val);
				}
			}
			break;
		case 'NULL':
			if (in_array('extension_api', $options))
			{
				$xmlrpc_val = new jmap_xmlrpcval('', $GLOBALS['xmlrpcString']);
			}
			if (in_array('null_extension', $options))
			{
				$xmlrpc_val = new jmap_xmlrpcval('', $GLOBALS['xmlrpcNull']);
			}
			else
			{
				$xmlrpc_val = new jmap_xmlrpcval();
			}
			break;
		case 'resource':
			if (in_array('extension_api', $options))
			{
				$xmlrpc_val = new jmap_xmlrpcval((int)$php_val, $GLOBALS['xmlrpcInt']);
			}
			else
			{
				$xmlrpc_val = new jmap_xmlrpcval();
			}
		// catch "user function", "unknown type"
		default:
			// giancarlo pinerolo <ping@alt.it>
			// it has to return
			// an empty object in case, not a boolean.
			$xmlrpc_val = new jmap_xmlrpcval();
			break;
		}
		return $xmlrpc_val;
}


function jmap_decode_chunked($buffer)
{
	// length := 0
	$length = 0;
	$new = '';

	$chunkend = strpos($buffer,"\r\n") + 2;
	$temp = substr($buffer,0,$chunkend);
	$chunk_size = hexdec( trim($temp) );
	$chunkstart = $chunkend;
	while($chunk_size > 0)
	{
		$chunkend = strpos($buffer, "\r\n", $chunkstart + $chunk_size);

		// just in case we got a broken connection
		if($chunkend == false)
		{
			$chunk = substr($buffer,$chunkstart);
			// append chunk-data to entity-body
			$new .= $chunk;
			$length += strlen($chunk);
			break;
		}

		// read chunk-data and crlf
		$chunk = substr($buffer,$chunkstart,$chunkend-$chunkstart);
		// append chunk-data to entity-body
		$new .= $chunk;
		// length := length + chunk-size
		$length += strlen($chunk);
		// read chunk-size and crlf
		$chunkstart = $chunkend + 2;

		$chunkend = strpos($buffer,"\r\n",$chunkstart)+2;
		if($chunkend == false)
		{
			break; //just in case we got a broken connection
		}
		$temp = substr($buffer,$chunkstart,$chunkend-$chunkstart);
		$chunk_size = hexdec( trim($temp) );
		$chunkstart = $chunkend;
	}
	return $new;
}

function jmap_guess_encoding($httpheader='', $xmlchunk='', $encoding_prefs=null)
{
	$matches = array();
	if(preg_match('/;\s*charset\s*=([^;]+)/i', $httpheader, $matches))
	{
		return strtoupper(trim($matches[1], " \t\""));
	}
	if(preg_match('/^(\x00\x00\xFE\xFF|\xFF\xFE\x00\x00|\x00\x00\xFF\xFE|\xFE\xFF\x00\x00)/', $xmlchunk))
	{
		return 'UCS-4';
	}
	elseif(preg_match('/^(\xFE\xFF|\xFF\xFE)/', $xmlchunk))
	{
		return 'UTF-16';
	}
	elseif(preg_match('/^(\xEF\xBB\xBF)/', $xmlchunk))
	{
		return 'UTF-8';
	}

	if (preg_match('/^<\?xml\s+version\s*=\s*'. "((?:\"[a-zA-Z0-9_.:-]+\")|(?:'[a-zA-Z0-9_.:-]+'))".
		'\s+encoding\s*=\s*' . "((?:\"[A-Za-z][A-Za-z0-9._-]*\")|(?:'[A-Za-z][A-Za-z0-9._-]*'))/",
		$xmlchunk, $matches))
	{
		return strtoupper(substr($matches[2], 1, -1));
	}

	if(extension_loaded('mbstring'))
	{
		if($encoding_prefs)
		{
			$enc = mb_detect_encoding($xmlchunk, $encoding_prefs);
		}
		else
		{
			$enc = mb_detect_encoding($xmlchunk);
		}
		if($enc == 'ASCII')
		{
			$enc = 'US-'.$enc;
		}
		return $enc;
	}
	else
	{
		return $GLOBALS['xmlrpc_defencoding'];
	}
}
function jmap_xmlrpc_encode_entitites($data, $src_encoding='', $dest_encoding='')
{
	if ($src_encoding == '')
	{
		$src_encoding = $GLOBALS['xmlrpc_internalencoding'];
	}

	switch(strtoupper($src_encoding.'_'.$dest_encoding))
	{
		case 'ISO-8859-1_':
		case 'ISO-8859-1_US-ASCII':
			$escaped_data = str_replace(array('&', '"', "'", '<', '>'), array('&amp;', '&quot;', '&apos;', '&lt;', '&gt;'), $data);
			$escaped_data = str_replace($GLOBALS['xml_iso88591_Entities']['in'], $GLOBALS['xml_iso88591_Entities']['out'], $escaped_data);
			break;
		case 'ISO-8859-1_UTF-8':
			$escaped_data = str_replace(array('&', '"', "'", '<', '>'), array('&amp;', '&quot;', '&apos;', '&lt;', '&gt;'), $data);
			$escaped_data = utf8_encode($escaped_data);
			break;
		case 'ISO-8859-1_ISO-8859-1':
		case 'US-ASCII_US-ASCII':
		case 'US-ASCII_UTF-8':
		case 'US-ASCII_':
		case 'US-ASCII_ISO-8859-1':
		case 'UTF-8_UTF-8':
			$escaped_data = str_replace(array('&', '"', "'", '<', '>'), array('&amp;', '&quot;', '&apos;', '&lt;', '&gt;'), $data);
			break;
		case 'UTF-8_':
		case 'UTF-8_US-ASCII':
		case 'UTF-8_ISO-8859-1':
			$escaped_data = '';
			$data = (string) $data;
			$ns = strlen ($data);
			for ($nn = 0; $nn < $ns; $nn++)
			{
			$ch = $data[$nn];
			$ii = ord($ch);
			//1 7 0bbbbbbb (127)
			if ($ii < 128)
			{
			switch($ii){
			case 34:
			$escaped_data .= '&quot;';
			break;
			case 38:
			$escaped_data .= '&amp;';
			break;
			case 39:
			$escaped_data .= '&apos;';
			break;
			case 60:
			$escaped_data .= '&lt;';
			break;
			case 62:
			$escaped_data .= '&gt;';
			break;
			default:
			$escaped_data .= $ch;
			} // switch
			}
			else if ($ii>>5 == 6)
			{
			$b1 = ($ii & 31);
			$ii = ord($data[$nn+1]);
			$b2 = ($ii & 63);
			$ii = ($b1 * 64) + $b2;
			$ent = sprintf ('&#%d;', $ii);
			$escaped_data .= $ent;
			$nn += 1;
			}
			else if ($ii>>4 == 14)
			{
			$b1 = ($ii & 15);
			$ii = ord($data[$nn+1]);
			$b2 = ($ii & 63);
			$ii = ord($data[$nn+2]);
			$b3 = ($ii & 63);
			$ii = ((($b1 * 64) + $b2) * 64) + $b3;
			$ent = sprintf ('&#%d;', $ii);
			$escaped_data .= $ent;
			$nn += 2;
			}
			else if ($ii>>3 == 30)
			{
			$b1 = ($ii & 7);
			$ii = ord($data[$nn+1]);
			$b2 = ($ii & 63);
			$ii = ord($data[$nn+2]);
			$b3 = ($ii & 63);
			$ii = ord($data[$nn+3]);
			$b4 = ($ii & 63);
			$ii = ((((($b1 * 64) + $b2) * 64) + $b3) * 64) + $b4;
			$ent = sprintf ('&#%d;', $ii);
			$escaped_data .= $ent;
			$nn += 3;
	}
	}
	break;
	default:
	$escaped_data = '';
}
return $escaped_data;
}

function jmap_xmlrpc_se($parser, $name, $attrs, $accept_single_vals=false)
{
	if ($GLOBALS['_xh']['isf'] < 2) {
		if (count($GLOBALS['_xh']['stack']) == 0)
		{
		if ($name != 'METHODRESPONSE' && $name != 'METHODCALL' && (
				$name != 'VALUE' && !$accept_single_vals))
		{
		$GLOBALS['_xh']['isf'] = 2;
		$GLOBALS['_xh']['isf_reason'] = 'missing top level xmlrpc element';
		return;
		}
		else
		{
				$GLOBALS['_xh']['rt'] = strtolower($name);
		}
		}
		else
		{
		$parent = end($GLOBALS['_xh']['stack']);
		if (!array_key_exists($name, $GLOBALS['xmlrpc_valid_parents']) || !in_array($parent, $GLOBALS['xmlrpc_valid_parents'][$name]))
		{
		$GLOBALS['_xh']['isf'] = 2;
		$GLOBALS['_xh']['isf_reason'] = "xmlrpc element $name cannot be child of $parent";
		return;
		}
		}
	
		switch($name)
		{
			case 'VALUE':
				$GLOBALS['_xh']['vt']='value'; // indicator: no value found yet
				$GLOBALS['_xh']['ac']='';
				$GLOBALS['_xh']['lv']=1;
				$GLOBALS['_xh']['php_class']=null;
				break;
			case 'I4':
			case 'INT':
			case 'STRING':
			case 'BOOLEAN':
			case 'DOUBLE':
			case 'DATETIME.ISO8601':
			case 'BASE' . '64':
				if ($GLOBALS['_xh']['vt']!='value')
				{
					//two data elements inside a value: an error occurred!
					$GLOBALS['_xh']['isf'] = 2;
					$GLOBALS['_xh']['isf_reason'] = "$name element following a {$GLOBALS['_xh']['vt']} element inside a single value";
					return;
				}
				$GLOBALS['_xh']['ac']=''; // reset the accumulator
				break;
			case 'STRUCT':
			case 'ARRAY':
				if ($GLOBALS['_xh']['vt']!='value')
				{
					//two data elements inside a value: an error occurred!
					$GLOBALS['_xh']['isf'] = 2;
					$GLOBALS['_xh']['isf_reason'] = "$name element following a {$GLOBALS['_xh']['vt']} element inside a single value";
					return;
				}
				// create an empty array to hold child values, and push it onto appropriate stack
				$cur_val = array();
				$cur_val['values'] = array();
				$cur_val['type'] = $name;
				if (@isset($attrs['PHP_CLASS']))
				{
					$cur_val['php_class'] = $attrs['PHP_CLASS'];
				}
				$GLOBALS['_xh']['valuestack'][] = $cur_val;
				$GLOBALS['_xh']['vt']='data'; // be prepared for a data element next
				break;
			case 'DATA':
				if ($GLOBALS['_xh']['vt']!='data')
				{
					//two data elements inside a value: an error occurred!
					$GLOBALS['_xh']['isf'] = 2;
					$GLOBALS['_xh']['isf_reason'] = "found two data elements inside an array element";
					return;
				}
			case 'METHODCALL':
			case 'METHODRESPONSE':
			case 'PARAMS':
				break;
			case 'METHODNAME':
			case 'NAME':
				$GLOBALS['_xh']['ac']='';
				break;
			case 'FAULT':
				$GLOBALS['_xh']['isf']=1;
				break;
			case 'MEMBER':
				$GLOBALS['_xh']['valuestack'][count($GLOBALS['_xh']['valuestack'])-1]['name']=''; // set member name to null, in case we do not find in the xml later on
			case 'PARAM':
				$GLOBALS['_xh']['vt']=null;
				break;
			case 'NIL':
				if ($GLOBALS['xmlrpc_null_extension'])
				{
					if ($GLOBALS['_xh']['vt']!='value')
					{
						//two data elements inside a value: an error occurred!
						$GLOBALS['_xh']['isf'] = 2;
						$GLOBALS['_xh']['isf_reason'] = "$name element following a {$GLOBALS['_xh']['vt']} element inside a single value";
						return;
					}
					$GLOBALS['_xh']['ac']=''; // reset the accumulator
					break;
				}
			default:
				$GLOBALS['_xh']['isf'] = 2;
				$GLOBALS['_xh']['isf_reason'] = "found not-xmlrpc xml element $name";
				break;
		}
	
		// Save current element name to stack, to validate nesting
		$GLOBALS['_xh']['stack'][] = $name;
		if($name!='VALUE')
		{
			$GLOBALS['_xh']['lv']=0;
		}
	}
}

/// Used in decoding xml chunks that might represent single xmlrpc values
function jmap_xmlrpc_se_any($parser, $name, $attrs)
{
	jmap_xmlrpc_se($parser, $name, $attrs, true);
}

/// xml parser handler function for close element tags
function jmap_xmlrpc_ee($parser, $name, $rebuild_xmlrpcvals = true)
{
	if ($GLOBALS['_xh']['isf'] < 2)
	{
		$curr_elem = array_pop($GLOBALS['_xh']['stack']);

		switch($name)
		{
			case 'VALUE':
				if ($GLOBALS['_xh']['vt']=='value')
				{
					$GLOBALS['_xh']['value']=$GLOBALS['_xh']['ac'];
					$GLOBALS['_xh']['vt']=$GLOBALS['xmlrpcString'];
				}

				if ($rebuild_xmlrpcvals)
				{
					$temp = new jmap_xmlrpcval($GLOBALS['_xh']['value'], $GLOBALS['_xh']['vt']);
					if (isset($GLOBALS['_xh']['php_class']))
						$temp->_php_class = $GLOBALS['_xh']['php_class'];
					$vscount = count($GLOBALS['_xh']['valuestack']);
					if ($vscount && $GLOBALS['_xh']['valuestack'][$vscount-1]['type']=='ARRAY')
					{
						$GLOBALS['_xh']['valuestack'][$vscount-1]['values'][] = $temp;
					}
					else
					{
						$GLOBALS['_xh']['value'] = $temp;
					}
				}
				else
				{
					if (isset($GLOBALS['_xh']['php_class']))
					{
					}

					$vscount = count($GLOBALS['_xh']['valuestack']);
					if ($vscount && $GLOBALS['_xh']['valuestack'][$vscount-1]['type']=='ARRAY')
					{
						$GLOBALS['_xh']['valuestack'][$vscount-1]['values'][] = $GLOBALS['_xh']['value'];
					}
				}
				break;
			case 'BOOLEAN':
			case 'I4':
			case 'INT':
			case 'STRING':
			case 'DOUBLE':
			case 'DATETIME.ISO8601':
			case 'BASE' . '64':
				$GLOBALS['_xh']['vt']=strtolower($name);
				if ($name=='STRING')
				{
					$GLOBALS['_xh']['value']=$GLOBALS['_xh']['ac'];
				}
				elseif ($name=='DATETIME.ISO8601')
				{
					$GLOBALS['_xh']['vt']=$GLOBALS['xmlrpcDateTime'];
					$GLOBALS['_xh']['value']=$GLOBALS['_xh']['ac'];
				}
				elseif ($name=='BASE' . '64')
				{
					$bas64FunctionNameDecode = 'base'. 64 . '_decode';
					$GLOBALS['_xh']['value']=$bas64FunctionNameDecode($GLOBALS['_xh']['ac']);
				}
				elseif ($name=='BOOLEAN')
				{
					if ($GLOBALS['_xh']['ac']=='1' || strcasecmp($GLOBALS['_xh']['ac'], 'true') == 0)
					{
						$GLOBALS['_xh']['value']=true;
					}
					else
					{
						// log if receiveing something strange, even though we set the value to false anyway
						$GLOBALS['_xh']['value']=false;
					}
				}
				elseif ($name=='DOUBLE')
				{
					if (!preg_match('/^[+-eE0123456789 \t.]+$/', $GLOBALS['_xh']['ac']))
					{
						$GLOBALS['_xh']['value']='ERROR_NON_NUMERIC_FOUND';
					}
					else
					{
						$GLOBALS['_xh']['value']=(double)$GLOBALS['_xh']['ac'];
					}
				}
				else
				{
					if (!preg_match('/^[+-]?[0123456789 \t]+$/', $GLOBALS['_xh']['ac']))
					{
						$GLOBALS['_xh']['value']='ERROR_NON_NUMERIC_FOUND';
					}
					else
					{
						$GLOBALS['_xh']['value']=(int)$GLOBALS['_xh']['ac'];
					}
				}
				//$GLOBALS['_xh']['ac']=''; // is this necessary?
				$GLOBALS['_xh']['lv']=3; // indicate we've found a value
				break;
			case 'NAME':
				$GLOBALS['_xh']['valuestack'][count($GLOBALS['_xh']['valuestack'])-1]['name'] = $GLOBALS['_xh']['ac'];
				break;
			case 'MEMBER':
				if ($GLOBALS['_xh']['vt'])
				{
					$vscount = count($GLOBALS['_xh']['valuestack']);
					$GLOBALS['_xh']['valuestack'][$vscount-1]['values'][$GLOBALS['_xh']['valuestack'][$vscount-1]['name']] = $GLOBALS['_xh']['value'];
				}
				break;
			case 'DATA':
				//$GLOBALS['_xh']['ac']=''; // is this necessary?
				$GLOBALS['_xh']['vt']=null; // reset this to check for 2 data elements in a row - even if they're empty
				break;
			case 'STRUCT':
			case 'ARRAY':
				// fetch out of stack array of values, and promote it to current value
				$curr_val = array_pop($GLOBALS['_xh']['valuestack']);
				$GLOBALS['_xh']['value'] = $curr_val['values'];
				$GLOBALS['_xh']['vt']=strtolower($name);
				if (isset($curr_val['php_class']))
				{
					$GLOBALS['_xh']['php_class'] = $curr_val['php_class'];
				}
				break;
			case 'PARAM':
				// add to array of params the current value,
				// unless no VALUE was found
				if ($GLOBALS['_xh']['vt'])
				{
					$GLOBALS['_xh']['params'][]=$GLOBALS['_xh']['value'];
					$GLOBALS['_xh']['pt'][]=$GLOBALS['_xh']['vt'];
				}
				break;
			case 'METHODNAME':
				$GLOBALS['_xh']['method']=preg_replace('/^[\n\r\t ]+/', '', $GLOBALS['_xh']['ac']);
				break;
			case 'NIL':
				if ($GLOBALS['xmlrpc_null_extension'])
				{
					$GLOBALS['_xh']['vt']='null';
					$GLOBALS['_xh']['value']=null;
					$GLOBALS['_xh']['lv']=3;
					break;
				}
				// drop through intentionally if nil extension not enabled
			case 'PARAMS':
			case 'FAULT':
			case 'METHODCALL':
			case 'METHORESPONSE':
				break;
			default:
				break;
		}
	}
}

function jmap_xmlrpc_ee_fast($parser, $name)
{
	jmap_xmlrpc_ee($parser, $name, false);
}

function jmap_xmlrpc_cd($parser, $data)
{
	// skip processing if xml fault already detected
	if ($GLOBALS['_xh']['isf'] < 2)
	{
		if($GLOBALS['_xh']['lv']!=3)
		{
			$GLOBALS['_xh']['ac'].=$data;
		}
	}
}

function jmap_xmlrpc_dh($parser, $data)
{
	// skip processing if xml fault already detected
	if ($GLOBALS['_xh']['isf'] < 2)
	{
		if(substr($data, 0, 1) == '&' && substr($data, -1, 1) == ';')
		{
			$GLOBALS['_xh']['ac'].=$data;
		}
	}
	return true;
}
