<!-- Moduł do zarzadzania aplikacjami -->

<?php
include 'cfg.php'; // ladowanie pliku konfigyracyjnego

    class Admin{

        // Function to display the login form
        function FormularzLogowania() {
            return '
            <div class="logowanie">
                <h3 class="heading">Panel CMS:</h3>
                <form method="post" name="LoginForm" enctype="multipart/form-data" action="' . $_SERVER['REQUEST_URI'] . '">
                    <table class="logowanie">
                        <tr><td class="log4_t">[login]</td><td><input type="text" name="login" class="logowanie" required /></td></tr>
                        <tr><td class="log4_t">[haslo]</td><td><input type="password" name="login_pass" class="logowanie" required /></td></tr>
                        <tr><td></td><td><input type="submit" name="x1_submit" class="logowanie" value="zaloguj" /></td></tr>
                    </table>
                </form>
            </div>';
        }

        // Funkcja do sprawdzania logowania
        // @return int 1 - zalogowany, 0 - niezalogowany
        function CheckLogin(){
            // Sprawdź, czy użytkownik jest już zalogowany
            if (isset($_SESSION['loggedin']) && $_SESSION['loggedin']) {
                return 1; // Użytkownik jest już zalogowany
            }

            // Sprawdź, czy formularz przekazał login i hasło
            if (isset($_POST['login']) && isset($_POST['login_pass'])) {
                return $this->CheckLoginCred($_POST['login'], $_POST['login_pass']); // Sprawdzenie danych logowania
            }

            return 0; // Nie ma danych logowania
        }

        // Funkcja do sprawdzania danych logowania
        /*
        Sprawdza, czy dane logowania są poprawne
        @param string $login Login użytkownika
        @param string $pass Hasło użytkownika
        @return int 1 - dane poprawne, 0 - dane niepoprawne
         */
        function CheckLoginCred($login, $pass){
            if ($login == ADMIN_LOGIN && $pass == ADMIN_PASSWORD) { // Sprawdzenie zdefiniowanych danych logowania
                $_SESSION['loggedin'] = true; // Ustawienie zmiennej sesyjnej na true
                return 1; // Pomyślne sprawdzenie
            } else {
                echo "Logowanie się nie powiodło.";
                return 0; // Niepoprawne dane
            }
        }

        // Funkcja do wyświetlania panelu administracyjnego
        /*
         * Wyświetla panel administracyjny
         */
        function LoginAdmin(){
            $status_login = $this->CheckLogin(); // Sprawdź dane logowania

            if ($status_login == 1) {
                echo '<h3 class="h3-admin">Lista Stron</h3>';
                echo $this->ListaPodstron(); // Wyświetl listę podstron
            } else {
                echo $this->FormularzLogowania(); // Wyświetlenie formularza logowania
            }
        }

        
        // Wylogowuje użytkownika
        function logout(){
            // Sprawdzenie i usunięcie zmiennych sesyjnych
            if (isset($_SESSION['loggedin'])) {
                unset($_SESSION['loggedin']);
            }
            // Przy wylogowaniu przekierowywanie na główną strone
            header('Location: ?idp=glowna');
            exit;
        }



        /*/ Check database connection
        if ($conn->connect_error) {
            die("Błąd połączenia z bazą danych: " . $conn->connect_error);
        }*/

        // Function to display a list of subpages
        function ListaPodstron() {
            global $conn; 
            $sql = "SELECT id, page_title FROM page_list"; // zapytanie do bazy, które ma pobrać id i tytuł z tabeli page_list
            $result = $conn->query($sql); // wysłanie zapytania do bazy danych

            if ($result->num_rows > 0) {
                echo "<table border='1' cellpadding='10' cellspacing='0'>";
                echo "<tr>
                        <th>ID</th>
                        <th>Tytuł Podstrony</th>
                        <th>Akcje</th>
                    </tr>";

                while ($row = $result->fetch_assoc()) {
                    $id = $row['id'];
                    $title = htmlspecialchars($row['page_title']); // Safe display of the title
                    echo "<tr>
                            <td>{$id}</td>
                            <td>{$title}</td>
                            <td>
                                <a href='edit.php?id={$id}'>Edytuj</a> | 
                                <a href='delete.php?id={$id}' onclick='return confirm(\"Czy na pewno chcesz usunąć tę podstronę?\")'>Usuń</a>
                            </td>
                        </tr>";
                }

                echo "</table>";
            } else {
                echo "<p>Brak podstron w bazie danych.</p>";
            }
        }

        // Function to allow editing subpages
        function EditPage(){
            // sprawdzenie czy uzytkownik jest zalogowanny
            $status_login = $this->CheckLogin();

            if ($status_login == 1) {
                echo '<h3 class="h3-admin">Strona edycji</h3>';

                // sprawdzenie czy w URL strony znajduje sie parametr ide który jest id edytowanej strony
                if (isset($_GET['ide'])) {

                    // sprawdzenie czy formularz jest wysłany metoda POST i czy wymagane dane sa wprowadzone
                    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_title'], $_POST['edit_content'], $_POST['edit_alias'])) {
                        // przygotwanie danych do zmiany: tytuł, zawartość, aktywność, alias lub id, zachowanie bezpieczenstwa po przez real_escape_string lub intval
                        $title = $GLOBALS['conn']->real_escape_string($_POST['edit_title']);
                        $content = $GLOBALS['conn']->real_escape_string($_POST['edit_content']);
                        $active = isset($_POST['edit_active']) ? 1 : 0;
                        $alias = $GLOBALS['conn']->real_escape_string($_POST['edit_alias']);
                        $id = intval($_GET['ide']);

                        // Zapytanie SQL aktualizujace dane podstrony
                        $query = "UPDATE page_list SET page_title='$title', page_content='$content', status='$active', alias='$alias' WHERE id='$id' LIMIT 1";

                        // sprawdzenie czy jest polaczenie z baza i czy zapytanie zostalo przetworzone poprawnie
                        if ($GLOBALS['conn']->query($query) === TRUE) {
                            echo "Strona została zaktualizowana pomyślnie.";
                            // przekierowanie na panel admina
                            header("Location: ?idp=admin");
                            exit;
                        } else {
                            // komunikat o błedzie podczas aktualizacji
                            echo "Błąd podczas aktualizacji: " . $GLOBALS['conn']->error;
                        }
                    } else {
                        // jesli formularz nie został wysłany pobieram dane strony do edycji
                        $query = "SELECT * FROM page_list WHERE id='" . intval($_GET['ide']) . "' LIMIT 1";
                        $result = $GLOBALS['conn']->query($query);

                        // sprawdzam czy strona o wskazanym id istnieje
                        if ($result && $result->num_rows > 0) {
                            $row = $result->fetch_assoc();

                            return '
                                    <div class="edit-container">
                                        <h3 class="edit-title">Edycja Strony</h3>
                                        <form method="post" action="' . $_SERVER['REQUEST_URI'] . '">
                                            <div class="form-group">
                                                <label for="edit_title">Tytuł:</label>
                                                <input type="text" id="edit_title" name="edit_title" value="' . htmlspecialchars($row['page_title']) . '" required />
                                            </div>
                                            <div class="form-group">
                                                <label for="edit_content">Zawartość:</label>
                                                <textarea id="edit_content" name="edit_content" required>' . htmlspecialchars($row['page_content']) . '</textarea>
                                            </div>
                                            <div class="form-group-inline">
                                                <label for="edit_active">Aktywna:</label>
                                                <input type="checkbox" id="edit_active" name="edit_active"' . ($row['status'] ? ' checked' : '') . ' />
                                            </div>
                                            <div class="form-group">
                                                <label for="edit_alias">Alias:</label>
                                                <input type="text" id="edit_alias" name="edit_alias" value="' . htmlspecialchars($row['alias']) . '" required />
                                            </div>
                                            <div class="form-group">
                                                <input type="submit" class="submit-button" value="Zapisz zmiany" />
                                            </div>
                                        </form>
                                    </div>';
                        } else {
                            return "Nie znaleziono strony do edycji.";
                        }
                    }
                } else {
                    return "Nie podano ID strony do edycji.";
                }
            } else {
                return $this->FormularzLogowania(); // Jeśli nie jesteś zalogowany, wyświetl formularz logowania
            }
    }

        // Function to add a new subpage
        function StworzPodstrone() {
            // Handle form submission
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $new_title = $_POST['title'];
                $new_content = $_POST['content'];
                $new_status = isset($_POST['status']) ? 1 : 0; // Checkbox: checked = 1, unchecked = 0

                // Add data to the database
                $insert_sql = "INSERT INTO page_list (page_title, page_content, status) VALUES (?, ?, ?)";
                $insert_stmt = $conn->prepare($insert_sql);
                $insert_stmt->bind_param('ssi', $new_title, $new_content, $new_status);

                if ($insert_stmt->execute()) {
                    echo "<p>Nowa podstrona została dodana.</p>";
                } else {
                    echo "<p>Wystąpił błąd podczas dodawania nowej podstrony.</p>";
                }
            }

            // Add subpage form
            echo "<h2>Dodaj Nową Podstronę</h2>";
            echo "<form method='POST' action=''>
                    <label for='title'>Tytuł Podstrony:</label><br>
                    <input type='text' id='title' name='title' required><br><br>

                    <label for='content'>Treść Podstrony:</label><br>
                    <textarea id='content' name='content' rows='4' cols='50' required></textarea><br><br>

                    <label for='status'>Aktywna:</label>
                    <input type='checkbox' id='status' name='status' value='1'><br><br>

                    <input type='submit' value='Dodaj Podstronę'>
                </form>";
        }

        // Function to delete a subpage
        function DeletePage(){
            // Sprawdź, czy użytkownik jest zalogowany
            $status_login = $this->CheckLogin();

            if ($status_login == 1) { // jesli zalogowano to...
                // Sprawdź, czy podano ID do usunięcia
                if (isset($_GET['idd'])) {
                    // intval słuzacy do zabezpieczenia przed SQL Injection
                    $id = intval($_GET['idd']);

                    // Zapytanie do usunięcia podstrony
                    $query = "DELETE FROM page_list WHERE id='$id' LIMIT 1";

                    // sprawdzenie czy jest polaczenie z baza i czy zapytanie zostalo przetworzone poprawnie
                    if ($GLOBALS['conn']->query($query) === TRUE) {
                        echo "Strona została usunięta pomyślnie.";
                        header("Location: ?idp=admin"); // Przekierowanie po udanym usunięciu na panel admina
                        exit;
                    } else {
                        echo "Błąd podczas usuwania: " . $GLOBALS['conn']->error;
                    }
                } else {
                    echo "Nie podano ID strony do usunięcia.";
                }
            } else {
                return $this->FormularzLogowania(); // Jeśli nie jesteś zalogowany, wyświetl formularz logowania
            }
        }
    }
?>