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
 * @var \Paydirt\Driver $paydirt
 * @package paydirt
 */
require_once dirname(dirname(__FILE__)).'/config.inc.php';
$config = array(
    'api_key' => CHARGIFY_API_KEY,
    'domain' => CHARGIFY_DOMAIN,
);
require_once dirname(dirname(__FILE__)).'/Paydirt.php';

if ($paydirt instanceof \Paydirt\Chargify\Driver) {
    $plan = $paydirt->getObject('Plan','truck');
    if (!empty($plan)) {
        print_r($plan->toArray());
    }
}
