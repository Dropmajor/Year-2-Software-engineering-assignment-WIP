<?php
require_once 'database_functions.php';
session_start();
//check if the user is logged in
if (empty("user_email")) {
    header('Location: index.php');
}
$connection = new database();
$user = mysqli_fetch_array($connection->runQuery("SELECT * FROM `user` WHERE email='". $_SESSION["user_email"] ."'"));

if($_SERVER["REQUEST_METHOD"] == "POST")
{
    if(isset($_POST["delete_account"]));
    {
        //delete the user meal plan links first as they reference the record that is to be deleted
        $connection->runQuery("DELETE FROM `user_meal_link` WHERE user_id='". $user["user_id"] ."'");
        $connection->prepareQuery("DELETE FROM user WHERE email=?", $_SESSION["user_email"]);
        //clear the session as the users account no longer exists
        session_unset();
        session_destroy();
        header("Location: index.php");
    }
}
else
{
    if(isset($_GET["mode"]))
    {
        if(isset($_GET["mode"]) == "logout")
        {
            session_unset();
            session_destroy();
            header("Location: index.php");
        }
    }
}
$connection = new database();
//get the users meal plan
$user_plan = $connection->runQuery("SELECT * FROM `user_meal_link` 
    JOIN `meal` 
        ON `user_meal_link`.`meal_id`=`meal`.`meal_id` 
    WHERE user_id='" . $user["user_id"] . "' 
    ORDER BY `meal_type` ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Account Page</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="Style.css">
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
    <style>
        button
        {
            padding: 0;
            border: none;
            background: none;
            color: #eeeeee;
            text-decoration: underline;
        }

        button:hover
        {
            color: black;
        }
    </style>
</head>
<body>
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

    <p style="font-size: 20px" class = "ph"><span style="color: black">You are currently logged in</p> <!--removed the as because an email is stored not a username -->
    <p class = "ph"><span style="color: black"><u>Your account settings:</u></span></p>
    <div>
        <hr class="dashed">
    </div>
    <p class = "p1"><a href="meal_display.php">Change Meal Plan</a></p>
    <p class = "p1"><a href="registration_subform.php?mode=update">Edit Account Details</a></p>
    <p class = "p1"><a href="?mode=logout">Log Out</a></p>
    <form method="post" action="<?php echo $_SERVER['PHP_SELF'];?>">
        <button class="p1" name="delete_account" type="submit" onclick="return confirm('Are you sure?')">Delete Account</button>
    </form>

    <p class = "ph"><span style="color: black"><u>Current Meal Plan</u></span></p>
    <div>
        <hr class="dashed">
    </div>
    <?php
    //display the names of all the meals in the users meal plan, if they wish for more details they can view that on the meal display page
        echo "<p class = 'p1'>Breakfast: ". mysqli_fetch_array($user_plan)["meal_name"] ."</p>
              <p class = 'p1'>Lunch: ". mysqli_fetch_array($user_plan)["meal_name"] ."</p>
              <p class = 'p1'>Dinner: ". mysqli_fetch_array($user_plan)["meal_name"] ."</p>"
    ?>
    <p class = "p1"><a href="meal_display.php?mode=view">View Full Meal Plan</a></p>
    <?php
        if($user["admin"])
        {
            echo "<p class = 'ph'><span style='color: black'><u>Admin Functions</u></span></p>
                  <div>
                      <hr class='dashed'>
                  </div>
                  <p class = 'p1'><a href='admin_form.php?mode=create'>Create New Meal Record</a></p>
                  <p class = 'p1'><a href='admin_form.php?mode=view'>Review Meal Records</a></p>
                  </div>";
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
