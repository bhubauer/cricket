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

use \cricket\components\DialogComponent;

abstract class Container implements MessageReceiver {
    // these are all public so that the default serialize implementation will work
    
    public $_components;     // Map<String,Component>
    public $_id;
    public $_localID;
    /** @var Container */
    public $_parent;
    
    public function __sleep() {
        $vars = array_keys(get_object_vars($this));
        $transients = $this->getTransientVariables();
        return array_diff($vars,$transients);
    }
    
    protected function getTransientVariables() {
        return array();
    }
    
    public function getParent() {
        return $this->_parent;
    }
    
    public function getId() {
        return $this->_id;
    }
    
    public function getLocalId() {
        return $this->_localID;
    }
    
    public function getDivId() {
        return "component_" . $this->_id;
    }
    
    public function getDivClass() {
        return "";
    }
    
    public function __construct($inID) {
        $this->_components = array();
        $this->_localID = $inID;
        $this->_id = null;
        $this->_parent = null;
    }
    
    public function setRedirect($inUrl) {
        /* @var $thisPage Page */
        $thisPage = $this->getPage();
        if($thisPage->isAjax()) {
            $thisPage->getAjaxResponse()->setRedirect($inUrl);
        }else{
            $this->getResponse()->sendRedirect($inUrl);
        }
    }
    
    public function broadcastMessage($inMessage,$inData = null) {
        MessageCenter::broadcastMessage($inMessage, $inData, $this);
    }
    
    public function broadcastMessageToTree($inMessage,$inData,$inSender) {
        if($inSender instanceof Container) {
            $this->receiveMessage($inMessage,$inData,$inSender);
        }else{
            $this->messageReceived($inMessage,$inData,$inSender);
        }
        
        foreach($this->_components as $id => $c) {
            $c->broadcastMessageToTree($inMessage,$inData,$inSender);
        }
    }
    
    // used to receive message from objects other than Containers
    public function messageReceived($inMessage,$inData,$inSender) {
        
    }
    
    protected function receiveMessage($inMessage,$inData,Container $inSender) {
        // override if need to receive message
    }
    
    public function resolveChildID($inLocalChildID) {
        if($this->_id === null) {
            return $inLocalChildID;
        }
        return $this->_id . "_" . $inLocalChildID;
    }
    
    protected function setChildIDs() {
        foreach($this->_components as $id => $c) {
            if($c->_id === null) {
                $c->_id = $this->resolveChildID($c->_localID);
                $c->setChildIDs();
            }
        }
    }
    
    
    protected function receiveActionRequest($parts) {
        $next = array_shift($parts);
        if(count($parts) == 0) {
            // action
            
            $currentTarget = $this;
            while($currentTarget) {
                $actionMethod = "action_{$next}";
                if(method_exists($currentTarget, $actionMethod)) {
                    $currentTarget->$actionMethod();
                    return true;
                }else{
                    $currentTarget = $currentTarget->_parent;
                }
            }
        }
        
        return false;
    }
    
    
    public function addComponent(Component $newComponent) {
        $this->_components[$newComponent->_localID] = $newComponent;
        $newComponent->_parent = $this;
        if($this->_id !== null) {
            $newComponent->_id = $this->resolveChildID($newComponent->_localID);
            $newComponent->setChildIDs();
        }else{
            if($this instanceof Page) {
                $newComponent->_id = $newComponent->_localID;
                $newComponent->setChildIDs();
            }else{
                $newComponent->_id = null;
            }
        }
        
        $newComponent->addedToParent();
    }
    
    // override if you need to know when you've been added to a new parent
    protected function addedToParent() {
        
    }
    
    public function removeComponent(Component $c) {
        unset($this->_components[$c->_localID]);
        $c->_parent = null;
        $c->_id = null;
    }
    
