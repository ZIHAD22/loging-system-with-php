<?php
##########################################################################
#Read It first then work in the file:
##########################################################################
/*
there are many custome function are made by me like:
clean,redirect , query , row_count , display_messece , set_messess,
and many thing so first cheack that than work ok good to go :)
*/
/*
And surver reletate function are made on db.php page on function __dr;
*/
##########################################################################
#Read It first then work in the file:
##########################################################################
require("./classes/mailconfiq.php");
require("./vendor/autoload.php");


use PHPMailer\PHPMailer\PHPMailer;


################################
#helper function for work
################################
function clean($result)
{
    global $conn;
    return htmlentities($result);
}
function redirect($location)
{
    return header("Location: {$location}");
}
function set_messages($message)
{
    if ($message) {
        $_SESSION['message'] = $message;
    } else {
        $message = "";
    }
}

function display_message()
{

    if (isset($_SESSION['message'])) {
        echo $_SESSION['message'];



        unset($_SESSION['message']);
    }
}

function token_generator() //this is token generater 
{

    $token = $_SESSION['token'] =  md5(uniqid(mt_rand(), true));
    return $token;
}

function from_validation($errors) //this is form validation messece
{
    $message = <<<MESSS
    <div class="alert alert-success alert-dismissible" role="alert">
    <button type="button" class="close" data-dismiss="alert">
        <span aria-hidden="true">×</span><span class="sr-only">Close</span>
    </button>$errors
</div>
MESSS;
    return $message;
}

function email_exists($email) //this function does the email is one the surver
{
    $sql = query("SELECT id FROM users WHERE email = '{$email}' ");
    if (row_count($sql) == 1) {
        return true;
    } else {
        return false;
    }
}

function username_exists($username) //this is same as a email_exists
{
    $sql = query("SELECT id FROM users WHERE username = '{$username}' ");
    if (row_count($sql) == 1) {
        return true;
    } else {
        return false;
    }
}
############################################################
#VALIDATION FUNCTION
############################################################

function validation_user_registration()
{

    if ($_SERVER['REQUEST_METHOD'] == "POST") {

        $min = 3;
        $max = 20;
        $error = [];

        $first_name = $_POST['first_name'];
        $last_name = $_POST['last_name'];
        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];




        if (strlen($first_name) < $min) {
            $error[] = "You first name is can't Lass {$min} Carater ";
        }


        if (strlen($last_name) < $min) {
            $error[] = "You last name is can't Lass {$min} Carater ";
        }


        if (strlen($username) < $min) {
            $error[] = "You user name is can't Lass {$min} Carater ";
        }


        if (strlen($first_name) > $max) {
            $error[] = "You first name is can't be more than {$max} Carater ";
        }


        if (strlen($last_name) > $max) {
            $error[] = "You last name is can't be more than {$max} Carater ";
        }


        if (strlen($username) > $max) {
            $error[] = "You user name is can't be more than {$max} Carater ";
        }

        if ($password !== $confirm_password) {
            $error[] = "You given password does not mach with confirm password ";
        }
        if (email_exists($email)) {
            $error[] = "Sorry this email is alrady register";
        }
        if (username_exists($username)) {
            $error[] = "Sorry this username is alrady been taken";
        }


        if (!empty($error)) {


            foreach ($error as $errors) {
                echo   from_validation($errors);
            }
        } else {
            if (register_user($first_name, $last_name, $username, $email, $password)) {

                set_messages(from_validation("Please cheack your email or spam folder for activetion link"));
                redirect("index.php");

                echo from_validation("USER REGISTERD");
            }
        }
    }
}

############################################################
#register_user FUNCTION with send email function
############################################################

