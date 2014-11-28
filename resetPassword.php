<?php

require_once './core/utilities.php';
require_once ( './core/userManagementCore.php' );
require_once ( './core/recaptchalib.php' );
require_once ( './core/Mailer.php' );
require_once ( './test/MailerStub.php' );

init();

try {
    //error_log( $_SERVER['QUERY_STRING'] );
    
    $mysql = connectDatabase();
    $email = getParameter($mysql,"email");    
    //echo "Email: " . $email . "<br/>";
    //echo "User name: " . getUserNameFromEmail($mysql, $email) . "<br/>";
    
    $mailer = new Mailer();
    /*try {
        $isTest = getParameter($mysql, "test");
        if ( !strcmp($isTest, 'true') ) {
            error_log( "Using stub mailer." );
            $mailer = new MailerStub();
        }
    } catch ( Exception $e ) {
        
    } */  
    
    if ( !checkUserEditKeyOrRECAPTCHA($mysql) ) {                
        echo "INVALID_CAPTCHA";
        return;
    }
    
    $verificationKey = rand_string(32);    
    $username = getUserNameFromEmail($mysql, $email);
    resetPassword($mysql, $username, $verificationKey );    
    
    echo "OK";
            
    $url = getBaseURL() . "forms/setNewPasswordForm.php?verificationKey=$verificationKey&username=$username";
    $mailer->sendEmail( $email, "Password reset request", "A password request has been requested. Click <a href='$url'>here</a> to reset the password within 15 minutes.", "postmaster@armyr.se" );
    
    
    
    
} catch ( Exception $e) {    
    echo "FAIL: " . $e->getMessage();
}

?> 
