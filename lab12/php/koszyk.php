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
        $output = '<div class="koszyk-container" style="border: 3px solid red;">
            <h1 style="color: red;">Test widoczności zmian w koszyku</h1>';
        
        // Dodaj skrypt JavaScript dla obsługi przycisków
        $output .= '
        <script>
            function aktualizujIlosc(produkt_id, ilosc) {
                const form = document.createElement("form");
                form.method = "POST";
                form.action = "?idp=-12";
                
                const actionInput = document.createElement("input");
                actionInput.type = "hidden";
                actionInput.name = "action";
                actionInput.value = "aktualizuj";
                
                const produktInput = document.createElement("input");
                produktInput.type = "hidden";
                produktInput.name = "produkt_id";
                produktInput.value = produkt_id;
                
                const iloscInput = document.createElement("input");
                iloscInput.type = "hidden";
                iloscInput.name = "ilosc";
                iloscInput.value = ilosc;
                
                form.appendChild(actionInput);
                form.appendChild(produktInput);
                form.appendChild(iloscInput);
                
                document.body.appendChild(form);
                form.submit();
            }
            
            function usunZKoszyka(produkt_id) {
                if (confirm("Czy na pewno chcesz usunąć ten produkt z koszyka?")) {
                    const form = document.createElement("form");
                    form.method = "POST";
                    form.action = "?idp=-12";
                    
                    const actionInput = document.createElement("input");
                    actionInput.type = "hidden";
                    actionInput.name = "action";
                    actionInput.value = "usun";
                    
                    const produktInput = document.createElement("input");
                    produktInput.type = "hidden";
                    produktInput.name = "produkt_id";
                    produktInput.value = produkt_id;
                    
                    form.appendChild(actionInput);
                    form.appendChild(produktInput);
                    
                    document.body.appendChild(form);
                    form.submit();
                }
            }
        </script>';

        if (empty($_SESSION['koszyk'])) {
            $output .= '<div class="koszyk-pusty">
                <h2>Twój koszyk jest pusty</h2>
                <a href="?idp=-10" class="btn-kontynuuj">Przejdź do sklepu</a>
            </div>';
        } else {
            $output .= '<h2>Twój koszyk</h2><div class="koszyk-produkty">';
            
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
            
            $output .= '</div>
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
        return '<div class="koszyk-produkt">
            <div class="produkt-zdjecie">
                ' . ($produkt['zdjecie_url'] ? 
                    '<img src="' . htmlspecialchars($produkt['zdjecie_url']) . '" alt="' . htmlspecialchars($produkt['tytul']) . '">' : 
                    '<div class="brak-zdjecia">Brak zdjęcia</div>') . '
            </div>
            <div class="produkt-info">
                <h3>' . htmlspecialchars($produkt['tytul']) . '</h3>
                <div class="produkt-cena">
                    <div>Cena netto: ' . number_format($produkt['cena_netto'], 2) . ' zł</div>
                    <div>VAT: ' . $produkt['podatek_vat'] . '%</div>
                    <div>Cena brutto: ' . number_format($cena_brutto, 2) . ' zł</div>
                </div>
            </div>
            <div class="produkt-ilosc">
                <label>Ilość:</label>
                <button onclick="aktualizujIlosc(' . $produkt['id'] . ', ' . ($ilosc-1) . ')" class="btn-ilosc">-</button>
                <span>' . $ilosc . '</span>
                <button onclick="aktualizujIlosc(' . $produkt['id'] . ', ' . ($ilosc+1) . ')" class="btn-ilosc">+</button>
                <div class="max-ilosc">(max: ' . $produkt['ilosc_sztuk'] . ' szt.)</div>
            </div>
            <div class="produkt-suma">
                <div>Suma:</div>
                ' . number_format($suma_produktu, 2) . ' zł
            </div>
            <button onclick="usunZKoszyka(' . $produkt['id'] . ')" class="btn-usun">Usuń</button>
        </div>';
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