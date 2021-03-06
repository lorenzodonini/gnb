<?php

require_once __DIR__."/../resource_mappings.php";
require_once getpageabsolute("utilityfunctions");

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

require_once getpageabsolute("db_functions");
require_once getpageabsolute("user");

if (empty($_SESSION["user_id"]))
    die("User missing");

$user = null;
if (isset($_POST['client_id'])) {
	$client_id	= santize_input($_POST['client_id'],SANITIZE_INT) ;
    $search = DB::i()->getUser($client_id);
    $user = new user($search);
}

if ($user == null) {
    include getSectionAbsolute('manage_clients');
    exit();
}

$data = DB::i()->getAccountsForUser($user->id);
$user->setAccounts($data);
$accounts = $user->accounts;

$selected = (count($accounts) > 0) ? $accounts[0] : null;

//Get the currently selected account
if (isset($_POST["account_id"])) {
	$account_id	= santize_input($_POST["account_id"],SANITIZE_INT) ;
    foreach($accounts as $acc) {
        if ( $account_id == $acc->id ) {
            $selected = $acc;
            break;
        }
    }
}

//Retrieving transaction list for account
if ($selected != null) {
    $selected->setTransactions(DB::i()->getAccountTransactions($selected->id));
}

$currency = '€';
$role = DB::i()->mapUserRole($user->role);
$status = DB::i()->mapUserStatus($user->status);
$banking = DB::i()->mapAuthenticationDevice($user->auth_device);

//VIEW STARTS FROM HERE
?>
<!--<button type="button" onclick="goToEmployeeArea('manage_clients')">Back</button><br>-->
<button type="button" class="details-button" onclick="goToEmployeeArea('manage_clients')">Back</button><br><br>
<p class="title4">
    <?php
    echo "Client $user->id overview";
    ?>
</p>
<table id="client_info" class="simple-text-big">
    <?php
    echo "<tr>
            <td><b>First name:</b></td>
            <td> $user->firstname </td>
        </tr>";
    echo "<tr>
            <td><b>Last name:</b></td>
            <td> $user->lastname </td>
        </tr>";
    echo "<tr>
            <td><b>Email:</b></td>
            <td> $user->email </td>
        </tr>";
    echo "<tr>
            <td><b>Role:</b></td>
            <td> $role </td>
        </tr>";
    echo "<tr>
            <td><b>Status:</b></td>
            <td> $status </td>
        </tr>";
    echo "<tr>
            <td><b>Banking:</b></td>
            <td> $banking </td>
        </tr>";
    ?>
</table>
<br>

<?php
if (count($accounts) == 0 || $selected == null) {
    echo "<p class='simple-text-big simple-text-centered'>This user does not have any accounts!</p>";
    exit(); //Don't need to write anything else
}
?>

<div class='simple-container-no-bounds simple-text-centered'>
    <label for="account_select" class="simple-label">Selected account: </label>
    <select class="select-bar" id="account_select" onchange="onSelectedAccount('my_accounts','client_details','<?= $user->id ?>')">
        <?php
        foreach ($accounts as $acc) {
            echo "<option value='".$acc->id."' ";
            if ($selected == $acc) {
                echo "selected";
            }
            echo ">".$acc->id."</option>";
        }
        ?>
    </select>
</div>

<p class="title4">Account information</p>
<table id="account_info" class="simple-text-big">
    <?php
    echo "<tr>
            <td><b>Account ID:</b></td>
            <td> $selected->id </td>
        </tr>";
    echo "<tr>
            <td><b>Current balance:</b></td>
            <td> $selected->balance $currency </td>
        </tr>";
    ?>
</table>

<p class="title4">Transaction history</p>

<?php
//This is probably duplicate code, since similar stuff could just be provided by transaction_history.php,
// but I still need to present this data differently.
?>

<table class="table-default">
    <thead>
        <tr class="thead-row-default">
            <th class="th-default"></th>
            <th class="th-default">Status</th>
            <th class="th-default">Src</th>
            <th class="th-default">Dst</th>
            <th class="th-default">Date/Time</th>
            <th class="th-default">Amount</th>
            <th class="th-default"></th>
        </tr>
    </thead>
    <tbody>
        <?php
        foreach ($selected->transactions as $transaction) {
            $t_status = $transaction->status;
            $arrow_class = null;
            $arrow_pending = null;
            if ($transaction->src == $selected->id) {
                //We are the source, so the transfer is outgoing.
                $arrow_class = 'outgoing-transfer-arrow';
                $arrow_pending = 'outgoing-pending-arrow';
            }
            else if ($transaction->dst == $selected->id) {
                //We are the destination, so the transfer is ingoing.
                $arrow_class = 'ingoing-transfer-arrow';
                $arrow_pending = 'ingoing-pending-arrow';
            }
            echo "<tr class='tbody-row-default'>
                    <td class='td-default'>";
            if ($transaction->status == DB::i()->mapTransactionStatus('approved')) {
                echo "<span class='$arrow_class'></span>";
            }
            else {
                echo "<span class='$arrow_pending'></span>";
            }
            echo "</td>";
            echo "<td class='td-default'>" . DB::i()->mapTransactionStatus($t_status) . "</td>";
            echo "<td class='td-default'>$transaction->src</td>";
            echo "<td class='td-default'>$transaction->dst</td>";
            echo "<td class='td-default'>$transaction->creation_date</td>";
            echo "<td class='td-default'>$transaction->amount</td>";
            echo "<td class='td-default'>
            <button type='button' class='details-button' onclick='goToClientTransactionDetails($user->id, $transaction->id)'>Details</button>
            </td>";
            echo "</tr>";
        }
        ?>
    </tbody>
</table>
