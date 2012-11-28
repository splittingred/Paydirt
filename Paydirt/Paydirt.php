<?php
/*
 * Paydirt
 *
 * Copyright 2012 by Shaun McCormick
 * All rights reserved.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, write to the Free Software Foundation, Inc., 59 Temple
 * Place, Suite 330, Boston, MA 02111-1307 USA
 */
/**
 * @package paydirt
 */
namespace Paydirt;
spl_autoload_register(function ($className) {
    $className = ltrim($className, "\\");
    preg_match('/^(.+)?([^\\\\]+)$/U', $className, $match);
    $className = str_replace("\\", "/", $match[1]) . str_replace(["\\", "_"], "/", $match[2]) . ".php";
    try {
        include_once PAYDIRT_PATH.$className;
    } catch (\Exception $e) {
        print_r($e->getTrace());
    }
});
defined('PAYDIRT_PATH') or define('PAYDIRT_PATH',dirname(dirname(__FILE__)).'/');
defined('PAYDIRT_DRIVER') or define('PAYDIRT_DRIVER','Chargify');
$config = array();
switch (PAYDIRT_DRIVER) {
    case 'Chargify':
        $config['api_key'] = CHARGIFY_API_KEY;
        $config['domain'] = CHARGIFY_DOMAIN;
        break;
}
return \Paydirt\Driver::getInstance(PAYDIRT_DRIVER,$config);