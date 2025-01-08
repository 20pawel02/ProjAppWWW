<?php
class Produkty {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function ZarzadzajProduktami() {
        $output = '<div class="produkty-panel">';

        // Obsługa formularzy
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'dodaj':
                    $this->DodajProdukt();
                    break;
                case 'usun':
                    $this->UsunProdukt($_POST['id']);
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
            
            <label>URL zdjęcia:</label><br>
            <input type="text" name="zdjecie_url"><br>
            
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
            $zdjecie_url = mysqli_real_escape_string($this->conn, $_POST['zdjecie_url']);

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
            // Możesz dodać tutaj logowanie błędu
            echo '<div class="error">Wystąpił błąd: ' . $e->getMessage() . '</div>';
            return false;
        }
    }

    private function UsunProdukt($id) {
        $id = intval($id);
        $query = "DELETE FROM produkty WHERE id = $id";
        mysqli_query($this->conn, $query);
    }

    private function EdytujProdukt() {
        if (!isset($_POST['id']) || !isset($_POST['tytul'])) return;

        try {
            $id = intval($_POST['id']);
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
            $zdjecie_url = mysqli_real_escape_string($this->conn, $_POST['zdjecie_url']);

        $query = "UPDATE produkty SET 
                        tytul = '$tytul',
                        opis = '$opis',
                        data_wygasniecia = " . ($data_wygasniecia ? "'$data_wygasniecia'" : "NULL") . ",
                        cena_netto = $cena_netto,
                        podatek_vat = $podatek_vat,
                        ilosc_sztuk = $ilosc_sztuk,
                        status_dostepnosci = '$status_dostepnosci',
                        kategoria_id = " . ($kategoria_id ? "$kategoria_id" : "NULL") . ",
                        gabaryt = '$gabaryt',
                        zdjecie_url = '$zdjecie_url'
                    WHERE id = $id";

            if (!mysqli_query($this->conn, $query)) {
                throw new Exception("Błąd podczas aktualizacji produktu: " . mysqli_error($this->conn));
            }
            
            return true;

        } catch (Exception $e) {
            echo '<div class="error">Wystąpił błąd: ' . $e->getMessage() . '</div>';
            return false;
        }
    }

    private function PokazProdukty() {
        $query = "SELECT p.*, k.nazwa as kategoria_nazwa 
                 FROM produkty p 
                 LEFT JOIN kategorie k ON p.kategoria_id = k.id 
                 ORDER BY p.data_utworzenia DESC";
        $result = mysqli_query($this->conn, $query);

        $output = '<h2>Lista produktów</h2>
        <table border="1">
            <tr>
                <th>ID</th>
                <th>Tytuł</th>
                <th>Cena netto</th>
                <th>VAT</th>
                <th>Ilość</th>
                <th>Status</th>
                <th>Kategoria</th>
                <th>Akcje</th>
            </tr>';

        while ($row = mysqli_fetch_assoc($result)) {
            $output .= '<tr>
                <td>' . $row['id'] . '</td>
                <td>' . htmlspecialchars($row['tytul']) . '</td>
                <td>' . number_format($row['cena_netto'], 2) . ' zł</td>
                <td>' . $row['podatek_vat'] . '%</td>
                <td>' . $row['ilosc_sztuk'] . '</td>
                <td>' . $row['status_dostepnosci'] . '</td>
                <td>' . htmlspecialchars($row['kategoria_nazwa']) . '</td>
                <td class="akcje">
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="action" value="usun">
                        <input type="hidden" name="id" value="' . $row['id'] . '">
                        <input type="submit" class="usun" value="Usuń">
                    </form>
                    <button class="edytuj" onclick="edytujProdukt(' . $row['id'] . ', ' . 
                        htmlspecialchars(json_encode([
                            'tytul' => $row['tytul'],
                            'opis' => $row['opis'],
                            'data_wygasniecia' => $row['data_wygasniecia'],
                            'cena_netto' => $row['cena_netto'],
                            'podatek_vat' => $row['podatek_vat'],
                            'ilosc_sztuk' => $row['ilosc_sztuk'],
                            'status_dostepnosci' => $row['status_dostepnosci'],
                            'kategoria_id' => $row['kategoria_id'],
                            'gabaryt' => $row['gabaryt'],
                            'zdjecie_url' => $row['zdjecie_url']
                        ]), JSON_HEX_APOS | JSON_HEX_QUOT) . 
                    ')">Edytuj</button>
                </td>
            </tr>';
        }

        $output .= '</table>';
        
        // Dodaj skrypt JavaScript do obsługi edycji
        $output .= '
        <script>
        function edytujProdukt(id, dane) {
            // Wypełnij formularz danymi produktu
            document.querySelector(\'input[name="action"]\').value = "edytuj";
            document.querySelector(\'input[name="id"]\').value = id;
            document.querySelector(\'input[name="tytul"]\').value = dane.tytul;
            document.querySelector(\'textarea[name="opis"]\').value = dane.opis;
            document.querySelector(\'input[name="data_wygasniecia"]\').value = dane.data_wygasniecia;
            document.querySelector(\'input[name="cena_netto"]\').value = dane.cena_netto;
            document.querySelector(\'input[name="podatek_vat"]\').value = dane.podatek_vat;
            document.querySelector(\'input[name="ilosc_sztuk"]\').value = dane.ilosc_sztuk;
            document.querySelector(\'select[name="status_dostepnosci"]\').value = dane.status_dostepnosci;
            document.querySelector(\'select[name="kategoria_id"]\').value = dane.kategoria_id;
            document.querySelector(\'input[name="gabaryt"]\').value = dane.gabaryt;
            document.querySelector(\'input[name="zdjecie_url"]\').value = dane.zdjecie_url;
            
            // Przewiń do formularza
            document.querySelector(\'.produkty-panel form\').scrollIntoView({ behavior: \'smooth\' });
            
            // Zmień tekst przycisku submit
            document.querySelector(\'.produkty-panel form input[type="submit"]\').value = "Aktualizuj produkt";
        }
        </script>';

        return $output;
    }
}
?> 