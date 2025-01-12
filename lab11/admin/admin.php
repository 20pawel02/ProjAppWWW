    <!-- Moduł do zarzadzania aplikacjami -->
<?php
include 'cfg.php'; // ladowanie pliku konfigyracyjnego
    class Admin{
        private $conn;

        public function __construct($conn = null) {
            $this->conn = $conn;
        }

        // Function to display the login form
        function FormularzLogowania() {
            return '
            <div class="logowanie">
                <h3 class="heading">Panel CMS:</h3>
                <form method="post" name="LoginForm" enctype="multipart/form-data" action="' . $_SERVER['REQUEST_URI'] . '">
                    <table class="logowanie">
                        <tr>
                            <td class="log4_t">Login:</td>
                            <td><input type="text" name="login" class="logowanie" required /></td>
                        </tr>
                        <tr>
                            <td class="log4_t">Hasło:</td>
                            <td><input type="password" name="login_pass" class="logowanie" required /></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td><input type="submit" name="x1_submit" class="logowanie" value="Zaloguj" /></td>
                        </tr>
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

        // Function to handle admin logout
        function logoutAdmin() {
            // Destroy the session
            session_start();
            session_destroy();
            
            // Redirect to the main page
            header("Location: index.php?idp=1");
            exit();
        }

        // Funkcja do wyświetlania panelu administracyjnego
        // Wyświetla panel administracyjny

        function LoginAdmin(){
            $status_login = $this->CheckLogin(); // Sprawdź dane logowania

            if ($status_login == 1) {
                echo '<div style="text-align: right; max-width: 790px; margin: 0 auto; padding: 10px;">';
                echo '<a href="?idp=-2" style="background-color: #333; color: white; padding: 8px 15px; text-decoration: none; border-radius: 4px;">Wyloguj</a>';
                echo '</div>';
                
                // Menu administracyjne
                echo '<div class="admin-menu" style="max-width: 790px; margin: 20px auto; padding: 10px; background-color: #f5f5f5; border-radius: 4px;">';
                echo '<a href="?idp=-5" style="margin-right: 15px;">Dodaj nową stronę</a>';
                echo '<a href="?idp=-8" style="margin-right: 15px;">Zarządzaj kategoriami</a>';
                echo '<a href="?idp=-9" style="margin-right: 15px;">Zarządzaj produktami</a>';
                echo '</div>';
                
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
                                <a href='index.php?idp=-3&id={$id}'>Edytuj</a> | 
                                <a href='../index.php?idp=-4&id={$id}' onclick='return confirm(\"Czy na pewno chcesz usunąć tę podstronę?\")'>Usuń</a>
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
                            header("Location: ?idp=-1   ");
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
                                                <label for="edit_title">Tytuł strony</label>
                                                <input type="text" id="edit_title" name="edit_title" value="' . htmlspecialchars($row['page_title']) . '" required />
                                            </div>
                                            
                                            <div class="form-group">
                                                <label for="edit_content">Treść strony</label>
                                                <textarea id="edit_content" name="edit_content" rows="10" required>' . htmlspecialchars($row['page_content']) . '</textarea>
                                            </div>
                                            
                                            <div class="form-group">
                                                <label for="edit_alias">Alias strony</label>
                                                <input type="text" id="edit_alias" name="edit_alias" value="' . (isset($row['page_alias']) ? htmlspecialchars($row['page_alias']) : '') . '" required />
                                            </div>
                                            
                                            <div class="form-group">
                                                <label>
                                                    <input type="checkbox" name="edit_active" ' . ($row['status'] ? 'checked' : '') . ' /> 
                                                    Strona aktywna
                                                </label>
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

        // Metoda do dodawania nowej podstrony
        function DodajNowaPodstrone() {
            // Sprawdzenie, czy użytkownik jest zalogowany
            if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
                return 'Brak dostępu. Zaloguj się.';
            }

            // Obsługa zapisu nowej podstrony
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_page'])) {
                // Pobierz dane z formularza
                $new_title = $_POST['page_title'] ?? '';
                $new_content = $_POST['page_content'] ?? '';
                $new_status = isset($_POST['page_status']) ? 1 : 0;
                $new_alias = $_POST['page_alias'] ?? '';

                // Walidacja danych
                if (empty($new_title)) {
                    return 'Błąd: Tytuł strony nie może być pusty.';
                }

                // Sprawdzenie unikalności aliasu
                $check_stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM page_list WHERE page_alias = ?");
                $check_stmt->bind_param("s", $new_alias);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();
                $check_row = $check_result->fetch_assoc();
                $check_stmt->close();

                if ($check_row['count'] > 0) {
                    return 'Błąd: Podany alias strony już istnieje. Wybierz inny.';
                }

                // Przygotowanie zapytania SQL INSERT
                $stmt = $this->conn->prepare("INSERT INTO page_list (page_title, page_content, status, page_alias) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssss", $new_title, $new_content, $new_status, $new_alias);

                // Wykonanie zapytania
                if ($stmt->execute()) {
                    $new_page_id = $stmt->insert_id;
                    $stmt->close();
                    return 'Nowa strona została pomyślnie dodana. ID strony: ' . $new_page_id;
                } else {
                    return 'Błąd podczas dodawania strony: ' . $stmt->error;
                }
            }

            // Formularz dodawania nowej podstrony
            $form = '
            <div class="add-page-form">
                <h2>Dodaj nową stronę</h2>
                <form method="post" action="">
                    <div class="form-group">
                        <label for="page_title">Tytuł strony:</label>
                        <input type="text" id="page_title" name="page_title" required placeholder="Wprowadź tytuł strony">
                    </div>
                    
                    <div class="form-group">
                        <label for="page_alias">Alias strony:</label>
                        <input type="text" id="page_alias" name="page_alias" required placeholder="Wprowadź unikalny alias (np. kontakt, onas)">
                        <small>Alias będzie używany w adresie URL strony</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="page_content">Treść strony:</label>
                        <textarea id="page_content" name="page_content" rows="10" cols="50" placeholder="Wprowadź treść strony"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="page_status">
                            <input type="checkbox" id="page_status" name="page_status" checked>
                            Strona aktywna
                        </label>
                    </div>
                    
                    <div class="form-group">
                        <input type="submit" name="add_page" value="Dodaj stronę">
                    </div>
                </form>
            </div>';

            return $form;
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
                $insert_stmt = $this->conn->prepare($insert_sql);
                $insert_stmt->bind_param('ssi', $new_title, $new_content, $new_status);

                if ($insert_stmt->execute()) {
                    echo "<p>Nowa podstrona została dodana.</p>";
                } else {
                    echo "<p>Wystąpił błąd podczas dodawania nowej podstrony.</p>";
                }
            }

            // Add subpage form
            $output = '<div class="form-container">
                <h2>Tworzenie nowej podstrony</h2>
                <form method="post" action="'.$_SERVER['REQUEST_URI'].'">
                    <div class="form-group">
                        <label for="title">Tytuł Podstrony:</label>
                        <input type="text" id="title" name="title" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="content">Treść Podstrony:</label>
                        <textarea id="content" name="content" rows="4" cols="50" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="status">Aktywna:</label>
                        <input type="checkbox" id="status" name="status" value="1">
                    </div>
                    
                    <input type="submit" value="Dodaj Podstronę">
                </form>
            </div>';

            return $output;
        }

        // Function to delete a subpage
        function DeletePage() {
            // Sprawdza, czy użytkownik jest zalogowany
            $status_login = $this->CheckLogin(); 
        
            if ($status_login == 1) { 
    
                if (isset($_GET['idd'])) {
                    $id = intval($_GET['idd']); 
                    // Tworzy zapytanie do bazy danych o usunięcie strony
                    $query = "DELETE FROM page_list WHERE id='$id' LIMIT 1";
    
                    // Wykonuje zapytanie i sprawdza, czy się powiodło
                    if ($GLOBALS['conn']->query($query) === TRUE) {
                        echo "Strona została usunięta pomyślnie.";
                        // Przekierowuje na panel admina
                        header("Location: ?idp=-1"); 
                        exit;
                    } else {
                        echo "Błąd podczas usuwania: " . $GLOBALS['conn']->error;
                    }
                } else {
                    echo "Nie podano ID strony do usunięcia.";
                }
            } else {
                // Wyświetla formularz logowania, jeśli nie jest zalogowany
                return $this->FormularzLogowania(); 
            }
        }

        // Metoda zarządzania kategoriami produktów
        function ZarzadzajKategoriami() {
            // Sprawdzenie, czy użytkownik jest zalogowany
            if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
                return 'Brak dostępu. Zaloguj się.';
            }

            // Obsługa akcji związanych z kategoriami
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Dodawanie kategorii
                if (isset($_POST['add_category'])) {
                    $name = $_POST['category_name'] ?? '';
                    $mother_id = intval($_POST['mother_category'] ?? 0);
                    $description = $_POST['category_description'] ?? '';

                    if (!empty($name)) {
                        $query = "INSERT INTO product_categories (name, mother_id, description) 
                                  VALUES ('" . $GLOBALS['conn']->real_escape_string($name) . "', 
                                          $mother_id, 
                                          '" . $GLOBALS['conn']->real_escape_string($description) . "')";
                        
                        if ($GLOBALS['conn']->query($query)) {
                            $message = "Kategoria została dodana pomyślnie.";
                        } else {
                            $message = "Błąd podczas dodawania kategorii: " . $GLOBALS['conn']->error;
                        }
                    }
                }

                // Edycja kategorii
                if (isset($_POST['edit_category'])) {
                    $category_id = intval($_POST['category_id'] ?? 0);
                    $name = $_POST['category_name'] ?? '';
                    $mother_id = intval($_POST['mother_category'] ?? 0);
                    $description = $_POST['category_description'] ?? '';

                    if ($category_id > 0 && !empty($name)) {
                        $query = "UPDATE product_categories 
                                  SET name = '" . $GLOBALS['conn']->real_escape_string($name) . "', 
                                      mother_id = $mother_id, 
                                      description = '" . $GLOBALS['conn']->real_escape_string($description) . "' 
                                  WHERE id = $category_id";
                        
                        if ($GLOBALS['conn']->query($query)) {
                            $message = "Kategoria została zaktualizowana pomyślnie.";
                        } else {
                            $message = "Błąd podczas aktualizacji kategorii: " . $GLOBALS['conn']->error;
                        }
                    }
                }

                // Usuwanie kategorii
                if (isset($_POST['delete_category'])) {
                    $category_id = intval($_POST['category_id'] ?? 0);
                    $recursive = isset($_POST['recursive_delete']) ? 1 : 0;

                    if ($category_id > 0) {
                        if ($recursive) {
                            // Rekurencyjne usuwanie podkategorii
                            $this->usunPodkategorie($category_id);
                        }

                        $query = "DELETE FROM product_categories WHERE id = $category_id";
                        
                        if ($GLOBALS['conn']->query($query)) {
                            $message = "Kategoria została usunięta pomyślnie.";
                        } else {
                            $message = "Błąd podczas usuwania kategorii: " . $GLOBALS['conn']->error;
                        }
                    }
                }
            }

            // Pobieranie listy kategorii do formularza
            $categories_query = "SELECT id, name, mother_id FROM product_categories ORDER BY mother_id, name";
            $categories_result = $GLOBALS['conn']->query($categories_query);
            $categories = [];
            $root_categories = [];

            while ($row = $categories_result->fetch_assoc()) {
                $categories[$row['id']] = $row;
                if ($row['mother_id'] == 0) {
                    $root_categories[] = $row;
                }
            }

            // Generowanie formularza zarządzania kategoriami
            $form = '
            <div class="edit-container">
                <h3 class="edit-title">Zarządzanie Kategoriami Produktów</h3>
                
                ' . (!empty($message) ? '<div class="alert alert-info">' . htmlspecialchars($message) . '</div>' : '') . '
                
                <div class="category-management">
                    <div class="category-list">
                        <h4>Istniejące Kategorie</h4>
                        <ul class="category-tree">';
            
            // Generowanie drzewa kategorii
            foreach ($root_categories as $root) {
                $form .= '<li>' . htmlspecialchars($root['name']);
                
                // Dodaj podkategorie
                $form .= $this->generujPodkategorie($categories, $root['id']);
                
                $form .= '</li>';
            }
            
            $form .= '</ul>
                    </div>
                    
                    <div class="category-forms">
                        <div class="add-category-form">
                            <h4>Dodaj Kategorię</h4>
                            <form method="post" action="' . $_SERVER['REQUEST_URI'] . '">
                                <div class="form-group">
                                    <label for="category_name">Nazwa Kategorii</label>
                                    <input type="text" id="category_name" name="category_name" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="mother_category">Kategoria Nadrzędna</label>
                                    <select id="mother_category" name="mother_category">
                                        <option value="0">Kategoria Główna</option>';
            
            foreach ($root_categories as $root) {
                $form .= '<option value="' . $root['id'] . '">' . htmlspecialchars($root['name']) . '</option>';
            }
            
            $form .= '</select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="category_description">Opis Kategorii</label>
                                    <textarea id="category_description" name="category_description"></textarea>
                                </div>
                                
                                <input type="submit" name="add_category" value="Dodaj Kategorię" class="submit-button">
                            </form>
                        </div>
                        
                        <div class="edit-category-form">
                            <h4>Edytuj Kategorię</h4>
                            <form method="post" action="' . $_SERVER['REQUEST_URI'] . '">
                                <div class="form-group">
                                    <label for="edit_category_select">Wybierz Kategorię</label>
                                    <select id="edit_category_select" name="category_id" required>
                                        <option value="">Wybierz kategorię</option>';
            
            foreach ($categories as $category) {
                $form .= '<option value="' . $category['id'] . '">' . 
                         ($category['mother_id'] == 0 ? '' : '— ') . 
                         htmlspecialchars($category['name']) . 
                         '</option>';
            }
            
            $form .= '</select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="edit_category_name">Nowa Nazwa Kategorii</label>
                                    <input type="text" id="edit_category_name" name="category_name" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="edit_mother_category">Nowa Kategoria Nadrzędna</label>
                                    <select id="edit_mother_category" name="mother_category">
                                        <option value="0">Kategoria Główna</option>';
            
            foreach ($root_categories as $root) {
                $form .= '<option value="' . $root['id'] . '">' . htmlspecialchars($root['name']) . '</option>';
            }
            
            $form .= '</select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="edit_category_description">Nowy Opis Kategorii</label>
                                    <textarea id="edit_category_description" name="category_description"></textarea>
                                </div>
                                
                                <input type="submit" name="edit_category" value="Aktualizuj Kategorię" class="submit-button">
                            </form>
                        </div>
                        
                        <div class="delete-category-form">
                            <h4>Usuń Kategorię</h4>
                            <form method="post" action="' . $_SERVER['REQUEST_URI'] . '">
                                <div class="form-group">
                                    <label for="delete_category_select">Wybierz Kategorię do Usunięcia</label>
                                    <select id="delete_category_select" name="category_id" required>
                                        <option value="">Wybierz kategorię</option>';
            
            foreach ($categories as $category) {
                $form .= '<option value="' . $category['id'] . '">' . 
                         ($category['mother_id'] == 0 ? '' : '— ') . 
                         htmlspecialchars($category['name']) . 
                         '</option>';
            }
            
            $form .= '</select>
                                </div>
                                
                                <div class="form-group">
                                    <label>
                                        <input type="checkbox" name="recursive_delete"> 
                                        Usuń również podkategorie
                                    </label>
                                </div>
                                
                                <input type="submit" name="delete_category" value="Usuń Kategorię" class="submit-button">
                            </form>
                        </div>
                    </div>
                </div>
            </div>';

            return $form;
        }

        // Metoda generująca podkategorie rekurencyjnie
        private function generujPodkategorie($categories, $mother_id) {
            $subcategories = array_filter($categories, function($cat) use ($mother_id) {
                return $cat['mother_id'] == $mother_id;
            });

            if (empty($subcategories)) {
                return '';
            }

            $output = '<ul>';
            foreach ($subcategories as $subcategory) {
                $output .= '<li>' . htmlspecialchars($subcategory['name']);
                $output .= $this->generujPodkategorie($categories, $subcategory['id']);
                $output .= '</li>';
            }
            $output .= '</ul>';

            return $output;
        }

        // Metoda usuwająca podkategorie rekurencyjnie
        private function usunPodkategorie($mother_id) {
            $query = "SELECT id FROM product_categories WHERE mother_id = " . intval($mother_id);
            $result = $GLOBALS['conn']->query($query);

            while ($row = $result->fetch_assoc()) {
                // Rekurencyjne usuwanie podkategorii
                $this->usunPodkategorie($row['id']);
                
                // Usuwanie konkretnej podkategorii
                $delete_query = "DELETE FROM product_categories WHERE id = " . intval($row['id']);
                $GLOBALS['conn']->query($delete_query);
            }
        }
    }
?>
