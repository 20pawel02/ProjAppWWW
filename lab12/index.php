<!DOCTYPE html>
<html lang="pl">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="Strona poświęcona lotom w kosmos">
        <title>Loty w Kosmos</title>
       
        <!-- Check if we are on the poligon.html page -->
        <?php
            $current_page = $_GET['idp'] ?? 1; // Set default value for current page
            echo '<link rel="stylesheet" href="css/' . ($current_page == 7 ? 'style2.css' : 'style.css') . '">'; // Load appropriate CSS file
        ?>

        <!-- Include configuration files -->
        <?php 
            session_start(); // Start session
            require_once('admin/admin.php');  // Include admin class
                
            // Regenerate session ID for security
            if (!isset($_SESSION['initialized'])) {
                $_SESSION['initialized'] = true; // Set initialized flag
            } else {
                session_regenerate_id(true); // Regenerate session ID
            }

            // Include required files
            include('cfg.php'); 
            include('php/contact.php');
            include('showpage.php');
            include('php/navbar.php');
            include('php/kategorie.php');
            include('php/produkty.php');
        ?>
    </head>

    <body>
        <!-- Navigation panel after the header, transitions between subpages -->
        <header>
            <h1>Loty w Kosmos</h1>
            <div class="navbar">
            <?php echo loadNav(); // Load navigation menu ?>
        </div>
        </header>

        <div class="content">
        <!-- Check if the file exists, and if so, include it -->
            <?php
            //-- Initialize admin instance --
                static $Admin = null;

            // Check if the 'id' parameter is set in the URL
            if (isset($_GET['idp'])) {
                $alias = $_GET['idp']; // Get the alias from the URL
            } else {
                // Set a default value for the alias if 'id' is not set
                $alias = 1; // or any other default value
            }

            // Switch statement to handle different page requests
            switch ($alias) {
                case -1: // admin panel
                case -2: // logout
                    if ($Admin === null) {                        
                        $Admin = new Admin($conn); // Create admin instance
                    }
                    if ($alias == -1) {
                        echo $Admin->LoginAdmin(); // Show admin login
                    } else {
                        $Admin->logoutAdmin(); // Handle logout
                    }
                    break;

                case -3: // edit page
                case -4: // delete page
                case -5: // create page
                    if ($Admin === null) {
                        $Admin = new Admin($conn); // Create admin instance
                    }
                    if (!isset($_SESSION['loggedin'])) {
                        header('Location: ?idp=-1'); // Redirect to login if not logged in
                        exit();
                    }
                    if ($alias == -3) {
                        if (!isset($_GET['id'])) {
                            echo "Nie podano ID strony do edycji."; // No page ID provided
                            break;
                        }
                        $_GET['ide'] = intval($_GET['id']); // Get page ID
                        echo $Admin->EditPage(); // Show edit page
                    } elseif ($alias == -4) {
                        echo $Admin->DeletePage(); // Handle delete page
                    } else {
                        echo $Admin->StworzPodstrone(); // Show create page form
                    }
                    break;

                case -6: // password recovery
                    $contact = new Contact(); // Create contact instance
                    echo $contact->PrzypomnijHaslo("169394@student.uwm.edu.pl"); // Show password recovery form
                    break;

                case -7: // contact
                    $contact = new Contact(); // Create contact instance
                    echo "<h2>Kontakt</h2>";
                    echo $contact->WyslijMailaKontakt("169394@student.uwm.edu.pl"); // Show contact form
                    break;

                case -8: // manage categories
                    if ($Admin === null) {
                        $Admin = new Admin($conn); // Create admin instance
                    }
                    if (!isset($_SESSION['loggedin'])) {
                        header('Location: ?idp=-1'); // Redirect to login if not logged in
                        exit();
                    }
                    $kategorie = new Kategorie($conn); // Create category instance
                    if (isset($_POST['dodaj_kategorie'])) {
                        $kategorie->DodajKategorie($_POST['nazwa'], $_POST['matka']); // Add new category
                    }
                    echo $kategorie->ZarzadzajKategoriami(); // Show category management
                    break;

                case -9: // manage products
                    if ($Admin === null) {
                        $Admin = new Admin($conn); // Create admin instance
                    }
                    if (!isset($_SESSION['loggedin'])) {
                        header('Location: ?idp=-1'); // Redirect to login if not logged in
                        exit();
                    }
                    $produkty = new Produkty($conn); // Create product instance
                    echo $produkty->ZarzadzajProduktami(); // Show product management
                    break;

                case -10: // shop
                    require_once('php/sklep.php'); // Include shop file
                    $sklep = new Sklep($conn); // Create shop instance
                    echo $sklep->PokazSklep(); // Show shop
                    break;

                case -11: // edit product
                    if ($Admin === null) {
                        $Admin = new Admin($conn); // Create admin instance
                    }
                    if (!isset($_SESSION['loggedin'])) {
                        header('Location: ?idp=-1'); // Redirect to login if not logged in
                        exit();
                    }
                    $produkty = new Produkty($conn); // Create product instance
                    echo $produkty->EdytujProdukt($_GET['id']); // Show edit product form
                    break;

                case -12: // cart
                    require_once('php/koszyk.php'); // Include cart file
                    $koszyk = new Koszyk($conn); // Create cart instance
                    if (isset($_POST['action'])) {
                        switch ($_POST['action']) {
                            case 'dodaj':
                                $koszyk->DodajDoKoszyka($_POST['produkt_id'], $_POST['ilosc'] ?? 1); // Add product to cart
                                break;
                            case 'usun':
                                $koszyk->UsunZKoszyka($_POST['produkt_id']); // Remove product from cart
                                break;
                            case 'aktualizuj':
                                $koszyk->AktualizujIlosc($_POST['produkt_id'], $_POST['ilosc']); // Update product quantity
                                break;
                            case 'czysc':
                                $_SESSION['koszyk'] = []; // Clear the cart
                                break;
                        }
                    }
                    echo $koszyk->PokazKoszyk(); // Show cart
                    break;

                default:
                    echo PokazStrone($alias); // Show page based on alias
                    break;
            };
            ?>
        </div>
        <footer>
            <p>2024 Loty Kosmiczne. Strona wykonana na zaliczenie przedmiotu: programowanie aplikacji www</p>
            <p>Autor: Paweł Wróbel 169394</p>
        </footer>
    </body>
</html>