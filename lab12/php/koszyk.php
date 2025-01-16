<?php
class Koszyk {
    private $conn; // Database connection
    
    // Constructor to initialize the database connection
    public function __construct($conn) {
        $this->conn = $conn; // Assign the connection to the class property
        if (!isset($_SESSION['koszyk'])) {
            $_SESSION['koszyk'] = []; // Initialize the shopping cart session variable
        }
    }

    // Function to add a product to the cart
    public function DodajDoKoszyka($produkt_id, $ilosc = 1) {
        $produkt_id = intval($produkt_id); // Convert product ID to integer
        if (!$this->SprawdzDostepnosc($produkt_id, $ilosc)) return false; // Check availability

        $_SESSION['koszyk'][$produkt_id] = ($_SESSION['koszyk'][$produkt_id] ?? 0) + $ilosc; // Add quantity to cart
        return true; // Return success
    }

    // Function to remove a product from the cart
    public function UsunZKoszyka($produkt_id) {
        unset($_SESSION['koszyk'][$produkt_id]); // Remove product from cart
        return true; // Return success
    }

    // Function to update the quantity of a product in the cart
    public function AktualizujIlosc($produkt_id, $ilosc) {
        if ($ilosc <= 0) return $this->UsunZKoszyka($produkt_id); // Remove if quantity is zero or less
        if ($this->SprawdzDostepnosc($produkt_id, $ilosc)) {
            $_SESSION['koszyk'][$produkt_id] = $ilosc; // Update quantity in cart
            return true; // Return success
        }
        return false; // Return failure
    }

    // Function to display the cart
    public function PokazKoszyk() {
        $output = '<div class="koszyk-container">
            <h2>Twój koszyk</h2>';
        
        if (empty($_SESSION['koszyk'])) { // Check if cart is empty
            $output .= '<div class="koszyk-pusty"><h2>Twój koszyk jest pusty</h2><a href="?idp=-10" class="btn-kontynuuj">Przejdź do sklepu</a></div>';
        } else {
            $output .= '<table class="table"><thead><tr><th>Zdjęcie</th><th>Nazwa</th><th>Cena netto</th><th>VAT</th><th>Cena brutto</th><th>Ilość</th><th>Suma</th><th>Akcje</th></tr></thead><tbody>';
            $suma = 0; // Initialize total sum

            foreach ($_SESSION['koszyk'] as $produkt_id => $ilosc) { // Iterate through cart items
                $query = "SELECT * FROM produkty WHERE id = $produkt_id"; // SQL query to get product details
                $result = mysqli_query($this->conn, $query);
                
                if ($produkt = mysqli_fetch_assoc($result)) { // Fetch product details
                    $cena_brutto = $produkt['cena_netto'] * (1 + $produkt['podatek_vat'] / 100); // Calculate gross price
                    $suma_produktu = $cena_brutto * $ilosc; // Calculate total for this product
                    $suma += $suma_produktu; // Add to total sum
                    $output .= $this->GenerujProduktKoszyka($produkt, $ilosc, $cena_brutto, $suma_produktu); // Generate product row
                }
            }
            $output .= '</tbody></table><div class="koszyk-podsumowanie"><div class="suma">Suma: ' . number_format($suma, 2) . ' zł</div><div class="koszyk-przyciski"><a href="?idp=-10" class="btn-kontynuuj">Kontynuuj zakupy</a><button class="btn-zamow">Złóż zamówienie</button></div></div>';
        }

        // JavaScript function to remove product from cart
        $output .= '<script>
            function usunZKoszyka(produktId) {
                if (confirm("Czy na pewno chcesz usunąć ten produkt z koszyka?")) {
                    fetch("?idp=-12", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/x-www-form-urlencoded",
                        },
                        body: "action=usun&produkt_id=" + produktId
                    })
                    .then(response => response.text())
                    .then(() => {
                        window.location.reload(); // Refresh the page after removing the product
                    })
                    .catch(error => {
                        console.error("Błąd:", error);
                        alert("Wystąpił błąd podczas usuwania produktu z koszyka");
                    });
                }
            }

            function aktualizujIlosc(produktId, nowaIlosc) {
                fetch("?idp=-12", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded",
                    },
                    body: "action=aktualizuj&produkt_id=" + produktId + "&ilosc=" + nowaIlosc
                })
                .then(response => response.text())
                .then(() => {
                    window.location.reload(); // Refresh the page after updating the quantity
                })
                .catch(error => {
                    console.error("Błąd:", error);
                    alert("Wystąpił błąd podczas aktualizacji ilości produktu");
                });
            }
        </script>';
        
        return $output; // Return the cart HTML
    }

    // Function to generate a product row in the cart
    private function GenerujProduktKoszyka($produkt, $ilosc, $cena_brutto, $suma_produktu) {
        return '<tr>
            <td class="produkt-zdjecie">' . ($produkt['zdjecie_url'] ? '<img src="' . htmlspecialchars($produkt['zdjecie_url']) . '" alt="' . htmlspecialchars($produkt['tytul']) . '">' : '<div class="brak-zdjecia">Brak zdjęcia</div>') . '</td>
            <td>' . htmlspecialchars($produkt['tytul']) . '</td>
            <td>' . number_format($produkt['cena_netto'], 2) . ' zł</td>
            <td>' . $produkt['podatek_vat'] . '%</td>
            <td>' . number_format($cena_brutto, 2) . ' zł</td>
            <td class="produkt-ilosc">
                <button onclick="aktualizujIlosc(' . $produkt['id'] . ', ' . ($ilosc - 1) . ')" class="btn-ilosc" ' . ($ilosc <= 1 ? 'disabled' : '') . '> - </button>
                <span>' . $ilosc . '</span>
                <button onclick="aktualizujIlosc(' . $produkt['id'] . ', ' . ($ilosc + 1) . ')" class="btn-ilosc"> + </button>
                <div class="max-ilosc">(max: ' . $produkt['ilosc_sztuk'] . ' szt.)</div>
            </td>
            <td>' . number_format($suma_produktu, 2) . ' zł</td>
            <td><button onclick="usunZKoszyka(' . $produkt['id'] . ')" class="btn-usun">Usuń</button></td>
        </tr>'; // Return the HTML for the product row
    }

    // Function to check product availability
    private function SprawdzDostepnosc($produkt_id, $ilosc) {
        $query = "SELECT ilosc_sztuk, status_dostepnosci FROM produkty WHERE id = " . intval($produkt_id); // SQL query to check availability
        $result = mysqli_query($this->conn, $query);
        
        if ($produkt = mysqli_fetch_assoc($result)) { // Fetch product details
            return $produkt['status_dostepnosci'] == 'dostępny' && $produkt['ilosc_sztuk'] >= $ilosc; // Check if available
        }
        return false; // Return false if product not found
    }
}
?>
