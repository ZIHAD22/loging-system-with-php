<?php include ("function/init.php");

session_destroy();

if (isset($_COOKIE['email'])){
    unset($_COOKIE['email']);
    setcookie("email" , "" , time() + 864000);
}



redirect("login.php");
