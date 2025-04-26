<?php
/**
 * Database connection for the Sandbaai Business Directory plugin.
 */

// Get a database connection
function getDbConnection() {
    $host = 'localhost'; // Replace with your database host
    $user = 'arqggken_wp607'; // Replace with your database username
    $password = '[G23Spp.l2'; // Replace with your database password
    $database = 'arqggken_wp607'; // Replace with your database name

    $conn = new mysqli($host, $user, $password, $database);

// Check connection
    if ($conn->connect_error) {
        error_log("Database connection failed: " . $conn->connect_error);
        return false;
    }

    return $conn;
}
?>