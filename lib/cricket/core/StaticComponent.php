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

abstract class StaticComponent extends Component {    
    private $renderLocalID;
    private $renderID;
    
    abstract public function renderStatic($inData);

    
    public function setRenderID($inRenderID) {
        $this->renderLocalID = $inRenderID;
        $this->renderID = $this->getParent()->getId() . "_" . $this->renderLocalID;
    }
    
    public function getRenderID() {
        return $this->renderLocalID;
    }
    
    
    public function getDivId() {
        return "component_" . $this->renderID;
    }

    public function getActionUrl($inActionID) {
        $pathInfo = "{$this->getId()}/{$this->renderLocalID}/{$inActionID}";
        
        $page = $this->getPage();
        return $page->getModule()->assembleURL($this->getRequest(),$page->getPageClassName(),$pathInfo,$page->getInstanceID());
    }

    
    public function render() {
        $this->renderStatic(null);
    }
    
    public function invalidateWithData($inData) {
        if($this->getPage()->isAjax()) {
            if(!isset($this->renderLocalID)) {
                throw new \Exception("Static Component does not have render id");
            }
            $this->getPage()->getRequest()->pushContext();
            ob_start();
            $this->renderStatic($inData);
            $this->getPage()->getAjaxResponse()->setUpdate($this->getDivId(),  ob_get_clean());
            $this->getPage()->getRequest()->popContext();
        }
    }
    
    public function invalidate() {
        throw new \Exception("Cannot invalidate() a StaticComponent.  Use invalidateWithData()");
    }
    
    protected function receiveActionRequest($parts) {
        $handled = parent::receiveActionRequest($parts);
        
        if(!$handled) {
            $renderID = array_shift($parts);
            $action = array_shift($parts);
            
            $this->setRenderID($renderID);
            
            
            $actionMethod = "action_{$action}";
            $currentTarget = $this;
            while($currentTarget) {
                if(method_exists($currentTarget, $actionMethod)) {
                    $currentTarget->$actionMethod();
                    $handled = true;
                    break;
                }else{
                    $currentTarget = $currentTarget->getParent();
                }
            }
        }
        
        return $handled;
    }

    
}
