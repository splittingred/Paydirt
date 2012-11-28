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
namespace Paydirt\Test;

class TestCase extends \PHPUnit_Framework_TestCase {
    /** @var \Paydirt\Chargify\Driver $driver */
    public $driver;

    public function setUp() {
        $this->driver = \Paydirt\Driver::getInstance('Chargify',$this->getConfig());
    }

    public function getConfig() {
        require_once dirname(dirname(dirname(__FILE__))).'/config.inc.php';

        return array(
            'api_key' => CHARGIFY_API_KEY,
            'domain' => CHARGIFY_DOMAIN,
        );
    }

}