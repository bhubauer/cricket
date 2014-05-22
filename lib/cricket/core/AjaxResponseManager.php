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


namespace cricket\core;


class AjaxResponseManager {
    /** @var AjaxResponse*/
    private $response;
    private $components;  // map of string => Component
    
    public function __construct() {
        $this->response = new AjaxResponse();
        $this->components = array();
    }
    
    /** @return AjaxResponse */
    public function getResponse() {
        return $this->response;
    }
    
    public function invalidate(Component $c) {
        $this->components[$c->getId()] = $c;
    }
    
    
    // todo: deal with removing, etc.
    
    public function renderInvalidComponents() {
        if(count($this->components) > 0) {
            // In the Java version, I wrap the pages current response with a wrapped response but
            // thats not needed hear cause the output buffer catches the components output.
            foreach($this->components as $id => $c) {
                $c->getPage()->getRequest()->pushContext();
                ob_start();
                $c->render();
                $this->response->setUpdate($c->getDivId(),ob_get_clean());
                $c->getPage()->getRequest()->popContext();
            }
        }
    }
    
    public function writeToResponse(ResponseContext $resp) {
        echo json_encode($this->response->m);
    }
    
    
    
    // not sure if this function is actually used...  it doesn't seem like it would work correctly
    public function renderNow(Component $c) {
        // In the Java version, I wrap the pages current response with a wrapped response but
        // thats not needed hear cause the output buffer catches the components output.
        ob_start();
        $c->render();
        $this->response->setUpdate($c->getDivId(),  ob_get_clean());
    }
    
}