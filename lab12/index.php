<!DOCTYPE html>
<html lang="pl">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="Strona poświęcona lotom w kosmos">
        <title>Loty w Kosmos</title>
       
        <!-- Sprawdzamy, czy jesteśmy na stronie poligon.html -->
        <?php
            $current_page = basename($_SERVER['PHP_SELF']);
            if ($current_page == 'poligon.html') {
                echo '<link rel="stylesheet" href="css/style2.css">';
            } else {
                echo '<link rel="stylesheet" href="css/style.css">';
            }
        ?>

        <!-- includowanie plikow konfiguracyjnych -->
        <?php 
            session_start();
            require_once('admin/admin.php');  
                
            // Regeneracja ID sesji dla bezpieczeństwa
            if (isset($_SESSION['initialized'])) {
                session_regenerate_id(true);
                $_SESSION['initialized'] = true;
            }

            // Regeneracja ID sesji dla bezpieczeństwa
            if (isset($_SESSION['initialized'])){
                session_regenerate_id(true);
                $_SESSION['initialized'] = true;
            }
            
            // includowanie wymaganych plików
            include('cfg.php'); 
            include('php/contact.php');
            include('showpage.php');
            include('php/navbar.php');
            include('php/kategorie.php');
            include('php/produkty.php');
        ?>
    </head>

    <body>
            <!-- Panel nawigacyjny po headerze strony, przejecia pomiedzy podstronami -->
        <header>
            <h1>Loty w Kosmos</h1>
            <div class="navbar">
            <?php echo loadNav(); ?>
        </div>
        </header>


        

        <div class="content">
        <!--  Sprawdzanie, czy plik istnieje, i jeśli tak, włączenie go -->
            <?php
            //-- Inicjalizacja instancji admin --
                static $Admin = null;

            // Check if the 'id' parameter is set in the URL
            if (isset($_GET['idp'])) {
                $alias = $_GET['idp'];
            } else {
                // Set a default value for the alias if 'id' is not set
                $alias = 1; // or any other default value
            }

            switch ($alias) {
                case -1: // admin panel
                    if ($Admin === null){                        
                        $Admin = new Admin($conn);
                    }
                    echo $Admin->LoginAdmin();
                    break;

                case -2:
                    if ($Admin === null){                        
                        $Admin = new Admin($conn);
                    }
                    $Admin->logoutAdmin();
                    break;

                case -3: // edycja strony
                    if ($Admin === null){
                        $Admin = new Admin($conn);
                    }
                    if(!isset($_SESSION['loggedin'])){
                        header('Location: ?idp=-1');
                        exit();
                    }
                    // Check if page ID is provided
                    if (!isset($_GET['id'])) {
                        echo "Nie podano ID strony do edycji.";
                        break;
                    }
                    // Pass the page ID to the EditPage method
                    $_GET['ide'] = intval($_GET['id']);
                    echo $Admin->EditPage();
                    break;

                case -4:
                    if($Admin === null){
                        $Admin = new Admin($conn);
                    }
                    if(!isset($_SESSION['loggedin'])){
                        header('Location: ?idp=-1');
                        exit();
                    }
                    echo $Admin->DeletePage();
                    break;
                
                case -5: // stworzenie strony
                    if($Admin === null){
                        $Admin = new Admin($conn);
                    }
                    if(!isset($_SESSION['loggedin'])){
                        header('Location: ?idp=-1');
                        exit();
                    }
                    echo $Admin->StworzPodstrone();
                    break;

                case -6: // przypomnij haslo
                    $contact = new Contact();
                    echo $contact->PrzypomnijHaslo("169394@student.uwm.edu.pl");
                    echo"<br></br>";
                    break;

                case -7: // kontakt
                    $contact = new Contact();
                    echo "<h2>Kontakt</h2>";
                    echo $contact->WyslijMailaKontakt("169394@student.uwm.edu.pl");
                    echo "<br></br>";
                    break;

                case -8: // zarzadzaj kategoriami
                    if ($Admin === null) {
                        $Admin = new Admin($conn);
                    }
                    if (!isset($_SESSION['loggedin'])) {
                        header('Location: ?idp=-1');
                        exit();
                    }
                    $kategorie = new Kategorie($conn);
                    
                    // Obsługa formularza dodawania kategorii
                    if (isset($_POST['dodaj_kategorie'])) {
                        $nazwa = $_POST['nazwa'];
                        $matka = $_POST['matka'];
                        $kategorie->DodajKategorie($nazwa, $matka);
                    }
                    
                    echo $kategorie->ZarzadzajKategoriami();
                    break;

                case -9: // Zarządzanie produktami
                    if ($Admin === null) {
                        $Admin = new Admin($conn);
                    }
                    if (!isset($_SESSION['loggedin'])) {
                        header('Location: ?idp=-1');
                        exit();
                    }
                    $produkty = new Produkty($conn);
                    echo $produkty->ZarzadzajProduktami();
                    break;

                case -10: // sklep
                    require_once('php/sklep.php');
                    $sklep = new Sklep($conn);
                    echo $sklep->PokazSklep();
                    break;

                case -11: // Edycja produktu
                    if ($Admin === null) {
                        $Admin = new Admin($conn);
                    }
                    if (!isset($_SESSION['loggedin'])) {
                        header('Location: ?idp=-1');
                        exit();
                    }
                    $produkty = new Produkty($conn);
                    echo $produkty->EdytujProdukt($_GET['id']);
                    break;

                case -12: // koszyk
                    require_once('php/koszyk.php');
                    $koszyk = new Koszyk($conn);
                    
                    // Obsługa akcji koszyka
                    if (isset($_POST['action'])) {
                        switch ($_POST['action']) {
                            case 'dodaj':
                                $koszyk->DodajDoKoszyka($_POST['produkt_id'], $_POST['ilosc'] ?? 1);
                                break;
                            case 'usun':
                                $koszyk->UsunZKoszyka($_POST['produkt_id']);
                                break;
                            case 'aktualizuj':
                                $koszyk->AktualizujIlosc($_POST['produkt_id'], $_POST['ilosc']);
                                break;
                        }
                    }
                    
                    echo $koszyk->PokazKoszyk();
                    break;

                default:
                    echo PokazStrone($alias);
                    break;
            };
            ?>
        </div>

        <div class="container">
            <?php
                $nr_indeksu = '169394';
                $nrGrupy = '4';
                echo 'Autor: Paweł Wróbel ' . $nr_indeksu . ' grupa ' . $nrGrupy . ' <br /><br />';
            ?>
        </div>
    </body>
</html>