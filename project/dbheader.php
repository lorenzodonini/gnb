<?php

$DB_HOST				= "localhost" ;
$DB_USERNAME			= "samurai" ;
$DB_PASSWORD			= "samurai" ;
$DB_SCHEMA				= "gnbdb" ;

$USER_TABLE_NAME		= "$DB_SCHEMA.user" ;
$USER_TABLE_KEY			= "id" ;
$USER_TABLE_ROLE		= "role" ;
$USER_TABLE_STATUS		= "status" ;
$USER_TABLE_APPROVER	= "approved_by_user_id" ;
$USER_TABLE_FIRSTNAME	= "first_name" ;
$USER_TABLE_LASTNAME	= "last_name" ;
$USER_TABLE_EMAIL		= "email" ;
$USER_TABLE_SALT		= "pw_salt" ;
$USER_TABLE_HASH		= "pw_hash" ;

$TAN_TABLE_NAME			= "$DB_SCHEMA.tan" ;
$TAN_TABLE_KEY			= "id" ;
$TAN_TABLE_ACCOUNT_ID	= "account_id" ;
$TAN_TABLE_USED_TS		= "used_timestamp" ;

$TRANSACTION_TABLE_NAME		= "$DB_SCHEMA.transaction" ;
$TRANSACTION_TABLE_KEY		= "id" ;
$TRANSACTION_TABLE_TO		= "destination_account_id" ;
$TRANSACTION_TABLE_FROM		= "source_account_id" ;
$TRANSACTION_TABLE_AP_AT	= "approved_at" ;
$TRANSACTION_TABLE_AP_BY	= "approved_by_user_id" ;
$TRANSACTION_TABLE_STATUS	= "status" ;
$TRANSACTION_TABLE_AMOUNT	= "amount" ;
$TRANSACTION_TABLE_DESC		= "description" ;
$TRANSACTION_TABLE_TAN		= "tan_id" ;
$TRANSACTION_TABLE_C_TS		= "creation_timestamp" ;

$ACCOUNT_TABLE_NAME			= "$DB_SCHEMA.account" ;
$ACCOUNT_TABLE_KEY			= "id" ;
$ACCOUNT_TABLE_USER_ID		= "user_id" ;

$ACCOUNTOVERVIEW_TABLE_NAME		= "$DB_SCHEMA.account_overview" ;
$ACCOUNTOVERVIEW_TABLE_KEY		= "id" ;
$ACCOUNTOVERVIEW_TABLE_USER_ID	= "user_id" ;
$ACCOUNTOVERVIEW_TABLE_BALANCE	= "balance" ;

$FAKE_APPROVER_USER_ID = 1;

# ROLES in USER TABLE
$USER_ROLES = array(
	'client'		=> 0,
	'employee'		=> 1
);
	
# STATUS in USER TABLE
$USER_STATUS = array(
	'unapproved'	=> 0,
	'approved'		=> 1,
	'rejected'		=> 2,
	'blocked'		=> 3
);

# STATUS in TRANSACTION TABLE
$TRANSACTION_STATUS = array(
	'unapproved'	=> 0,
	'approved'		=> 1,
	'rejected'		=> 2
);

$DB_CONNECTION = getDatabaseConnection();
$debug = false;
//$debug = true;


function printError($message) {
	print "<br><br><div style=\"color: red;\">ERROR: $message</div><br><br>";
}

function printDebug($function, $sql) {
	global $debug;

	if ($debug) {
		print "$function ( $sql )<br>";
	}
}

function executeSelectStatementOneRecord($sql)
{
	printDebug("executeSelectStatementOneRecord", $sql);

	$data = executeSelectStatement($sql);
	$result = $data[0];

	return $result;
}

function executeSelectStatement($sql)
{
	printDebug("executeSelectStatement", $sql);

	global $DB_CONNECTION;

	$result = mysql_query($sql, $DB_CONNECTION);

	if ($result == false) {

		$message = 'Invalid query: ' . mysql_error() . '<br>';
		$message .= 'Query: ' . $sql . '<br>';

		printError($message);
		return -1;

	} else {

		$data = array();
		while($row = mysql_fetch_assoc($result)) {
			$data[] = $row;
		}
	}

	return $data;
}
 
function executeAddStatementOneRecord($sql)
{
	printDebug("executeAddStatementOneRecord", $sql);

	global $DB_CONNECTION;

	$result = mysql_query($sql, $DB_CONNECTION);

	if ($result == false) {

		$message = 'Invalid query: ' . mysql_error() . '<br>';
		$message .= 'Query: ' . $sql . '<br>';

		printError($message);
		return -1;

	} else {
		$data = mysql_insert_id();
	}

	return $data;
}

function executeSetStatement($sql)
{
	printDebug("executeSetStatement", $sql);

	global $DB_CONNECTION;

	$result = mysql_query($sql, $DB_CONNECTION);

	if ($result == false) {

		$message = 'Invalid query: ' . mysql_error() . '<br>';
		$message .= 'Query: ' . $sql . '<br>';

		printError($message);
		return -1;

	} else {
		$data = mysql_affected_rows();
	}

	return $data;
}

function getDatabaseConnection() {

	global $DB_HOST;
	global $DB_USERNAME;
	global $DB_PASSWORD;
	global $DB_SCHEMA;

	$connection = mysql_connect($DB_HOST, $DB_USERNAME, $DB_PASSWORD);

	if ($connection == false) {
		return NULL; //TODO: Do something with error message?
	}

	if (mysql_select_db($DB_SCHEMA, $connection) == false) {
		return NULL; //TODO: Do something with error message?
	}

	return $connection;
}

function closeDatabaseConnection($connection) {
	mysql_close($connection);
}

?>
