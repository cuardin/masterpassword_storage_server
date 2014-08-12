<?php

require_once('../simpletest/autorun.php');  
require_once('../simpletest/web_tester.php');
require_once('../core/utilities.php' );
require_once('../core/seedManagementCore.php' );
SimpleTest::prefer(new TextReporter());

class getSeedTest extends WebTestCase {
    
    private $mysql = null;
    private $username = "testName"; //For some reason testUser does not work with getSeed.php.....
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
        validateUser($this->mysql, $this->username, $this->password );
        $url = getBaseURL() . "getSeed.php?" .
                "username=$this->username";
        //echo $url;
        $this->get( $url );        
        $this->assertText('1:1');                 
                 
    }        

    function testAuthenticateGetSeedBadUser() {
        $this->get(getBaseURL() . "getSeed.php?" .
                "username=anotherName" );        
        $this->assertText(':1');  
        $this->assertNoText('1:1');
    }
}