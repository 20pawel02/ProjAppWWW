<?php
class Koszyk {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
        if (!isset($_SESSION['koszyk'])) {
            $_SESSION['koszyk'] = array();
        }
    }

    public function DodajDoKoszyka($produkt_id, $ilosc = 1) {
        $produkt_id = intval($produkt_id);
        
        if (!$this->SprawdzDostepnosc($produkt_id, $ilosc)) {
            return false;
        }
        
        // Aktualizuj ilość w koszyku
        if (isset($_SESSION['koszyk'][$produkt_id])) {
            $nowa_ilosc = $_SESSION['koszyk'][$produkt_id] + $ilosc;
            if (!$this->SprawdzDostepnosc($produkt_id, $nowa_ilosc)) {
                return false;
            }
            $_SESSION['koszyk'][$produkt_id] = $nowa_ilosc;
        } else {
            $_SESSION['koszyk'][$produkt_id] = $ilosc;
        }
        return true;
    }

    public function UsunZKoszyka($produkt_id) {
        if (isset($_SESSION['koszyk'][$produkt_id])) {
            unset($_SESSION['koszyk'][$produkt_id]);
            return true;
        }
        return false;
    }

    public function AktualizujIlosc($produkt_id, $ilosc) {
        if ($ilosc <= 0) {
            return $this->UsunZKoszyka($produkt_id);
        }
        
        $query = "SELECT ilosc_sztuk FROM produkty WHERE id = $produkt_id";
        $result = mysqli_query($this->conn, $query);
        $produkt = mysqli_fetch_assoc($result);
        
        if ($produkt && $produkt['ilosc_sztuk'] >= $ilosc) {
            $_SESSION['koszyk'][$produkt_id] = $ilosc;
            return true;
        }
        return false;
    }

    public function PokazKoszyk() {
        $output = '<div class="koszyk-container" style="background-color: rgba(255, 255, 255, 0.95); padding: 20px; border-radius: 8px; margin: 20px 0; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <h2>Twój koszyk</h2>';
        
        if (empty($_SESSION['koszyk'])) {
            $output .= '<div class="koszyk-pusty">
                <h2>Twój koszyk jest pusty</h2>
                <a href="?idp=-10" class="btn-kontynuuj">Przejdź do sklepu</a>
            </div>';
        } else {
            $output .= '<table class="table">
                <thead>
                    <tr>
                        <th>Zdjęcie</th>
                        <th>Nazwa</th>
                        <th>Cena netto</th>
                        <th>VAT</th>
                        <th>Cena brutto</th>
                        <th>Ilość</th>
                        <th>Suma</th>
                        <th>Akcje</th>
                    </tr>
                </thead>
                <tbody>';
            
            $suma = 0;
            foreach ($_SESSION['koszyk'] as $produkt_id => $ilosc) {
                $query = "SELECT * FROM produkty WHERE id = $produkt_id";
                $result = mysqli_query($this->conn, $query);
                
                if ($produkt = mysqli_fetch_assoc($result)) {
                    $cena_brutto = $produkt['cena_netto'] * (1 + $produkt['podatek_vat']/100);
                    $suma_produktu = $cena_brutto * $ilosc;
                    $suma += $suma_produktu;
                    
                    $output .= $this->GenerujProduktKoszyka($produkt, $ilosc, $cena_brutto, $suma_produktu);
                }
            }
            
            $output .= '</tbody></table>
            <div class="koszyk-podsumowanie">
                <div class="suma">Suma: ' . number_format($suma, 2) . ' zł</div>
                <div class="koszyk-przyciski">
                    <a href="?idp=-10" class="btn-kontynuuj">Kontynuuj zakupy</a>
                    <button class="btn-zamow">Złóż zamówienie</button>
                </div>
            </div>';
        }
        
        $output .= '</div>';
        return $output;
    }

    private function GenerujProduktKoszyka($produkt, $ilosc, $cena_brutto, $suma_produktu) {
        return '<tr>
            <td class="produkt-zdjecie">
                ' . ($produkt['zdjecie_url'] ? 
                    '<img src="' . htmlspecialchars($produkt['zdjecie_url']) . '" alt="' . htmlspecialchars($produkt['tytul']) . '">' : 
                    '<div class="brak-zdjecia">Brak zdjęcia</div>') . '
            </td>
            <td>' . htmlspecialchars($produkt['tytul']) . '</td>
            <td>' . number_format($produkt['cena_netto'], 2) . ' zł</td>
            <td>' . $produkt['podatek_vat'] . '%</td>
            <td>' . number_format($cena_brutto, 2) . ' zł</td>
            <td class="produkt-ilosc">
                <button onclick="aktualizujIlosc(' . $produkt['id'] . ', ' . ($ilosc-1) . ')" class="btn-ilosc">-</button>
                <span>' . $ilosc . '</span>
                <button onclick="aktualizujIlosc(' . $produkt['id'] . ', ' . ($ilosc+1) . ')" class="btn-ilosc">+</button>
                <div class="max-ilosc">(max: ' . $produkt['ilosc_sztuk'] . ' szt.)</div>
            </td>
            <td>' . number_format($suma_produktu, 2) . ' zł</td>
            <td>
                <button onclick="usunZKoszyka(' . $produkt['id'] . ')" class="btn-usun">Usuń</button>
            </td>
        </tr>';
    }

    private function SprawdzDostepnosc($produkt_id, $ilosc) {
        $query = "SELECT ilosc_sztuk, status_dostepnosci FROM produkty WHERE id = " . intval($produkt_id);
        $result = mysqli_query($this->conn, $query);
        
        if ($produkt = mysqli_fetch_assoc($result)) {
            return $produkt['status_dostepnosci'] == 'dostępny' && $produkt['ilosc_sztuk'] >= $ilosc;
        }
        return false;
    }
}
?> 