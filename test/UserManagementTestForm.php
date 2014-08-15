<?php

require_once('../simpletest/autorun.php');  
require_once('../simpletest/web_tester.php');
require_once('../core/utilities.php' );
require_once('../core/userManagementCore.php' );

SimpleTest::prefer(new TextReporter());

class UserManagementTestForm extends WebTestCase {
    
    private $mysql = null;
    private $username = "testUser";
    private $password = "testPassword";    
    private $email = "test@armyr.se";    
    private $verificationKey = "testKey";
    private $privateKey = null;
    
    
    public function setUp() {
        $this->mysql = connectDatabase();
        $this->privateKey = getPrivateKey();

        //Delete any old test users.
        deleteUser( $this->mysql, $this->username );                

    }
    
    public function tearDown() {        
        deleteUser( $this->mysql, $this->username );
    }

    function testCreateUserSimple() {        
        $this->get(getBaseURL() . "forms/createUserForm.php");        
        $this->assertResponse( array(200) );
        $this->assertTrue( $this->setField("username", $this->username));        
        $this->assertTrue( $this->setField("email", $this->email));
        $this->assertTrue( $this->setField("privateKey", getUserCreationKey()));
        $this->clickSubmit();
        $this->assertText( "OK" );
    }
         
}


?>
