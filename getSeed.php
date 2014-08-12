<?php

require_once './core/utilities.php';
require_once './core/seedManagementCore.php';

try {
    $mysql = connectDatabase();
    $username = getParameter($mysql,"username");        
    
    $seed = getSeed ( $mysql, $username );
    $globalSeed = getGlobalSeed ();
    echo "$seed:$globalSeed";
    
} catch ( Exception $e) {
    echo "FAIL: " . $e->getMessage();
}
