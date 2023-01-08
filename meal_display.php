<?php
require_once 'database_functions.php';
session_start();
if (!isset($_SESSION["logged in"])) {
    $_SESSION["logged in"] = false;
    header('Location: index.php');
}
$connection = new database();
//get the user account so that you can generate a meal plan based off their diet and weight band
$user = mysqli_fetch_array($connection->runQuery("SELECT * FROM `user` 
JOIN `weight_band` ON `user`.`weight_band`=`weight_band`.`weight_id` WHERE email='". $_SESSION["user_email"] ."'"));
if(!empty($_GET) && !isset($_POST["confirm"]))
{
    //if the mode is view that means the user wants to view their existing meal plan, not create a new one
    if(isset($_GET["mode"]) && $_GET["mode"] == "view")
    {
        //get the users existing meal plan
        $meal_plan = $connection->runQuery("SELECT * FROM `user_meal_link`
    INNER JOIN `meal` 
    	ON `user_meal_link`.`meal_id`=`meal`.`meal_id`
    INNER JOIN `meal_type`
    	ON `meal`.`meal_type`=`meal_type`.`type_id`
    WHERE user_id='". $user["user_id"] ."' ORDER BY type_id");
    }
}
else
{
    $meal_plan = generateMealPlan($user["weight_id"], $user["dietary_preference"], $connection);
}

if(isset($_POST))
{
    if(isset($_POST["confirm"]))
    {
        //delete the previous relationship between the user and the meal records as they are no longer needed
        $connection->runQuery("DELETE FROM `user_meal_link` WHERE user_id='". $user["user_id"] ."'");
        //insert the meal plan by creating a relationship between the user and the meal records
        $connection->insertData("user_meal_link", array((int)$user["user_id"], (int)$_POST["breakfast"]));
        $connection->insertData("user_meal_link", array((int)$user["user_id"], (int)$_POST["lunch"]));
        $connection->insertData("user_meal_link", array((int)$user["user_id"], (int)$_POST["dinner"]));
        header('Location: account_page.php');
    }
}
//selects 3 meals randomly within a weight band, each one being from the breakfast, lunch and dinner categories respectively
function generateMealPlan($weightBand, $diet = 0, $connection)
{
    $pref = "";
    //if the preference doesn't equal zero that means the user has a dietary need/preference and appropriate meals should be displayed accordingly
    if($diet != 0)
    {
        //create the where clause for the sql command so that meals that match the dietary preference are returned
        $pref = "AND dietary_type=" . $diet;
    }
    //this command selects all the meals from each type individually (breakfast, lunch, dinner) and orders each of these types randomly to yield a random meal for each field
    //finally each query is limited to one record, a union operation is done to combine these meals into a result and their types are joined to them
    return $connection->runQuery("SELECT * FROM ((SELECT * FROM `meal` WHERE meal_type='1' AND weight_band='". $weightBand ."' ". $pref ." ORDER BY RAND() LIMIT 0,1)
    UNION
    (SELECT * FROM `meal` WHERE meal_type='2' AND weight_band='". $weightBand ."' ". $pref ." ORDER BY RAND() LIMIT 0,1)
    UNION
    (SELECT * FROM `meal` WHERE meal_type='3' AND weight_band='". $weightBand ."' ". $pref ." ORDER BY RAND() LIMIT 0,1)) AS plan
    JOIN `meal_type` ON `plan`.`meal_type`=`meal_type`.`type_id`");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Meal Display</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="Style.css">
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

    <!-- Title -->
    <div id="log in" class="form-center">
        <p> <?php echo $user["weight_band"] ?> Meal Plan </p>
    </div>
    <div class="container py-5">
        <div class="row text-center text-white mb-5">

            <!-- Meal Plan Display generation, html/CSS bootstrap tutorial help: "https://bbbootstrap.com/snippets/product-list-65909871" -->

        </div>
        <div class="row">
            <div class="col-lg-8 mx-auto">

                <ul class="list-group shadow">
                    <?php
                    $totalCalories = 0;
                    //store the ids of each meal in the plan so that they can be linked to the user in the database if chosen
                    $mealIndexes = array();
                    //display each meal
                    while($meal = mysqli_fetch_array($meal_plan))
                    {
                        $totalCalories += $meal["calories"];
                        //add the meal records id
                        $mealIndexes[] = $meal["meal_id"];

                        echo "<li class='list-group-item'>
                        
                        <div class='media align-items-lg-center flex-column flex-lg-row p-3'>
                            <div class='media-body order-2 order-lg-1'>
                                <h5 style = color:black class='mt-0 font-weight-bold mb-2'>". $meal["type"] .":</h5>
                                <p class='font-italic text-muted mb-0 small'>Calories: <u>". $meal["calories"] ."kcal</u></p>
                                <div class='d-flex align-items-center justify-content-between mt-1'>
                                    <h6 style = color:black class='font-weight-bold my-2'>". $meal["meal_name"] ."</h6>
                                </div>
                            </div><img width='275px' height='250px' class='ml-lg-5 order-1 order-lg-2'
                                    src='". ((!empty($meal["meal_image"])) ? $meal["meal_image"] : "https://external-content.duckduckgo.com/iu/?u=http%3A%2F%2Fwww.fremontgurdwara.org%2Fwp-content%2Fuploads%2F2020%2F06%2Fno-image-icon-2.png&f=1&nofb=1&ipt=6cd5a3acdd380efbd0eb95399e81ea30f041d3d19b02d23a48c9dfde91725bc6&ipo=images") ."'>
                        </div>
                    </li>";
                    }
                    ?>
                </ul>
                <div>
                <h2>Total Calories: <?php echo $totalCalories?></h2>
                </div>
                <div>
                    <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                        <input type="hidden" name="breakfast" value="<?php echo $mealIndexes[0]?>">
                        <input type="hidden" name="lunch" value="<?php echo $mealIndexes[1]?>">
                        <input type="hidden" name="dinner" value="<?php echo $mealIndexes[2]?>">
                        <button type='submit' class='btn btn-primary' name='confirm'>Confirm</button>
                    </form>
                </div>
                <div>
                    <p>Not happy with Choice?</p>
                    <form action="?mode=generate">
                        <button type='submit' class='btn btn-primary' >New Plan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="account footer">
    <footer>
        <br><a href="tos.php">Terms of service</a>
    </footer>
</div>
</body>
</html>
