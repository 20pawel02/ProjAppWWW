<?php
// Configuration file for database connection

// Database connection details
$host = 'localhost'; // Database host
$user = 'root'; // Database user
$password = ''; // Database password
$dbname = 'moja_strona'; // Database name

// Define constants for admin login credentials
if (!defined('ADMIN_LOGIN')) {
    define('ADMIN_LOGIN', 'admin');      // Admin panel login
}

if (!defined('ADMIN_PASSWORD')) {
    define('ADMIN_PASSWORD', 'haslo');   // Admin panel password
}

//--------------------------------------------------------------------------------------------------------------------
// Database connection
//--------------------------------------------------------------------------------------------------------------------

// Create a new database connection
$conn = new mysqli($host, $user, $password, $dbname); 
// Check if the connection was successful
if ($conn->connect_error) { 
    die('<b>Połączenie zostało przerwane: </b>' . $conn->connect_error); // Display error message if connection fails
}

// Set UTF-8 encoding
if (!$conn->set_charset("utf8")) {
    error_log("Błąd ustawienia kodowania UTF-8: " . $conn->error); // Log error if setting fails
    die("Przepraszamy, wystąpił problem z konfiguracją."); // Display error message
}

// Set strict mode for MySQL
$conn->query("SET SESSION sql_mode = 'STRICT_ALL_TABLES'");

// Automatically close the connection at the end of the script
register_shutdown_function(function() use ($conn) {
    if ($conn instanceof mysqli) {
        $conn->close(); // Close the connection
    }
});
?>