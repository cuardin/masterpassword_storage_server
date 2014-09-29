<?php

require_once ('./core/utilities.php');



try {
    $mysql = connectDatabase();

    $username = getParameter($mysql, "username");
    $password = getParameter($mysql, "password");

    //Check that credentials are good.
    if (authenticateUser($mysql, $username, $password)) {
        //And list the files
        $stmt = $mysql->prepare("SELECT * FROM masterpassword_files WHERE username=?");
        if ( !$stmt ) {
            throw new Exception ();
        }
        
        if ( !$stmt->bind_param('s', $username) ) {
            throw new Exception();
        }
        if ( !$stmt->execute() ) {
            throw new Exception();
        }
        $res = $stmt->get_result();
        if (!$res) {
            throw new Exception();
        }

        $data = array();

        for ($row_no = $res->num_rows - 1; $row_no >= 0; $row_no--) {
            $res->data_seek($row_no);
            $row = $res->fetch_assoc();
            
            $data[$row['fileName']] = $row['fileContents'];            
        }       
        
        Header('Content-type: application/json');
        print(json_encode($data));
    }
} catch ( Exception $e )  {
    mb_http_output('UTF-8');
    echo "FAIL: " . $e->getMessage();
}
?>
