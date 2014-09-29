<?php

require_once ( './core/utilities.php' );
require_once ( './core/userManagementCore.php' );

init();

try {
    $mysql = connectDatabase();
    
    $username = getParameter($mysql, "username");    
    $verificationOK = false;
    
    //First try verification key
    try {
        $verificationKey = getParameter($mysql, "verificationKey");
        $verificationKeyStored = getOneValueFromUserList($mysql, 'verificationKey', $username);

        //Now check the fetched verification key against the stored
        if (!strcmp($verificationKeyStored, $verificationKey)) {
            $verificationOK = true;
            validateUser($mysql,$username);
        }
    } catch ( Exception $e ) {
        //Do nothing.        
    }
    
    if ( !$verificationOK ) {
        //Now try the private key
        try {
            $privateKey = getparameter( $mysql, "privateKey" );
            //Now check the fetched private key against the stored
            if (!strcmp($privateKey, getPrivateKey())) {
                $verificationOK = true;
                validateUser($mysql, $username );
            }
        } catch ( Exception $e ) {
            //Do nothing.
        }
    }
    
    if ( !$verificationOK ) {
        throw new Exception( "Verification key did not match stored key." );
    }
        
    echo "OK";
} catch ( Exception $e ) {    
    echo ( "FAIL: " );
    echo ( $e->getMessage() );
}
?> 
