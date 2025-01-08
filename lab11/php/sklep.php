<?php
class Sklep {
    private $conn;
    private $kategorie;

    public function __construct($conn) {
        $this->conn = $conn;
        $this->kategorie = new Kategorie($conn);
    }

    public function PokazSklep() {
        $output = '<div class="container">';
        
        // Sekcja kategorii
        $output .= $this->PokazKategorie();
        
        // Sekcja produktów
        $output .= $this->PokazProdukty();
        
        $output .= '</div>';
        
        return $output;
    }

    private function PokazKategorie() {
        $query = "SELECT * FROM kategorie WHERE matka = 0 ORDER BY nazwa";
        $result = mysqli_query($this->conn, $query);
        
        $output = '<div class="sklep-kategorie">
            <h2>Kategorie produktów</h2>
            <ul class="kategorie-lista">';
        
        while ($row = mysqli_fetch_assoc($result)) {
            $output .= $this->GenerujDrzewoKategorii($row);
        }
        
        $output .= '</ul></div>';
        
        return $output;
    }

    private function GenerujDrzewoKategorii($kategoria) {
        $output = '<li class="kategoria-item">';
        $output .= '<a href="?idp=-10&kat=' . $kategoria['id'] . '">' . 
                   htmlspecialchars($kategoria['nazwa']) . '</a>';
        
        // Pobierz podkategorie
        $query = "SELECT * FROM kategorie WHERE matka = " . $kategoria['id'] . " ORDER BY nazwa";
        $result = mysqli_query($this->conn, $query);
        
        if (mysqli_num_rows($result) > 0) {
            $output .= '<ul class="podkategorie-lista">';
            while ($row = mysqli_fetch_assoc($result)) {
                $output .= $this->GenerujDrzewoKategorii($row);
            }
            $output .= '</ul>';
        }
        
        $output .= '</li>';
        return $output;
    }

    private function PokazProdukty() {
        $warunek = "";
        if (isset($_GET['kat'])) {
            $kat_id = intval($_GET['kat']);
            $warunek = "WHERE p.kategoria_id = $kat_id";
        }

        $query = "SELECT p.*, k.nazwa as kategoria_nazwa 
                 FROM produkty p 
                 LEFT JOIN kategorie k ON p.kategoria_id = k.id 
                 $warunek
                 ORDER BY p.data_utworzenia DESC";
        
        $result = mysqli_query($this->conn, $query);
        
        $output = '<div class="sklep-produkty">
            <h2>' . (isset($_GET['kat']) ? 'Produkty w kategorii' : 'Wszystkie produkty') . '</h2>
            <div class="produkty-grid">';
        
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $output .= $this->GenerujProdukt($row);
            }
        } else {
            $output .= '<p class="brak-produktow">Brak produktów w tej kategorii.</p>';
        }
        
        $output .= '</div></div>';
        
        return $output;
    }

    private function GenerujProdukt($produkt) {
        $cena_brutto = $produkt['cena_netto'] * (1 + $produkt['podatek_vat']/100);
        $status_class = $this->GetStatusClass($produkt);
        
        return '<div class="produkt-karta">
            <div class="produkt-zdjecie">
                <img src="' . htmlspecialchars($produkt['zdjecie_url']) . '" 
                     alt="' . htmlspecialchars($produkt['tytul']) . '"
                     onerror="this.src=\'images/placeholder.jpg\'">
            </div>
            <div class="produkt-info">
                <h3>' . htmlspecialchars($produkt['tytul']) . '</h3>
                <p class="produkt-opis">' . htmlspecialchars($produkt['opis']) . '</p>
                <div class="produkt-detale">
                    <span class="cena">
                        ' . number_format($cena_brutto, 2) . ' zł
                        <small>brutto</small>
                    </span>
                    <span class="status ' . $status_class . '">
                        ' . htmlspecialchars($produkt['status_dostepnosci']) . '
                    </span>
                </div>
                <div class="produkt-akcje">
                    <button class="btn-koszyk" onclick="dodajDoKoszyka(' . $produkt['id'] . ')">
                        Dodaj do koszyka
                    </button>
                </div>
            </div>
        </div>';
    }

    private function GetStatusClass($produkt) {
        if ($produkt['ilosc_sztuk'] <= 0) {
            return 'status-niedostepny';
        }
        
        if ($produkt['status_dostepnosci'] === 'niedostępny') {
            return 'status-niedostepny';
        }
        
        if ($produkt['status_dostepnosci'] === 'oczekujący') {
            return 'status-oczekujacy';
        }
        
        return 'status-dostepny';
    }
}
?> 