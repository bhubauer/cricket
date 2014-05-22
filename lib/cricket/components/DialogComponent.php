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


namespace cricket\components;


abstract class DialogComponent extends \cricket\core\Component {
    
    public $mTitle;
    public $mFocus;
    public $mWidth;
    public $mExtraOptions = array();
    public $mMinTop = 50;
    
    public function __construct($inID,$inTitle,$inWidth,$inFocus) {
        parent::__construct($inID);
        $this->mTitle = $inTitle;
        $this->mFocus = $inFocus;
        $this->mWidth = $inWidth;
    }
    
    public function setDialogOption($inName,$inValue) {
        $this->mExtraOptions[$inName] = $inValue;
    }
    
    protected function openDialog() {
        if($this->mFocus !== null) {
            $this->getAjaxResponse()->addPostScript("jQuery('#" . $this->mFocus . "').focus();");
        }
        
        $this->getAjaxResponse()->addPostScript("cricket_alert_position('" . $this->getDivId() . "',{$this->mMinTop});");
    }
    
    protected function getDialogOptions() {
        $result = array();
        
        $result['title'] = $this->mTitle === null ? "" : $this->mTitle;
        $result['width'] = $this->mWidth;
        
        foreach($this->mExtraOptions as $k => $v) {
            $result[$k] = $v;
        }
        
        //$result['position'] = "center";
        
        return $result;
    }
    
    // you can call this to close the dialog at any time.
    public function action_close() {
        $this->getAjaxResponse()->addPostScript("jQuery('#" . $this->getDivId() . "').dialog('close');");
    }
    
    // do not call this directly.  This is so that our object is removed from the tree
    // if the JS code on the browser closes the dialog
    public function action_closeDialog() {
        $this->removeFromParent();
    }
}