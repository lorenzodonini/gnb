<?php
/**
 * Created by PhpStorm.
 * User: lorenzodonini
 * Date: 22/10/15
 * Time: 19:28
 */

session_start();

require_once __DIR__."/../resource_mappings.php";
require_once getpageabsolute("db_functions");
require_once getpageabsolute("user");

if (isset($_POST['surname'])) {
    //query by surname
    $search = getClientBySurname($_POST['surname']);
    $result = array();
    for ($i=0; $i < count($search); $i++) {
        $user = new user($search[$i]);
        $result[$i] = $user->getBasicInfo();
    }
    header('Content-type: application/json');
    echo json_encode($result);
}
elseif (isset($_POST['id'])) {
    //query by id
    $search = getClientDetails($_POST['id']);
    $user = new user($search);
    $result = array();
    $result[0] = $user->getBasicInfo();
    header('Content-type: application/json');
    echo json_encode($result);
}
else {
    //Error?
}

?>
