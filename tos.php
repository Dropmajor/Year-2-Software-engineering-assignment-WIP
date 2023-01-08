<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Terms of Service</title>
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
</head>
<style>
    .fa {
        display: inline-block;
        font: normal normal normal 14px/1 FontAwesome;
        font-size: 65px;
        text-rendering: auto;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
    }
    hr.dashed {
        border-top: 3px dashed #bbb;
    }

    .ph {
        padding-left: 10px;
    }

    .p1 {
        padding-left: 30px;
    }

</style>
<body>
<!-- The top Navigation Menu -->
<div class="mobile-container">


    <div class="topnav">
        <a href="javascript:void(0);" class="icon" onclick="hamBurgerMenu()">
            <i class="fa fa-bars"></i>
        </a>
        <nav>
            <a href="#home" class="active">Diet<br>Planner</a>
            <div id="myLinks">
                <a style = "background-color: #2E5266" href="#">My Account</a>
                <a style = "background-color: #2E5266" href="meal_display.php">Generate Meal Plan</a>
                <a style = "background-color: #2E5266" href="?mode=logout">Sign out</a>
        </nav>


    </div>

    <h2>Terms of Service:</h2>
    Diet planner does not hold any of the users data or sell to third party advertisers. Enjoy using the app! <br>
    <a href="index.php">Return</a>
</div>

<div id="account footer">
    <footer>
        <a href="#">Settings</a><br>
        <br><a href="tos.php">Terms of service</a>
    </footer>
</div>

</body>
</html>
