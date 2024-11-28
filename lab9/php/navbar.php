<?php
function loadNav() {
    global $conn; // Użycie globalnej zmiennej połączenia z bazą danych

    // Zapytanie SQL do pobrania wszystkich aktywnych podstron
    $query = "SELECT alias, page_title FROM page_list WHERE status = 1"; // Pobieramy tylko aktywne strony
    $result = $conn->query($query); 

    // Inicjalizacja zmiennej do przechowywania HTML nawigacji
    $navHtml = '<nav><ul>';

    // Iteracja przez wyniki zapytania
    while ($row = $result->fetch_assoc()) {
        // Dodanie linku do nawigacji dla każdej podstrony
        $navHtml .= '<li><a href="?idp=' . htmlspecialchars($row['alias']) . '">' . htmlspecialchars($row['page_title']) . '</a></li>';
    }

    // Sprawdzenie, czy użytkownik jest zalogowany
    if (isset($_SESSION['loggedin'])) {
        // Jeśli użytkownik jest zalogowany, dodaj linki do panelu administracyjnego
        $navHtml .= '<li><a class="logout" href="?idp=logout">WYLOGUJ</a></li>';
    }else{
        $navHtml .= '<li><a class="haslo" href="?idp=haslo">ODZYSKIWANIE HASŁA</a></li>';
    }

    $navHtml .= '</ul></nav>'; // Zamknięcie listy i nawigacji

    return $navHtml; // Zwrócenie HTML nawigacji
}
?>