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
namespace Paydirt;
defined('PAYDIRT_PATH') or define('PAYDIRT_PATH',dirname(dirname(__FILE__)).'/');
require_once PAYDIRT_PATH.'/config.inc.php';
require_once PAYDIRT_PATH.'/Paydirt/Paydirt.php';

$paths = explode(PATH_SEPARATOR, get_include_path());
$paths[] = PAYDIRT_PATH;
set_include_path(implode(PATH_SEPARATOR,$paths));

ini_set('display_errors', 1);
error_reporting(E_ALL | E_STRICT);

