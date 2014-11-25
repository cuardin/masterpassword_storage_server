<?php

require_once( dirname(__FILE__).'/utilitiesSecret.php' );

function init() {
    header('Content-Type: text/html; charset=utf-8');
}

function rand_string($length) {
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";

    $size = strlen($chars);
    $str = '';
    for ($i = 0; $i < $length; $i++) {
        $str .= $chars[rand(0, $size - 1)];
    }

    return $str;
}

function connectDatabase() {
    $username = getSQLUsername();
    $password = getSQLPassword();
    $databaseName = getSQLDBName();
    
    $mysql = new mysqli("localhost", $username, $password, $databaseName);
    if ($mysql->connect_errno) {
        echo ('FAIL: Could not connect: ' . $mysql->connect_error);
        return false;
    }
    $mysql->set_charset('utf8'); //Set the charset to utf-8
    return $mysql;
}

function authenticateUser($mysql, $username, $password) {

    //First check if the user name exists
    //This introduces a security concern in that the list of user names can 
    //be leaked, but I do not care.
    $usernameStored = getOneValueFromUserList($mysql, "username", $username);
    if ( strcmp( $username, $usernameStored) ) {
        throw new Exception ( "BAD_LOGIN" );
    }
    
    //Then get the password. This setup allows a hacker to do a timing attach 
    //and find the list of usernames in the database. Ignoring that for now.
    $passwordStored = getOneValueFromUserList($mysql, "password", $username);  
    
    //Hash the password, using the salt stored
    $passwordCrypt = crypt($password, $passwordStored);                         
    
    //Now check the fetched password against the stored
    if (strcmp($passwordStored, $passwordCrypt)) {                
        throw new Exception( "BAD_LOGIN" );
     }

     return true;
}

function getUserNameFromEmail($mysql, $email) {
    $query = 'SELECT username FROM masterpassword_users WHERE email=?';
    return getOneValueFromDataBase($mysql, $query, $email);
}

function getOneValueFromUserList($mysql, $field, $username) {
    if (preg_match('/[^a-z]/i', $field)) {
        throw new Exception( "Field name contained invalid characters." );
    }
    $query = 'SELECT ' . $field . ' FROM masterpassword_users WHERE username=?';    
    return getOneValueFromDataBase($mysql, $query, $username);
}

function getOneValueFromDataBase($mysql, $query, $variable) {
    $stmt = $mysql->prepare($query);
    $value = ''; //Initialize
    if (!$stmt) {
        throw new Exception ( "SQL Syntax Error");
    }   
    if ( !$stmt->bind_param('s', $variable) ) {
        throw new Exception ( "Error binding parameter");
    }
    if ( !$stmt->execute() ) {
        throw new Exception( "Error executing SQL statement");
    }
    if ( !$stmt->bind_result($value) ) {
        throw new Exception ( "Error binding result");
    }    
    if ( $stmt->fetch() === false ) {
        throw new Exception ( "Error fetching data" );
    }    
    if ( !$stmt->close() ) {
        throw new Exception( "Error closing statemebt");
    }
    
    if ( $value == '' ) {
        $value = null;
    }
    return $value;
    
}


function getDateString() {
    return date(DATE_ISO8601);
}


function getParameter($mysql, $paramName) {
    //echo $paramName . ":";
    if ( !array_key_exists($paramName, $_GET) && !array_key_exists($paramName, $_POST)) {        
        throw new Exception ( "Parameter requested was not provided: " . $paramName);
    }
    $rawValue = $_GET[$paramName];
    if (!strcmp($rawValue, "")) {
        $rawValue = $_POST[$paramName];
    }    
    return $mysql->real_escape_string($rawValue);
}

function getTotalRowsInTable($mysql, $table )
{
    $query = "SELECT COUNT(*) FROM $table";
    $result = mysqli_query($mysql,$query);    
    $rows = mysqli_fetch_row($result);
    return $rows[0];
}

function checkRoomForOneMoreFile($mysql)
{
    return getTotalRowsInTable($mysql, "masterpassword_files") < getMaxNumberOfFiles();
}

function checkRoomForOneMoreUser($mysql)
{
    return getTotalRowsInTable($mysql, "masterpassword_users") < getMaxNumberOfUSers();
}