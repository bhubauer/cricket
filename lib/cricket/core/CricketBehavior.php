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

// PRIOR TO THE PUBLIC RELEASE OF CRICKET, THIS FILE WAS A MEANS TO ALL APPS TO OPT IN
// TO NEW BEHAVIOR THAT OTHERWISE MIGHT BREAK THE EXISTING APP.   AT THE POINT OF PUBLIC RELEASE
// THIS LEGACY BEHAVIOR HAS BEEN REMOVED.   THIS FILE HOWEVER REMAINS IN PLACE IN ANTICIPATION OF 
// FUTURE SIMILAR ISSUES.


//////////////////////////////////////////

// THE PURPOSE OF THIS CLASS IS TO ALLOW AN APPLICATION TO LET CRICKET KNOW
// WHICH BEHAVIORS IT EXPECTS.   SOMETIMES A CHANGE IS REQUIRED THAT WOULD
// MAKE EXISTING CODE BREAK.  THIS ALLOWS A NEWER APPLICATION TO LET CRICKET
// KNOW THAT IT CAN ACCEPT THE NEW BEHAVIORS

// in your applications page.php (the dispatcher), you should call
// CricketBehavior::setBehavior(CricketBehavior::V2), where V2 is whatever
// the most recent version number is that you are compatible with
//
// if a new incomaptible change is introduced into cricket, you should
// define a new version number, add some new constants.  Set the default
// value to be what would be compatible, then specify the new incompatible
// value in the new version setting.   Then the cricket code should
// consult the setting value to see which behavior should be performed:
//    if(CricketBehavior::$behavior[CricketBehavior::SHOULD_DO_SOMETHING]) {
//      ....
//    }

class CricketBehavior {
    
    /////////////////////////////////////////////////////////////////////////////
    //  BEHAVIOR VERSION NUMBERS -- NEWEST ON TOP
    //                              NEW APPS SHOULD ALWAYS USE NEWEST
    /////////////////////////////////////////////////////////////////////////////
    
//    const V2 = 2;
//    const V3 = 3;
    
    static public function setBehavior($inVersionNumber) {
        
        // apply settings from end of list forward through, and including, 
        // the version specified
        
        foreach(array_reverse(self::$VERSIONS) as $v) {
            foreach($v[1] as $behavior => $setting) {
                self::$behavior[$behavior] = $setting;
            }
            
            if($v[0] == $inVersionNumber) {
                break;
            }
        }
    }
    
    /////////////////////////////////////////////////////////////////////////////
    //  GROUP DEFINITIONS -- ALL GROUPS ARE DELTAS TO WHAT CAME BEFORE
    //                       NEW CHANGES ON TOP!
    /////////////////////////////////////////////////////////////////////////////

    static private $VERSIONS = array(
//        array(
//            self::V3,
//            array(
//                self::DESTROY_SESSION_ON_LOGOUT => true,
//            )
//        ),
//        array(
//            self::V2,
//            array(
//                self::ALWAYS_REQUIRE_INSTANCE_ID_WHEN_SESSION_IS_CONSULTED => true,
//            )
//        )
    );
    
    
    /////////////////////////////////////////////////////////////////////////////
    // CURRENT BEHAVIOR -- the settings here should always represent
    //                     the "compatible" setting
    /////////////////////////////////////////////////////////////////////////////

    static public $behavior = array(

    );
    
    /////////////////////////////////////////////////////////////////////////////
    // BEHAVIOR FLAG NAMES
    /////////////////////////////////////////////////////////////////////////////
            
}