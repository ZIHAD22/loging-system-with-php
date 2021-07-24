<?php

$conn = new mysqli("localhost", "root", "", "loging_app");

function escape($string)
{
    global $conn;
    return mysqli_escape_string($conn, $string);
}

function query($query)
{
    global $conn;
    return  mysqli_query($conn, $query);
}

function fetcharray($result)
{
    global $conn;
    return mysqli_fetch_array($result);
}

function confirm($result)
{
    global $conn;
    if (!$result) {
        die("Query Falif" . mysqli_error($conn));
    }
}

function row_count($result)
{

    global $conn;
    return mysqli_num_rows($result);
}
