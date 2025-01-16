<?php
include 'cfg.php'; // Include configuration file for database connection

class Admin {
    private $conn; // Database connection

    // Constructor to initialize the database connection
    public function __construct($conn = null) {
        $this->conn = $conn; // Assign the connection to the class property
    }

    // Function to generate the login form
    function FormularzLogowania() {
        return '
        <div class="logowanie">
            <h3 class="heading">Panel CMS:</h3>
            <form method="post" name="LoginForm" action="' . $_SERVER['REQUEST_URI'] . '">
                <table class="logowanie">
                    <tr>
                        <td class="log4_t">Login:</td>
                        <td><input type="text" name="login" class="logowanie" required /></td>
                    </tr>
                    <tr>
                        <td class="log4_t">Hasło:</td>
                        <td><input type="password" name="login_pass" class="logowanie" required /></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td><input type="submit" name="x1_submit" class="logowanie" value="Zaloguj" /></td>
                    </tr>
                </table>
            </form>
        </div>'; // Return the HTML for the login form
    }

    // Function to check login credentials
    function CheckLoginCred($login, $pass) {
        // Compare provided credentials with defined constants
        if ($login == ADMIN_LOGIN && $pass == ADMIN_PASSWORD) {
            $_SESSION['loggedin'] = true; // Set session variable for logged in status
            return 1; // Return success
        }
        return 0; // Return failure
    }

    // Function to check if the user is logged in
    function CheckLogin() {
        // Check if the user is already logged in
        if (isset($_SESSION['loggedin']) && $_SESSION['loggedin']) {
            return 1; // User is logged in
        }
        // Check if login form was submitted
        if (isset($_POST['login']) && isset($_POST['login_pass'])) {
            return $this->CheckLoginCred($_POST['login'], $_POST['login_pass']); // Validate credentials
        }
        return 0; // User is not logged in
    }

    // Function to log out the admin
    function logoutAdmin() {
        session_start(); // Start session
        session_destroy(); // Destroy session data
        header("Location: index.php?idp=1"); // Redirect to homepage
        exit(); // Stop script execution
    }

    // Function to handle admin login
    function LoginAdmin() {
        $status_login = $this->CheckLogin(); // Check login status
        if ($status_login == 1) { // If logged in
            echo '<div style="text-align: right; max-width: 790px; margin: 0 auto; padding: 10px;">
                <a href="?idp=-2" style="background-color: #333; color: white; padding: 8px 15px; text-decoration: none; border-radius: 4px;">Wyloguj</a>
            </div>';
            echo '<div class="admin-menu" style="max-width: 790px; margin: 20px auto; padding: 10px; background-color: #f5f5f5; border-radius: 4px;">
                <a href="?idp=-5" style="margin-right: 15px;">Dodaj nową stronę</a>
                <a href="?idp=-8" style="margin-right: 15px;">Zarządzaj kategoriami</a>
                <a href="?idp=-9" style="margin-right: 15px;">Zarządzaj produktami</a>
            </div>';
            echo '<h3 class="h3-admin">Lista Stron</h3>';
            echo $this->ListaPodstron(); // Display the list of pages
        } else {
            echo $this->FormularzLogowania(); // Show login form if not logged in
        }
    }

    // Function to list subpages
    function ListaPodstron() {
        global $conn; // Use global connection variable
        $sql = "SELECT id, page_title FROM page_list"; // SQL query to get pages
        $result = $conn->query($sql); // Execute query

        if ($result->num_rows > 0) { // If there are results
            $output = "<table border='1' cellpadding='10' cellspacing='0'>
                <tr>
                    <th>ID</th>
                    <th>Tytuł Podstrony</th>
                    <th>Akcje</th>
                </tr>";

            while ($row = $result->fetch_assoc()) { // Fetch each row
                $id = $row['id'];
                $title = htmlspecialchars($row['page_title']); // Escape HTML characters
                $output .= "<tr>
                    <td>{$id}</td>
                    <td>{$title}</td>
                    <td>
                        <a href='index.php?idp=-3&id={$id}'>Edytuj</a> | 
                        <a href='index.php?idp=-4&idd={$id}' onclick='return confirm(\"Czy na pewno chcesz usunąć tę podstronę?\")'>Usuń</a>
                    </td>
                </tr>";
            }
            $output .= "</table>"; // Close the table
        } else {
            $output = "<p>Brak podstron w bazie danych.</p>"; // No pages found
        }
        return $output; // Return the output
    }

