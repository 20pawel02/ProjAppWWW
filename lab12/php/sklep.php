<?php
class Sklep {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function PokazSklep() {
        $output = '<div class="sklep-container">
            <h1 style="color: red;">Test widoczności zmian</h1>';
        
        // Dodaj panel kategorii
        $output .= $this->PokazKategorie();
        
        // Dodaj listę produktów
        $output .= '<div class="produkty-grid">';
        $output .= $this->PokazProdukty();
        $output .= '</div>';
        
        $output .= '</div>';
        return $output;
    }

    private function PokazKategorie() {
        $query = "SELECT * FROM kategorie ORDER BY nazwa";
        $result = mysqli_query($this->conn, $query);
        
        $output = '<div class="sklep-kategorie">
            <h3>Kategorie</h3>
            <ul class="kategorie-lista">';
            
        while ($row = mysqli_fetch_assoc($result)) {
            $output .= '<li class="kategoria-item">
                <a href="?idp=-10&kategoria=' . $row['id'] . '">' . htmlspecialchars($row['nazwa']) . '</a>
            </li>';
        }
        
        $output .= '</ul></div>';
        return $output;
    }

    private function PokazProdukty() {
        $where = "";
        if (isset($_GET['kategoria'])) {
            $kategoria_id = intval($_GET['kategoria']);
            $where = "WHERE p.kategoria_id = $kategoria_id";
        }

        $query = "SELECT p.*, k.nazwa as kategoria_nazwa 
                 FROM produkty p 
                 LEFT JOIN kategorie k ON p.kategoria_id = k.id 
                 $where
                 ORDER BY p.data_utworzenia DESC";
        
        $result = mysqli_query($this->conn, $query);
        
        $output = '';
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $output .= $this->GenerujKarteProduktu($row);
            }
        } else {
            $output .= '<div class="brak-produktow">
                <p>Brak dostępnych produktów w tej kategorii.</p>
            </div>';
        }
        
        return $output;
    }

    private function GenerujKarteProduktu($produkt) {
        $cena_brutto = $produkt['cena_netto'] * (1 + $produkt['podatek_vat']/100);
        
        return '<div class="produkt-karta">
            <div class="produkt-zdjecie">
                ' . ($produkt['zdjecie_url'] ? 
                    '<img src="' . htmlspecialchars($produkt['zdjecie_url']) . '" alt="' . htmlspecialchars($produkt['tytul']) . '">' : 
                    '<div class="brak-zdjecia">Brak zdjęcia</div>') . '
            </div>
            <div class="produkt-info">
                <h3>' . htmlspecialchars($produkt['tytul']) . '</h3>
                <div class="produkt-opis">' . htmlspecialchars($produkt['opis']) . '</div>
                <div class="produkt-detale">
                    <div class="produkt-cena">
                        <div>Cena netto: ' . number_format($produkt['cena_netto'], 2) . ' zł</div>
                        <div>VAT: ' . $produkt['podatek_vat'] . '%</div>
                        <div>Cena brutto: ' . number_format($cena_brutto, 2) . ' zł</div>
                    </div>
                    <div class="produkt-status">' . $produkt['status_dostepnosci'] . '</div>
                </div>
                <form method="post" action="?idp=-12" class="form-koszyk">
                    <input type="hidden" name="action" value="dodaj">
                    <input type="hidden" name="produkt_id" value="' . $produkt['id'] . '">
                    <button type="submit" class="btn-koszyk" ' . 
                        ($produkt['status_dostepnosci'] == 'dostępny' ? '' : 'disabled') . '>
                        Dodaj do koszyka
                    </button>
                </form>
            </div>
        </div>';
    }
}
?> 