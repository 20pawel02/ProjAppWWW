<?php
// Plik konfiguracyjny do połączenia z bazą danych

// Dane połączenia z bazą danych
$dbhost = 'localhost';
$dbuser = 'root';
$dbpass = '';
$baza = 'moja_strona';

$conn = new mysqli($dbhost, $dbuser, $dbpass, $baza); // Utworzenie nowego połączenia z bazą danych

if ($conn->connect_error) { // Sprawdzenie, czy połączenie zostało nawiązane
    die('<b>Połączenie zostało przerwane: </b>' . $conn->connect_error); // Jeśli połączenie nie zostało nawiązane, wyświetlenie komunikatu o błędzie
}
?>