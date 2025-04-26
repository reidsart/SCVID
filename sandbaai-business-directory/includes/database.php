<?php
function getDbConnection() {
    global $wpdb;
    
    // Using WordPress's built-in database connection
    // Instead of creating a separate connection
    return $wpdb;
}