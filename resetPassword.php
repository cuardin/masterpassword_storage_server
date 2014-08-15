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
    $privateKey = getParameter($mysql, "privateKey");

    $mailer = new Mailer();
    try {
        $isTest = getParameter($mysql, "test");
        if ( !strcmp($isTest, 'true') ) {
           $mailer = new MailerStub();
        }
    } catch ( Exception $e ) {
        //DO nothing.
    }

    if (strcmp($privateKey, getUserCreationKey())) {
        throw new Exception("Incorrect anti-spam key");
    }

    resetPassword($mysql, $username, $verificationKey );
    
    //Now send an email
    $to = getOneValueFromUserList($mysql, "email", $username);
    $subject = "New password email";
    $message = "Hello! you have requested a password reset. Your verification key is: " . $randomPassword;
    $from = "reset_password_masterpassword@armyr.se";
    $headers = "From:" . $from;
    
    echo "OK"; 
    $mailer->sendEmail($email, $subject, $message, $from);

    
} catch (Exception $e) {
    echo "FAIL: " . $e->getMessage();
}
?> 

