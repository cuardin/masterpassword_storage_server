<?php

require_once('../simpletest/autorun.php');  
require_once('../simpletest/web_tester.php');
require_once('../core/utilities.php' );
require_once('../core/seedManagementCore.php' );
SimpleTest::prefer(new TextReporter());

class getSeedTest extends UnitTestCase {
    
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
        insertUser($this->mysql, $this->username, 
                $this->verificationKey, $this->email);      
    }
    
    public function tearDown() {        
        deleteUser( $this->mysql, $this->username );
    }
    
    //************************************************
    //Core functions
    //************************************************
    
    public function testGetGlobalSeed() {
        $seed = getGlobalSeed();
        $this->assertEqual("1", $seed);
    }

    public function testGetSeedInvalidatedUser() {
        //This should work just fine.
        $seed = getSeed( $this->mysql, $this->username );
        $this->assertEqual("1", $seed);
    }
    
    public function testGetSeedValidUser() {        
        validateUser($this->mysql, $this->username, $this->password );
        
        $seed = getSeed( $this->mysql, $this->username );
        
        $this->assertEqual("1", $seed);
    }   
    
    public function testGetSeedNonexistentUser() {        
        //This should work just fine.
        $seed = getSeed( $this->mysql, "anotherName" );
        $this->assertNotEqual("1", $seed);
    }
    
    public function testUpdateSeed() {        
        validateUser($this->mysql, $this->username, $this->password );
        
        setSeed( $this->mysql, $this->username, "2" );
        $seed = getSeed( $this->mysql, $this->username );
                
        $this->assertEqual("2", $seed);
    }   

    //************************************************
    //External functions
    //************************************************

    function testAuthenticateGetSeedSuccess() {
        //TODO
        /*
        validateUser($this->mysql, $this->username, $this->password );
        
        $this->get(getBaseURL() . "getSeed.php?" .
                "username=$this->username&password=--" );        
        $this->assertText('FAIL');                 
         * 
         */
        $this->fail("Not implemented");
    }        

    function testAuthenticateGetSeedBadUser() {
        //TODO
        /*
        $this->get(getBaseURL() . "authenticateUser.php?" .
                "username=$this->username&password=--" );        
        $this->assertText('FAIL');  
         * 
         */     
        $this->fail("Not implemented");
    }
    
    function testAuthenticateGetSeedBadSeed() {
        //TODO
        /*
        $this->get(getBaseURL() . "authenticateUser.php?" .
                "username=$this->username&password=--" );        
        $this->assertText('FAIL');  
         * 
         */
        $this->fail("Not implemented");
    }

}