function register_user($first_name, $last_name, $username, $email, $password)
{


    $first_name = escape($first_name);
    $last_name = escape($last_name);
    $username = escape($username);
    $email = escape($email);
    $password = escape($password);

    $password = md5($password);
    $validation = md5($username . microtime());

    $sql = query("INSERT INTO users(first_name , last_name , username , password , email , validation_code , acctive) VALUES ('{$first_name}' ,'{$last_name}' ,  '{$username}' , '{$password}' , '{$email}' , '{$validation}' , 0  )");
    confirm($sql);


    $subject = "Active Account";
    $msg = "pleace Click The Link Below To Activate Your Account <a href='http://localhost/login/activate.php?email=$email&code=$validation'>CLICK HERE</a>";





    send_email($email, $subject, $msg, $username);  //send email function




}
############################################################
#send_email FUNCTION by phpmailer composer
############################################################
function send_email($email, $subject, $msg, $username)
{


    $mail = new PHPMailer();

    //Server settings
    $mail->isSMTP();                                            //Send using SMTP
    $mail->Host       = Condiq::SMTP_HOST;                     //Set the SMTP server to send through
    $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
    $mail->Username   = Condiq::SMTP_USER;                     //SMTP username
    $mail->Password   = Condiq::SMTP_PASS;                               //SMTP password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         //Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
    $mail->Port       = Condiq::SMTP_PORT;


    $mail->setFrom('noreplai-CMS@gmail.com', 'MD:ZHAD');
    $mail->addAddress("$email", "$username");


    $mail->isHTML(true);                                  //Set email format to HTML
    $mail->Subject = "$subject";
    $mail->Body    = "$msg";
    $mail->AltBody = "$msg";

    if (!$mail->send()) {
        echo from_validation("Message could not be sent . Please connect to te internet");
    } else {
        echo from_validation("Message has been sent");
    }
}
############################################################
#activate_user FUNCTION by email send
############################################################
function activate_user()
{
    if ($_SERVER['REQUEST_METHOD'] == "GET") {


        if (isset($_GET['email'])) {

            $email = clean($_GET['email']);
            $validation_code = clean($_GET['code']);
            $sql = query("SELECT id FROM users WHERE email='" . escape($email) . "' AND validation_code = '" . escape($validation_code) . "'");
            confirm($sql);
            if (row_count($sql) == 1) {
                $sql2 = query("UPDATE users SET acctive = 1 , validation_code = 0 WHERE email ='" . escape($_GET['email']) . "' AND validation_code = '" . escape($_GET['code']) . "' ");
                confirm($sql2);
                set_messages("<p class='bg-success'>Your account has been activate please login </p>");
                redirect("login.php");
            } else {
                set_messages("<p class='bg-danger'>Your account has been not  activate please tray agan </p>");
                redirect("activate.php");
            }
        }
    }
}
############################################################
#validation_user_login FUNCTION
############################################################

function validation_user_login()
{

    $error = [];

    if ($_SERVER['REQUEST_METHOD'] == "POST") {




        if (isset($_POST['login-submit'])) {
            $email = clean($_POST['email']);
            $password = clean($_POST['password']);
            $remember = isset($_POST['remember']);

            if (empty($email)) {
                $error[] = "Email fild can't be empty";
            }
            if (empty($password)) {
                $error[] = "Password fild can't be empty";
            }

            if (!empty($error)) {


                foreach ($error as $errors) {
                    echo   from_validation($errors);
                }
            } else {
                if (login_user($email, $password, $remember)) {
                    redirect("admin.php");
                } else {
                    echo   from_validation("YOUR DETELS IS NOT CORRECT");
                }
            }
        }
    }
}

####################################################################
#user login FUNCTION with the help of validation_user_login function
####################################################################

function login_user($email, $password, $remember)
{


    $sql = query("SELECT password , id FROM users WHERE email = '" . escape($email) . "' AND acctive = 1 ");
    if (row_count($sql) == 1) {

        $row = fetcharray($sql);
        $db_password = $row['password'];

        if (md5($password) === $db_password) {

            if ($remember == "on") {
                setcookie("email", $email, time() + 864000);
            }
            $_SESSION['email'] = $email;

            return true;
        } else {
            return false;
        }
    } else {
        echo   from_validation("YOUR DETELS IS NOT CORRECT");
    }
}
####################################################################
#loghed in  FUNCTION
####################################################################
function logged_in()
{
    if (isset($_SESSION['email']) || isset($_COOKIE['email'])) {
        return true;
    } else {
        return false;
    }
}
####################################################################
#recover_passwor
####################################################################

