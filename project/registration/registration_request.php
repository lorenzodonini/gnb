<?php

function checkPasswordStrength($pass) {
    if (strlen($pass) < 8 || strlen($pass) > 20) {
        return false;
    }

    if (!preg_match("#[0-9]+#",$pass)) {
        return false;
    }
    if (!preg_match("#[a-zA-Z]+#", $pass)) {
        return false;
    }
    return true;
}

/*Just process the received form, store the data inside the DB,
maybe return an error if the data already existed and finally return to the index */

require_once __DIR__."/../resource_mappings.php";
require_once getpageabsolute("db_functions");
require_once getPageAbsolute("mail");
require_once getpageabsolute("user");

global $pages;

$error = "?error=";
if (!isset($_POST['type'])
    || !isset($_POST['email'])
    || !isset($_POST['firstname'])
    || !isset($_POST['lastname'])
    || !isset($_POST['banking'])
    || !isset($_POST['password'])
    || !isset($_POST['password_repeat'])) {
    $error = $error."0";
    header("Location:".getPageURL('registration').$error);
    exit();
}

//Getting form stuff
$type = $_POST['type'];
$email = $_POST['email'];
$firstname = $_POST['firstname'];
$lastname = $_POST['lastname'];
$password = $_POST['password'];
$passwordRepeat = $_POST['password_repeat'];
$banking = $_POST['banking'];

// Checking all of the conditions on server side as well.
if ($type == ''
        || $email == ''
        || $firstname == ''
        || $lastname == ''
        || $banking == ''
        || $password == ''
        || $passwordRepeat == '') {
    $error = $error."0";
    header("Location:".getPageURL('registration').$error);
    exit();
}
if ($password != $passwordRepeat) {
    $error = $error."1";
    header("Location:".getPageURL('registration').$error);
    exit();
}
if (!filter_var($email,FILTER_VALIDATE_EMAIL)) {
    $error = $error."2";
    header("Location:".getPageURL('registration').$error);
    exit();
}
if (!checkPasswordStrength($password)) {
    $error = $error."4";
    header("Location:".getPageURL('registration').$error);
    exit();
}

//TODO: WE STILL NEED TO SAVE THIS TO DB SOMEHOW
$random_pin = null;
//Checking the required banking option
if ($banking == 'email') {
    //We will need to send TANs to the user once he has been approved. Need a flag on the db
}
else if ($banking == 'app') {
    //We need to show a unique PIN to the client and allow him to download the SMC.
    $random_pin = mt_rand(0,9);
    for ($i = 0; $i < 6; $i++) {
        $random_pin .= mt_rand(0,9);
    }
}
else {
    //We received a forged request, with an invalid role
    $error = $error."6";
    header("Location:".getPageURL('registration').$error);
    exit();
}

$result = true;
//Checking the role
if ($type == 'client') {
    $result = DB::i()->addClient($firstname, $lastname, $email, $password);
}
else if ($type == 'employee') {
    $result = DB::i()->addEmployee($firstname, $lastname, $email, $password);
}
else {
    //We received a forged request, with an invalid role
    $error = $error."5";
    header("Location:".getPageURL('registration').$error);
    exit();
}
if (!$result) {
    $error = $error."3";
    header("Location:".getPageURL('registration').$error);
    exit();
}

$_SESSION['banking'] = $banking;
$_SESSION['pin'] = $random_pin;

$gnbmailer = new GNBMailer();
$gnbmailer->sendMail_Registration($email, "$firstname $lastname");

$logo_svg = getMedia('logo_svg'); //GNB logo

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" type="text/css" href="../style/gnb.css">
    <link rel="icon" type="image/png" href="../media/gnb_icon.png" />
    <title>Registration</title>
</head>
<body>
<div class="mainContainer">
    <img src="<?php echo $logo_svg ?>" alt="GNB Logo" class="logo_big">
    <div class="simple-container">
        <?php
        $frame = null;
        if ($_SESSION['banking'] == 'email') {
            $frame = getFrameAbsolute('reg_default');
        }
        else if ($_SESSION['banking'] == 'app') {
            $frame = getFrameAbsolute('reg_pin');
        }
        include $frame;
        ?>
    </div>
    <div class="simple-container">
        <h1 class="title4 simple-text-centered">
            This is gonna be LEGENDARY!!!
        </h1>
        <p class="simple-text simple-text-centered">
            <a href="../index.php">Return to Home page</a></p>
    </div>
</div>
</body>
</html>
