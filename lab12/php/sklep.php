<?php
class Sklep {
    private $conn; // Zmienna do przechowywania połączenia z bazą danych
    
    // Konstruktor do inicjalizacji połączenia z bazą danych
    public function __construct($conn) {
        $this->conn = $conn; // Przypisanie połączenia do właściwości klasy
    }

    // Funkcja do wyświetlania sklepu
    public function PokazSklep() {
        $output = '<div class="sklep-container" style="background-color: rgba(255, 255, 255, 0.9); padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <h1 style="color: red;">Sklep internetowy</h1>';
        
        // Dodaj panel kategorii
        $output .= $this->PokazKategorie();
        
        // Dodaj listę produktów
        $output .= '<div class="produkty-grid">';
        $output .= $this->PokazProdukty(); // Wywołanie funkcji do wyświetlenia produktów
        $output .= '</div>';
        
        $output .= '</div>';
        return $output; // Zwrócenie HTML sklepu
    }

    // Funkcja do wyświetlania kategorii
    private function PokazKategorie() {
        $query = "SELECT * FROM kategorie ORDER BY nazwa"; // Zapytanie SQL do pobrania kategorii
        $result = mysqli_query($this->conn, $query); // Wykonanie zapytania
        
        $output = '<div class="sklep-kategorie">
            <h3>Kategorie</h3>
            <ul class="kategorie-lista">';
            
        while ($row = mysqli_fetch_assoc($result)) { // Iteracja przez wyniki zapytania
            $output .= '<li class="kategoria-item">
                <a href="?idp=-10&kategoria=' . $row['id'] . '">' . htmlspecialchars($row['nazwa']) . '</a>
            </li>'; // Dodanie kategorii do listy
        }
        
        $output .= '</ul></div>';
        return $output; // Zwrócenie HTML kategorii
    }

    // Funkcja do wyświetlania produktów
    private function PokazProdukty() {
        $where = ""; // Zmienna do przechowywania warunków zapytania
        if (isset($_GET['kategoria'])) { // Sprawdzenie, czy kategoria jest ustawiona
            $kategoria_id = intval($_GET['kategoria']); // Konwersja ID kategorii na liczbę całkowitą
            $where = "WHERE p.kategoria_id = $kategoria_id"; // Ustawienie warunku w zapytaniu
        }

        // Zapytanie SQL do pobrania produktów
        $query = "SELECT p.*, k.nazwa as kategoria_nazwa 
                 FROM produkty p 
                 LEFT JOIN kategorie k ON p.kategoria_id = k.id 
                 $where
                 ORDER BY p.data_utworzenia DESC";
        
        $result = mysqli_query($this->conn, $query); // Wykonanie zapytania
        
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
        
        if (mysqli_num_rows($result) > 0) { // Sprawdzenie, czy są dostępne produkty
            while ($row = mysqli_fetch_assoc($result)) { // Iteracja przez wyniki zapytania
                $output .= $this->GenerujWierszProduktu($row); // Generowanie wiersza produktu
            }
        } else {
            $output .= '<tr><td colspan="9" class="text-center">Brak dostępnych produktów w tej kategorii.</td></tr>'; // Komunikat, gdy brak produktów
        }
        
        $output .= '</tbody></table></div>';
        return $output; // Zwrócenie HTML produktów
    }

    // Funkcja do generowania wiersza produktu
    private function GenerujWierszProduktu($produkt) {
        $cena_brutto = $produkt['cena_netto'] * (1 + $produkt['podatek_vat']/100); // Obliczenie ceny brutto
        
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
                <form method="post" action="?idp=-12" class="form-koszyk" onsubmit="return dodajDoKoszyka(event, ' . $produkt['id'] . ');">
                    <input type="hidden" name="action" value="dodaj">
                    <input type="hidden" name="produkt_id" value="' . $produkt['id'] . '">
                    <button type="submit" class="btn-koszyk" ' . 
                        ($produkt['status_dostepnosci'] == 'dostępny' ? '' : 'disabled') . '>
                        Dodaj do koszyka
                    </button>
                </form>
            </td>
        </tr>'; // Zwrócenie HTML dla wiersza produktu
    }
}
?>