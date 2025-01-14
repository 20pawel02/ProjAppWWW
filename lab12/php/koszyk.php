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
        
        $output .= '
        <script>
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
                    // Odśwież stronę po usunięciu
                    window.location.reload();
                })
                .catch(error => {
                    console.error("Błąd:", error);
                    alert("Wystąpił błąd podczas usuwania produktu z koszyka");
                });
            }
        }
        </script>';
        
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
                <button onclick="usunZKoszyka(' . $produkt['id'] . ')" class="btn-usun" data-produkt-id="' . $produkt['id'] . '">Usuń</button>
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

    public function ZarzadzajKoszykiem() {
        $output = '<div class="koszyk-panel" style="background-color: rgba(255, 255, 255, 0.95); 
                                                    padding: 20px; 
                                                    border-radius: 8px; 
                                                    margin: 20px 0; 
                                                    box-shadow: 0 2px 4px rgba(0,0,0,0.1);">';
        
        $output .= '<h2>Zarządzanie Koszykami</h2>';
        
        // Wyświetl listę aktywnych koszyków
        $output .= $this->PokazAktywneKoszyki();
        
        $output .= '</div>';
        return $output;
    }

    private function PokazAktywneKoszyki() {
        $query = "SELECT k.*, u.username 
                 FROM koszyk k 
                 LEFT JOIN users u ON k.user_id = u.id 
                 WHERE k.status = 'aktywny' 
                 ORDER BY k.data_utworzenia DESC";
        
        $result = mysqli_query($this->conn, $query);
        
        $output = '<div class="table-responsive">
            <table border="1">
                <tr>
                    <th>ID Koszyka</th>
                    <th>Użytkownik</th>
                    <th>Data utworzenia</th>
                    <th>Suma</th>
                    <th>Status</th>
                    <th>Akcje</th>
                </tr>';
        
        while ($row = mysqli_fetch_assoc($result)) {
            $output .= '<tr>
                <td>' . $row['id'] . '</td>
                <td>' . htmlspecialchars($row['username'] ?? 'Gość') . '</td>
                <td>' . date('Y-m-d H:i', strtotime($row['data_utworzenia'])) . '</td>
                <td>' . number_format($row['suma'], 2) . ' zł</td>
                <td>' . $row['status'] . '</td>
                <td>
                    <a href="?idp=-10&action=szczegoly&id=' . $row['id'] . '" class="btn-szczegoly">Szczegóły</a>
                    <a href="?idp=-10&action=usun&id=' . $row['id'] . '" class="btn-usun" 
                       onclick="return confirm(\'Czy na pewno chcesz usunąć ten koszyk?\')">Usuń</a>
                </td>
            </tr>';
        }
        
        $output .= '</table></div>';
        return $output;
    }
}
?> 