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
    $userCreationKey = getParameter($mysql, "userCreationKey");

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

    if (strcmp($userCreationKey, getUserCreationKey())) {
        throw new Exception("Incorrect anti-spam key");
    }

    resetPassword($mysql, $username, $verificationKey );
    
    //Now send an email
    $to = getOneValueFromUserList($mysql, "email", $username);
    $subject = "New password email";
    $message = "Hello! you have requested a password reset. Your verification key is: " . $verificationKey;
    $from = "reset_password_masterpassword@armyr.se";
    $headers = "From:" . $from;
    
    echo "OK"; 
    $mailer->sendEmail($to, $subject, $message, $from);

    
} catch (Exception $e) {
    echo "FAIL: " . $e->getMessage();
}
?> 

