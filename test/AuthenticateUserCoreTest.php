<?php

require_once('../simpletest/autorun.php');  
require_once('../core/utilities.php' );
require_once('../core/userManagementCore.php' );

class authenticateUserCoreTest extends UnitTestCase {
    
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
                $this->password, $this->verificationKey, $this->email);        
    }
    
    public function tearDown() {        
        deleteUser( $this->mysql, $this->username );
    }
    
    public function testAuthenticateUserSimple() {
        
        validateUser($this->mysql, $this->username, $this->password);
        
        $this->assertTrue( authenticateUser($this->mysql, $this->username, 
                $this->password) );
    }
    
    public function testAuthenticateUserNotValidated() {
        try {
            authenticateUser($this->mysql, $this->username, 
                $this->password);
            $this->fail( "No exception cought");
        } catch ( Exception $e ) {
            $this->assertEqual( "UNVALIDATED_USER", $e->getMessage() );
        }        
    }
    
    public function testAuthenticateUserWrongUsername() {
        validateUser($this->mysql, $this->username, $this->password );
        try {
            authenticateUser($this->mysql, "n/a", 
                $this->password);
            $this->fail( "No exception cought");
        } catch ( Exception $e ) {
            $this->assertEqual( "BAD_LOGIN", $e->getMessage() );
        }        
    }

    public function testAuthenticateUserWrongPassword() {
        validateUser($this->mysql, $this->username, $this->password );
        try {
            authenticateUser($this->mysql, $this->username, 
                "N/A");
            $this->fail( "No exception cought");
        } catch ( Exception $e ) {
            $this->assertEqual( "BAD_LOGIN", $e->getMessage() );
        }        
    }   
    
    public function testAuthenticateUserUnvalidated() {
        
        //De-authenticate user.
        $query = "UPDATE masterpassword_users SET verificationKey='ABC' WHERE username=?";        
        
        try {
            $stmt = $this->mysql->prepare($query);
            if (!$stmt) {
                throw new Exception('Error preparing sql statement');
            }
            if (!$stmt->bind_param('s', $this->username)) {
                throw new Exception('Error binding parameters');
            }
            if (!$stmt->execute()) {
                throw new Exception('Error executing statement');
            }
            if (!$stmt->close()) {
                throw new Exception('Error closing statement');
            }
        } catch (Exception $e) {            
            throw new Exception(htmlspecialchars($this->mysql->error));
        }
        
        //First try with bad password
        try { 
            authenticateUser($this->mysql, $this->username, "--");
        } catch ( Exception $e ) {
            $this->assertEqual( $e->getMessage(), "BAD_LOGIN" );
        }
        
        try { 
            authenticateUser($this->mysql, $this->username, $this->password);
        } catch ( Exception $e ) {
            $this->assertEqual( $e->getMessage(), "UNVALIDATED_USER" );
        }
    }

}

?>
