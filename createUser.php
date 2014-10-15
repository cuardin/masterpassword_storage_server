<?php

require_once ( './core/utilities.php' );
require_once ( './core/userManagementCore.php' );
require_once ( './core/recaptchalib.php' );
require_once ( './core/Mailer.php' );
require_once ( './test/MailerStub.php' );

init();

try {
    $mysql = connectDatabase();

    $verificationKey = rand_string(32);

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
        if (!strcmp($privateKeyProvided, getUserCreationKey())) {           
            $isHuman = true;
        }
    } catch (Exception $e) {
        //Do nothing.
    }

    if ( !$isHuman ) {
        throw new Exception ( "Anti-spam key did not match" );
    }
    
    $id = insertUser($mysql, $username, $password, $verificationKey, $email);
    if ( $id == 0 ) {
        echo "FAIL: duplicate user";
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

