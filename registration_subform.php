<?php
require_once 'database_functions.php';
session_start();
//if the user is logged in check if mode is set to update, as if it is not set the user cant update their details
if(!empty($_SESSION["user_email"]) && !isset($_GET["mode"]))
{
    //if the mode isnt set, set it
    header('Location: registration_subform.php?mode=update');
} //if the user is not logged in and the user is registering redirect to the login page as there is nothing for them to enter the details of
else if (empty($_SESSION["user_email"]) && !isset($_POST["register"]))
{
    header('Location: index.php');
}

$connection = new database();
//fetch the dietary preferences so that they can be displayed to the user
$dietaryPreferences = $connection->runQuery("SELECT * FROM `dietary_type`");

if($_SERVER["REQUEST_METHOD"] == "POST")
{
    //validate the details to be registered
    if(isset($_POST["register"])) {
        //check if the user successfully confirmed their password
        if ($_POST["password"] != $_POST["confirm_password"]) {
            header('Location: index.php?mode=register&error=Passwords do not match!');
        } else {
            //check if the email that the user has used is not taken
            if (mysqli_num_rows($connection->runQuery("SELECT * FROM `user` WHERE email LIKE '" . $_POST["email"] . "'")) > 0) {
                header('Location: index.php?mode=register&error=This email is already associated with an account, please use another');
            }
        }
    }
    if(isset($_POST["create"]) || isset($_POST["update"]))
    {
        //calculate the BMI using the equation weight divided by height in meters ^ 2
        //since the user enters their height in cm divide by 100 to get thier height in meters
        $BMI = $_POST["weight"] / pow($_POST["height"] / 100, 2) ;
        //The BMI value has set ranges, a value under 18 is underweight, over 25 is overweight and anything between is normal
        if($BMI < 18)
        {
            //underweight
            $weightBand = 1;
        }
        else if($BMI > 25)
        {
            //overweight
            $weightBand = 3;
        }
        else
        {
            //average
            $weightBand = 2;
        }
        if(isset($_POST["create"]))
        {
            //create the user account in the database
            $connection->insertData("user", array($_POST["email"], hash('sha256', $_POST["password"]), (float)$_POST["weight"],
                (float)$_POST["height"], $weightBand, (int)$_POST["dietary_choice"], 0));
            $_SESSION["user_email"] = $_POST["email"];
        }
        elseif (isset($_POST["update"]))
        {
            $connection->prepareQuery("UPDATE `user` 
            SET `user_weight`='".(float)$_POST["weight"]."', `user_height`='". (float)$_POST["height"] ."', `weight_band`='". $weightBand ."', 
            `dietary_preference`='". (int)$_POST["dietary_choice"] ."' WHERE `email`=?", $_SESSION["user_email"]);
        }
        header('Location: meal_display.php');
    }
}
if(isset($_GET["mode"]) && $_GET["mode"] == "update")
{
    //get the users existing details so that they can be displayed to the user for updating
    $userAccount = mysqli_fetch_array($connection->prepareQuery("SELECT * FROM `user` WHERE email=?", $_SESSION["user_email"]));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
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
    <meta charset="UTF-8">
    <title><?php if(isset($_GET["mode"]) && $_GET["mode"] == "update") echo "Update details"; else echo "Enter details" ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="Style.css">
</head>
<body>
<!-- The top Navigation Menu -->
<div class="mobile-container">

    <!-- The top Navigation Menu -->
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
    <div align="center">
        <h2><?php if(isset($_GET["mode"]) && $_GET["mode"] == "update") echo "Update"; else echo "Enter";?> your details</h2>

    </div>
    <div id='log in' class='form-center'>

        <form method="post" action="<?php echo $_SERVER['PHP_SELF'];?>">
            <label for="weight" >Weight</label> <br>
            <input name="weight" placeholder="Enter your weight" type="number" min="40" max="200" required class="form-control"
                   <?php if(isset($_GET["mode"]) && $_GET["mode"] == "update") echo "value='". $userAccount["user_weight"] ."'"?>> <br>
            <label for="height">Height</label> <br>
            <input name="height" placeholder="Enter your height" type="number" min="80" max="250" required class="form-control"
                <?php if(isset($_GET["mode"]) && $_GET["mode"] == "update") echo "value='". $userAccount["user_height"] ."'"?>> <br>
            <label for="dietary_choice">Dietary choice</label> <br>
            <select name="dietary_choice" class="form-control">
                <?php
                //display all dietary options for the user to select
                while($row = mysqli_fetch_array($dietaryPreferences))
                {
                    echo "<option value='". $row["dietary_id"] ."' ". ((isset($_GET["mode"]) && $_GET["mode"] == "update" && $row["dietary_id"] == $userAccount["dietary_preference"])
                            ? "selected" : "") .">". $row["dietary_type"] ."</option>";
                }
                ?>
            </select> <br>
            <?php
            //if the user is registering remember their password and email they choose so that it can be entered with the rest of the details
            if(isset($_POST["register"])){
                echo "<input name='email' type='hidden' value='". $_POST["email"] ."'>
                <input name='password' type='hidden' value='". $_POST["password"] ."'>";
            } ?>
            <button name="<?php if(isset($_GET["mode"]) && $_GET["mode"] == "update") echo "update"; else echo "create";?>"
                    type="submit" class="btn btn-primary">Confirm</button>
        </form>
    </div>
    <p><a href="index.php">Click here to cancel registration</a></p>
</div>
<div id="account footer">
    <footer>
        <br><a href="tos.php">Terms of service</a>
    </footer>
</div>
</body>
</html>
