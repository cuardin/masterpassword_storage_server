<?php

require_once('../simpletest/autorun.php');  
require_once('../simpletest/web_tester.php');
require_once('../core/utilities.php' );
require_once('../core/userManagementCore.php' );

SimpleTest::prefer(new TextReporter());

class UserManagementTest extends WebTestCase {
        
    private $mysql = null;
    private $username = "testUser";
    private $username2 = "testUser2";
    private $password = "testPassword";    
    private $email = "test@armyr.se";
    private $email2 = "test2@armyr.se";
    private $verificationKey = "testKey";
    private $userEditKey = null;    
    
    
    public function setUp() {
        $this->mysql = connectDatabase();
        $this->userEditKey = getUserEditKey();        

        //Delete any old test users.
        deleteUser( $this->mysql, $this->username );                

    }
    
    public function tearDown() {        
        deleteUser( $this->mysql, $this->username );
        deleteUser( $this->mysql, $this->username2 );
    }
    
    function testCreateUserSimple() {        
        $this->get( getBaseURL() . "/createUser.php?" .
                "username=$this->username&password=$this->password&" .                
                "email=$this->email&userCreationKey=$this->userEditKey&" .
                "test=true&userEditKey=" . getUserEditKey() );        
        $this->assertText("OK");                 
    }
    
    function testCreateDuplicateUser() {        
        //Arrange
        $id = insertUser($this->mysql, $this->username, 
                $this->password,  $this->email);
        $this->assertNotEqual($id, 0);        
        
        //Act: Same username different email
        $this->get( getBaseURL() . "/createUser.php?" .
                "username=$this->username&password=$this->password&" .                
                "email=$this->email2&userEditKey=$this->userEditKey&" .
                "test=true" );                
        $this->assertText( "DUPLICATE_USER" );                         
        
        //Act: Same email different username
        $this->get( getBaseURL() . "/createUser.php?" .
                "username=$this->username2&password=$this->password&" .                
                "email=$this->email&userEditKey=$this->userEditKey&" .
                "test=true" );        
        $this->assertText( "DUPLICATE_USER" );                         
    }
    
    function testCreateUserBadPrivateKey() {        
        $this->get(getBaseURL() . "createUser.php?" .
                "username=$this->username&password1=$this->password&" .
                "password2=$this->password&" .
                "email=$this->email&userEditKey=--");        
        $this->assertText('FAIL');                 
    }
    
    function testCreateUserBadUserCreationKey() {        
        $this->get( getBaseURL() . "/createUser.php?" .
                "username=$this->username&password1=$this->password&" .
                "password2=$this->password&" .
                "email=$this->email&userCreationKey=--&" .
                "test=true&userEditKey=" . getUserEditKey() );        
        $this->assertText('FAIL');                 
    }
    
    function testCreateUserMissingParameter() {        
        $this->get(getBaseURL() . "createUser.php?" .
                "username=$this->username");        
        $this->assertText('FAIL');                 
    }
     
    function testCreateAndAuthenticateUser() {
        $this->get(getBaseURL() . "createUser.php?" .
                "username=$this->username&email=test@armyr.se&".
                "password=$this->password&".
                "userCreationKey=" . getUserEditKey() .
                "&test=true&userEditKey=" . getUserEditKey() );        
        $this->assertText('OK');
        
        $this->assertEqual($this->username, 
                getOneValueFromUserList($this->mysql, "username", $this->username));
                                        
        $this->get(getBaseURL() . "authenticateUser.php?" .
                "username=$this->username&password=$this->password" );                
        $this->assertText('OK');
                
    }
    
    function testEradicateUserSimple() {                
        insertUser($this->mysql, $this->username, $this->password,
               $this->verificationKey, $this->email);                
        
        $this->get( getBaseURL() . "eradicateUser.php?" .
                "username=$this->username&password=$this->password");                
        $this->assertText( 'OK');                 
    }

    function testEradicateUserBadPassword() {                
        insertUser($this->mysql, $this->username, $this->password,
               $this->verificationKey, $this->email);                
        
        $this->get( getBaseURL() . "eradicateUser.php?" .
                "username=$this->username&password=badPassword&" .
                "userEditKey=$this->userEditKey");                
        $this->assertText( 'FAIL' );                 
    }

    function testEradicateUserBadPrivateKey() {                
        insertUser($this->mysql, $this->username, $this->password,
               $this->verificationKey, $this->email);                
        
        $this->get( getBaseURL() . "eradicateUser.php?" .
                "username=$this->username&password=badPassword&" .
                "userEditKey=--");                
        $this->assertText( 'FAIL' );                 
    }
     
    function testResetPassword()
    {
        insertUser($this->mysql, $this->username, $this->password,
               $this->verificationKey, $this->email);                
        
        //TODO: Do the rest.
    }
     
}
