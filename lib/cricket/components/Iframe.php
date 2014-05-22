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

use cricket\core\Component;

class Iframe extends Component {
    
    public $attributes = array();
    public $styles = array();
    
	public function __construct($inID, $inUrl, $inHeight = 768) {
		$this->url = $inUrl;
		$this->height = $inHeight;
		parent::__construct($inID);
	}
    
    public function setFrameAttribute($inName,$inValue) {
        $this->attributes[$inName] = $inValue;
    }
    
    public function setFrameStyle($inName,$inValue) {
        $this->styles[$inName] = $inValue;
    }
	
	public function render() {
		$url = $this->url;
		$height = $this->height;
        $self = $this;
		$this->renderFunction(function($ctx,$tpx) use ($url, $height,$self){
            global $INLINE;
            
            $extraAttributes = array();
            foreach($self->attributes as $k => $v) {
                $extraAttributes[] = "$k = '{$INLINE(\cricket\utils\Utils::escape_html($v))}'";
            }
            $extraAttributes = implode(" ",$extraAttributes);
            
            $extraStyles = "";
            foreach($self->styles as $k => $v) {
                $extraStyles .= \cricket\utils\Utils::escape_html("$k:$v;");
            }
            
			echo "<iframe style='width:100%;height:{$height}px;$extraStyles' src='$url' $extraAttributes></iframe>";
		});
	}
}