<?php
class Kategorie {
    private $conn; // Database connection

    // Constructor to initialize the database connection
    public function __construct($conn) {
        $this->conn = $conn; // Assign the connection to the class property
        $this->StworzTabeleKategorii(); // Create categories table if it doesn't exist
    }

    // Function to create the categories table
    private function StworzTabeleKategorii() {
        $query = "CREATE TABLE IF NOT EXISTS kategorie (
            id INT AUTO_INCREMENT PRIMARY KEY,
            matka INT DEFAULT 0,
            nazwa VARCHAR(255) NOT NULL UNIQUE
        )"; // SQL query to create the table
        $this->conn->query($query); // Execute the query
    }

    // Function to add a new category
    public function DodajKategorie($nazwa, $matka = 0) {
        $nazwa = trim($nazwa); // Trim whitespace
        if (empty($nazwa)) return "Nazwa kategorii nie może być pusta."; // Check if name is empty

        $nazwa = $this->conn->real_escape_string($nazwa); // Escape special characters
        $matka = intval($matka); // Convert to integer

        // Check if parent category exists
        if ($matka > 0) {
            $checkQuery = "SELECT id FROM kategorie WHERE id = $matka";
            if ($this->conn->query($checkQuery)->num_rows === 0) {
                return "Kategoria nadrzędna nie istnieje."; // Parent category does not exist
            }
        }

        // SQL query to insert the new category
        $query = "INSERT INTO kategorie (nazwa, matka) VALUES ('$nazwa', $matka)";
        return $this->conn->query($query) ? true : "Błąd podczas dodawania kategorii: " . $this->conn->error; // Return success or error
    }

    // Function to delete a category
    public function UsunKategorie($id) {
        $id = intval($id); // Convert to integer
        $this->conn->query("DELETE FROM kategorie WHERE id = $id"); // SQL query to delete category
        return $this->conn->affected_rows > 0; // Return true if category was deleted
    }

    // Function to edit a category
    public function EdytujKategorie($id, $nazwa, $matka = null) {
        $id = intval($id); // Convert to integer
        $nazwa = trim($nazwa); // Trim whitespace
        if (empty($nazwa)) return "Nazwa kategorii nie może być pusta."; // Check if name is empty

        $nazwa = $this->conn->real_escape_string($nazwa); // Escape special characters
        if ($matka !== null) {
            $matka = intval($matka); // Convert to integer
            if ($matka > 0) {
                $checkQuery = "SELECT id FROM kategorie WHERE id = $matka";
                if ($this->conn->query($checkQuery)->num_rows === 0) {
                    return "Kategoria nadrzędna nie istnieje."; // Parent category does not exist
                }
            }
        }

        if ($id === $matka) return "Kategoria nie może być swoją własną nadrzędną."; // Category cannot be its own parent

        // SQL query to update the category
        $query = "UPDATE kategorie SET nazwa = '$nazwa'" . ($matka !== null ? ", matka = $matka" : "") . " WHERE id = $id";
        return $this->conn->query($query) ? true : "Błąd podczas edycji kategorii: " . $this->conn->error; // Return success or error
    }

    // Function to get category options for a dropdown
    private function PobierzOpcjeKategorii($selected = 0) {
        $query = "SELECT id, nazwa FROM kategorie ORDER BY nazwa"; // SQL query to get categories
        $result = $this->conn->query($query); // Execute query
        $options = ''; // Initialize options string

        while ($row = $result->fetch_assoc()) { // Fetch each row
            $options .= '<option value="' . $row['id'] . '"' . ($selected == $row['id'] ? ' selected' : '') . '>' . htmlspecialchars($row['nazwa']) . '</option>'; // Create option element
        }

        return $options; // Return options
    }

    // Function to manage categories
    public function ZarzadzajKategoriami() {
        $html = '<div class="kategorie-panel">'; // Start panel
        if ($_SERVER['REQUEST_METHOD'] == 'POST') { // Check if form is submitted
            if (isset($_POST['dodaj'])) {
                $result = $this->DodajKategorie($_POST['nazwa'], $_POST['matka']); // Add category
                $html .= $result === true ? '<div class="success">Kategoria została dodana.</div>' : '<div class="error">' . $result . '</div>'; // Success or error message
            } elseif (isset($_POST['usun'])) {
                $result = $this->UsunKategorie($_POST['id']); // Delete category
                $html .= $result ? '<div class="success">Kategoria została usunięta.</div>' : '<div class="error">Błąd podczas usuwania kategorii.</div>'; // Success or error message
            } elseif (isset($_POST['edytuj'])) {
                $result = $this->EdytujKategorie($_POST['id'], $_POST['nazwa'], $_POST['matka']); // Edit category
                $html .= $result === true ? '<div class="success">Kategoria została zaktualizowana.</div>' : '<div class="error">' . $result . '</div>'; // Success or error message
            }
        }

        // Form to add a new category
        $html .= '<form method="post">
            <input type="text" name="nazwa" placeholder="Nazwa kategorii" required>
            <select name="matka"><option value="0">Kategoria główna</option>' . $this->PobierzOpcjeKategorii() . '</select>
            <input type="submit" name="dodaj" value="Dodaj">
        </form>';

        $html .= '<div class="kategorie-lista">' . $this->WyswietlKategorieEdytowalne() . '</div>'; // Display editable categories
        $html .= '</div>'; // Close panel
        return $html; // Return the HTML
    }

    // Function to display editable categories
    private function WyswietlKategorieEdytowalne() {
        $html = '<div class="kategorie-drzewo">'; // Start tree structure
        $query = "SELECT * FROM kategorie WHERE matka = 0 ORDER BY nazwa"; // SQL query to get main categories
        $result = $this->conn->query($query); // Execute query

        while ($row = $result->fetch_assoc()) { // Fetch each row
            $html .= $this->WyswietlKategorieEdytowalneRekurencyjnie($row); // Recursive call to display subcategories
        }

        $html .= '</div>'; // Close tree structure
        return $html; // Return the HTML
    }

    // Recursive function to display editable categories
    private function WyswietlKategorieEdytowalneRekurencyjnie($kategoria) {
        $html = '<div class="kategoria">'; // Start category div
        $html .= '<div class="kategoria-panel">';
        $html .= '<span class="kategoria-nazwa">' . htmlspecialchars($kategoria['nazwa']) . '</span>'; // Display category name
        $html .= '<form method="post">
            <input type="hidden" name="id" value="' . $kategoria['id'] . '">
            <input type="text" name="nazwa" placeholder="Nowa nazwa" value="' . htmlspecialchars($kategoria['nazwa']) . '">
            <select name="matka"><option value="0">Kategoria główna</option>' . $this->PobierzOpcjeKategorii($kategoria['matka']) . '</select>
            <button type="submit" name="edytuj">Zapisz zmiany</button>
            <button type="submit" name="usun" onclick="return confirm(\'Czy na pewno chcesz usunąć tę kategorię?\')">Usuń</button>
        </form>'; // Form to edit or delete category
        $html .= '</div>';

        // SQL query to get subcategories
        $query = "SELECT * FROM kategorie WHERE matka = {$kategoria['id']} ORDER BY nazwa";
        $result = $this->conn->query($query);

        if ($result->num_rows > 0) { // If there are subcategories
            $html .= '<div class="podkategorie">'; // Start subcategories div
            while ($row = $result->fetch_assoc()) { // Fetch each subcategory
                $html .= $this->WyswietlKategorieEdytowalneRekurencyjnie($row); // Recursive call
            }
            $html .= '</div>'; // Close subcategories div
        }

        $html .= '</div>'; // Close category div
        return $html; // Return the HTML
    }
}
?>