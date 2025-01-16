<?php
class Produkty {
    private $conn; // Database connection
    
    // Constructor to initialize the database connection
    public function __construct($conn) {
        $this->conn = $conn; // Assign the connection to the class property
    }

    // Function to manage products
    public function ZarzadzajProduktami() {
        $output = '<div class="produkty-panel" style="background-color: rgba(255, 255, 255, 0.95); padding: 20px; border-radius: 8px; margin: 20px 0; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">';

        // Check if a product is to be deleted
        if (isset($_GET['action']) && $_GET['action'] == 'usun' && isset($_GET['id'])) {
            $this->UsunProdukt($_GET['id']); // Delete product
            header('Location: ?idp=-9'); // Redirect to product management
            exit;
        }

        // Handle form submission for adding or editing products
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['action'])) {
                switch ($_POST['action']) {
                    case 'dodaj':
                        $this->DodajProdukt(); // Add product
                        break;
                    case 'edytuj':
                        $this->EdytujProdukt(); // Edit product
                        break;
                }
            }
        }

        $output .= $this->FormularzProduktu(); // Display product form
        $output .= $this->PokazProdukty(); // Display list of products
        $output .= '</div>'; // Close panel
        return $output; // Return the output
    }

    // Function to generate the product form
    private function FormularzProduktu() {
        $kategorie = $this->PobierzKategorie(); // Get categories for dropdown
        
        return '
        <h2>Dodaj/Edytuj Produkt</h2>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="dodaj">
            <input type="hidden" name="id" value="">
            <label>Tytuł:</label><br>
            <input type="text" name="tytul" required><br>
            <label>Opis:</label><br>
            <textarea name="opis" required></textarea><br>
            <label>Data wygaśnięcia:</label><br>
            <input type="date" name="data_wygasniecia"><br>
            <label>Cena netto:</label><br>
            <input type="number" step="0.01" name="cena_netto" required><br>
            <label>VAT (%):</label><br>
            <input type="number" step="0.01" name="podatek_vat" required><br>
            <label>Ilość sztuk:</label><br>
            <input type="number" name="ilosc_sztuk" required><br>
            <label>Status dostępności:</label><br>
            <select name="status_dostepnosci">
                <option value="dostępny">Dostępny</option>
                <option value="niedostępny">Niedostępny</option>
                <option value="oczekujący">Oczekujący</option>
            </select><br>
            <label>Kategoria:</label><br>
            <select name="kategoria_id">' . $this->GenerujOpcjeKategorii($kategorie) . '</select><br>
            <label>Gabaryt:</label><br>
            <input type="text" name="gabaryt"><br>
            <label>Zdjęcie produktu:</label><br>
            <input type="file" name="zdjecie" accept="image/*"><br>
            <input type="submit" value="Zapisz produkt">
        </form>'; // Return the HTML for the product form
    }

    // Function to generate category options for a dropdown
    private function GenerujOpcjeKategorii($kategorie, $selected_id = null, $parent_id = 0, $indent = '') {
        $output = '<option value="">-- Wybierz kategorię --</option>'; // Default option
        foreach ($kategorie as $kat) { // Iterate through categories
            if ($kat['matka'] == $parent_id) {
                $selected = ($selected_id == $kat['id']) ? 'selected' : ''; // Check if this category is selected
                $output .= '<option value="' . $kat['id'] . '" ' . $selected . '>' . $indent . htmlspecialchars($kat['nazwa']) . '</option>'; // Create option element
                $output .= $this->GenerujOpcjeKategorii($kategorie, $selected_id, $kat['id'], $indent . '- '); // Recursive call for subcategories
            }
        }
        return $output; // Return options
    }

    // Function to fetch categories from the database
    private function PobierzKategorie() {
        $query = "SELECT * FROM kategorie ORDER BY matka, id"; // SQL query to get categories
        $result = mysqli_query($this->conn, $query); // Execute query
        return mysqli_fetch_all($result, MYSQLI_ASSOC); // Return all categories as an associative array
    }

    // Function to add a new product
    private function DodajProdukt() {
        if (!isset($_POST['tytul'])) return; // Check if title is set

        try {
            $zdjecie_url = ''; // Initialize image URL
            if (isset($_FILES['zdjecie']) && $_FILES['zdjecie']['error'] == 0) { // Check if file is uploaded
                $dozwolone_typy = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg']; // Allowed file types
                $max_rozmiar = 5 * 1024 * 1024; // 5MB max size

                // Check file type and size
                if (!in_array($_FILES['zdjecie']['type'], $dozwolone_typy)) {
                    throw new Exception("Niedozwolony typ pliku."); // Error for invalid file type
                }

                if ($_FILES['zdjecie']['size'] > $max_rozmiar) {
                    throw new Exception("Plik jest zbyt duży."); // Error for file too large
                }

                $katalog_docelowy = 'images/produkty/'; // Directory for images
                if (!file_exists($katalog_docelowy)) {
                    mkdir($katalog_docelowy, 0777, true); // Create directory if it doesn't exist
                }

                $nazwa_pliku = time() . '_' . basename($_FILES['zdjecie']['name']); // Create unique file name
                $sciezka_docelowa = $katalog_docelowy . $nazwa_pliku; // Full path for the file

                // Move uploaded file to the target directory
                if (move_uploaded_file($_FILES['zdjecie']['tmp_name'], $sciezka_docelowa)) {
                    $zdjecie_url = $sciezka_docelowa; // Set image URL
                } else {
                    throw new Exception("Wystąpił błąd podczas przesyłania pliku."); // Error during upload
                }
            }

            // Prepare SQL query to insert new product
            $tytul = mysqli_real_escape_string($this->conn, $_POST['tytul']); // Escape title
            $opis = mysqli_real_escape_string($this->conn, $_POST['opis']); // Escape description
            $data_wygasniecia = !empty($_POST['data_wygasniecia']) ? 
                mysqli_real_escape_string($this->conn, $_POST['data_wygasniecia']) : NULL; // Escape expiration date
            $cena_netto = floatval($_POST['cena_netto']); // Convert to float
            $podatek_vat = floatval($_POST['podatek_vat']); // Convert to float
            $ilosc_sztuk = intval($_POST['ilosc_sztuk']); // Convert to integer
            $status_dostepnosci = mysqli_real_escape_string($this->conn, $_POST['status_dostepnosci']); // Escape status
            $kategoria_id = !empty($_POST['kategoria_id']) ? intval($_POST['kategoria_id']) : NULL; // Convert to integer
            $gabaryt = mysqli_real_escape_string($this->conn, $_POST['gabaryt']); // Escape size

            // SQL query to insert the new product
            $query = "INSERT INTO produkty (
                tytul, 
                opis, 
                data_wygasniecia, 
                cena_netto, 
                podatek_vat, 
                ilosc_sztuk, 
                status_dostepnosci, 
                kategoria_id, 
                gabaryt, 
                zdjecie_url
            ) VALUES (
                '$tytul', 
                '$opis', 
                '$data_wygasniecia', 
                $cena_netto, 
                $podatek_vat, 
                $ilosc_sztuk, 
                '$status_dostepnosci', 
                $kategoria_id, 
                '$gabaryt', 
                '$zdjecie_url'
            )";

            if (!mysqli_query($this->conn, $query)) {
                throw new Exception("Błąd podczas dodawania produktu: " . mysqli_error($this->conn)); // Error during insertion
            }
            
            return true; // Return success

        } catch (Exception $e) {
            echo '<div class="error">Wystąpił błąd: ' . $e->getMessage() . '</div>'; // Display error message
            return false; // Return failure
        }
    }

    // Function to delete a product
    public function UsunProdukt($id) {
        $id = intval($id); // Convert to integer
        $query = "DELETE FROM produkty WHERE id = $id"; // SQL query to delete product
        return $this->conn->query($query); // Execute query and return result
    }

    // Function to edit a product
    public function EdytujProdukt($id) {
        $id = intval($_GET['id']); // Get product ID
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Check if form is submitted
            $tytul = $this->conn->real_escape_string($_POST['tytul']); // Escape title
            $opis = $this->conn->real_escape_string($_POST['opis']); // Escape description
            $cena_netto = floatval($_POST['cena_netto']); // Convert to float
            $podatek_vat = floatval($_POST['podatek_vat']); // Convert to float
            $ilosc_sztuk = intval($_POST['ilosc_sztuk']); // Convert to integer
            $status = $this->conn->real_escape_string($_POST['status_dostepnosci']); // Escape status
            $kategoria_id = intval($_POST['kategoria_id']); // Convert to integer
            
            // SQL query to update the product
            $query = "UPDATE produkty SET 
                     tytul = '$tytul',
                     opis = '$opis',
                     cena_netto = $cena_netto,
                     podatek_vat = $podatek_vat,
                     ilosc_sztuk = $ilosc_sztuk,
                     status_dostepnosci = '$status',
                     kategoria_id = $kategoria_id,
                     data_modyfikacji = NOW()
                     WHERE id = $id";

            if ($this->conn->query($query)) {
                header("Location: ?idp=-9"); // Redirect to product management
                exit;
            }
        }
        
        // Fetch product data for editing
        $query = "SELECT * FROM produkty WHERE id = $id"; // SQL query to get product details
        $result = $this->conn->query($query);
        $produkt = $result->fetch_assoc(); // Fetch product details
        
        return $this->FormularzEdycjiProduktu($produkt); // Return the edit form
    }

    // Function to generate the product edit form
    private function FormularzEdycjiProduktu($produkt) {
        $kategorie = $this->PobierzKategorie(); // Get all categories for dropdown
        
        $output = '<div class="edit-product-form">
            <h2>Edycja produktu</h2>
            <form method="post">
                <input type="hidden" name="action" value="edytuj">
                <input type="hidden" name="id" value="' . $produkt['id'] . '">
                <label>Tytuł:</label><br>
                <input type="text" name="tytul" value="' . htmlspecialchars($produkt['tytul']) . '" required><br>
                <label>Opis:</label><br>
                <textarea name="opis" required>' . htmlspecialchars($produkt['opis']) . '</textarea><br>
                <label>Cena netto:</label><br>
                <input type="number" step="0.01" name="cena_netto" value="' . $produkt['cena_netto'] . '" required><br>
                <label>VAT (%):</label><br>
                <input type="number" step="0.01" name="podatek_vat" value="' . $produkt['podatek_vat'] . '" required><br>
                <label>Ilość sztuk:</label><br>
                <input type="number" name="ilosc_sztuk" value="' . $produkt['ilosc_sztuk'] . '" required><br>
                <label>Status dostępności:</label><br>
                <select name="status_dostepnosci">
                    <option value="dostępny" ' . ($produkt['status_dostepnosci'] == 'dostępny' ? 'selected' : '') . '>Dostępny</option>
                    <option value="niedostępny" ' . ($produkt['status_dostepnosci'] == 'niedostępny' ? 'selected' : '') . '>Niedostępny</option>
                    <option value="oczekujący" ' . ($produkt['status_dostepnosci'] == 'oczekujący' ? 'selected' : '') . '>Oczekujący</option>
                </select><br>
                <label>Kategoria:</label><br>
                <select name="kategoria_id" required>' . $this->GenerujOpcjeKategorii($kategorie, $produkt['kategoria_id']) . '</select><br>
                <input type="submit" value="Zapisz zmiany">
            </form>
        </div>'; // Return the HTML for the edit form
        
        return $output; // Return the output
    }

    // Function to display the list of products
    private function PokazProdukty() {
        $query = "SELECT p.*, k.nazwa as kategoria_nazwa 
                 FROM produkty p 
                 LEFT JOIN kategorie k ON p.kategoria_id = k.id 
                 ORDER BY p.data_utworzenia DESC"; // SQL query to get products
        $result = mysqli_query($this->conn, $query); // Execute query

        $output = '<h2>Lista produktów</h2>
        <div class="table-responsive">
        <table border="1">
            <tr>
                <th>ID</th>
                <th>Tytuł</th>
                <th>Data modyfikacji</th>
                <th>Data wygaśnięcia</th>
                <th>Cena netto</th>
                <th>VAT</th>
                <th>Ilość sztuk</th>
                <th>Status</th>
                <th>Kategoria</th>
                <th>Gabaryt</th>
                <th>Zdjęcie</th>
                <th>Akcje</th>
            </tr>';

        while ($row = mysqli_fetch_assoc($result)) { // Fetch each product
            $output .= '<tr>
                <td>' . $row['id'] . '</td>
                <td>' . htmlspecialchars($row['tytul']) . '</td>
                <td>' . ($row['data_modyfikacji'] ? date('Y-m-d H:i', strtotime($row['data_modyfikacji'])) : '-') . '</td>
                <td>' . ($row['data_wygasniecia'] ? date('Y-m-d', strtotime($row['data_wygasniecia'])) : '-') . '</td>
                <td>' . number_format($row['cena_netto'], 2) . ' zł</td>
                <td>' . $row['podatek_vat'] . '%</td>
                <td>' . $row['ilosc_sztuk'] . '</td>
                <td>' . $row['status_dostepnosci'] . '</td>
                <td>' . htmlspecialchars($row['kategoria_nazwa']) . '</td>
                <td>' . htmlspecialchars($row['gabaryt']) . '</td>
                <td>' . ($row['zdjecie_url'] ? '<img src="' . htmlspecialchars($row['zdjecie_url']) . '" alt="Produkt" style="max-width: 50px;">' : '-') . '</td>
                <td>
                    <a href="?idp=-11&id=' . $row['id'] . '" class="btn-edytuj">Edytuj</a>
                    <a href="?idp=-9&action=usun&id=' . $row['id'] . '" class="btn-usun" 
                       onclick="return confirm(\'Czy na pewno chcesz usunąć ten produkt?\')">Usuń</a>
                </td>
            </tr>'; // Generate product row
        }

        $output .= '</table></div>'; // Close table
        return $output; // Return the output
    }
}
?>