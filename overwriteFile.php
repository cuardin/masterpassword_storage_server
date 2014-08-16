<?php

require_once ('./core/utilities.php');
require_once ('./core/fileManagementCore.php');


        
try {
    $mysql = connectDatabase();

    $username = getParameter($mysql, "username");
    $password = getParameter($mysql, "password");
    $filename = getParameter($mysql,"filename");
    $fileContents = getParameter($mysql, "fileContents");

    //Check that credentials are good.
    if (authenticateUser($mysql, $username, $password)) {        
        overwriteFile($mysql, $username, $filename, $fileContents);
        echo "OK";                    
    } else {
        echo "FAIL: Authentication error";
    }
} catch ( Exception $e) {
    echo "FAIL: " . $e->getMessage();
}


?>
