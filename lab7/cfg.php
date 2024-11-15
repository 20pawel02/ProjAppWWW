<?php
$dbhost = 'localhost';
$dbuser = 'root';
$dbpass = '';
$baza = 'moja_strona';


$conn = new mysqli($dbhost, $dbuser, $dbpass, $baza);

if ($conn->connect_error) {
    die('<b>Połączenie zostało przerwane: </b>' . $conn->connect_error);
}
?>