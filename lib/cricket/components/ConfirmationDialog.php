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


class ConfirmationButton {
    public $label;
    public $message;
    public $callback;
    
    public function __construct($inLabel = null,$inMessage = null,$inCallback = null) {
        $this->label = $inLabel;
        $this->message = $inMessage;
        $this->callback = $inCallback;
    }
}


class ConfirmationDialog extends ModalComponent {
    const MESSAGE_OK = "ConfirmationDialog.CONFIRM_OK";
    const MESSAGE_CANCEL = "ConfirmationDialog.CONFIRM_CANCEL";
    const BUTTON_OK = "OK";
    const BUTTON_CANCEL = "Cancel";
    
    public $question;
    public $payload;
    public $buttons;
    
    public $canceled = true;
    
    public function __construct(
        $inID, 
        $inTitle, 
        $inQuestion,
        $inPayload = null,
        $inWidth = 400,
        $inButtons = null     // default Cancel, OK
    ) {
        parent::__construct($inID, $inTitle, $inWidth, null);
        $this->question = $inQuestion;
        $this->payload = $inPayload;
        
        if($inButtons) {
            $this->buttons = $inButtons;
        }else{
            $b = new ConfirmationButton();
            $b->label = self::BUTTON_CANCEL;
            $b->message = self::MESSAGE_CANCEL;
            $this->buttons = array($b);
            
            $b = new ConfirmationButton();
            $b->label = self::BUTTON_OK;
            $b->message = self::MESSAGE_OK;
            $this->buttons[] = $b;
        }
    }
    
    public function render() {
        $this->renderTemplate('cricket/templates/_ConfirmationDialog.php',array(
            'question' => $this->question,
            'buttons' => $this->buttons,
        ));
    }    
    
    public function action_closeDialog() {
        parent::action_closeDialog();
        
        if($this->canceled) {
            $this->broadcastMessage(self::MESSAGE_CANCEL);
        }
    }
    
    public function action_button() {
        $this->canceled = false;
        
        $index = $this->getIntParameter('index');
        $button = $this->buttons[$index];
        if($button->callback) {
            $button->callback($this->payload);
        }
        if($button->message) {
            $this->broadcastMessage($this->buttons[$index]->message, $this->payload);
        }
        $this->action_close();
    }
}