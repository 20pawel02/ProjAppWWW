<?php
// Function to load the navigation menu
function loadNav() {
    global $conn; // Use global connection variable

    // SQL query to fetch all active subpages
    $query = "SELECT id, page_title FROM page_list WHERE status = 1"; // Get only active pages
    $result = $conn->query($query); // Execute query

    // Initialize variable to store navigation HTML
    $navHtml = '<nav><ul>';

    // Iterate through query results
    while ($row = $result->fetch_assoc()) {
        // Add a link to the navigation for each subpage
        $navHtml .= '<li><a href="?idp=' . htmlspecialchars($row['id']) . '">' . htmlspecialchars($row['page_title']) . '</a></li>';
    }

    // Add link to the shop in the main menu
    $navHtml .= '<li><a href="?idp=-10" class="sklep-link">Sklep</a></li>';
    $navHtml .= '<li><a href="?idp=-12" class="koszyk-link">Koszyk';
    if (!empty($_SESSION['koszyk'])) {
        $navHtml .= ' (' . array_sum($_SESSION['koszyk']) . ')'; // Show item count in cart
    }
    $navHtml .= '</a></li>';

    // Check if the user is logged in
    if (isset($_SESSION['loggedin'])) {
        // If logged in, add links to the admin panel
        $navHtml .= '<li><a class="admin" href="?idp=-1">ADMIN</a></li>';
        $navHtml .= '<li><a class="logout" href="?idp=-2">WYLOGUJ</a></li>';
    } else {
        $navHtml .= '<li><a class="haslo" href="?idp=-6">ODZYSKIWANIE HASŁA</a></li>'; // Link for password recovery
    }

    $navHtml .= '</ul></nav>'; // Close the list and navigation

    return $navHtml; // Return the navigation HTML        
}
?>