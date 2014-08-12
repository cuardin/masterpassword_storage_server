<?php

function getGlobalSeed() {
    return "1";
}

function getSeed( $mysql, $username ) {
    try {
        $seed = getOneValueFromUserList($mysql, "seed", $username);        
        return $seed;
    } catch ( Exception $e ) {
        return rand(2, 1000);
    }
}

function setSeed( $mysql, $username, $seed ) {
    try {
        $query = "UPDATE masterpassword_users SET seed=? WHERE username=?";
    
        $stmt = $mysql->prepare($query);
        if (!$stmt) {
            throw new Exception('Error preparing sql statement');
        }
        if (!$stmt->bind_param('is', $seed, $username)) {
            throw new Exception('Error binding parameters');
        }
        if (!$stmt->execute()) {
            throw new Exception('Error executing statement');
        }
        if (!$stmt->close()) {
            throw new Exception('Error closing statement');
        }
    } catch (Exception $e) {
        //IF there was an error, say nothing.
    }
}


