<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of MailerStub
 *
 * @author cuardin
 */
class MailerStub implements IMailer {
    public $to;
    public $subject;
    public $message;
    public $from;
    
    public function sendEmail($to, $subject, $message, $from) {
        $this->to = $to;
        $this->subject = $subject;
        $this->message = $message;
        $this->from = $from;
    }
    

}
