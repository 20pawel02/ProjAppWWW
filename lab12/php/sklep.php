<?php
class Sklep {
    private $conn; // Database connection
    
    // Constructor to initialize the database connection
    public function __construct($conn) {
        $this->conn = $conn; // Assign the connection to the class property
    }

    // Function to display the shop
    public function PokazSklep() {
        $output = '<div class="sklep-container" style="background-color: rgba(255, 255, 255, 0.9); padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <h1 style="color: red;">Sklep internetowy</h1>';
        
        // Add category panel
        $output .= $this->PokazKategorie();
        
        // Add product list
        $output .= '<div class="produkty-grid">';
        $output .= $this->PokazProdukty(); // Display products
        $output .= '</div>';
        
        $output .= '</div>'; // Close shop container
        return $output; // Return the output
    }

    // Function to display categories
    private function PokazKategorie() {
        $query = "SELECT * FROM kategorie ORDER BY nazwa"; // SQL query to get categories
        $result = mysqli_query($this->conn, $query); // Execute query
        
        $output = '<div class="sklep-kategorie">
            <h3>Kategorie</h3>
            <ul class="kategorie-lista">'; // Start category list
            
        while ($row = mysqli_fetch_assoc($result)) { // Fetch each category
            $output .= '<li class="kategoria-item">
                <a href="?idp=-10&kategoria=' . $row['id'] . '">' . htmlspecialchars($row['nazwa']) . '</a>
            </li>'; // Create category link
        }
        
        $output .= '</ul></div>'; // Close category list
        return $output; // Return the output
    }

    // Function to display products
    private function PokazProdukty() {
        $where = ""; // Initialize where clause
        if (isset($_GET['kategoria'])) { // Check if category is selected
            $kategoria_id = intval($_GET['kategoria']); // Get category ID
            $where = "WHERE p.kategoria_id = $kategoria_id OR p.kategoria_id IN (SELECT id FROM kategorie WHERE matka = $kategoria_id)"; // Add to where clause for subcategories
        }

        // SQL query to get products
        $query = "SELECT p.*, k.nazwa as kategoria_nazwa 
                 FROM produkty p 
                 LEFT JOIN kategorie k ON p.kategoria_id = k.id 
                 $where
                 ORDER BY p.data_utworzenia DESC";
        
        $result = mysqli_query($this->conn, $query); // Execute query
        
        $output = '<div style="background-color: rgba(255, 255, 255, 0.95); padding: 20px; border-radius: 8px; margin: 20px 0; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <table class="table">
                <thead>
                    <tr>
                        <th>Zdjęcie</th>
                        <th>Nazwa</th>
                        <th>Opis</th>
                        <th>Kategoria</th>
                        <th>Cena netto</th>
                        <th>VAT</th>
                        <th>Cena brutto</th>
                        <th>Status</th>
                        <th>Akcje</th>
                    </tr>
                </thead>
                <tbody>';
        
        if (mysqli_num_rows($result) > 0) { // Check if there are products
            while ($row = mysqli_fetch_assoc($result)) { // Fetch each product
                $output .= $this->GenerujWierszProduktu($row); // Generate product row
            }
        } else {
            $output .= '<tr><td colspan="9" class="text-center">Brak dostępnych produktów w tej kategorii.</td></tr>'; // No products found
        }
        
        $output .= '</tbody></table></div>'; // Close table
        return $output; // Return the output
    }

    // Function to generate a product row
    private function GenerujWierszProduktu($produkt) {
        $cena_brutto = $produkt['cena_netto'] * (1 + $produkt['podatek_vat']/100); // Calculate gross price
        
        return '<tr>
            <td class="produkt-zdjecie">
                ' . ($produkt['zdjecie_url'] ? 
                    '<img src="' . htmlspecialchars($produkt['zdjecie_url']) . '" alt="' . htmlspecialchars($produkt['tytul']) . '">' : 
                    '<div class="brak-zdjecia">Brak zdjęcia</div>') . '
            </td>
            <td>' . htmlspecialchars($produkt['tytul']) . '</td>
            <td>' . htmlspecialchars($produkt['opis']) . '</td>
            <td>' . htmlspecialchars($produkt['kategoria_nazwa']) . '</td>
            <td>' . number_format($produkt['cena_netto'], 2) . ' zł</td>
            <td>' . $produkt['podatek_vat'] . '%</td>
            <td>' . number_format($cena_brutto, 2) . ' zł</td>
            <td>' . $produkt['status_dostepnosci'] . '</td>
            <td>
                <form method="post" action="?idp=-12" class="form-koszyk">
                    <input type="hidden" name="action" value="dodaj">
                    <input type="hidden" name="produkt_id" value="' . $produkt['id'] . '">
                    <button type="submit" class="btn-koszyk" ' . 
                        ($produkt['status_dostepnosci'] == 'dostępny' ? '' : 'disabled') . '>
                        Dodaj do koszyka
                    </button>
                </form>
            </td>
        </tr>'; // Return the HTML for the product row
    }
}
?>