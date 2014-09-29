<?php
require_once('../simpletest/autorun.php');  
require_once('../core/userManagementCore.php');
require_once('../core/utilities.php');


class UserManagementCoreTest extends UnitTestCase {
    
    private $mysql = null;
    private $username = "testUser";    
    private $password = "testPassword";
    private $verificationKey = "testKey";
    private $email = "test@armyr.se";
    private $privateKey = null;
    
    public function setUp() {
        $this->mysql = connectDatabase();
        $this->privateKey = getPrivateKey();

        deleteUser( $this->mysql, $this->username );
    }
    
    public function tearDown() {        
        deleteUser( $this->mysql, $this->username );
    }
    
    public function testInsertAndDeleteUser() {
        
        //echo "Inserting user<br/>";
        insertUser($this->mysql, $this->username, 
                $this->password, $this->verificationKey, $this->email);
        
        //Now check that a user was actually inserted.
        $this->assertEqual( $this->username, 
                getOneValueFromUserList($this->mysql, "username", 
                        $this->username) );
        
        //And check that the password was set.
        $passwordStored = getOneValueFromUserList($this->mysql, "password", 
                        $this->username);        
        $passwordCrypt = crypt($this->password, $passwordStored );
        $this->assertEqual($passwordStored, $passwordCrypt);
        
        //Now delete user
        //echo "Deleting user<br/>";
        deleteUser( $this->mysql, $this->username );
        
        //And check that the user was actually deleted
        $this->assertNull( 
                getOneValueFromUserList($this->mysql, "username", 
                        $this->username) );
    }
    
    public function testDeleteUserWithPrivateKey() {
        //Create a user to delete
        insertUser($this->mysql, $this->username, $this->password,
               $this->verificationKey, $this->email);
        
        deleteUserWithKey( $this->mysql, $this->username, "", $this->privateKey );
        
                //And check that the user was actually deleted
        $this->assertNull( 
                getOneValueFromUserList($this->mysql, "username", 
                        $this->username) );
    }
    
    public function testDeleteUserWithWrongPrivateKey() {
        //Create a user to delete
        insertUser($this->mysql, $this->username, $this->password,
               $this->verificationKey, $this->email);
        
        //And validate user emial, otherwise we canot authenticate.        
        validateUser( $this->mysql, $this->username, $this->password, $this->password );
        
        $this->expectException();
        deleteUserWithKey( $this->mysql, $this->username, "", "" );
        
    }
       
    public function testDoValidateUser() {
        //Create a user to validate
        insertUser($this->mysql, $this->username, $this->password,
               $this->verificationKey, $this->email);        
        
        validateUser( $this->mysql, $this->username, $this->password, $this->password );
                
        $this->assertEqual( "0", getOneValueFromUserList($this->mysql, 
                "verificationKey", $this->username) );             
    }
    
    public function testValidateUser() {
        //Create a user to validate
        insertUser($this->mysql, $this->username, $this->password,
               $this->verificationKey, $this->email);        
        
        $verificationKey = getOneValueFromUserList($this->mysql, 
                "verificationKey", $this->username);
        
        validateUserWithKey( $this->mysql, $this->username, $this->password, $verificationKey );
                
        $this->assertEqual( "0", getOneValueFromUserList($this->mysql, 
                "verificationKey", $this->username) );             
    }
    
    public function testValidateUserWrongKey() {
        //Create a user to validate
        insertUser($this->mysql, $this->username, $this->password,
               $this->verificationKey, $this->email);        
        
        $this->expectException();
        validateUserWithKey( $this->mysql, $this->username, $this->password, "" );
    } 
    
    public function testResetPassword() {
        //Create a user to validate
        insertUser($this->mysql, $this->username, $this->password,
               $this->verificationKey, $this->email);        
        
        validateUser( $this->mysql, $this->username, $this->password, $this->password );
        
        resetPassword( $this->mysql, $this->username, "newKey" );
        
        //Make sure key was actually changed.
        $keyInDB = getOneValueFromUserList($this->mysql, "verificationKey", $this->username);        
        $this->assertEqual($keyInDB, "newKey");

    }
}

?>
