<?php
require_once('../simpletest/autorun.php');  
require_once('../core/userManagementCore.php');
require_once('../core/utilities.php');


class UserManagementCoreTest extends UnitTestCase {
    
    private $mysql = null;
    private $username = "testUser";    
    private $username2 = "anotherUser";    
    private $password = "testPassword";
    //private $verificationKey = "testKey";
    private $email = "test@armyr.se";
    private $email2 = "test2@armyr.se";
    private $privateKey = null;
    
    public function setUp() {
        $this->mysql = connectDatabase();
        $this->privateKey = getPrivateKey();

        deleteUser( $this->mysql, $this->username );
        deleteUser( $this->mysql, $this->username2 );
    }
    
    public function tearDown() {        
        deleteUser( $this->mysql, $this->username );
        deleteUser( $this->mysql, $this->username2 );        
    }
    
    public function testInsertAndDeleteUser() {
        
        //echo "Inserting user<br/>";
        $id = insertUser($this->mysql, $this->username, 
                $this->password,  $this->email);
        $this->assertNotEqual($id, 0);
        
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
    
    public function testInsertUserWithDulicateUsernameOrPassword() {                
        //Arrange
        //Insert a user with an email adress.
        $id1 = insertUser($this->mysql, $this->username, 
                $this->password, $this->email);
        $this->assertNotEqual($id1, 0);
        
        //Act
        //Now insert a user with different username but same adress        
        $id2 = insertUser($this->mysql, $this->username2, 
            $this->password, $this->email);
        $this->assertEqual($id2, 0);
        
        //And insert a user with different address but same username
        $id3 = insertUser($this->mysql, $this->username2, 
            $this->password, $this->email);
        $this->assertEqual($id3, 0);
        
    }
    
    public function testDeleteUserWithPrivateKey() {
        //Create a user to delete
        insertUser($this->mysql, $this->username, $this->password,
               $this->email);
        
        deleteUserWithKey( $this->mysql, $this->username, "", $this->privateKey );
        
                //And check that the user was actually deleted
        $this->assertNull( 
                getOneValueFromUserList($this->mysql, "username", 
                        $this->username) );
    }
    
    public function testDeleteUserWithWrongPrivateKey() {
        //Create a user to delete
        insertUser($this->mysql, $this->username, $this->password,
                $this->email);                    
        
        $this->expectException();
        deleteUserWithKey( $this->mysql, $this->username, "", "" );
        
    }
       
    
    public function testResetPassword() {
        //Create a user to validate
        insertUser($this->mysql, $this->username, $this->password,
               $this->email);                        
        
        resetPassword( $this->mysql, $this->username, "newKey" );
        
        //Make sure key was actually changed.
        $keyInDB = getOneValueFromUserList($this->mysql, "verificationKey", $this->username);        
        $this->assertEqual($keyInDB, "newKey");
    }    
}

?>