    public function removeAllComponents() {
        $this->_components = array();
    }
    
    
    public function addDialogComponent(DialogComponent $newComponent) {
        if($this->getPage()->isAjax()) {
            $this->addComponent($newComponent);
            $newComponent->invalidate();
            $closeUrl = $newComponent->getActionUrl("closeDialog");
            $newComponent->openDialog();
            $this->getAjaxResponse()->openDialog($newComponent->getDivId(),$closeUrl,$newComponent->getDialogOptions());
        }
    }
    
    
    /** @return Component */
    public function getComponent($localID) {
        if(isset($this->_components[$localID])) {
            return $this->_components[$localID];            
        }
        return null;
    }
    
    /** @return Page */
    public function getPage() {
        $result = null;
        
        if($this->_parent !== null) {
            $result = $this->_parent;
            while($result->_parent !== null) {
                $result = $result->_parent;
            }
        }else{
            $result = $this;
        }
        
        if($result instanceof Page) {
            return $result;
        }else{
            return null;
        }
    }
    
    /** @return Component */
    public function findComponent($inID) {
        foreach($this->_components as $id => $thisComp) {
            if($thisComp->_id == $inID) {
                return $thisComp;
            }else{
                $thisComp = $thisComp->findComponent($inID);
                if($thisComp !== null) {
                    return $thisComp;
                }
            }
        }
        
        return null;
    }
    
    
    abstract public function getActionUrl($inActionID);
    
    
    
    static public function resolveResourceUrl($page,$class,$path) {
        $result = null;
        $a = array();
        $fsPath = Container::resolveTemplatePath($page,$class,$path,$a,false);
        if($fsPath !== null) {
            $result = $page->getRequest()->translatePath($fsPath);
        }
        
        return $result;
    }
    
    
    static public function resolveTemplatePath($page,$class,$path,&$inParams,$showMissingTemplate = true) {
        // if starts with a slash, then its a full path from the contextRoot
        // that means there is no wayt to do a file system full path...  thats probably ok
        
        if(preg_match("/^\//",$path)) {
            $result = Application::getInstance()->getActiveModule()->resolveResourcePath($path,$page->getRequest()->getAttribute("contextPath"));
            if($result) {
                return $result;
            }else{
                $inParams['templatePath'] = $path;
                $a = array();
                return Container::resolveTemplatePath($page,$class, "missing_template.php", $a);
            }
        }
        
        $iter = new SearchPathIterator($class);
        while($iter->hasNext()) {
            $testPath = $iter->next() . "/" . $path;
            if(file_exists($testPath)) {
                return $testPath;
            }
        }
        
        // if didn't find it and we aren't the Page class, then allow the page to be involved
        if($class != get_class($page)) {
            return self::resolveTemplatePath($page, get_class($page), $path, $inParams, $showMissingTemplate);
        }else{
            if($showMissingTemplate) {
                $inParams['templatePath'] = $path;
                return Container::resolveTemplatePath($page,$class, "missing_template.php", $a);
            }else{
                return null;
            }
        }
    }
    
    public function resourceUrl($inPath) {
        return Container::resolveResourceUrl($this->getPage(), get_class($this), $inPath);
    }
    
    public function resourcePath($inPath) {
        $a = array();
        return Container::resolveTemplatePath($this->getPage(),get_class($this),$inPath,$a,false);
    }
    
    protected function locateTemplate($inTemplatePath,&$inParamsArray) {
        return Container::resolveTemplatePath($this->getPage(), get_class($this), $inTemplatePath, $inParamsArray);
    }
    
    // this method sets up the rendering context, and calls the given function with ($cricket,$tpl)
    //    public function render() {
    //        $self = $this;
    //        $this->renderFunction(function(\cricket\core\CricketContext $cricket,\cricket\core\TemplateInheritanceContext $tpl) use($self) {
    //            $cricket->component($self->mContentID);
    //        });
    //    }
    // this is useful its overkill to create an actual template file, such as would be the case for some kind
    // of wrapper component that just needs to render a child.
    protected function renderFunction($inFunction) {
        /* @var $ctx CricketContext */
        $ctx = $this->getPage()->getRequest()->getAttribute("cricket");
        /* @var $savedComponent Container */
        $savedComponent = $ctx->getComponent();
        $ctx->setComponent($this);
        
        $tpl = new TemplateInheritanceContext($ctx);
        $inFunction($ctx,$tpl);
        $tpl->flush();
                
        $ctx->setComponent($savedComponent);
    }
    
