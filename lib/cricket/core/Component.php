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

abstract class Component extends Container {
    
    public function __construct($inID) {
        parent::__construct($inID);
    }
    
    public function getActionUrl($inActionID) {
        $pathInfo = "{$this->getId()}/{$inActionID}";
        
        $page = $this->getPage();
        return $page->getModule()->assembleURL($this->getRequest(),$page->getPageClassName(),$pathInfo,$page->getInstanceID());
    }
    
    public abstract function render();
    
    public function invalidate() {
        $this->getPage()->invalidateComponent($this);
    }
    
    public function renderNow($inParams = null) {
        $thisPage = $this->getPage();
        $thisRequest = $thisPage->getRequest();
        
        $thisRequest->pushContext();
        if($inParams !== null) {
            foreach($inParams as $k => $v) {
                $thisRequest->setAttribute($k,$v);
            }          
        }
        $thisPage->renderComponentNow($this);
        $thisRequest->popContext();
    }
    
    
    public function removeFromParent() {
        if($this->getParent() !== null) {
            $this->getParent()->removeComponent($this);
        }
    }
    
}