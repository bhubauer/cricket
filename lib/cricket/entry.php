<?php

/*
 * (C) Copyright 2014 Bill Hubauer <bill@hubauer.com>
 * 
 * This file is part of Cricket  https://github.com/bhubauer/cricket
 * 
 * This library is free software; you can redistribute it and/or modify it under the terms of the 
 * GNU Lesser General Public License as published by the Free Software Foundation; either 
 * version 2.1 of the License, or (at your option) any later version.
 * 
 * This library is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; 
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
 * See the GNU Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public License along with this library; 
 * if not, visit http://www.gnu.org/licenses/lgpl-2.1.html or write to the 
 * Free Software Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
 */


require_once(dirname(__FILE__) . "/autoinclude.php");
$APPLICATION = null;

// create a global $INLINE that can be used for function calls or consts in string interpolation
// global $INLINE; $x = "something something {$INLINE(self::VALUE)} something";
// or $x = "something somethign {$GLOBALS['INLINE'](self::VALUE)} something";
$INLINE = function($v) { return $v; };

// TODO:  These should not be set in here
//ini_set('display_errors', 0);
//ini_set('display_startup_errors', 0);
//ini_set('log_errors',1);
//error_reporting(E_ALL ^ E_DEPRECATED);
error_reporting(E_ALL);
