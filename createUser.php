<?php

require_once ( './core/utilities.php' );
require_once ( './core/userManagementCore.php' );
require_once ( './core/recaptchalib.php' );
require_once ( './core/Mailer.php' );
require_once ( './test/MailerStub.php' );

init();

try {
    $mysql = connectDatabase();    

    //Escape all the user input to be SQL safe.
    $username = getParameter($mysql, "username");
    $email = getParameter($mysql, "email");
    $password = getParameter($mysql, "password");        
    
    $mailer = new Mailer();
    try {
        $isTest = getParameter($mysql, "test");
        if ( !strcmp($isTest, 'true') ) {
            $mailer = new MailerStub();
        }
    } catch ( Exception $e ) {
        
    }                    
    //Check if we have a recaptcha a user creation key
    $isHuman = false;        
    try {             
        $privateKeyProvided = getParameter($mysql, "userEditKey");                                        
        if (!strcmp($privateKeyProvided, getUserEditKey())) {           
            $isHuman = true;            
        } else {                        
        }
    } catch (Exception $e) {
        //Do nothing.         
    }        
    
    
    if ( !$isHuman ) {
        $challenge = getParameter($mysql, "recapcha_challenge_field");
        $response = getParameter($mysql, "recapcha_response_field");        
        
        $privateCAPTHCAkey = getCAPCHAPrivateKey();                
        
        try {
            $resp = recaptcha_check_answer($privateCAPTHCAkey, $_SERVER["REMOTE_ADDR"], $challenge, $response);        
        } catch ( Exception $e ) {
            echo "reCAPCHA errored: " . $e->getMessage();
            return;
        }        
        
        if (!$resp->is_valid) {
            echo "INVALID_CAPTCHA";
            return;
        } else {
            $isHuman = true;
        }    

    }          
    
    $message = insertUser($mysql, $username, $password, $email);
    echo $message;                 

} catch (Exception $e) {
    echo ( "FAIL: " . $e->getMessage() );
}
?> 

