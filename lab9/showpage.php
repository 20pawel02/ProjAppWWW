<?php

include('cfg.php');

// ----------------------------------------------------------------
// Funkcja PokazStrone wyświetla treść strony o podanym aliasie.
// @param string $alias Alias strony do wyświetlenia.
// @return string Treść strony lub komunikat o braku strony.
// ----------------------------------------------------------------

function PokazStrone($alias) {
    global $conn;
    $alias_clear = htmlspecialchars($alias); // ochrona przed atakami typu XSS

    $query = "SELECT * FROM page_list WHERE alias = ? LIMIT 1";
    $stmt = $conn->prepare($query); // przygotowanie zapytania sql
    $stmt->bind_param("s", $alias_clear); // powiazanie parametru z zapytaniem

    $stmt->execute(); // wykonanie zapytania 
    $result = $stmt->get_result(); // pobranie wynikow zapytania
    $row = $result->fetch_assoc(); // pobranie pierwszego wiersza wynikow

    $stmt->close(); // zamkniecie zapytania
    return empty($row['id']) ? '[nie_znaleziono_strony]' : $row['page_content']; // Zwrócenie treści strony lub komunikatu o braku strony
}

// ----------------------------------------------------------------
// Sprawdzenie, czy zmienna $_GET['idp'] jest ustawiona
// Jeśli tak, wywołanie funkcji PokazStrone z wartością z $_GET['idp']
// Jeśli nie, wyświetlenie komunikatu o braku strony
// ----------------------------------------------------------------

if (isset($_GET['idp'])) {
    $alias = $_GET['idp'];
    echo PokazStrone($alias);
} else {
    echo '[nie_znaleziono_strony]';
}
?>