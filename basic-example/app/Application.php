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


namespace app;

use cricket\core\Application as CricketApp;
use cricket\core\Dispatcher;

class Application extends CricketApp {    
    
    public function __construct() {
        parent::__construct(array(
            // tell cricket we are using a regular PHP session
            CricketApp::CONFIG_SESSION => CricketApp::PHP_SESSION,
            // provide the php session configuration
            CricketApp::CONFIG_PHP_SESSION => array(
                CricketApp::PHP_SESSION_EXPIRE => 0,
                CricketApp::PHP_SESSION_NAME => "example1",
                CricketApp::PHP_SESSION_PATH => Dispatcher::getInstance()->getRequest()->getContextUri(),
            )
        ));
    }
        
}