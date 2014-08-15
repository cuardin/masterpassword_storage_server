<?php

require_once('../simpletest/autorun.php');  
require_once('../simpletest/web_tester.php');
require_once('../core/utilities.php' );
require_once('../core/userManagementCore.php' );

SimpleTest::prefer(new TextReporter());

class UserManagementTest extends WebTestCase {
        
    private $mysql = null;
    private $username = "testUser";
    private $password = "testPassword";    
    private $email = "test@armyr.se";
    private $verificationKey = "testKey";
    private $privateKey = null;
    private $userCreationKey = null;
    
    
    public function setUp() {
        $this->mysql = connectDatabase();
        $this->privateKey = getPrivateKey();
        $this->userCreationKey = getUserCreationKey();

        //Delete any old test users.
        deleteUser( $this->mysql, $this->username );                

    }
    
    public function tearDown() {        
        deleteUser( $this->mysql, $this->username );
    }

    function testCreateUserSimple() {        
        $this->get( getBaseURL() . "/createUser.php?" .
                "username=$this->username&password1=$this->password&" .
                "password2=$this->password&" .
                "email=$this->email&privateKey=$this->userCreationKey&" .
                "test=true");        
        $this->assertText("OK");                 
        $this->assertText("Verification email");
        $this->assertText("Hello! Press this link to verify this email address: " .
            "http://masterpassword.armyr.se/php_scripts/verifyEmail.php?username=" .
            $this->username );
        $this->assertText("create_new_user_masterpassword@armyr.se" );                 
    }
    
    function testCreateUserBadKey() {        
        $this->get(getBaseURL() . "createUser.php?" .
                "username=$this->username&password1=$this->password&" .
                "password2=$this->password&" .
                "email=$this->email&privateKey=--");        
        $this->assertText('FAIL');                 
    }
    
    function testCreateUserMissingParameter() {        
        $this->get(getBaseURL() . "createUser.php?" .
                "username=$this->username");        
        $this->assertText('FAIL');                 
    }
    
    function testVerifyUserSimple() {        
        insertUser($this->mysql, $this->username, 
                $this->verificationKey, $this->email);
        
        $this->get(getBaseURL() . "verifyEmail.php?" .
                "username=$this->username&" .
                "password=$this->password&" .
                "verificationKey=$this->verificationKey");        
        $this->assertText('OK');                 
    }
    
    function testVerifyUserPrivateKey() {        
        insertUser($this->mysql, $this->username, 
                $this->verificationKey, $this->email);
        
        $this->get(getBaseURL() . "verifyEmail.php?" .
                "username=$this->username&" .
                "password=$this->password&" .
                "privateKey=$this->privateKey");        
        $this->assertText('OK');                 
    }
    
    function testVerifyUserBadKey() {        
        insertUser($this->mysql, $this->username, 
                $this->verificationKey, $this->email);
        
        $this->get(getBaseURL() . "verifyEmail.php?" .
                "username=$this->username&" .
                "verificationKey=--");        
        $this->assertText('FAIL');                 
    }
    
    
    function testResetPasswordSimple() {
        //Create a user to edit.
        insertUser($this->mysql, $this->username, 
               $this->verificationKey, $this->email);        
        
        validateUser( $this->mysql, $this->username, $this->password );
        $keyInDB = getOneValueFromUserList($this->mysql, "verificationKey", $this->username);        
        $this->assertEqual("0", $keyInDB);
        
        $this->get(getBaseURL() . "resetPassword.php?" .
                "username=$this->username&privateKey=" . getPrivateKey() );        
        $this->assertText('OK');
        
        //Make sure password was changed.
        $keyInDB = getOneValueFromUserList($this->mysql, "verificationKey", $this->username);        
        $this->assertNotEqual("0", $keyInDB);

    }
    
    function testCreateAndAuthenticateUser() {
        $this->get(getBaseURL() . "createUser.php?" .
                "username=$this->username&email=test@armyr.se&".
                "privateKey=" . getUserCreationKey() );        
        $this->assertText('OK');
        
        $this->assertEqual($this->username, getOneValueFromUserList($this->mysql, "username", $this->username));
        
        $this->get(getBaseURL() . "verifyEmail.php?" .
                "username=$this->username&password=$this->password&".
                "privateKey=" . getPrivateKey() );        
        $this->assertText('OK');
                        
        $this->get(getBaseURL() . "authenticateUser.php?" .
                "username=$this->username&password=$this->password" );                
        $this->assertText('OK');
    }

    function testEradicateUserSimple() {                
        insertUser($this->mysql, $this->username, 
               $this->verificationKey, $this->email);                
        validateUser( $this->mysql, $this->username, 
                $this->password );
        
        $this->get( getBaseURL() . "eradicateUser.php?" .
                "username=$this->username&password=$this->password&" .
                "privateKey=--");                
        $this->assertText( 'OK');                 
    }

    function testEradicateUserBadPassword() {                
        insertUser($this->mysql, $this->username, 
               $this->verificationKey, $this->email);                
        validateUser( $this->mysql, $this->username, 
                $this->password );
        
        $this->get( getBaseURL() . "eradicateUser.php?" .
                "username=$this->username&password=badPassword&" .
                "privateKey=$this->privateKey");                
        $this->assertText( 'FAIL' );                 
    }

    function testEradicateUserBadPrivateKey() {                
        insertUser($this->mysql, $this->username, 
               $this->verificationKey, $this->email);                
        validateUser( $this->mysql, $this->username, 
                $this->password );
        
        $this->get( getBaseURL() . "eradicateUser.php?" .
                "username=$this->username&password=badPassword&" .
                "privateKey=--");                
        $this->assertText( 'FAIL' );                 
    }
}


?>
