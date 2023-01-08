<?php
require_once 'database_functions.php';
session_start();
$connection = new database();
//if the user is not logged in send them back to the login page
if (empty($_SESSION["user_email"])) {
    header('Location: index.php');
}
else
{ //check if the user has the priveleges to access this page, if not send them back to the account page
    $account = mysqli_fetch_array($connection->prepareQuery("SELECT * FROM `user` WHERE email=?", $_SESSION["user_email"]));
    if(empty($account["admin"]))
    {
        header('Location: account_page.php');
    }
}

if($_SERVER["REQUEST_METHOD"] == "POST")
{
    if(isset($_POST["create"]))
    {
        $connection->insertData("meal", array($_POST["meal_name"], $_POST["meal_image"], (int)$_POST["meal_type"],
            (int)$_POST["calories"], (int)$_POST["dietary_type"], (int)$_POST["weight_band"]));
        header('Location: admin_form.php?mode=view&confirmation=Entry has been successfully created');
    }
    elseif (isset($_POST["update"]))
    {
        $connection->updateData("meal", array($_POST["meal_name"], $_POST["meal_image"], (int)$_POST["meal_type"],
            (int)$_POST["calories"], (int)$_POST["dietary_type"], (int)$_POST["weight_band"]), (int)$_POST["updateID"]);
        header('Location: admin_form.php?mode=view');
    }
    elseif (isset($_POST["delete"]))
    {
        //check if a user has the meal in their plan since entry cannot be deleted without the foreign key references being deleted first
        if(mysqli_num_rows($connection->runQuery("SELECT * FROM `user_meal_link` WHERE meal_id=". $_POST["id"])) > 0)
        {
            header('Location: admin_form.php?mode=update&id='. $_POST["id"] .'&confirmation=This record can\'t be deleted as a user has it as part of a meal plan');
        }
        else
        {
            //delete the selected entry
            $connection->runQuery("DELETE FROM `meal` WHERE meal_id='". $_POST["id"] ."'");
            header('Location: admin_form.php?mode=view&confirmation=Entry has been successfully deleted');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
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
    <title><?php //set the title to correlate to the current action that the admin is doing
        if(isset($_GET["mode"]))
        {
            if($_GET["mode"] == "create")
                echo "Create Record";
            elseif ($_GET["mode"] == "update")
                echo "Update Record";
            else
                echo "View Records";
        }
        else
        {
            echo "View records";
        }?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="Style.css">
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
    <?php
    //if the mode is to create or update, output the form to do this
        if(isset($_GET["mode"]) && $_GET["mode"] == "create" || $_GET["mode"] == "update")
        {
            if($_GET["mode"] == "update")
            {
                //if updating, get the record to update
                $meal = mysqli_fetch_array($connection->runQuery("SELECT * FROM `meal` WHERE meal_id='". $_GET["id"] ."'"));
            }
            //get all meal types, weight bands and dietary types so that the admin can assign them to the meal record they are updating/creating
            $mealTypes = $connection->runQuery("SELECT * FROM `meal_type`");
            $weightBand = $connection->runQuery("SELECT * FROM `weight_band`");
            $dietaryPreferences = $connection->runQuery("SELECT * FROM `dietary_type`");
            echo "<div id='log in' class='form-center'>
            <p> Create New Meal Record </p>
            </div>
            <div class='form-center' align='center'>
            <form method='post' action='". $_SERVER['PHP_SELF'] ."'> 
            <label for='meal name entry'>Meal Name:</label>
            <input name='meal_name' type='text' class='form-control' id='meal name entry' aria-describedby='meal name entry'    
                   maxlength='20' required placeholder='Enter meal name' 
                   value='". (($_GET["mode"] == "update")? $meal["meal_name"] : "") ."'>

            <div class='form-group' class='form-center'>
                <label for='meal image'>Meal Image Link:</label>
                <input name='meal_image' type='text' class='form-control' id='meal image' placeholder='Enter image link'
                maxlength='128' required value='". (($_GET["mode"] == "update")? $meal["meal_image"] : "") ."'>
            </div>
            
            <div class='form-group' class='form-center'>
            <label for='calories'>Calories:</label>
            <input name='calories' type='number' placeholder='calories' class='form-control' min='0' max='9999' required
            value='". (($_GET["mode"] == "update")? $meal["calories"] : "") ."'>
            </div>
            
            <div class='form-group' class='form-center' align='center'>
            <label for='meal_types'>Meal type:</label>
            <select name='meal_type'>";
                while($type = mysqli_fetch_array($mealTypes)) //output the meal types (breakfast, lunch, dinner) for selection
                {
                    echo "<option value='". $type["type_id"] ."' ".
                        (($_GET["mode"] == "update" && $type["type_id"] == $meal["meal_type"])? "selected" : "") .">". $type["type"] ."</option>";
                }
            echo "</select>
            </div>
            
            <div class='form-group form-center'>
            <label for='weight_band'>Weight category:</label>
            <select name='weight_band'>";
            while($band = mysqli_fetch_array($weightBand)) //output the weight band (underweight, average, overweight) options
            {
                echo "<option value='". $band["weight_id"] ."' ".
                    (($_GET["mode"] == "update" && $band["weight_id"] == $meal["weight_band"])? "selected" : "") .">". $band["weight_band"] ."</option>";
            }
            echo "
            </select>
            </div>
            
            <div class='form-group form-center'>
            <label for='dietary_type'>Dietary type:</label>
            <select name='dietary_type'>";
            while($preference = mysqli_fetch_array($dietaryPreferences)) //output the dietary options (none, vegan, vegetarian, etc)
            {
                echo "<option value='". $preference["dietary_id"] ."' ".
                    (($_GET["mode"] == "update" && $preference["dietary_id"] == $meal["dietary_type"])? "selected" : "") .">". $preference["dietary_type"] ."</option>";
            }
            echo "
            </select>
            </div>
            <button type='submit' class='btn btn-primary' name='". (($_GET["mode"] == "update")? "update" : "create") ."'>Submit</button>
            ". (($_GET["mode"] == "update")? "<input type='hidden' name='updateID' value='". $_GET["id"] ."'>" : "") ."
            </form>
            </div>";
            if($_GET["mode"] == "update")
            {
                //if the mode is update, create a hidden field to store the id of the field that is being updated
                echo "<form method='post' action='". $_SERVER['PHP_SELF'] ."' class='form-center' align='center'>
                        <input type='hidden' name='id' value='". $_GET["id"] ."'>
                        <button name='delete' type='submit' class='btn btn-primary'>Delete</button>
                      </form>";
            }
        }
        else
        {
            //get all the meal records along with their details for display
            $meals = $connection->runQuery("SELECT * FROM `meal`
            INNER JOIN `meal_type`
                ON `meal`.`meal_type`=`meal_type`.`type_id`
            INNER JOIN `weight_band`
                ON `meal`.`weight_band`=`weight_band`.`weight_id`
            INNER JOIN `dietary_type`
                ON `meal`.`dietary_type`=`dietary_type`.`dietary_id`");
            echo "
            <div id='log in' class='form-center'>
            <p> Review Meal Records </p>
            </div>
            <table>
            <th>Name</th>
            <th>Cals</th>
            <th>Type</th>
            <th>Band</th>
            <th>Dietary</th>
            <th></th>
            ";
            //output each record for display
            while($meal = mysqli_fetch_array($meals))
            {
                echo "<tr onclick='location.href = \"?mode=update&id=". $meal["meal_id"] ."\"'>
                    <td>". $meal["meal_name"] ."</td>
                    <td>". $meal["calories"] ."</td>
                    <td>". $meal["type"] ."</td>
                    <td>". $meal["weight_band"] ."</td>
                    <td>". $meal["dietary_type"] ."</td>
                </tr>";
            }
            echo "</table>";
        }
    ?>
    <div align="center">
        <form action='account_page.php'>
            <button type="submit" class="btn btn-primary">Return</button>
        </form>
    </div>
    <?php if(isset($_GET["confirmation"])) echo "<p>" . $_GET["confirmation"] ."</p>"; ?>
</div>

<div id="account footer">
    <footer>
        <br><a href="tos.php">Terms of service</a>
    </footer>
</div>
</body>
</html>