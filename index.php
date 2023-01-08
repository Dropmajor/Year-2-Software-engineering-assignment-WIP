<?php
require_once 'database_functions.php';
session_start();
//if the user is already logged in redirect them to the account page as there is no need to prompt them to login again
if (!empty($_SESSION["user_email"]))
{
    header('Location: account_page.php');
}

if($_SERVER["REQUEST_METHOD"] == "POST")
{
    $connection = new database();
    if(isset($_POST["login"]))
    {
        $existingAccount = $connection->prepareQuery("SELECT * FROM `user` WHERE email=?", $_POST["email"]);
        //if there is a non-zero amount of rows that means there is an account matching the email
        if($existingAccount->num_rows > 0)
        {
            $account = mysqli_fetch_array($existingAccount);
            //compare a hashed version of the password to the hashed password stored on the database
            $password = hash('sha256', $_POST["password"]);
            if($account["password"] == $password)
            {
                //if the passwords match redirect the user to the account page and remember their login in the session
                $_SESSION["user_email"] = $_POST["email"];
                header('Location: account_page.php');
            }
            else
            {
                $loginError = "Username or password does not match";
            }
        }
        else
        {
            $loginError = "Username or password does not match";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php if(isset($_GET["mode"]) && $_GET["mode"] == "register") echo "Register"; else echo "Login";?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="Style.css">
</head>

<!--hamburger menu tutorial taken from https://www.w3schools.com/howto/howto_js_mobile_navbar.asp-->
<script>
    function hamBurgerMenu() {
        var links = document.getElementById("myLinks");
        if (links.style.display === "block") {
            links.style.display = "none";
        }

        else {
            links.style.display = "block";
        }
    }
</script>

<body>
<!-- The top Navigation Menu -->
<div class="mobile-container">
<div class="topnav">
    <a href="javascript:void(0);" class="icon" onclick="hamBurgerMenu()">
        <i class="fa fa-bars"></i>
    </a>
    <a class="active">Diet<br>Planner</a>
    <div id="myLinks">
        <a style = "background-color: #2E5266" href="account_page.php">My Account</a>
        <a style = "background-color: #2E5266" href="meal_display.php">Generate Meal Plan</a>
        <a style = "background-color: #2E5266" href="account_page.php?mode=logout">Sign out</a>
    </div>
</div>

    <div id ="log in"> <p> <?php if(isset($_GET["mode"]) && $_GET["mode"] == "register") echo "Register";
    else echo "Login";?> </p> </div>

    <!-- Register Form -->
        <form method="post" action="<?php if(isset($_GET["mode"]) && $_GET["mode"] == "register")
            echo 'registration_subform.php'; else echo $_SERVER['PHP_SELF']; ?>">
            <div class ="form form-group">
                <label for="email entry">Email address</label>
                <input name="email" type="email" class="form-control" id="email entry" aria-describedby="emailHelp" placeholder="Please Enter email" maxlength="16" required>
<!-- Register Form -->

        </div>
            <div class="form-group">
                <label for="password entry">Password</label>
                <input name="password" type="password" class="form-control" id="password entry" placeholder="Please Enter Password" maxlength="16" required>
            </div>
<?php
    //check if the mode is set to register, if it is output an extra input field for confirming the password of the account that is being created by the user
    if(isset($_GET["mode"]) && $_GET["mode"] == "register")
    {
        echo "<label>Confirm password</label>
           <input name='confirm_password' type='password' class='form-control' id='password entry' placeholder='Confirm Password' maxlength='16' required><br>
           <button type='submit' class='btn btn-primary' name='register'>Submit</button>";
    }
    else
    {
        echo ((!empty($loginError)) ? $loginError : "").
            "<br><button type='submit' class='btn btn-primary' name='login'>Submit</button>";
    }
    ?>
            <div class="form-check">
            </div>
        </form>
        <?php
        //output depending on the current mode a way to switch to the opposite mode
        if(isset($_GET["mode"]) && $_GET["mode"] == "register")
            echo "<p>Already a member? <a href='?mode=login'>Login</a></p>";
        else
            echo "<p>Not a member? <a href='?mode=register'>Register</a></p>";
        if(!empty($_GET["error"]))
        {
            echo "<p>". $_GET["error"] ."</p>";
        }
        ?>
</div>
    <div id="account footer">
        <footer>
            <br><a href="tos.php">Terms of service</a>
        </footer>
    </div>
</body>
</html>
