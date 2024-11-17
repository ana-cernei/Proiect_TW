<?php
require_once 'config.php';

// Establish database connection
$dbConn = mysqli_connect($dbHost, $dbUser, $dbPass, $dbName);

// Check connection
if (!$dbConn) {
    die("MySQL connection failed: " . mysqli_connect_error());
}

// Function to execute a query
function dbQuery($sql)
{
    global $dbConn;
    $result = mysqli_query($dbConn, $sql);
    if (!$result) {
        die("Query failed: " . mysqli_error($dbConn));
    }
    return $result;
}

// Function to get the number of affected rows
function dbAffectedRows()
{
    global $dbConn;
    return mysqli_affected_rows($dbConn);
}

// Function to fetch an array result
function dbFetchArray($result, $resultType = MYSQLI_NUM)
{
    return mysqli_fetch_array($result, $resultType);
}

// Function to fetch an associative array result
function dbFetchAssoc($result)
{
    return mysqli_fetch_assoc($result);
}

// Function to fetch a row result
function dbFetchRow($result)
{
    return mysqli_fetch_row($result);
}

// Function to free result memory
function dbFreeResult($result)
{
    mysqli_free_result($result);
}

// Function to get the number of rows in the result
function dbNumRows($result)
{
    return mysqli_num_rows($result);
}

// Function to select a database (not commonly used in modern code)
function dbSelect($dbName)
{
    global $dbConn;
    return mysqli_select_db($dbConn, $dbName);
}

// Function to get the last inserted ID
function dbInsertId()
{
    global $dbConn;
    return mysqli_insert_id($dbConn);
}
?>