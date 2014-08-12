<?php

require_once './core/utilities.php';

try {                
    $globalSeed = getGlobalSeed ();
    echo "$globalSeed";
    
} catch ( Exception $e) {
    echo "FAIL: " . $e->getMessage();
}
