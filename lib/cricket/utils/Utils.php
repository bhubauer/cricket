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

class Utils {
    
    // this exists so you can do this:     if(Utils::isEmpty(someFunction()))    
    static public function isEmpty($inValue) {
        return empty($inValue);
    }
    
    // this is wrapped in case we want to change the default flags for htmlentities
    static public function escape_html($inValue) {
        return htmlentities($inValue,ENT_QUOTES,'UTF-8');
    }
    
    
    static public function formatCurrency($inValue) {
        // TODO:  Should not be U.S. centric
        
        return sprintf('$%s',  number_format($inValue,2));
    }
    
    static public function formatTextArea($inValue) {
        $data = Utils::escape_html($inValue);
        return str_replace("\n", "<br>", $data);
    }
    
    // look for URLs in content and turn them into links
    static public function injectLinks($inContent,$inClass=null,$inTarget=null) {
        $URL_REGEX = '/(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w\.-]*)*\/?[^\s]*[^\.]/';

        $class = "";
        if($inClass) {
            $class = " class = '{$inClass}'";
        }
        
        $target = "";
        if($inTarget) {
            $target = " target = '{$inTarget}'";
        }
        
        return preg_replace_callback($URL_REGEX,function($matches) use($class,$target) {
            if(strpos($matches[0],"http") === 0) {
                return "<a href='{$matches[0]}'{$class}{$target}>{$matches[0]}</a>";
            }else{
                $parts = explode(".",$matches[0]);
                if(count($parts) == 3) {
                    return "<a href='http://{$matches[0]}'{$class}{$target}>{$matches[0]}</a>";
                }else{
                    return $matches[0];
                }
            }
        },$inContent);
    }
    
    // default until absolute is 7 days
    static public function formatRelativeDate($inTimestamp,$inDurationUntilAbsolute = 604800,$inAbsoluteDateFormat = 'n/j/Y') { 
        $delta = time() - $inTimestamp;
        
        if($delta > $inDurationUntilAbsolute) {
            return date($inAbsoluteDateFormat,$inTimestamp);
        }
        
        if($delta < 0) {
            return "net yet";
        }
        
        if($delta < 30) {
            return "just now";
        }
        
        if($delta < 60) {
            return "$delta seconds ago";
        }
        
        if($delta < 60 * 60) {
            $minutes = round($delta / 60);
            if($minutes < 2) {
                return "$minutes minute ago";
            }else{
                return "$minutes minutes ago";
            }
        }
        
        if($delta < 60 * 60 * 24) {
            $hours = round($delta / (60 * 60));
            if($hours <  2) {
                return "$hours hour ago";
            }else{
                return "$hours hours ago";
            }
        }
        
        if($delta < 60 * 60 * 24 * 7) {
            $days = round($delta / (60 * 60 * 24));
            if($days < 2) {
                return "$days day ago";
            }else{
                return "$days days ago";
            }
        }
        
        if($delta < 60 * 60 * 24 * 30) {
            $weeks = round($delta / (60 * 60 * 24 * 7));
            if($weeks < 2) {
                return "$weeks week ago";
            }else{
                return "$weeks weeks ago";
            }
        }
        
        if($delta < 60 * 60 * 24 * 7 * 365) {
            $months = round($delta / (60 * 60 * 24 * 30));
            if($months < 2) {
                return "$months month ago";
            }else{
                return "$months months ago";
            }
        }
        
        $years = round($delta / (60 * 60 * 24 * 365));
        if($years < 2) {
            return "$years year ago";
        }else{
            return "$years years ago";
        }
        
    }
    
    static public function load_dynamic_url($inURL) {
        $c = curl_init();
        curl_setopt($c, CURLOPT_URL, $inURL);
        curl_setopt($c, CURLOPT_HEADER, 0);
        curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($c);
        curl_close($c);
        return $c;
    }
    
    
    static public function isCLI() {
        return (!isset($_SERVER['SERVER_SOFTWARE']) && (php_sapi_name() == 'cli' || (is_numeric($_SERVER['argc']) && $_SERVER['argc'] > 0)));
    }

    
    static public function error_print_r($inValue) {
        ob_start();
        print_r($inValue);
        error_log(ob_get_clean());
    }
    
    
}         
