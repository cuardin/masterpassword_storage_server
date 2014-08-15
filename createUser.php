<?php

require_once ( './core/utilities.php' );
require_once ( './core/userManagementCore.php' );
require_once ( './core/recaptchalib.php' );
require_once ( './core/Mailer.php' );
require_once ( './test/MailerStub.php' );



try {
    $mysql = connectDatabase();

    $verificationKey = rand_string(32);

    //Escape all the user input to be SQL safe.
    $username = getParameter($mysql, "username");
    $email = getParameter($mysql, "email");
    
    $mailer = new Mailer();
    try {
        $isTest = getParameter($mysql, "test");
        $privateKey = getParameter($mysql, "privateKey");
        if ( !strcmp($isTest, 'true') && !strcmp($privateKey,  getPrivateKey() )) {
           $mailer = new MailerStub();
        }
    } catch ( Exception $e ) {
        //DO nothing.
    }

    $userNameStored = getOneValueFromUserList($mysql, 'username', $username);
    if (!($userNameStored == null)) {
        throw new Exception('User name allready exists: ' . $userNameStored);
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
    
    insertUser($mysql, $username, $verificationKey, $email);
    
    echo "OK";
    
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

