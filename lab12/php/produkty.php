<?php
class Produkty {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function ZarzadzajProduktami() {
        $output = '<div class="produkty-panel" style="background-color: rgba(255, 255, 255, 0.95); padding: 20px; border-radius: 8px; margin: 20px 0; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">';

        // Obsługa usuwania produktu
        if (isset($_GET['action']) && $_GET['action'] == 'usun' && isset($_GET['id'])) {
            $this->UsunProdukt($_GET['id']);
            header('Location: ?idp=-9');
            exit;
        }

        // Obsługa formularzy
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'dodaj':
                    $this->DodajProdukt();
                    break;
                case 'edytuj':
                    $this->EdytujProdukt();
                    break;
            }
        }

        // Formularz dodawania/edycji produktu
        $output .= $this->FormularzProduktu();

        // Lista produktów
        $output .= $this->PokazProdukty();

        $output .= '</div>';

        return $output;
    }

    private function FormularzProduktu() {
        $kategorie = $this->PobierzKategorie();
        
        $form = '
        <h2>Dodaj/Edytuj Produkt</h2>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="dodaj">
            <input type="hidden" name="id" value="">
            
            <label>Tytuł:</label><br>
            <input type="text" name="tytul" required><br>
            
            <label>Opis:</label><br>
            <textarea name="opis" required></textarea><br>
            
            <label>Data wygaśnięcia:</label><br>
            <input type="date" name="data_wygasniecia"><br>
            
            <label>Cena netto:</label><br>
            <input type="number" step="0.01" name="cena_netto" required><br>
            
            <label>VAT (%):</label><br>
            <input type="number" step="0.01" name="podatek_vat" required><br>
            
            <label>Ilość sztuk:</label><br>
            <input type="number" name="ilosc_sztuk" required><br>
            
            <label>Status dostępności:</label><br>
            <select name="status_dostepnosci">
                <option value="dostępny">Dostępny</option>
                <option value="niedostępny">Niedostępny</option>
                <option value="oczekujący">Oczekujący</option>
            </select><br>
            
            <label>Kategoria:</label><br>
            <select name="kategoria_id">
                ' . $this->GenerujOpcjeKategorii($kategorie) . '
            </select><br>
            
            <label>Gabaryt:</label><br>
            <input type="text" name="gabaryt"><br>
            
            <label>Zdjęcie produktu:</label><br>
            <input type="file" name="zdjecie" accept="image/*"><br>
            
            <input type="submit" value="Zapisz produkt">
        </form>';

        return $form;
    }

    private function GenerujOpcjeKategorii($kategorie, $parent_id = 0, $indent = '') {
        $output = '<option value="">-- Wybierz kategorię --</option>';
        foreach ($kategorie as $kat) {
            if ($kat['matka'] == $parent_id) {
                $output .= '<option value="' . $kat['id'] . '">' . $indent . htmlspecialchars($kat['nazwa']) . '</option>';
                $output .= $this->GenerujOpcjeKategorii($kategorie, $kat['id'], $indent . '- ');
            }
        }
        return $output;
    }

    private function PobierzKategorie() {
        $query = "SELECT * FROM kategorie ORDER BY matka, id";
        $result = mysqli_query($this->conn, $query);
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }

    private function DodajProdukt() {
        if (!isset($_POST['tytul'])) return;

        try {
            // Obsługa przesyłanego pliku
            $zdjecie_url = '';
            if (isset($_FILES['zdjecie']) && $_FILES['zdjecie']['error'] == 0) {
                $dozwolone_typy = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'];
                $max_rozmiar = 5 * 1024 * 1024; // 5MB

                if (!in_array($_FILES['zdjecie']['type'], $dozwolone_typy)) {
                    throw new Exception("Niedozwolony typ pliku. Dozwolone są tylko obrazy JPEG, JPG, PNG i GIF.");
                }

                if ($_FILES['zdjecie']['size'] > $max_rozmiar) {
                    throw new Exception("Plik jest zbyt duży. Maksymalny rozmiar to 5MB.");
                }

                $katalog_docelowy = 'images/produkty/';
                if (!file_exists($katalog_docelowy)) {
                    mkdir($katalog_docelowy, 0777, true);
                }

                $nazwa_pliku = time() . '_' . basename($_FILES['zdjecie']['name']);
                $sciezka_docelowa = $katalog_docelowy . $nazwa_pliku;

                if (move_uploaded_file($_FILES['zdjecie']['tmp_name'], $sciezka_docelowa)) {
                    $zdjecie_url = $sciezka_docelowa;
                } else {
                    throw new Exception("Wystąpił błąd podczas przesyłania pliku.");
                }
            }

            // Pozostała część kodu dodawania produktu
            $tytul = mysqli_real_escape_string($this->conn, $_POST['tytul']);
            $opis = mysqli_real_escape_string($this->conn, $_POST['opis']);
            $data_wygasniecia = !empty($_POST['data_wygasniecia']) ? 
                mysqli_real_escape_string($this->conn, $_POST['data_wygasniecia']) : NULL;
            $cena_netto = floatval($_POST['cena_netto']);
            $podatek_vat = floatval($_POST['podatek_vat']);
            $ilosc_sztuk = intval($_POST['ilosc_sztuk']);
            $status_dostepnosci = mysqli_real_escape_string($this->conn, $_POST['status_dostepnosci']);
            $kategoria_id = !empty($_POST['kategoria_id']) ? intval($_POST['kategoria_id']) : NULL;
            $gabaryt = mysqli_real_escape_string($this->conn, $_POST['gabaryt']);

            $query = "INSERT INTO produkty (
                tytul, 
                opis, 
                data_wygasniecia, 
                cena_netto, 
                podatek_vat, 
                ilosc_sztuk, 
                status_dostepnosci, 
                kategoria_id, 
                gabaryt, 
                zdjecie_url
                ) VALUES (
                    '$tytul',
                    '$opis',
                    " . ($data_wygasniecia ? "'$data_wygasniecia'" : "NULL") . ",
                    $cena_netto,
                    $podatek_vat,
                    $ilosc_sztuk,
                    '$status_dostepnosci',
                    " . ($kategoria_id ? "$kategoria_id" : "NULL") . ",
                    '$gabaryt',
                    '$zdjecie_url'
                )";

            if (!mysqli_query($this->conn, $query)) {
                throw new Exception("Błąd podczas dodawania produktu: " . mysqli_error($this->conn));
            }
            
            return true;

        } catch (Exception $e) {
            echo '<div class="error">Wystąpił błąd: ' . $e->getMessage() . '</div>';
            return false;
        }
    }

    public function UsunProdukt($id) {
        $id = intval($id);
        
        // Sprawdź czy produkt istnieje
        $check_query = "SELECT id FROM produkty WHERE id = $id";
        $check_result = mysqli_query($this->conn, $check_query);
        
        if (mysqli_num_rows($check_result) == 0) {
            return false;
        }
        
        $query = "DELETE FROM produkty WHERE id = $id";
        if (mysqli_query($this->conn, $query)) {
            return true;
        }
        return false;
    }

    public function EdytujProdukt($id) {
        $id = intval($_GET['id']);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Obsługa zapisywania zmian
            $tytul = $this->conn->real_escape_string($_POST['tytul']);
            $opis = $this->conn->real_escape_string($_POST['opis']);
            $cena_netto = floatval($_POST['cena_netto']);
            $podatek_vat = floatval($_POST['podatek_vat']);
            $ilosc_sztuk = intval($_POST['ilosc_sztuk']);
            $status = $this->conn->real_escape_string($_POST['status_dostepnosci']);
            $kategoria_id = intval($_POST['kategoria_id']);
            
            $query = "UPDATE produkty SET 
                     tytul = '$tytul',
                     opis = '$opis',
                     cena_netto = $cena_netto,
                     podatek_vat = $podatek_vat,
                     ilosc_sztuk = $ilosc_sztuk,
                     status_dostepnosci = '$status',
                     kategoria_id = $kategoria_id,
                     data_modyfikacji = NOW()
                     WHERE id = $id";
                     
            if ($this->conn->query($query)) {
                header("Location: ?idp=-9");
                exit;
            }
        }
        
        // Pobierz dane produktu
        $query = "SELECT * FROM produkty WHERE id = $id";
        $result = $this->conn->query($query);
        $produkt = $result->fetch_assoc();
        
        // Formularz edycji
        $output = '<div class="edit-product-form" style="background-color: rgba(255, 255, 255, 0.8); 
                                    padding: 20px; 
                                    border-radius: 5px;
                                    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                                    margin: 20px auto;
                                    max-width: 800px;">
            <h2>Edycja produktu</h2>
            <form method="post">
                <div class="form-group">
                    <label>Tytuł:</label>
                    <input type="text" name="tytul" value="' . htmlspecialchars($produkt['tytul']) . '" required>
                </div>
                
                <div class="form-group">
                    <label>Opis:</label>
                    <textarea name="opis" required>' . htmlspecialchars($produkt['opis']) . '</textarea>
                </div>
                
                <div class="form-group">
                    <label>Cena netto:</label>
                    <input type="number" step="0.01" name="cena_netto" value="' . $produkt['cena_netto'] . '" required>
                </div>
                
                <div class="form-group">
                    <label>VAT (%):</label>
                    <input type="number" step="0.01" name="podatek_vat" value="' . $produkt['podatek_vat'] . '" required>
                </div>
                
                <div class="form-group">
                    <label>Ilość sztuk:</label>
                    <input type="number" name="ilosc_sztuk" value="' . $produkt['ilosc_sztuk'] . '" required>
                </div>
                
                <div class="form-group">
                    <label>Status dostępności:</label>
                    <select name="status_dostepnosci">
                        <option value="dostępny" ' . ($produkt['status_dostepnosci'] == 'dostępny' ? 'selected' : '') . '>Dostępny</option>
                        <option value="niedostępny" ' . ($produkt['status_dostepnosci'] == 'niedostępny' ? 'selected' : '') . '>Niedostępny</option>
                        <option value="oczekujący" ' . ($produkt['status_dostepnosci'] == 'oczekujący' ? 'selected' : '') . '>Oczekujący</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Kategoria:</label>
                    <select name="kategoria_id" required>';
        
        // Pobierz kategorie
        $kat_query = "SELECT id, nazwa FROM kategorie ORDER BY nazwa";
        $kat_result = $this->conn->query($kat_query);
        while ($kat = $kat_result->fetch_assoc()) {
            $selected = ($kat['id'] == $produkt['kategoria_id']) ? 'selected' : '';
            $output .= '<option value="' . $kat['id'] . '" ' . $selected . '>' . htmlspecialchars($kat['nazwa']) . '</option>';
        }
        
        $output .= '</select>
                </div>
                
                <button type="submit" class="btn-zapisz">Zapisz zmiany</button>
            </form>
        </div>';
        
        return $output;
    }

    private function PokazProdukty() {
        $query = "SELECT p.*, k.nazwa as kategoria_nazwa 
                 FROM produkty p 
                 LEFT JOIN kategorie k ON p.kategoria_id = k.id 
                 ORDER BY p.data_utworzenia DESC";
        $result = mysqli_query($this->conn, $query);

        $output = '<h2>Lista produktów</h2>
        <div class="table-responsive">
        <table border="1">
            <tr>
                <th>ID</th>
                <th>Tytuł</th>
                <th>Data modyfikacji</th>
                <th>Data wygaśnięcia</th>
                <th>Cena netto</th>
                <th>VAT</th>
                <th>Ilość sztuk</th>
                <th>Status</th>
                <th>Kategoria</th>
                <th>Gabaryt</th>
                <th>Zdjęcie</th>
                <th>Akcje</th>
            </tr>';

        while ($row = mysqli_fetch_assoc($result)) {
            $output .= '<tr>
                <td>' . $row['id'] . '</td>
                <td>' . htmlspecialchars($row['tytul']) . '</td>
                <td>' . ($row['data_modyfikacji'] ? date('Y-m-d H:i', strtotime($row['data_modyfikacji'])) : '-') . '</td>
                <td>' . ($row['data_wygasniecia'] ? date('Y-m-d', strtotime($row['data_wygasniecia'])) : '-') . '</td>
                <td>' . number_format($row['cena_netto'], 2) . ' zł</td>
                <td>' . $row['podatek_vat'] . '%</td>
                <td>' . $row['ilosc_sztuk'] . '</td>
                <td>' . $row['status_dostepnosci'] . '</td>
                <td>' . htmlspecialchars($row['kategoria_nazwa']) . '</td>
                <td>' . htmlspecialchars($row['gabaryt']) . '</td>
                <td>' . ($row['zdjecie_url'] ? '<img src="' . htmlspecialchars($row['zdjecie_url']) . '" alt="Produkt" style="max-width: 50px;">' : '-') . '</td>
                <td>
                    <a href="?idp=-11&id=' . $row['id'] . '" class="btn-edytuj">Edytuj</a>
                    <a href="?idp=-9&action=usun&id=' . $row['id'] . '" class="btn-usun" 
                       onclick="return confirm(\'Czy na pewno chcesz usunąć ten produkt?\')">Usuń</a>
                </td>
            </tr>';
        }

        $output .= '</table></div>';
        
        return $output;
    }
}
?> 