<?php

include('cfg.php'); // Include configuration file

// ----------------------------------------------------------------
// Function PokazStrone displays the content of the page with the given alias.
// @param string $alias Alias of the page to display.
// @return string Page content or message if the page is not found.
// ----------------------------------------------------------------
function PokazStrone($id) {
    global $conn; // Use global connection variable
    $id_clear = htmlspecialchars($id); // Sanitize the input

    if ($id_clear == -1) { // Check if the alias is for the admin panel
        if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
            return '[brak_dostepu]'; // Return access denied message
        }
        return '
        <div class="admin-panel">
            <h2>Panel Administracyjny</h2>
            <ul>
                <li><a href="?action=manage_pages">Zarządzaj stronami</a></li>
                <li><a href="?action=manage_users">Zarządzaj użytkownikami</a></li>
                <li><a href="?action=site_settings">Ustawienia strony</a></li>
            </ul>
        </div>'; // Return admin panel HTML
    }

    // SQL query to fetch the page content
    $query = "SELECT * FROM page_list WHERE id = ? LIMIT 1";
    $stmt = $conn->prepare($query); // Prepare the statement
    $stmt->bind_param("s", $id_clear); // Bind parameters
    $stmt->execute(); // Execute the statement
    $result = $stmt->get_result(); // Get the result
    $row = $result->fetch_assoc(); // Fetch the row
    $stmt->close(); // Close the statement

    return empty($row['id']) ? '[nie_znaleziono_strony]' : $row['page_content']; // Return page content or not found message
}

// ----------------------------------------------------------------
// Check if the variable $_GET['idp'] is set
// If so, call the function PokazStrone with the value from $_GET['idp']
// If not, display a message indicating that the page is not found
// ----------------------------------------------------------------
?>