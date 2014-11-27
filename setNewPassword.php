<?php

require_once ( './core/utilities.php' );
require_once ( './core/userManagementCore.php' );

init();

try {
    //error_log( $_SERVER['QUERY_STRING'] );
    
    $mysql = connectDatabase();
    $username = getParameter($mysql,"username");        
    $verificationKey = getParameter($mysql,"verificationKey");        
    $newPassword = getParameter($mysql,"newPassword");        
                        
    validateUserWithKey($mysql, $username, $verificationKey, $newPassword );
            
    echo "OK";
    
} catch ( Exception $e) {    
    echo "FAIL: " . $e->getMessage();
}

