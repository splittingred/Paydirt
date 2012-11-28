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
namespace Paydirt\Test\Paydirt\Chargify;

class Statement extends \Paydirt\Test\TestCase {
	public function test_get() {
        $statement = $this->driver->getObject('Statement',14666495);
        $this->assertNotEmpty($statement,'Statement not found!');
        if (!empty($statement)) {
            $this->assertInstanceOf('\Paydirt\Chargify\Statement',$statement);
        }
	}

}