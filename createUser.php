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
        $privateKeyProvided = getParameter($mysql, "userCreationKey");                                        
        if (!strcmp($privateKeyProvided, getUserEditKey())) {           
            $isHuman = true;            
        } else {                        
        }
    } catch (Exception $e) {
        //Do nothing.         
    }        
    
    
    if ( !$isHuman ) {
        $challenge = getParameter($mysql, "recaptcha_challenge_field");
        $response = getParameter($mysql, "recaptcha_response_field");        
        
        $privateCAPTHCAkey = getCAPCHAPrivateKey();        
        $resp = recaptcha_check_answer($privateCAPTHCAkey, $_SERVER["REMOTE_ADDR"], $challenge, $response);        
        if (!$resp->is_valid) {
            echo "INVALID_CAPTCHA";
            return;
        } else {
            $isHuman = true;
        }    
    }          
    
    $id = insertUser($mysql, $username, $password, $email);
    if ( $id == 0 ) {
        echo "DUPLICATE_USER";
        return;
    } else {    
        echo "OK";
    }    
    
    //Now send an email    
    $subject = "Verification email";
    $message = "Hello! Press this link to verify this email address: " .
            "http://masterpassword.armyr.se/php_scripts/verifyEmail.php?username=" .
            $username . "&verificationKey=" . $verificationKey;
    $from = "create_new_user_masterpassword@armyr.se";    
    
    
    $mailer->sendEmail($email, $subject, $message, $from);
    

} catch (Exception $e) {
    echo ( "FAIL: " . $e->getMessage() );
}
?> 

