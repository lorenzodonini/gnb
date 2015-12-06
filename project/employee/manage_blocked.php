<?php

require_once __DIR__."/../resource_mappings.php";

//Worst case, an unauthenticated user is trying to access this page directly
if (!isset($_SESSION["username"]) || !isset($_SESSION["role"])) {
    include(getPageAbsolute('error'));
    exit();
}
//The user is logged in, but tries to access another page directly
else if (!isset($frame)) {
    header("Location:".getPageURL('home'));
    exit();
}
//Vertical privilege escalation attempt -> no go
$role = $_SESSION["role"];
if ($role != "employee") {
    include(getPageAbsolute('error'));
    exit();
}

require_once getpageabsolute("user");
require_once getpageabsolute("db_functions");

//Handling unlocking action
if (isset($_POST['action']) && isset($_POST['users'])) {
    $action = $_POST['action'];
    $requests = $_POST['users'];
    $approver_id = $_SESSION['user_id'];

    if ($action == "unblock") {
        user::unblockUsers($requests, $approver_id);
    }
}

$data = DB::i()->getBlockedUsers();
$newUsers = array();
if ($data != null) {
    foreach ($data as $u) {
        array_push($newUsers, new user($u));
    }
}

//IN CASE WE HAVE NO PENDING REQUESTS
if (count($newUsers) == 0) {
    echo "<p>There currently are no blocked users</p>";
    exit();
}

?>

<p class="simple-label">There are currently <?php echo count($newUsers) ?> blocked users</p><br>

<table class="table-default">
    <thead>
    <tr class="thead-row-default">
        <th class="th-default"></th>
        <th class="th-default">First Name</th>
        <th class="th-default">Last Name</th>
        <th class="th-default">Email</th>
        <th class="th-default">Role</th>
    </tr>
    </thead>
    <tbody>
    <?php
    for ($i =0; $i < count($newUsers); $i++) {
        $user = $newUsers[$i];
        $role = DB::i()->mapUserRole($user->role);
        echo "<tr class='tbody-row-default'>
            <td class='td-default'><input type='checkbox' name='action_check' id='$user->id:$role'>
                <label for='$user->id:$role'><span></span></label></td>
            <td class='td-default'>$user->firstname</td>
            <td class='td-default'>$user->lastname</td>
            <td class='td-default'>$user->email</td>";
        echo "<td class='td-default'>$role</td> <!-- We want an icon here -->
        </tr>";
    }
    ?>
    </tbody>
</table>
<div class="select-all-container">
    <input type="checkbox" id="selectAll_check" onclick="checkAllBoxes()">
    <label for="selectAll_check"><span></span>Select/deselect all</label>
</div>

<p class="simple-text-big simple-text-centered">What should be done with the selected registration requests?</p>
<div class="button-container">
    <button type="button" class="simpleButton" onclick="unblockUsers()">Unblock</button>
</div>