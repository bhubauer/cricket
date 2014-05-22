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

use cricket\core\Container;


// sometimes objects other than containers need to broadcast messages.
// this allows that.    .  

class MessageCenter {
    /** @var Page */    public static $page;
    public static $active = true;
    public static $blocked = array();
    public static $receivers = array();
    
    public static $sending = false;
    public static $toRemove = array();

    public static function registerPage(Page $inPage) {
        self::$page = $inPage;
    }
    
    public static function registerReceiver(MessageReceiver $inReceiver) {
        self::$page->mcRegisterReceiver($inReceiver);
    }
    
    public static function removeReceiver(MessageReceiver $inReceiver) {
        if(self::$sending) {
            self::$toRemove[] = $inReceiver;
        }else{
            self::$page->mcRemoveReceiver($inReceiver);
        }
    }
    
    /** @return Page */
    public static function getActivePage() {
        return self::$page;
    }
    
    public static function setEnabled($inEnabled) {
        $old = self::$active;
        self::$active = $inEnabled;
        return $old;
    }
    
    public static function setMessageEnabled($inMessage,$inEnabled) {
        $old = !isset(self::$blocked[$inMessage]);
        if($inEnabled) {
            unset(self::$blocked[$inMessage]);
        }else{
            self::$blocked[$inMessage] = 1;
        }
        return $old;
    }
    
    
    public static function canSendMessage($inMessage) {
        $send = false;
        if(self::$active) {
            if(!isset(self::$blocked[$inMessage])) {
                $send = true;
            }
        }
        
        return $send;
    }
    
    
    
    public static function broadcastMessage($inMessage,$inData,$sender = null) {
        if(self::canSendMessage($inMessage)) {
            self::$sending++;
            
            $page = self::$page;
            
            if($sender) {
                // if the sender is a container and it doesn't yet have a page (constructor) we need to not send the message since thats
                // the behavior that the old system used.
                
                if($sender instanceof Container) {
                    $page = $sender->getPage();
                }
            }else{
                $sender = self::$page;
            }
            
            if($page) {
                $page->broadcastMessageToTree($inMessage, $inData, $sender);
            }
            
            
            foreach(self::$page->_mcReceivers as /* @var $thisReceiver MessageReceiver */ $thisReceiver) {
                if(!in_array($thisReceiver, self::$toRemove)) {
                    $thisReceiver->messageReceived($inMessage, $inData, $sender);
                }
            }
            
            self::$sending--;
            
            if(self::$sending == 0) {
                if(count(self::$toRemove)) {
                    foreach(self::$toRemove as $removeThis) {
                        self::$page->mcRemoveReceiver($removeThis);
                    }
                    
                    self::$toRemove = array();
                }
            }
        }
    }
    
}
