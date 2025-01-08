<?php
class Kategorie {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
        $this->StworzTabeleKategorii();
    }

    // Metoda tworząca tabelę kategorii jeśli nie istnieje
    private function StworzTabeleKategorii() {
        $query = "CREATE TABLE IF NOT EXISTS kategorie (
            id INT AUTO_INCREMENT PRIMARY KEY,
            matka INT DEFAULT 0,
            nazwa VARCHAR(255) NOT NULL
        )";
        
        $this->conn->query($query);
    }

    // Metoda dodająca nową kategorię
    public function DodajKategorie($nazwa, $matka = 0) {
        $nazwa = mysqli_real_escape_string($this->conn, $nazwa);
        $matka = intval($matka);
        
        $query = "INSERT INTO kategorie (nazwa, matka) VALUES ('$nazwa', $matka)";
        if($this->conn->query($query)) {
            return true;
        }
        return false;
    }

    // Metoda usuwająca kategorię
    public function UsunKategorie($id) {
        $id = intval($id);
        
        // Najpierw sprawdź czy kategoria ma podkategorie
        $check_query = "SELECT id FROM kategorie WHERE matka = $id";
        $result = $this->conn->query($check_query);
        
        if($result->num_rows > 0) {
            // Jeśli ma podkategorie, usuń je najpierw
            while($row = $result->fetch_assoc()) {
                $this->UsunKategorie($row['id']);
            }
        }
        
        // Teraz usuń samą kategorię
        $query = "DELETE FROM kategorie WHERE id = $id LIMIT 1";
        return $this->conn->query($query);
    }

    // Metoda edytująca kategorię
    public function EdytujKategorie($id, $nazwa, $matka = null) {
        $id = intval($id);
        $nazwa = mysqli_real_escape_string($this->conn, $nazwa);
        
        if($matka !== null) {
            $matka = intval($matka);
            $query = "UPDATE kategorie SET nazwa = '$nazwa', matka = $matka WHERE id = $id LIMIT 1";
        } else {
            $query = "UPDATE kategorie SET nazwa = '$nazwa' WHERE id = $id LIMIT 1";
        }
        
        return $this->conn->query($query);
    }

    // Metoda zarządzania kategoriami
    public function ZarzadzajKategoriami() {
        $html = '<div class="kategorie-panel">';
        
        // Obsługa formularzy
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            if(isset($_POST['dodaj'])) {
                $nazwa = $_POST['nazwa'] ?? '';
                $matka = $_POST['matka'] ?? 0;
                if($this->DodajKategorie($nazwa, $matka)) {
                    $html .= '<div class="success">Kategoria została dodana.</div>';
                }
            }
            elseif(isset($_POST['usun'])) {
                $id = $_POST['id'] ?? 0;
                if($this->UsunKategorie($id)) {
                    $html .= '<div class="success">Kategoria została usunięta.</div>';
                }
            }
            elseif(isset($_POST['edytuj'])) {
                $id = $_POST['id'] ?? 0;
                $nazwa = $_POST['nazwa'] ?? '';
                $matka = $_POST['matka'] ?? null;
                if($this->EdytujKategorie($id, $nazwa, $matka)) {
                    $html .= '<div class="success">Kategoria została zaktualizowana.</div>';
                }
            }
        }

        // Formularz dodawania kategorii
        $html .= '
        <div class="form-section">
            <h3>Dodaj kategorię</h3>
            <form method="post">
                <input type="text" name="nazwa" placeholder="Nazwa kategorii" required>
                <select name="matka">
                    <option value="0">Kategoria główna</option>
                    ' . $this->PobierzOpcjeKategorii() . '
                </select>
                <input type="submit" name="dodaj" value="Dodaj">
            </form>
        </div>';

        // Lista kategorii z możliwością edycji
        $html .= '<div class="kategorie-lista">';
        $html .= '<h3>Lista kategorii</h3>';
        $html .= $this->WyswietlKategorieEdytowalne();
        $html .= '</div>';

        $html .= '</div>';
        return $html;
    }

    // Metoda pomocnicza do generowania opcji select
    private function PobierzOpcjeKategorii($selected = 0) {
        $query = "SELECT id, nazwa FROM kategorie ORDER BY nazwa LIMIT 100";
        $result = $this->conn->query($query);
        $options = '';
        
        while($row = $result->fetch_assoc()) {
            $options .= '<option value="' . $row['id'] . '"' . 
                       ($selected == $row['id'] ? ' selected' : '') . '>' . 
                       htmlspecialchars($row['nazwa']) . '</option>';
        }
        
        return $options;
    }

    // Metoda wyświetlająca kategorie w formie drzewa
    private function WyswietlKategorie() {
        $html = '<div class="kategorie-drzewo">';
        
        // Pobierz kategorie główne
        $query = "SELECT * FROM kategorie WHERE matka = 0 ORDER BY nazwa LIMIT 100";
        $result = $this->conn->query($query);
        
        while($row = $result->fetch_assoc()) {
            $html .= $this->WyswietlKategorieDrzewo($row);
        }
        
        $html .= '</div>';
        return $html;
    }

    // Metoda pomocnicza do rekurencyjnego wyświetlania kategorii
    private function WyswietlKategorieDrzewo($kategoria) {
        $html = '<div class="kategoria">';
        $html .= '<span>' . htmlspecialchars($kategoria['nazwa']) . '</span>';
        
        // Formularz edycji
        $html .= '
        <form method="post" style="display: inline-block; margin-left: 10px;">
            <input type="hidden" name="id" value="' . $kategoria['id'] . '">
            <input type="text" name="nazwa" placeholder="Nowa nazwa">
            <input type="submit" name="edytuj" value="Edytuj">
            <input type="submit" name="usun" value="Usuń" onclick="return confirm(\'Czy na pewno chcesz usunąć tę kategorię?\')">
        </form>';
        
        // Pobierz podkategorie
        $query = "SELECT * FROM kategorie WHERE matka = {$kategoria['id']} ORDER BY nazwa LIMIT 50";
        $result = $this->conn->query($query);
        
        if($result->num_rows > 0) {
            $html .= '<div class="podkategorie">';
            while($row = $result->fetch_assoc()) {
                $html .= $this->WyswietlKategorieDrzewo($row);
            }
            $html .= '</div>';
        }
        
        $html .= '</div>';
        return $html;
    }

    // Dodaj nową metodę WyswietlKategorieEdytowalne()
    private function WyswietlKategorieEdytowalne() {
        $html = '<div class="kategorie-drzewo">';
        
        // Pobierz kategorie główne
        $query = "SELECT * FROM kategorie WHERE matka = 0 ORDER BY nazwa LIMIT 100";
        $result = $this->conn->query($query);
        
        while($row = $result->fetch_assoc()) {
            $html .= $this->WyswietlKategorieEdytowalneRekurencyjnie($row);
        }
        
        $html .= '</div>';
        return $html;
    }

    // Dodaj nową metodę WyswietlKategorieEdytowalneRekurencyjnie()
    private function WyswietlKategorieEdytowalneRekurencyjnie($kategoria) {
        $html = '<div class="kategoria">';
        
        // Panel edycji kategorii
        $html .= '<div class="kategoria-panel">';
        $html .= '<span class="kategoria-nazwa">' . htmlspecialchars($kategoria['nazwa']) . '</span>';
        
        // Formularz edycji
        $html .= '<form method="post" class="edycja-form">
            <input type="hidden" name="id" value="' . $kategoria['id'] . '">
            <input type="text" name="nazwa" placeholder="Nowa nazwa" value="' . htmlspecialchars($kategoria['nazwa']) . '">
            <select name="matka">
                <option value="0">Kategoria główna</option>
                ' . $this->PobierzOpcjeKategorii($kategoria['matka']) . '
            </select>
            <button type="submit" name="edytuj" class="btn-edytuj">Zapisz zmiany</button>
            <button type="submit" name="usun" class="btn-usun" onclick="return confirm(\'Czy na pewno chcesz usunąć tę kategorię?\')">Usuń</button>
        </form>';
        $html .= '</div>';
        
        // Pobierz i wyświetl podkategorie
        $query = "SELECT * FROM kategorie WHERE matka = {$kategoria['id']} ORDER BY nazwa LIMIT 50";
        $result = $this->conn->query($query);
        
        if($result->num_rows > 0) {
            $html .= '<div class="podkategorie">';
            while($row = $result->fetch_assoc()) {
                $html .= $this->WyswietlKategorieEdytowalneRekurencyjnie($row);
            }
            $html .= '</div>';
        }
        
        $html .= '</div>';
        return $html;
    }
}
?> 