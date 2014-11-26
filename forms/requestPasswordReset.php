<?require_once('../core/utilitiesSecret.php');?>
<!DOCTYPE html>
<html>
    <head>
        <title>Request password reset</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
    <body>
        <h1>Request password reset</h1>
        <form method="POST" action="<?echo getBaseURL() . "resetPassword.php";?>">
            <label for="email">Email</label>
            <input type="email"/>
            <button>Submit</button>
        </form>
    </body>
</html>
