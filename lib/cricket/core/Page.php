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


abstract class Page extends Container {
    const   MODE_STATELESS = 'stateless';
    const   MODE_RELOAD = 'reload';
    const   MODE_PRESERVE = 'preserve';
    
    static public $SESSION_MODE = self::MODE_RELOAD;
    static public $SESSION_PAGE_VERSION = 1;
    
    
    /** @var RequestContext */
    private $_request;    // transient
    
    /** @var ResponseContext */
    private $_response;   // transient
    
    /** @var AjaxResponseManager */
    private $_ajax;       // transient
    
    public $_instanceID;
    public $_loaded = false;
    
    /** @var cricket\core\Module */
    private $_module;

    public $_mcReceivers = array(); // array of MessageReceiver

    

    public function __construct() {
        parent::__construct(null);
    }

    public function load() {
        if(!$this->_loaded) {
            $this->_loaded = true;
            $this->_instanceID = $this->generateInstanceID();
            $this->init();
        }
    }
    
    
    protected function init() {
        
    }
    
    abstract public function render();
    
    public function hasState() {
        $class = get_class($this);
        return $class::$SESSION_MODE != self::MODE_STATELESS;
    }
    
    /** @return RequestContext */
    public function getRequest() {
        return $this->_request;
    }
    
    /** @return ResponseContext */
    public function getResponse() {
        return $this->_response;
    }
    
    
    public function setAjaxManager(AjaxResponseManager $aj) {
        $this->_ajax = $aj;
    }
    
    /** @return AjaxResponseManager */
    public function getAjaxManager() {
        return $this->_ajax;
    }
    
    public function isAjax() {
        return $this->_ajax !== null;
    }
    
    public function beginRequest(RequestContext $req,ResponseContext $resp) {
        $this->_request = $req;
        $this->_response = $resp;
    }
    
    // not sure that this is really needed since these fields are transient
    public function endRequest() {
        $this->_request = null;
        $this->_response = null;
        $this->_ajax = null;
    }
    
    public function post() {
        $this->render();
    }
    
    public function getInstanceID() {
        return $this->_instanceID;
    }
    
    // TODO: Consider allowing compoment to contribute a css class to the wrapper div
    
    public function renderComponent($inID) {
        /* @var $c Component */
        $c = $this->findComponent($inID);
        if($c !== null) {
            $cDivID = $c->getDivId();
            $cDivClass = $c->getDivClass();
            if($cDivClass) {
                $cDivClass = " class='$cDivClass'";
            }
            echo "<div id = '$cDivID'{$cDivClass}>";
            $this->_request->pushContext();
            $c->render();
            $this->_request->popContext();
            echo "</div>";
        }else{
            echo "<div style='color:white;background-color:red;'>MISSING COMPONENT: " . $inID . "</div>";
        }
    }
    
    
    public function renderStaticComponent($inID,$inRenderID,$inData) {
        /* @var $c StaticComponent */
        $c = $this->findComponent($inID);
        if($c !== null) {
            $c->setRenderID($inRenderID);
            $cDivID = $c->getDivId();
            $cDivClass = $c->getDivClass();
            if($cDivClass) {
                $cDivClass = " class='$cDivClass'";
            }
            echo "<div id = '$cDivID'{$cDivClass}>";
            $this->_request->pushContext();
            $c->renderStatic($inData);
            $this->_request->popContext();
            echo "</div>";
        }else{
            echo "<div style='color:white;background-color:red;'>MISSING COMPONENT: " . $inID . "</div>";
        }
    }
    
    // returns the "leaf name" of the class "PageOne" for app\pages\PageOne
    public function getPageClassName() {
        $pageSearchPaths = $this->getModule()->getPageSearchPaths();
        $thisClass = get_class($this);
        
        $longestMatch = "";
        foreach($pageSearchPaths as $thisPath) {
            if(strpos($thisClass,$thisPath) === 0) {
                if(strlen($thisPath) > strlen($longestMatch)) {
                    $longestMatch = $thisPath;
                }
            }
        }
        
        return substr($thisClass, strlen($longestMatch)+1);
    }
    
    /** @return cricket\core\Module */
    public function getModule() {
        if(!isset($this->_module)) {
            list($module,$class) = Application::getInstance()->pageClass2ModuleAndPageID(get_class($this));
            $this->_module = $module;
        }
        
        return $this->_module;
    }

    // $inActionID can be null for page.  $inPageClassName = null for *this* page
    public function getActionUrl($inActionID,$inPageClassName = null) {
        $module = null;
        $instanceID = null;
        if($inPageClassName === null) {
            $inPageClassName = get_class($this);
            $module = $this->getModule();
            $instanceID = $this->getInstanceID();
        }else{
            list($module,$class) = Application::getInstance()->pageClass2ModuleAndPageID($inPageClassName);
        }
        
        if($module == null) {
            throw new \Exception("Can't locate module for class: $inPageClassName");
        }
        return $module->assembleURL($this->getRequest(),$inPageClassName,$inActionID,$instanceID);
    }
    
    public function getPageUrl($inPageClassName = null) {
        return $this->getActionUrl(null,$inPageClassName);
    }
    
    public function generateInstanceID() {
        return uniqid(null,true);
    }
    
    public function invalidateComponent(Component $aThis) {
        if($this->_ajax !== null) {
            $this->_ajax->invalidate($aThis);
        }
    }
    
    public function renderComponentNow(Component $aThis) {
        if($this->_ajax !== null) {
            $this->_ajax->renderNow($aThis);
        } 
    }
    
	
	
    static public function contributeToHead($page) {
        $cricketJS = Container::resolveResourceUrl($page, get_class($page), "cricket/js/cricket.js");
        $pageID = $page->getInstanceID();
        return <<<END
            <script type="text/javascript" src = "$cricketJS"></script>
            <script type="text/javascript">
                _CRICKET_PAGE_INSTANCE_ = '$pageID';
            </script>
END;
    }
    
    
    public function getHeadContributions() {
        $added = array();
        $results = array();
                
        $this->contributeComponentsToHead($added,$results);
        return implode("\n", $results);
    }
    
    
   
    public function dispatchRequest($parts) {
        if(count($parts) == 0) {
            if($this->getRequest()->getMethod() == 'POST') {
                $this->post();
            }else{
                $this->render();
            }
            
            return true;
        }
                
        return $this->receiveActionRequest($parts);
    }
    
    protected function receiveActionRequest($parts) {
        $next = array_shift($parts);
        if(count($parts) == 0) {
            // page action
            $actionMethod = "action_$next";
            if(method_exists($this,$actionMethod)) {
                $this->$actionMethod();
                return true;
            }
        }else{
            $component = $this->findComponent($next);
            if($component !== null) {
                return $component->receiveActionRequest($parts);
            }
        }
        
        return false;
    }
    
    
    // BEGIN PRIVATE MESSAGE RECEIVER METHODS
    public function mcRegisterReceiver(MessageReceiver $inReceiver) {
        $this->_mcReceivers[] = $inReceiver;
    }
    
    public function mcRemoveReceiver(MessageReceiver $inReceiver) {
        $p = array_search($inReceiver,$this->_mcReceivers);
        if($p !== false) {
            unset($this->_mcReceivers[$p]);
            $this->_mcReceivers = array_values($this->_mcReceivers);
        }
    }
    // END PRIVATE MESSAGE RECEIVER METHODS
    
    
}