<?php

require_once './core/utilities.php';

init();

try {
    $mysql = connectDatabase();
    $username = getParameter($mysql,"email");    
    
    $mailer = new Mailer();
    try {
        $isTest = getParameter($mysql, "test");
        if ( !strcmp($isTest, 'true') ) {
            $mailer = new MailerStub();
        }
    } catch ( Exception $e ) {
        
    }   
    
    if ( !checkUserEditKeyOrRECAPTCHA($mysql) ) {
        echo "INVALID_CAPCHA";
        return;
    }
    $verificationKey = rand_string($length);    
    
    resetPassword($mysql, $email, $verificationKey );
    
} catch ( Exception $e) {    
    echo "FAIL: " . $e->getMessage();
}

?> 
