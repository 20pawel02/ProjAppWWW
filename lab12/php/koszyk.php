<?php
class Koszyk {
    private $conn; // Zmienna do przechowywania połączenia z bazą danych
    
    // Konstruktor do inicjalizacji połączenia z bazą danych
    public function __construct($conn) {
        $this->conn = $conn; // Przypisanie połączenia do właściwości klasy
        if (!isset($_SESSION['koszyk'])) {
            $_SESSION['koszyk'] = []; // Inicjalizacja zmiennej sesyjnej koszyka
        }
    }

    // Funkcja do dodawania produktu do koszyka
    public function DodajDoKoszyka($produkt_id, $ilosc = 1) {
        $produkt_id = intval($produkt_id); // Konwersja ID produktu na liczbę całkowitą
        if (!$this->SprawdzDostepnosc($produkt_id, $ilosc)) return false; // Sprawdzenie dostępności

        $_SESSION['koszyk'][$produkt_id] = ($_SESSION['koszyk'][$produkt_id] ?? 0) + $ilosc; // Dodanie ilości do koszyka
        return true; // Zwrócenie sukcesu
    }

    // Funkcja do usuwania produktu z koszyka
    public function UsunZKoszyka($produkt_id) {
        unset($_SESSION['koszyk'][$produkt_id]); // Usunięcie produktu z koszyka
        return true; // Zwrócenie sukcesu
    }

    // Funkcja do aktualizacji ilości produktu w koszyku
    public function AktualizujIlosc($produkt_id, $ilosc) {
        if ($ilosc <= 0) return $this->UsunZKoszyka($produkt_id); // Usunięcie, jeśli ilość jest zerowa lub mniejsza
        if ($this->SprawdzDostepnosc($produkt_id, $ilosc)) {
            $_SESSION['koszyk'][$produkt_id] = $ilosc; // Aktualizacja ilości w koszyku
            return true; // Zwrócenie sukcesu
        }
        return false; // Zwrócenie błędu
    }

    // Funkcja do wyświetlania koszyka
    public function PokazKoszyk() {
        $output = '<div class="koszyk-container">
            <h2>Twój koszyk</h2>';
        
        if (empty($_SESSION['koszyk'])) { // Sprawdzenie, czy koszyk jest pusty
            $output .= '<div class="koszyk-pusty"><h2>Twój koszyk jest pusty</h2><a href="?idp=-10" class="btn-kontynuuj">Przejdź do sklepu</a></div>';
        } else {
            $output .= '<table class="table"><thead><tr><th>Zdjęcie</th><th>Nazwa</th><th>Cena netto</th><th>VAT</th><th>Cena brutto</th><th>Ilość</th><th>Suma</th><th>Akcje</th></tr></thead><tbody>';
            $suma = 0; // Inicjalizacja całkowitej sumy

            foreach ($_SESSION['koszyk'] as $produkt_id => $ilosc) { // Iteracja przez przedmioty w koszyku
                $query = "SELECT * FROM produkty WHERE id = $produkt_id"; // Zapytanie SQL do pobrania szczegółów produktu
                $result = mysqli_query($this->conn, $query);
                
                if ($produkt = mysqli_fetch_assoc($result)) { // Pobranie szczegółów produktu
                    $cena_brutto = $produkt['cena_netto'] * (1 + $produkt['podatek_vat'] / 100); // Obliczenie ceny brutto
                    $suma_produktu = $cena_brutto * $ilosc; // Obliczenie sumy dla tego produktu
                    $suma += $suma_produktu; // Dodanie do całkowitej sumy
                    $output .= $this->GenerujProduktKoszyka($produkt, $ilosc, $cena_brutto, $suma_produktu); // Generowanie wiersza produktu
                }
            }
            $output .= '</tbody></table><div class="koszyk-podsumowanie"><div class="suma">Suma: ' . number_format($suma, 2) . ' zł</div><div class="koszyk-przyciski"><a href="?idp=-10" class="btn-kontynuuj">Kontynuuj zakupy</a><button class="btn-zamow" onclick="zlozZamowienie()">Złóż zamówienie</button></div></div>';
        }

        // Funkcje JavaScript do zarządzania koszykiem
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
                        window.location.reload(); // Odświeżenie strony po usunięciu produktu
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
                    window.location.reload(); // Odświeżenie strony po aktualizacji ilości
                })
                .catch(error => {
                    console.error("Błąd:", error);
                    alert("Wystąpił błąd podczas aktualizacji ilości produktu");
                });
            }

            function zlozZamowienie() {
                if (confirm("Czy na pewno chcesz złożyć zamówienie?")) {
                    // Czyszczenie koszyka
                    fetch("?idp=-12", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/x-www-form-urlencoded",
                        },
                        body: "action=czysc"
                    })
                    .then(response => response.text())
                    .then(() => {
                        alert("Zamówienie zostało złożone! Dziękujemy za zakupy.");
                        window.location.reload(); // Odświeżenie strony po złożeniu zamówienia
                    })
                    .catch(error => {
                        console.error("Błąd:", error);
                        alert("Wystąpił błąd podczas składania zamówienia");
                    });
                }
            }
        </script>';
        
        return $output; // Zwrócenie HTML koszyka
    }

    // Funkcja do generowania wiersza produktu w koszyku
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
        </tr>'; // Zwrócenie HTML dla wiersza produktu w koszyku
    }

    // Funkcja do sprawdzania dostępności produktu
    private function SprawdzDostepnosc($produkt_id, $ilosc) {
        $query = "SELECT ilosc_sztuk, status_dostepnosci FROM produkty WHERE id = " . intval($produkt_id); // Zapytanie SQL do sprawdzenia dostępności
        $result = mysqli_query($this->conn, $query);
        
        if ($produkt = mysqli_fetch_assoc($result)) { // Pobranie szczegółów produktu
            return $produkt['status_dostepnosci'] == 'dostępny' && $produkt['ilosc_sztuk'] >= $ilosc; // Sprawdzenie, czy produkt jest dostępny
        }
        return false; // Zwrócenie fałszu, jeśli produkt nie został znaleziony
    }
}
?>
