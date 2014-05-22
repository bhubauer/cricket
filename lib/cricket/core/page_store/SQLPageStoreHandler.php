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
use cricket\sql\Connection;
use cricket\sql\Model;

/*
CREATE TABLE `cricket_page_store` (
  `instance_id` varchar(100) NOT NULL,
  `timestamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `page_data` longblob,
  PRIMARY KEY (`instance_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8
 * 
 */

class SQLPageStoreHandler implements PageStoreHandler {
    const   CONFIG_TABLE = 'table';
    const   CONFIG_ID_FIELD = 'id';
    const   CONFIG_DATA_FIELD = 'data';
    const   CONFIG_TIMESTAMP_FIELD = 'timestamp';
    
    const   STORE_TABLE = 'cricket_page_store';
    const   STORE_ID_FIELD = 'instance_id';         // MUST BE UNIQUE KEY
    const   STORE_DATA_FIELD = 'page_data';
    const   STORE_TIMESTAMP_FIELD = 'timestamp';    // MUST BE SET ON UPDATE
    
    private $connection;
    private $config;
    
    public function __construct(\PDO $inPDO,$inConfig = array()) {
        $this->config = array(
            self::CONFIG_TABLE => self::STORE_TABLE,
            self::CONFIG_ID_FIELD => self::STORE_ID_FIELD,
            self::CONFIG_DATA_FIELD => self::STORE_DATA_FIELD,
            self::CONFIG_TIMESTAMP_FIELD => self::STORE_TIMESTAMP_FIELD
        );
        
        foreach($inConfig as $k => $v) {
            $this->config[$k] = $v;
        }
        
        $this->connection = Connection::connectionWithPDO($inPDO);
    }
    
    public function load($sessionID,$instanceID,$pageVersion) {
        $key = "{$sessionID}_{$instanceID}_{$pageVersion}";
        
        $r = $this->connection->query("SELECT {$this->config[self::CONFIG_DATA_FIELD]} FROM {$this->config[self::CONFIG_TABLE]} WHERE {$this->config[self::CONFIG_ID_FIELD]} = :id", array(":id" => $key))->fetch_assoc();
        if($r !== null) {
            $data = $r[$this->config[self::CONFIG_DATA_FIELD]];
            return unserialize($data);
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
        $key = "{$sessionID}_{$instanceID}_{$pageVersion}";
        
        $sql = "
            INSERT INTO {$this->config[self::CONFIG_TABLE]} 
                ({$this->config[self::CONFIG_ID_FIELD]},{$this->config[self::CONFIG_DATA_FIELD]},{$this->config[self::CONFIG_TIMESTAMP_FIELD]})
                VALUES (:id,:data,CURRENT_TIMESTAMP)
            ON DUPLICATE KEY UPDATE {$this->config[self::CONFIG_DATA_FIELD]} = :data,{$this->config[self::CONFIG_TIMESTAMP_FIELD]} = CURRENT_TIMESTAMP
        ";
        
        $this->connection->query($sql,array(":id" => $key,":data" => serialize($inPage)),false);
    }
    
    public function gc($maxAgeInSeconds) {
        $dt = new \DateTime();
        $dt->sub(new \DateInterval("PT{$maxAgeInSeconds}S"));
        $age = $dt->format(Model::DATETIME_FORMAT);
        $this->connection->query("DELETE FROM {$this->config[self::CONFIG_TABLE]} WHERE {$this->config[self::CONFIG_TIMESTAMP_FIELD]} < :age",array(":age" => $age),false);
    }
}