    // Function to edit a page
    function EditPage() {
        $status_login = $this->CheckLogin(); // Check if user is logged in
        if ($status_login == 1) { // If logged in
            if (isset($_GET['ide'])) { // Check if page ID is provided
                if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_title'], $_POST['edit_content'])) {
                    // Handle form submission for editing
                    $title = $GLOBALS['conn']->real_escape_string($_POST['edit_title']); // Escape title
                    $content = $GLOBALS['conn']->real_escape_string($_POST['edit_content']); // Escape content
                    $active = isset($_POST['edit_active']) ? 1 : 0; // Check if active
                    $id = intval($_GET['ide']); // Get page ID

                    // SQL query to update the page
                    $query = "UPDATE page_list SET page_title='$title', page_content='$content', status='$active' WHERE id='$id'";
                    if ($GLOBALS['conn']->query($query) === TRUE) {
                        echo "Strona została zaktualizowana pomyślnie."; // Success message
                        header("Location: ?idp=-1"); // Redirect to page list
                        exit;
                    } else {
                        echo "Błąd podczas aktualizacji: " . $GLOBALS['conn']->error; // Error message
                    }
                } else {
                    // Fetch the page data for editing
                    $query = "SELECT * FROM page_list WHERE id='" . intval($_GET['ide']) . "'";
                    $result = $GLOBALS['conn']->query($query);
                    if ($result && $result->num_rows > 0) {
                        $row = $result->fetch_assoc(); // Get the page data
                        return '
                        <div class="edit-container">
                            <h3 class="edit-title">Edycja Strony</h3>
                            <form method="post" action="' . $_SERVER['REQUEST_URI'] . '">
                                <div class="form-group">
                                    <label for="edit_title">Tytuł strony</label>
                                    <input type="text" id="edit_title" name="edit_title" value="' . htmlspecialchars($row['page_title']) . '" required />
                                </div>
                                <div class="form-group">
                                    <label for="edit_content">Treść strony</label>
                                    <textarea id="edit_content" name="edit_content" rows="10" required>' . htmlspecialchars($row['page_content']) . '</textarea>
                                </div>
                                <div class="form-group">
                                    <label>
                                        <input type="checkbox" name="edit_active" ' . ($row['status'] ? 'checked' : '') . ' /> 
                                        Strona aktywna
                                    </label>
                                </div>
                                <div class="form-group">
                                    <input type="submit" class="submit-button" value="Zapisz zmiany" />
                                </div>
                            </form>
                        </div>'; // Return the edit form
                    } else {
                        return "Nie znaleziono strony do edycji."; // Page not found
                    }
                }
            } else {
                return "Nie podano ID strony do edycji."; // No page ID provided
            }
        } else {
            return $this->FormularzLogowania(); // Show login form if not logged in
        }
    }

    // Function to create a new subpage
    function StworzPodstrone() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') { // Check if form is submitted
            $new_title = $_POST['title']; // Get title
            $new_content = $_POST['content']; // Get content
            $new_status = isset($_POST['status']) ? 1 : 0; // Check if active

            // Prepare SQL query to insert new page
            $insert_sql = "INSERT INTO page_list (page_title, page_content, status) VALUES (?, ?, ?)";
            $insert_stmt = $this->conn->prepare($insert_sql); // Prepare statement
            $insert_stmt->bind_param('ssi', $new_title, $new_content, $new_status); // Bind parameters

            if ($insert_stmt->execute()) { // Execute the statement
                echo "<p>Nowa podstrona została dodana.</p>"; // Success message
            } else {
                echo "<p>Wystąpił błąd podczas dodawania nowej podstrony.</p>"; // Error message
            }
        }

        // Return the form for creating a new subpage
        return '<div class="form-container">
            <h2>Tworzenie nowej podstrony</h2>
            <form method="post" action="'.$_SERVER['REQUEST_URI'].'">
                <div class="form-group">
                    <label for="title">Tytuł Podstrony:</label>
                    <input type="text" id="title" name="title" required>
                </div>
                <div class="form-group">
                    <label for="content">Treść Podstrony:</label>
                    <textarea id="content" name="content" rows="4" cols="50" required></textarea>
                </div>
                <div class="form-group">
                    <label for="status">Aktywna:</label>
                    <input type="checkbox" id="status" name="status" value="1">
                </div>
                <input type="submit" value="Dodaj Podstronę">
            </form>
        </div>';
    }

    // Function to delete a page
    function DeletePage() {
        $status_login = $this->CheckLogin(); // Check if user is logged in
        if ($status_login == 1) { // If logged in
            if (isset($_GET['idd'])) { // Check if page ID is provided
                $id = intval($_GET['idd']); // Get page ID
                $query = "DELETE FROM page_list WHERE id='$id'"; // SQL query to delete page
                if ($GLOBALS['conn']->query($query) === TRUE) {
                    echo "Strona została usunięta pomyślnie."; // Success message
                    header("Location: ?idp=-1"); // Redirect to page list
                    exit;
                } else {
                    echo "Błąd podczas usuwania: " . $GLOBALS['conn']->error; // Error message
                }
            } else {
                echo "Nie podano ID strony do usunięcia."; // No page ID provided
            }
        } else {
            return $this->FormularzLogowania(); // Show login form if not logged in
        }
    }
}
?>