<?php

include('cfg.php');

// ----------------------------------------------------------------
// Funkcja PokazStrone wyświetla treść strony o podanym aliasie.
// @param string $alias Alias strony do wyświetlenia.
// @return string Treść strony lub komunikat o braku strony.
// ----------------------------------------------------------------

function PokazStrone($id) {
    global $conn;
    $id_clear = htmlspecialchars($id); // ochrona przed atakami typu XSS

    // Specjalna obsługa dla panelu administracyjnego
    if ($id_clear == -1) {
        // Sprawdzenie, czy użytkownik jest zalogowany
        if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
            return '[brak_dostepu]';
        }
        
        // Zwróć treść panelu administracyjnego
        return '
        <div class="admin-panel">
            <h2>Panel Administracyjny</h2>
            <ul>
                <li><a href="?action=manage_pages">Zarządzaj stronami</a></li>
                <li><a href="?action=manage_users">Zarządzaj użytkownikami</a></li>
                <li><a href="?action=site_settings">Ustawienia strony</a></li>
            </ul>
        </div>
        ';
    }

    $query = "SELECT * FROM page_list WHERE id = ? LIMIT 1";
    $stmt = $conn->prepare($query); // przygotowanie zapytania sql
    $stmt->bind_param("s", $id_clear); // powiazanie parametru z zapytaniem

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
?>