function recover_password()
{
    if ($_SERVER['REQUEST_METHOD'] == "POST") {

        if (isset($_POST['recover-submit'])) {


            if (isset($_SESSION['token']) && $_POST['token'] === $_SESSION['token']) {

                $email = escape($_POST['email']);


                $sql_fin_a = query("SELECT email FROM users WHERE email  = '{$email}' AND acctive  = 1 ");
                if (row_count($sql_fin_a) == 1) {


                    $sql = query("SELECT username FROM users WHERE email = '{$email}' "); // this sql comand for pool user name from database
                    $row = fetcharray($sql);
                    $username = $row['username']; //this is the pooling username

                    $validation_code = md5($email .  microtime()); //this is validation code that we send to email and set in database to reset 

                    $sql_set_validation_ons = query("UPDATE users SET validation_code = '{$validation_code}' WHERE email = '{$email}' "); // by this sql statment we set code on databace 
                    confirm($sql_set_validation_ons);


                    setcookie("temp_access_code", "{$validation_code}", time() + 600);
                    $subject = "Password Reset Link From CMS";

                    $msg = "Hera is your password reset code {$validation_code} . Please Click here to reset password;
                                http://localhost/login/code.php?email=$email&code=$validation_code";


                    send_email($email, $subject, $msg, $username);
                } else if (email_exists($email)) {
                    echo   from_validation("please Active you account");
                } else {
                    echo   from_validation("your email is not found in database");
                }
            }
        }
    }
    if(isset($_POST['cancel-submit'])){
        redirect("login.php");
    }
}
####################################################################
#recover_passwor _validation code cheaker
####################################################################
function validation_code()
{


    if (isset($_COOKIE['temp_access_code'])) {  //this is cookie chaking 


        if (!isset($_GET['email']) && !isset($_GET['code'])) { //this is chaking if those are set or not

            echo set_messages( from_validation("Pleace check you email or try agan")); //this is show validation messess
            redirect("recover.php"); //this is header function that i made in a function called redirect
        } elseif (empty($_GET['email']) || empty($_GET['code'])) { //this is for validation 
            echo  set_messages( from_validation("Pleace check you email for reset password"));
            redirect("recover.php");
        } else {
            if(isset($_POST['code_submit'])){
            if (isset($_POST['code'])) { //this is a inpute box that you find on code.php
                $validation_code = clean($_POST['code']); //this is clean function that i made on top 
                $email = clean($_GET['email']);
                $sql = query("SELECT id FROM users WHERE validation_code = '{$validation_code}' AND email = '{$email}' "); //this query made by our query function on the top 
                if (row_count($sql) == 1) { //this is row count function 
                    setcookie("temp_access_code", "{$validation_code}", time() + 300);//this is temp accouce code 
                    redirect("reset.php?email=$email&code=$validation_code");
                } else {
                    $fvc = query("SELECT * FROM users WHERE email = '".escape($email)."' ");
                    $row = fetcharray($fvc);
                    $vc = $row['validation_code'];
                    $em = $row['email'];
                    $user_vc = $_GET['code'];
                    $user_em = $_GET['email'];

                    if(!$user_vc === !$vc){
                    echo from_validation("Pleace check validation code does not match");
                }elseif(!$em === !$user_em){
                    set_messages(from_validation("Pleace check email does not match"));
                    redirect("recover.php");
                  }
                  
                }
            }
        }
    }


    } else {
        set_messages(from_validation("your validation code is exparid"));
        redirect("index.php");
    }
    if(isset($_POST['code-cancel'])){
        redirect('recover.php');
    }

}
####################################################################
#reset_passwor function
####################################################################
function reset_password (){




    if(isset($_COOKIE['temp_access_code'])){
        if(isset($_GET['email']) && isset($_GET['code'])){
            
            if(isset($_SESSION['token']) && isset($_POST['token'])){
                 if($_POST['token'] === $_SESSION['token']){

                    if($_POST['password'] === $_POST['confirm_password']){
                        $update_password = md5($_POST['password']);
                        $email = $_GET['email'];
                        $sql_UP = query("UPDATE users SET password = '".escape($update_password)."' WHERE email = '".escape($email)."' ");
                        confirm($sql_UP);
                        set_messages( from_validation("Your Password was Updateed pleace loging with your new password"));
                        redirect("login.php");
                    }else{
                        set_messages(from_validation("Your password does not match "));
                    }
                 
                 }
            }
        }else{
            echo "this is not work";
        }


    }else{
        redirect("recover.php");
        set_messages(from_validation("your validation code is exparid"));
    }





}
