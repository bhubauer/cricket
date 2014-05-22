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


namespace cricket\utils;

abstract class ExitHandler {
    
    protected $errorHandlerInstalled;
    protected $previousErrorHandler;
    protected $fatalTypes;
    
    public function __construct() {
        register_shutdown_function(function($me) {
            $me->handleShutdown();
        }, $this);
        
        $this->previousErrorHandler = null;
        $this->fatalTypes = 0;
        $this->errorHandlerInstalled = false;
    }
    
    // pass in non-fatal error types you want to be fatal:   (E_NOTICE | E_WARNING) for example.
    public function makeErrorTypeFatal($inType) {
        $this->fatalTypes = $inType;
        $this->installErrorHandler();
    }
    
    protected function installErrorHandler() {
        if(!$this->errorHandlerInstalled) {
            $this->errorHandlerInstalled = true;
            $this->previousErrorHandler = set_error_handler(array($this,"errorHandler"));
        }
    }
    
    public function errorHandler($errno,$errstr,$errfile, $errline,$errcontext) {
        
        // first see if its normally a fatal error
        $isFatal = false;
        switch($errno) {
            case E_ERROR:
            case E_USER_ERROR:
                $isFatal = true;
        }
        
        if(!$isFatal) {
            // next check to see if PHP config wants this acted upon.  keep in mind that this is the only way to detect the "@" warning suppression
            if($errno & error_reporting()) {
                // next see if its one we care about.
                if($errno & $this->fatalTypes) {
                    trigger_error("$errstr: $errfile:$errline", E_USER_ERROR);
                }
            }
        }
        
        if($this->previousErrorHandler) {
            return call_user_func($this->previousErrorHandler, $errno,$errstr,$errfile,$errline,$errcontext);
        }
        
        return false;
    }
    
    public function handleShutdown() {
        $lastError = error_get_last();
        if($lastError != null) {
            $scriptWasHalted = true;
            switch($lastError['type']) {
                case E_WARNING:
                case E_NOTICE:
                case E_CORE_WARNING:
                case E_COMPILE_WARNING:
                case E_USER_WARNING:
                case E_USER_NOTICE:
                case E_STRICT:
                case E_DEPRECATED:
                case E_USER_DEPRECATED:
                    $scriptWasHalted = false;
                    break;
            }
            
            if($scriptWasHalted) {
                $this->handleError($lastError);
            }
        }
    }
    
    public function handleError($lastError) {
        if(!Utils::isCLI()) {
            // normally PHP would return a 500 error in this situation, but if you have display error on or xdebug installed, it won't
            // this is not a problem on production servers, but if you reply on that status code for development, its a problem.
            // this will force the response code

            $protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
            $responseCode = "{$protocol} 500 Internal Server Error";
            header($responseCode);
        }

        $this->reportError($lastError['message'],$lastError['file'],$lastError['line']);
    }
    
    // this is called if the script was terminated by a fatal error.  Subclasses can report anyway they like
    abstract function reportError($inMessage,$inFile,$inLine);
    
    
}