    protected function renderTemplate($inTemplatePath,$inParamsArray = array()) {
        $fullTemplatePath = $this->locateTemplate($inTemplatePath,$inParamsArray);
        
        /* @var $page Page */
        $page = $this->getPage();
        
        foreach($inParamsArray as $k => $v) {
            $page->getRequest()->setAttribute($k,$v);
        }
        
        /* @var $ctx CricketContext */
        $ctx = $page->getRequest()->getAttribute("cricket");
        /* @var $savedComponent Container */
        $savedComponent = $ctx->getComponent();
        $ctx->setComponent($this);
        
        
        { // scope block -- PHP doesn't have brace level scope, right... what is the purpose of this?
            foreach($page->getRequest()->getFlattenedMap() as $k => $v) {
                $$k = $v;
            }
            $tpl = new TemplateInheritanceContext($ctx);
            if(!($this instanceof Page)) {
                echo "<!-- BEGIN TEMPLATE: $fullTemplatePath -->";
            }
            require($fullTemplatePath);
            if(!($this instanceof Page)) {
                echo "<!-- END TEMPLATE: $fullTemplatePath -->";
            }
            $tpl->flush();
        }
        
        $ctx->setComponent($savedComponent);
    }
    
    
    public function renderTemplateToString($inTemplatePath,$inParamsArray = array()) {
        $result = "";
            
        $fullTemplatePath = $this->locateTemplate($inTemplatePath,$inParamsArray);
        
        /* @var $page Page */
        $page = $this->getPage();
        
        foreach($inParamsArray as $k => $v) {
            $page->getRequest()->setAttribute($k,$v);
        }
        
        /* @var $ctx CricketContext */
        $ctx = $page->getRequest()->getAttribute("cricket");
        /* @var $savedComponent Container */
        $savedComponent = $ctx->getComponent();
        $ctx->setComponent($this);
        
        
        { // scope block -- PHP doesn't have brace level scope, right... what is the purpose of this?
            foreach($page->getRequest()->getFlattenedMap() as $k => $v) {
                $$k = $v;
            }
            $tpl = new TemplateInheritanceContext($ctx);
            ob_start();
            require($fullTemplatePath);
            $tpl->flush();
            $result = ob_get_clean();
        }
        
        $ctx->setComponent($savedComponent);
        
        return $result;
    }
    
    
    
    
    protected function renderJSON($inArray) {
    	echo json_encode($inArray);
    }
	
    // this calls the static method contributeToHead($page) on each class this this hierachy up to $class
    // ensures that each class only contributes once.
    public function contributeClassHierarchyToHead(&$added,&$results) {
		$this->contributeClassHierarchyToHeadFromClass(get_class($this),$this->getPage(),$added,$results);
    }
    
    public function contributeClassHierarchyToHeadFromClass($class,$page,&$added,&$results) {
        $classes = array();
        $ref = new \ReflectionClass($class);
        while($ref !== false) {
            if(!isset($added[$ref->getName()])) {
                array_unshift($classes, $ref);
                $added[$ref->getName()] = 1;
            }
            $ref = $ref->getParentClass();
        }
        
        foreach($classes as $thisClass) {
            if($thisClass->hasMethod("contributeToHead")) {
                /* @var $thisMethod ReflectionMethod */
                $thisMethod = $thisClass->getMethod("contributeToHead");
                if($thisMethod->getDeclaringClass()->getName() == $thisClass->getName()) {
                    $results[] = $thisMethod->invoke(null,$page);
                }
            }
        }    
    }

    
    public function contributeComponentsToHead(&$added,&$results) {
        $this->contributeClassHierarchyToHead($added,$results);
        foreach($this->_components as $id => $c) {
            $c->contributeComponentsToHead($added,$results);
        }
    }
    
    
    
