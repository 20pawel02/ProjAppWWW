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
            nazwa VARCHAR(255) NOT NULL UNIQUE
        )";
        
        $this->conn->query($query);
    }

    // Metoda dodająca nową kategorię
    public function DodajKategorie($nazwa, $matka = 0) {
        $nazwa = trim($nazwa);
        if (empty($nazwa)) {
            return "Nazwa kategorii nie może być pusta.";
        }

        $nazwa = $this->conn->real_escape_string($nazwa);
        $matka = intval($matka);

        // Sprawdzenie, czy matka istnieje (jeśli podana)
        if ($matka > 0) {
            $checkQuery = "SELECT id FROM kategorie WHERE id = $matka";
            $result = $this->conn->query($checkQuery);
            if ($result->num_rows === 0) {
                return "Kategoria nadrzędna nie istnieje.";
            }
        }
        
        $query = "INSERT INTO kategorie (nazwa, matka) VALUES ('$nazwa', $matka)";
        if ($this->conn->query($query)) {
            return true;
        }
        return "Błąd podczas dodawania kategorii: " . $this->conn->error;
    }

    // Metoda usuwająca kategorię
    public function UsunKategorie($id) {
        $id = intval($id);
        
        // Najpierw sprawdź czy kategoria ma podkategorie
        $check_query = "SELECT id FROM kategorie WHERE matka = $id";
        $result = $this->conn->query($check_query);
        
        if ($result->num_rows > 0) {
            // Jeśli ma podkategorie, usuń je najpierw
            while ($row = $result->fetch_assoc()) {
                $this->UsunKategorie($row['id']);
            }
        }
        
        // Teraz usuń samą kategorię
        $query = "DELETE FROM kategorie WHERE id = $id LIMIT 1";
        if ($this->conn->query($query)) {
            return true;
        }
        return "Błąd podczas usuwania kategorii: " . $this->conn->error;
    }

    // Metoda edytująca kategorię
    public function EdytujKategorie($id, $nazwa, $matka = null) {
        $id = intval($id);
        $nazwa = trim($nazwa);

        if (empty($nazwa)) {
            return "Nazwa kategorii nie może być pusta.";
        }

        $nazwa = $this->conn->real_escape_string($nazwa);

        // Sprawdzenie, czy matka istnieje (jeśli podana)
        if ($matka !== null) {
            $matka = intval($matka);
            if ($matka > 0) {
                $checkQuery = "SELECT id FROM kategorie WHERE id = $matka";
                $result = $this->conn->query($checkQuery);
                if ($result->num_rows === 0) {
                    return "Kategoria nadrzędna nie istnieje.";
                }
            }
        }

        // Unikaj cykli w drzewie kategorii
        if ($id === $matka) {
            return "Kategoria nie może być swoją własną nadrzędną.";
        }

        $query = "UPDATE kategorie SET nazwa = '$nazwa'";
        if ($matka !== null) {
            $query .= ", matka = $matka";
        }
        $query .= " WHERE id = $id LIMIT 1";

        if ($this->conn->query($query)) {
            return true;
        }
        return "Błąd podczas edycji kategorii: " . $this->conn->error;
    }

    // Pomocnicza metoda generująca opcje select
    private function PobierzOpcjeKategorii($selected = 0) {
        $query = "SELECT id, nazwa FROM kategorie ORDER BY nazwa LIMIT 100";
        $result = $this->conn->query($query);
        $options = '';
        
        while ($row = $result->fetch_assoc()) {
            $options .= '<option value="' . $row['id'] . '"' . 
                       ($selected == $row['id'] ? ' selected' : '') . '>' . 
                       htmlspecialchars($row['nazwa']) . '</option>';
        }
        
        return $options;
    }

    // Drzewo kategorii
    private function WyswietlKategorieEdytowalne() {
        $html = '<div class="kategorie-drzewo">';
        
        $query = "SELECT * FROM kategorie WHERE matka = 0 ORDER BY nazwa LIMIT 100";
        $result = $this->conn->query($query);
        
        while ($row = $result->fetch_assoc()) {
            $html .= $this->WyswietlKategorieEdytowalneRekurencyjnie($row);
        }
        
        $html .= '</div>';
        return $html;
    }

    private function WyswietlKategorieEdytowalneRekurencyjnie($kategoria) {
        $html = '<div class="kategoria">';
        
        $html .= '<div class="kategoria-panel">';
        $html .= '<span class="kategoria-nazwa">' . htmlspecialchars($kategoria['nazwa']) . '</span>';
        
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
        
        $query = "SELECT * FROM kategorie WHERE matka = {$kategoria['id']} ORDER BY nazwa LIMIT 50";
        $result = $this->conn->query($query);
        
        if ($result->num_rows > 0) {
            $html .= '<div class="podkategorie">';
            while ($row = $result->fetch_assoc()) {
                $html .= $this->WyswietlKategorieEdytowalneRekurencyjnie($row);
            }
            $html .= '</div>';
        }
        
        $html .= '</div>';
        return $html;
    }

    // Dodaj metodę ZarzadzajKategoriami() do klasy Kategorie
    public function ZarzadzajKategoriami() {
        $html = '<div class="kategorie-panel">';
        
        // Obsługa formularzy
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            if(isset($_POST['dodaj'])) {
                $nazwa = $_POST['nazwa'] ?? '';
                $matka = $_POST['matka'] ?? 0;
                $result = $this->DodajKategorie($nazwa, $matka);
                if($result === true) {
                    $html .= '<div class="success">Kategoria została dodana.</div>';
                } else {
                    $html .= '<div class="error">' . $result . '</div>';
                }
            }
            elseif(isset($_POST['usun'])) {
                $id = $_POST['id'] ?? 0;
                $result = $this->UsunKategorie($id);
                if($result === true) {
                    $html .= '<div class="success">Kategoria została usunięta.</div>';
                } else {
                    $html .= '<div class="error">' . $result . '</div>';
                }
            }
            elseif(isset($_POST['edytuj'])) {
                $id = $_POST['id'] ?? 0;
                $nazwa = $_POST['nazwa'] ?? '';
                $matka = $_POST['matka'] ?? null;
                $result = $this->EdytujKategorie($id, $nazwa, $matka);
                if($result === true) {
                    $html .= '<div class="success">Kategoria została zaktualizowana.</div>';
                } else {
                    $html .= '<div class="error">' . $result . '</div>';
                }
            }
        }

        // Formularz dodawania kategorii
        $html .= '
        <div class="form-section">
            <h3>Dodaj nową kategorię</h3>
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
}
?>
