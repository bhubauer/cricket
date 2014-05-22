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

namespace app\pages;

use cricket\core\Page;
use cricket\core\Component;


class CounterPanel extends Component {
    
    public $count = 0;
    
    public function action_increment() {
        $this->count++;
        $this->invalidate();
    }
    
    public function action_decrement() {
        $this->count--;
        $this->invalidate();
    }
    
    public function action_set() {
        $this->count = $this->getIntParameter('count');
        $this->invalidate();
    }
    
    public function render() {
        $this->renderTemplate("_counter_panel.php", array(
            'count' => $this->count,
        ));
    }
}


class PageExample extends Page {
    
    public function init() {
        $this->addComponent(new CounterPanel('counter'));
    }
    
    public function action_increment() {
        $this->getComponent("counter")->action_increment();
    }
    
    public function render() {
        $this->renderTemplate("example.php", array(
            'pageTitle' => "Example Page",
        ));
    }
    
}
