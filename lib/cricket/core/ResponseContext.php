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


class ResponseContext {
    
    
    public function setContentType($inType) {
        header("Content-Type: $inType");
    }
    
    public function sendError($inErrorCode) {
        header("HTTP/1.0 $inErrorCode");
        flush();
    }
    
    public function sendAccessDenied() {
        $this->sendError("403 Forbidden");
        echo "403 Forbidden";
    }
    
    public function sendRedirect($inUrl) {
        header("Location: $inUrl");
        flush();
    }
    
    
}