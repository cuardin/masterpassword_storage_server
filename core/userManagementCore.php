<?php

function insertUser($mysql, $username, $verificationKey, $email) {    

    $password = "0";
    $seed = "1";
    $query = "INSERT INTO masterpassword_users (username, password, verificationKey, seed, email)" .
            "VALUES (?, ?, ?, ?, ?)";

    try {
        $stmt = $mysql->prepare($query);
        if (!$stmt) {
            throw new Exception('Error preparing sql statement');
        }
        if (!$stmt->bind_param('sssis', $username, $password, $verificationKey, $seed, $email)) {
            throw new Exception('Error binding parameters');
        }
        if (!$stmt->execute()) {
            throw new Exception('Error executing statement');
        }
        if (!$stmt->close()) {
            throw new Exception('Error closing statement');
        }

        /*if (!autoExtendLicense($mysql, $email, 'NEW')) {
            //echo "Extending license. <br/>";
            throw new Exception("Error extending license");
        }*/
        
        return mysqli_insert_id($mysql);        
        
    } catch (Exception $e) {
        //echo $e->getMessage() . "<br/>";
        throw new Exception(htmlspecialchars($mysql->error));
    }
}

function deleteUser($mysql, $username) {
    $query = "DELETE FROM masterpassword_users WHERE username=?";
    //echo $query . "<br/>";
    try {
        $stmt = $mysql->prepare($query);
        if (!$stmt) {
            throw new Exception('Error preparing sql statement');
        }
        if (!$stmt->bind_param('s', $username)) {
            throw new Exception('Error binding parameters');
        }
        if (!$stmt->execute()) {
            throw new Exception('Error executing statement');
        }
        if (!$stmt->close()) {
            throw new Exception('Error closing statement');
        }
    } catch (Exception $e) {
        //echo $e->getMessage() . "<br/>";
        throw new Exception(htmlspecialchars($mysql->error));
    }
}

function validateUser($mysql, $username, $password) {
    $query = "UPDATE masterpassword_users SET verificationKey='0', password=? WHERE username=?";
    //echo $query . "<br/>";
    try {
        $stmt = $mysql->prepare($query);
        if (!$stmt) {
            throw new Exception('Error preparing sql statement');
        }
        if (!$stmt->bind_param('ss', crypt($password), $username)) {
            throw new Exception('Error binding parameters');
        }
        if (!$stmt->execute()) {
            throw new Exception('Error executing statement');
        }
        if (!$stmt->close()) {
            throw new Exception('Error closing statement');
        }
    } catch (Exception $e) {
        //echo $e->getMessage() . "<br/>";
        throw new Exception(htmlspecialchars($mysql->error));
    }
}

function deleteUserWithKey($mysql, $username, $password, $privateKey) {
    if (!strcmp($privateKey, getPrivateKey())) {
        deleteUser($mysql, $username);
    } else {
        throw new Exception("Authentication failed");
    }
}

function validateUserWithKey($mysql, $username, $password, $verificationKey) {
    $verificationKeyStored = getOneValueFromUserList($mysql, 'verificationKey', $username);
    if (strcmp($verificationKeyStored, $verificationKey)) {
        throw new Exception("Key provided did not match stored key");
    }
    validateUser($mysql, $username, $password);
}

function resetPassword($mysql, $username, $verificationKey) {    
    
    try {
        $query = "UPDATE masterpassword_users SET verificationKey=? WHERE username=?";

        $stmt = $mysql->prepare($query);

        if (!$stmt) {
            throw new Exception();
        }

        if (!$stmt->bind_param('ss', $verificationKey, $username)) {
            throw new Exception();
        }

        if (!$stmt->execute()) {
            throw new Exception();
        }
        if (!$stmt->close()) {
            throw new Exception();
        }
    } catch (Exception $e) {
        throw new Exception(
        "Error resetting password: " . htmlspecialchars($mysql->error) );
    }
}


?>
