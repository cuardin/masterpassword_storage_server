<!--
To change this template, choose Tools | Templates
and open the template in the editor.
-->
<!DOCTYPE html>
<html>
    <head>
        <title>Create User</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    </head>

    <body>      
        <form action="http://www.armyr.se/masterpassword/php_scripts/createUser.php" method="POST">
            <table>
            <tr><td>Username*:</td><td><input type="text" name="username"></td></tr>
            <tr><td>Email*:</td><td><input type="text" name="email"></td></tr>
            <tr><td colspan="2">
                <?php
                require_once('../core/recaptchalib.php');
                $publickey = "6LdI69gSAAAAADgNiIJ0q50vek6kTjj4vjMxWeTy";
                echo recaptcha_get_html($publickey);
                ?>
                </td></tr>
            <tr><td colspan="2" align="right"><input type="text" name="privateKey" hidden>
            <input type="submit"></td></tr>
            </table>
        </form>
        <p>* We will not purposefully give your username or email address to any one for any reason. 
            However, these will be stored in plain-text in our database.</p>        
    </body>
</html>
