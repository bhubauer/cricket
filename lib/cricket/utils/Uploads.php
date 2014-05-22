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

class Uploads {
    const FILE_OK = null;
    const FILE_CORRUPT = 'corrupt';
    const FILE_MISSING = 'no-file';
    const FILE_TOO_BIG = 'too-big';
    const FILE_UNKNOWN_ERROR = 'unknown-error';

    
    // pass in $_FILES['some-name']
    static public function validateFileUpload($inFileUpload,$maxSize = null) {
        // Undefined | Multiple Files | $_FILES Corruption Attack
        // If this request falls under any of them, treat it invalid.
        if (
            !isset($inFileUpload['error']) ||
            is_array($inFileUpload['error'])
        ) {
            return self::FILE_CORRUPT;
        }

        // Check $_FILES['upfile']['error'] value.
        switch ($inFileUpload['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_NO_FILE:
                return self::FILE_MISSING;
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                return self::FILE_TOO_BIG;
            default:
                return self::FILE_UNKNOWN_ERROR;
        }

        if($maxSize) {
            if($inFileUpload['size'] > $maxSize) {
                return self::FILE_TOO_BIG;
            }
        }
        
        return self::FILE_OK;
    }
    
    
    static public function getFileUploadMimeType($inFileUpload) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $type = finfo_file($inFileUpload['tmp_name']);
        finfo_close($finfo);
        
        if(!$type) {
            $type = $inFileUpload['type'];
        }
        
        return $type;
    }
    
    static public function isUploadBrowserSafeImage($inFileUpload) {
        $type = @exif_imagetype($inFileUpload['tmp_name']);
        if($type !== false) {
            $mimeType = image_type_to_mime_type($type);
            switch($mimeType) {
                case 'image/gif':
                case 'image/jpeg':
                case 'image/png':
                    return true;
            }
        }
        
        return false;
    }
}
