<?php

/**
 * JCH Optimize - Joomla! plugin to aggregate and minify external resources for
 * optmized downloads
 * @author Samuel Marshall <sdmarshall73@gmail.com>
 * @copyright Copyright (c) 2010 Samuel Marshall
 * @license GNU/GPLv3, See LICENSE file
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 *
 * This plugin, inspired by CssJsCompress <http://www.joomlatags.org>, was
 * created in March 2010 and includes other copyrighted works. See individual
 * files for details.
 */
if (!defined('_JCH_EXEC'))
{
        define('_JCH_EXEC', '1');
}

defined('_JCH_EXEC') or die('Restricted access');

function loadJchOptimizeClass($sClass)
{
//        global $_PROFILER;
//        JDEBUG ? $_PROFILER->mark('beforeLoadClass - ' . $sClass) : null;

        if(is_array($sClass))
        {
                foreach($sClass as $class)
                {
                        loadJchOptimizeClass($class);
                }
        }
        else
        {
                $class  = $sClass;
        }
        
        $prefix = substr($class, 0, 3);

        // If the class already exists do nothing.
        if (class_exists($class, false))
        {
                return true;
        }

        if ($prefix !== 'Jch')
        {
                return false;
        }
        else
        {
                $class = str_replace($prefix, '', $class);
        }

        if (strpos($class, '\\') !== FALSE)
        {
                $filename = str_replace('Optimize\\', '', $class);
                $file     = dirname(__FILE__) . '/libs/' . $filename . '.php';
        }
        elseif (strpos($class, 'Platform') === 0)
        {
                $class    = str_replace('Platform', '', $class);
                $filename = strtolower($class);
                $file     = dirname(dirname(__FILE__)) . '/platform/' . $filename . '.php';

                loadJchOptimizeClass('JchInterface' . $class);
        }
        elseif (strpos($class, 'Interface') === 0)
        {
                $filename = strtolower(str_replace('Interface', '', $class));
                $file     = dirname(__FILE__) . '/interfaces/' . $filename . '.php';
        }
        else
        {
                $filename = str_replace('Optimize', '', $class);
                $filename = strtolower(($class == 'Optimize') ? 'jchoptimize' : $filename);
                $file     = dirname(__FILE__) . '/' . $filename . '.php';
        }

        if (!file_exists($file))
        {
                throw new Exception(sprintf('File not found: %s', $file));
        }
        else
        {
                include $file;

                if (!class_exists($sClass) && !interface_exists($sClass))
                {
                        throw new Exception(sprintf('Class not found: %s', $sClass));
                }
        }

//        JDEBUG ? $_PROFILER->mark('afterLoadClass - ' . $sClass) : null;
}

spl_autoload_register('loadJchOptimizeClass');




