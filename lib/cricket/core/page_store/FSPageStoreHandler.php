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


namespace cricket\core\page_store;
use cricket\core\Page;

class FSPageStoreHandler implements PageStoreHandler {
    private $_store;
    
    public function __construct($storePath) {
        if(!file_exists($storePath)) {
            mkdir($storePath,0777,true);
        }
        
        $this->_store = $storePath;
    }
    
    public function load($sessionID,$instanceID,$pageVersion) {
        $fileName = "{$sessionID}_{$instanceID}_{$pageVersion}.store";
        $fileName = str_replace("/", "__", $fileName);
        $path = "{$this->_store}/$fileName";
        
        if(file_exists($path)) {
            $data = file_get_contents($path);
            if($data) {
                return unserialize($data);
            }
        }
        
        return null;
    }
    
    public function save($sessionID,Page $inPage,$inSaveAsInstanceID = null) {
        $pageClass = get_class($inPage);
        $pageVersion = $pageClass::$SESSION_PAGE_VERSION;
        
        $instanceID = $inPage->getInstanceID();
        if($inSaveAsInstanceID != null) {
            $instanceID = $inSaveAsInstanceID;
        }
        $fileName = "{$sessionID}_{$instanceID}_{$pageVersion}.store";
        $fileName = str_replace("/", "__", $fileName);
        $path = "{$this->_store}/$fileName";
        
        file_put_contents($path, serialize($inPage));
    }
    
    public function gc($maxAgeInSeconds) {        
        $i = new \DirectoryIterator($this->_store);
        foreach($i as $fileInfo) {
            if($fileInfo->isFile()) {
                if($fileInfo->getMTime() < (time() - $maxAgeInSeconds)) {
                    if(preg_match('/\.store$/',$fileInfo->getFilename())) {
                        unlink($fileInfo->getPathname());
                    }
                }
            }
        }
    }
}