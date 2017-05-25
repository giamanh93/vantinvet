<?php
/**
* @version		$Id: mod_mylivechat.php
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
defined('_JEXEC') or die('Restricted access');

$mylivechat_id = $params->get('mylivechat_id', '');
$mylivechat_displaytype = $params->get('mylivechat_displaytype', '');
$mylivechat_membership = $params->get('mylivechat_membership', '');
$mylivechat_encrymode = $params->get('mylivechat_encrymode', '');
$mylivechat_encrykey = $params->get('mylivechat_encrykey', '');

$isIntegrateUser = false;
if($mylivechat_membership == "yes")
{
	$isIntegrateUser = true;
}

require(JModuleHelper::getLayoutPath('mod_mylivechat'));


function GetEncrypt($data, $encrymode,$encrykey)
{
	if($encrymode=="basic")
		return BasicEncrypt($data,$encrykey);
	return $data;
}

function BasicEncrypt($data, $encryptkey)
{
	$EncryptLoopCount = 4;

	$vals = MakeArray($data, true);
	$keys = MakeArray($encryptkey, false);

	$len = sizeof($vals);
	$len2 = sizeof($keys);

	for ($t = 0; $t < $EncryptLoopCount; $t++)
	{
		for ($i = 0; $i < $len; $i++)
		{
			$v = $vals[$i];
			$im = ($v + $i) % 5;

			for ($x = 0; $x < $len; $x++)
			{
				if ($x == $i)
					continue;
				if ($x % 5 != $im)
					continue;

				for ($y = 0; $y <$len2; $y++)
				{
					$k = $keys[$y];
					if ($k == 0)
						continue;

					$vals[$x] += $v % $k;
				}
			}
		}
	}
	return implode('-', $vals);
}

function MakeArray($str, $random)
{
	$len = pow(2, floor(log(strlen($str), 2)) + 1) + 8;
	if ($len < 32) $len = 32;

	$arr = Array();
	$strarr = str_split($str);
	if ($random==true)
	{
		for ($i = 0; $i < $len; $i++)
			$arr[] = ord($strarr[rand() % strlen($str)]);

		$start = 1 + rand() % ($len - strlen($str) - 2);

		for ($i = 0; $i < strlen($str); $i++)
			$arr[$start + $i] = ord($strarr[$i]);

		$arr[$start - 1] = 0;
		$arr[$start + strlen($str)] = 0;
	}
	else
	{
		for ($i = 0; $i < $len; $i++)
			$arr[] = ord($strarr[$i % strlen($str)]);
	}

	return $arr;
}

function EncodeJScript($str)
{
	$chars="0123456789ABCDEF";
	$chars = str_split($chars);

	$sb = "";
	$l = strlen($str);
	$strarr = str_split($str);
	for ($i = 0; $i < $l; $i++)
	{
		$c = $strarr[$i];
		if ($c == '\\' || $c == '"' || $c == '\'' || $c == '>' || $c == '<' || $c == '&' || $c == '\r' || $c == '\n')
		{
			if ($sb == "")
			{
				if ($i > 0)
				{
					$sb .= substr($str, 0, $i);
				}
			}
			if ($c == '\\')
			{
				$sb.="\\x5C";
			}
			else if ($c == '"')
			{
				$sb.="\\x22";
			}
			else if ($c == '\'')
			{
				$sb.="\\x27";
			}
			else if ($c == '\r')
			{
				$sb.="\\x0D";
			}
			else if ($c == '\n')
			{
				$sb.="\\x0A";
			}
			else if ($c == '<')
			{
				$sb.="\\x3C";
			}
			else if ($c == '>')
			{
				$sb.="\\x3E";
			}
			else if ($c == '&')
			{
				$sb.="\\x26";
			}
			else
			{
				$code = $c;
				$a1 = $code & 0xF;
				$a2 = ($code & 0xF0) / 0x10;
				$sb.="\\x";
				$sb.=$chars[$a2];
				$sb.=$chars[$a1];
			}
		}
		else if ($sb != "")
		{
			$sb .= $c;
		}
	}
	if ($sb != "")
		return $sb;
	return $str;
}
?>