<?php
class Produkty {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
        $this->StworzTabeleProdukty();
    }

    // Metoda tworząca tabelę produktów jeśli nie istnieje
    private function StworzTabeleProdukty() {
        $query = "CREATE TABLE IF NOT EXISTS produkty (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tytul VARCHAR(255) NOT NULL,
            opis TEXT,
            data_utworzenia DATETIME DEFAULT CURRENT_TIMESTAMP,
            data_modyfikacji DATETIME ON UPDATE CURRENT_TIMESTAMP,
            data_wygasniecia DATE,
            cena_netto DECIMAL(10,2) NOT NULL,
            podatek_vat DECIMAL(4,2) NOT NULL,
            ilosc_magazyn INT NOT NULL DEFAULT 0,
            status_dostepnosci ENUM('dostepny', 'niedostepny', 'oczekujacy') DEFAULT 'niedostepny',
            kategoria_id INT,
            gabaryt_produktu ENUM('maly', 'sredni', 'duzy') DEFAULT 'sredni',
            zdjecie_url VARCHAR(255),
            FOREIGN KEY (kategoria_id) REFERENCES kategorie(id) ON DELETE SET NULL
        )";
        
        $this->conn->query($query);
    }

    // Metoda dodająca nowy produkt
    public function DodajProdukt($dane) {
        // Walidacja danych
        if (empty($dane['tytul']) || empty($dane['cena_netto']) || empty($dane['podatek_vat'])) {
            return "Wymagane pola nie zostały wypełnione.";
        }

        // Przygotowanie zapytania
        $query = "INSERT INTO produkty (
            tytul, 
            opis, 
            data_wygasniecia, 
            cena_netto, 
            podatek_vat, 
            ilosc_magazyn, 
            status_dostepnosci, 
            kategoria_id, 
            gabaryt_produktu, 
            zdjecie_url
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param(
            'sssddiisss',
            $dane['tytul'],
            $dane['opis'],
            $dane['data_wygasniecia'],
            $dane['cena_netto'],
            $dane['podatek_vat'],
            $dane['ilosc_magazyn'],
            $dane['status_dostepnosci'],
            $dane['kategoria_id'],
            $dane['gabaryt_produktu'],
            $dane['zdjecie_url']
        );

        if ($stmt->execute()) {
            return true;
        }
        return "Błąd podczas dodawania produktu: " . $stmt->error;
    }

    // Metoda usuwająca produkt
    public function UsunProdukt($id) {
        $id = intval($id);
        $query = "DELETE FROM produkty WHERE id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $id);
        
        if ($stmt->execute()) {
            return true;
        }
        return "Błąd podczas usuwania produktu: " . $stmt->error;
    }

    // Metoda edytująca produkt
    public function EdytujProdukt($id, $dane) {
        $id = intval($id);
        
        // Walidacja danych
        if (empty($dane['tytul']) || empty($dane['cena_netto']) || empty($dane['podatek_vat'])) {
            return "Wymagane pola nie zostały wypełnione.";
        }

        $query = "UPDATE produkty SET 
            tytul = ?, 
            opis = ?, 
            data_wygasniecia = ?, 
            cena_netto = ?, 
            podatek_vat = ?, 
            ilosc_magazyn = ?, 
            status_dostepnosci = ?, 
            kategoria_id = ?, 
            gabaryt_produktu = ?, 
            zdjecie_url = ?
            WHERE id = ? LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param(
            'sssddiisssi',
            $dane['tytul'],
            $dane['opis'],
            $dane['data_wygasniecia'],
            $dane['cena_netto'],
            $dane['podatek_vat'],
            $dane['ilosc_magazyn'],
            $dane['status_dostepnosci'],
            $dane['kategoria_id'],
            $dane['gabaryt_produktu'],
            $dane['zdjecie_url'],
            $id
        );

        if ($stmt->execute()) {
            return true;
        }
        return "Błąd podczas edycji produktu: " . $stmt->error;
    }

    // Metoda sprawdzająca dostępność produktu
    private function SprawdzDostepnosc($produkt) {
        // Sprawdź datę wygaśnięcia
        if (!empty($produkt['data_wygasniecia'])) {
            $data_wygasniecia = strtotime($produkt['data_wygasniecia']);
            if ($data_wygasniecia < time()) {
                return 'niedostepny';
            }
        }

        // Sprawdź ilość w magazynie
        if ($produkt['ilosc_magazyn'] <= 0) {
            return 'niedostepny';
        }

        // Sprawdź status
        if ($produkt['status_dostepnosci'] !== 'dostepny') {
            return $produkt['status_dostepnosci'];
        }

        return 'dostepny';
    }

    // Metoda wyświetlająca listę produktów
    public function PokazProdukty() {
        $query = "SELECT p.*, k.nazwa as kategoria_nazwa 
                 FROM produkty p 
                 LEFT JOIN kategorie k ON p.kategoria_id = k.id 
                 ORDER BY p.data_utworzenia DESC";
        $result = $this->conn->query($query);

        $html = '<div class="produkty-lista">';
        
        while ($produkt = $result->fetch_assoc()) {
            $dostepnosc = $this->SprawdzDostepnosc($produkt);
            $cena_brutto = $produkt['cena_netto'] * (1 + $produkt['podatek_vat']/100);
            
            $html .= '<div class="produkt-item">';
            if ($produkt['zdjecie_url']) {
                $html .= '<img src="' . htmlspecialchars($produkt['zdjecie_url']) . '" alt="' . htmlspecialchars($produkt['tytul']) . '">';
            }
            $html .= '<h3>' . htmlspecialchars($produkt['tytul']) . '</h3>';
            $html .= '<p class="opis">' . htmlspecialchars($produkt['opis']) . '</p>';
            $html .= '<p class="cena">Cena: ' . number_format($cena_brutto, 2) . ' PLN</p>';
            $html .= '<p class="dostepnosc">Status: ' . $dostepnosc . '</p>';
            $html .= '<p class="kategoria">Kategoria: ' . htmlspecialchars($produkt['kategoria_nazwa']) . '</p>';
            
            // Przyciski edycji i usuwania dla administratora
            if (isset($_SESSION['loggedin']) && $_SESSION['loggedin']) {
                $html .= '<div class="admin-buttons">';
                $html .= '<button onclick="edytujProdukt(' . $produkt['id'] . ')" class="btn-edytuj">Edytuj</button>';
                $html .= '<button onclick="usunProdukt(' . $produkt['id'] . ')" class="btn-usun">Usuń</button>';
                $html .= '</div>';
            }
            
            $html .= '</div>';
        }
        
        $html .= '</div>';
        return $html;
    }

    // Główna metoda zarządzania produktami
    public function ZarzadzajProduktami() {
        if (!isset($_SESSION['loggedin'])) {
            return "Brak dostępu. Zaloguj się.";
        }

        $html = '<div class="produkty-panel">';
        
        // Obsługa formularzy
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (isset($_POST['dodaj_produkt'])) {
                $result = $this->DodajProdukt($_POST);
                $html .= $result === true ? 
                    '<div class="success">Produkt został dodany.</div>' : 
                    '<div class="error">' . $result . '</div>';
            }
            elseif (isset($_POST['usun_produkt'])) {
                $result = $this->UsunProdukt($_POST['id']);
                $html .= $result === true ? 
                    '<div class="success">Produkt został usunięty.</div>' : 
                    '<div class="error">' . $result . '</div>';
            }
            elseif (isset($_POST['edytuj_produkt'])) {
                $result = $this->EdytujProdukt($_POST['id'], $_POST);
                $html .= $result === true ? 
                    '<div class="success">Produkt został zaktualizowany.</div>' : 
                    '<div class="error">' . $result . '</div>';
            }
        }

        // Formularz dodawania produktu
        $html .= $this->FormularzProduktu();
        
        // Lista produktów
        $html .= $this->PokazProdukty();
        
        $html .= '</div>';
        return $html;
    }

    // Metoda generująca formularz produktu
    private function FormularzProduktu($produkt = null) {
        $html = '
        <div class="form-section">
            <h3>' . ($produkt ? 'Edytuj produkt' : 'Dodaj nowy produkt') . '</h3>
            <form method="post" enctype="multipart/form-data">
                ' . ($produkt ? '<input type="hidden" name="id" value="' . $produkt['id'] . '">' : '') . '
                <div class="form-group">
                    <label for="tytul">Tytuł*:</label>
                    <input type="text" id="tytul" name="tytul" value="' . ($produkt ? htmlspecialchars($produkt['tytul']) : '') . '" required>
                </div>
                
                <div class="form-group">
                    <label for="opis">Opis:</label>
                    <textarea id="opis" name="opis">' . ($produkt ? htmlspecialchars($produkt['opis']) : '') . '</textarea>
                </div>
                
                <div class="form-group">
                    <label for="cena_netto">Cena netto*:</label>
                    <input type="number" step="0.01" id="cena_netto" name="cena_netto" value="' . ($produkt ? $produkt['cena_netto'] : '') . '" required>
                </div>
                
                <div class="form-group">
                    <label for="podatek_vat">VAT (%)*:</label>
                    <input type="number" step="0.01" id="podatek_vat" name="podatek_vat" value="' . ($produkt ? $produkt['podatek_vat'] : '23') . '" required>
                </div>
                
                <div class="form-group">
                    <label for="ilosc_magazyn">Ilość w magazynie:</label>
                    <input type="number" id="ilosc_magazyn" name="ilosc_magazyn" value="' . ($produkt ? $produkt['ilosc_magazyn'] : '0') . '">
                </div>
                
                <div class="form-group">
                    <label for="status_dostepnosci">Status:</label>
                    <select id="status_dostepnosci" name="status_dostepnosci">
                        <option value="dostepny" ' . ($produkt && $produkt['status_dostepnosci'] == 'dostepny' ? 'selected' : '') . '>Dostępny</option>
                        <option value="niedostepny" ' . ($produkt && $produkt['status_dostepnosci'] == 'niedostepny' ? 'selected' : '') . '>Niedostępny</option>
                        <option value="oczekujacy" ' . ($produkt && $produkt['status_dostepnosci'] == 'oczekujacy' ? 'selected' : '') . '>Oczekujący</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="kategoria_id">Kategoria:</label>
                    <select id="kategoria_id" name="kategoria_id">
                        ' . $this->PobierzOpcjeKategorii($produkt ? $produkt['kategoria_id'] : null) . '
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="gabaryt_produktu">Gabaryt:</label>
                    <select id="gabaryt_produktu" name="gabaryt_produktu">
                        <option value="maly" ' . ($produkt && $produkt['gabaryt_produktu'] == 'maly' ? 'selected' : '') . '>Mały</option>
                        <option value="sredni" ' . ($produkt && $produkt['gabaryt_produktu'] == 'sredni' ? 'selected' : '') . '>Średni</option>
                        <option value="duzy" ' . ($produkt && $produkt['gabaryt_produktu'] == 'duzy' ? 'selected' : '') . '>Duży</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="data_wygasniecia">Data wygaśnięcia:</label>
                    <input type="date" id="data_wygasniecia" name="data_wygasniecia" value="' . ($produkt ? $produkt['data_wygasniecia'] : '') . '">
                </div>
                
                <div class="form-group">
                    <label for="zdjecie_url">URL zdjęcia:</label>
                    <input type="url" id="zdjecie_url" name="zdjecie_url" value="' . ($produkt ? htmlspecialchars($produkt['zdjecie_url']) : '') . '">
                </div>
                
                <div class="form-group">
                    <input type="submit" name="' . ($produkt ? 'edytuj_produkt' : 'dodaj_produkt') . '" value="' . ($produkt ? 'Zapisz zmiany' : 'Dodaj produkt') . '">
                </div>
            </form>
        </div>';
        
        return $html;
    }

    // Metoda pomocnicza do pobierania opcji kategorii
    private function PobierzOpcjeKategorii($selected = null) {
        $query = "SELECT id, nazwa FROM kategorie ORDER BY nazwa";
        $result = $this->conn->query($query);
        $options = '<option value="">Wybierz kategorię</option>';
        
        while ($row = $result->fetch_assoc()) {
            $options .= '<option value="' . $row['id'] . '"' . 
                       ($selected == $row['id'] ? ' selected' : '') . '>' . 
                       htmlspecialchars($row['nazwa']) . '</option>';
        }
        
        return $options;
    }
}
?> 