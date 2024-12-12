<?php
    // Plik konfiguracyjny do połączenia z bazą danych

    // Dane połączenia z bazą danych
    $host = 'localhost';
    $user = 'root';
    $password = '';
    $dbname = 'moja_strona';

    if (!defined('ADMIN_LOGIN')) {
        define('ADMIN_LOGIN', 'admin');      // Login do panelu administracyjnego
    }
    
    if (!defined('ADMIN_PASSWORD')) {
        define('ADMIN_PASSWORD', 'haslo');   // Hasło do panelu administracyjnego
    }


    //--------------------------------------------------------------------------------------------------------------------
    // Połączenie z bazą danych
    //--------------------------------------------------------------------------------------------------------------------

    // Utworzenie nowego połączenia z bazą danych
    $conn = new mysqli($host, $user, $password, $dbname); 
    // Sprawdzenie, czy połączenie zostało nawiązane
    if ($conn->connect_error) { 
        die('<b>Połączenie zostało przerwane: </b>' . $conn->connect_error); // Jeśli połączenie nie zostało nawiązane, wyświetlenie komunikatu o błędzie
    }

    // Ustawienie kodowania UTF-8
    if (!$conn->set_charset("utf8")) {
        error_log("Błąd ustawienia kodowania UTF-8: " . $conn->error);
        die("Przepraszamy, wystąpił problem z konfiguracją.");
    }

    // Ustawienie trybu ścisłego dla MySQL
    $conn->query("SET SESSION sql_mode = 'STRICT_ALL_TABLES'");

    // Automatyczne zamykanie połączenia na końcu skryptu
    register_shutdown_function(function() use ($conn) {
        if ($conn instanceof mysqli) {
            $conn->close();
        }
    });
?>