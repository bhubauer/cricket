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


class AjaxResponse {
    public $m;      // map<String,Object>
    
    // TODO:  Add support for component based preserve scrolling -- allow component to 
    // contribute jQuery selector for div to preserve
    
    public function __construct() {
        $this->m = array(
            'message' => "",
            'redirect' => "",
            'scripts_pre' => array(), // List<String>
            'updates' => array(),  // Map<String,String>
            'append' => array(),
            'scripts_post' => array(), // List<String>
            'modal' => null,
            'sounds' => array(),
        );
    }
    
    
    // see http://jqueryui.com/docs/dialog/ for options
    public function openDialog($inID,$inCloseUrl,$inOptions) {
        $params = array(
            'content' => '',
            'id' => $inID,
            'options' => $inOptions,
            'closeUrl' => $inCloseUrl
        );
        
        $this->m["dialog"] = $params;
    }
    
    public function playSound($inSoundHref) {
        $this->m['sounds'][] = $inSoundHref;
    }
    
    public function setRedirect($s) {
        $this->m['redirect'] = $s;
    }
    
    public function addPreScript($s) {
        $this->m['scripts_pre'][] = $s;
    }
    
    public function addPostScript($s) {
        $this->m['scripts_post'][] = $s;
    } 
    
    public function setUpdate($inID,$inContent) {
        $this->m['updates'][$inID] = $inContent;
    }
    
    public function setAppendTo($inID,$inContent) {
        $this->m['append'][$inID] = $inContent;
    }
    
    public function setMessage($inMessage) {
        $this->m['message'] = $inMessage;
    }
}