    /** @return RequestContext */
    public function getRequest() {
        return $this->getPage()->getRequest();
    }
    
    /** @return ResponseContext */
    public function getResponse() {
        return $this->getPage()->getResponse();
    }
    
    public function getParams() {
        return $_REQUEST;
    }

    public function getFiles() {
        return $_FILES;
    }
    
    public function getParameter($inName,$inDefaultValue = null) {
        if(isset($_REQUEST[$inName])) {
            return $_REQUEST[$inName];
        }else{
            return $inDefaultValue;
        }
    }
    
    public function getIntParameter($inName,$inDefaultValue = null) {
        $result = $this->getParameter($inName);
        if($result !== null) {
            $result = filter_var($result, FILTER_SANITIZE_NUMBER_INT);
            if (filter_var($result, FILTER_VALIDATE_INT) === false) {
                return $inDefaultValue;
            } else {
                return intval($result);
            }
        }
        
        return $inDefaultValue;
    }
    
    public function getBoolParameter($inName,$inDefaultValue = null) {
        $result = $this->getParameter($inName);
        if($result !== null) {
            return filter_var($result, FILTER_VALIDATE_BOOLEAN);
        }
        
        return $inDefaultValue;
    }
    
    
    /** @return AjaxResponse */
    public function getAjaxResponse() {
        /* @var $p Page */
        $p = $this->getPage();
        if($p->getAjaxManager() !== null) {
            return $p->getAjaxManager()->getResponse();
        }
        return null;
    }
    
    
    // this function renders a component into the ajax response.
    // you would use this if you needed to inject a new component into
    // a response without invalidating the whole parent component.
    const DYNAMIC_REPLACE = 'replace';  // replaces contenst of $inContainerElementID
    const DYNAMIC_APPEND = 'append';    // appends to children of $inContainerElementID
    public function addDynamicAjaxComponent($inContainerElementID,$inNewComponent,$inMode = self::DYNAMIC_REPLACE) {
        $this->addComponent($inNewComponent);
        ob_start();
        $this->getPage()->renderComponent($inNewComponent->_id);
        $data = ob_get_clean();
        if($inMode == self::DYNAMIC_REPLACE) {
            $this->getAjaxResponse()->setUpdate($inContainerElementID, $data);
        }else{
            $this->getAjaxResponse()->setAppendTo($inContainerElementID,$data);
        }
    }
    
    
    public function renderDynamicStaticComponent($inContainerElementID,$inComponentID,$inRenderID,$inRenderData,$inMode = self::DYNAMIC_REPLACE) {
        $c = $this->getComponent($inComponentID);
        if($c) {
            ob_start();
            $this->getPage()->renderStaticComponent($c->_id, $inRenderID, $inRenderData);
            $data = ob_get_clean();
            if($inMode == self::DYNAMIC_REPLACE) {
                $this->getAjaxResponse()->setUpdate($inContainerElementID, $data);
            }else{
                $this->getAjaxResponse()->setAppendTo($inContainerElementID,$data);
            }
        }else{
            throw new \Exception("Unknown component: $inComponentID");
        }
    }
    
    public function putSession($key,$value) {
        $this->getRequest()->setSessionAttribute($key,$value);
    }
    
    public function getSession($key) {
        return $this->getRequest()->getSessionAttribute($key);
    }
    
    // these methods are the same as above, except the ask the application to set and get the values
    // and it uses a prefix that creates an application private namespace.
    // the above methods use the global namespace
    
    public function putAppSession($key,$value) {
        Application::getInstance()->setSessionAttribute($this->getRequest(),$key, $value);
    }
    
    public function getAppSession($key) {
        return Application::getInstance()->getSessionAttribute($this->getRequest(),$key);
    }
    
    public function clearAppSession($key) {
        Application::getInstance()->clearSessionAttribute($this->getRequest(),$key);
    }
    
}