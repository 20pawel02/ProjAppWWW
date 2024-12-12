<!DOCTYPE html>
<html lang="pl">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="Strona poświęcona lotom w kosmos">
        <title>Loty w Kosmos</title>
        <link rel="stylesheet" href="css/style.css">

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
                case -1:
                    if ($Admin === null){                        
                        $Admin = new Admin();
                    }
                    echo $Admin->LoginAdmin();
                    break;

                case -2:
                    if ($Admin === null){                        
                        $Admin = new Admin();
                    }
                    $Admin->logoutAdmin();
                    break;

                case -3:
                    if ($Admin === null){
                        $Admin = new Admin();
                    }
                    if(!isset($_SESSION['loggedin'])){
                        header('Location: ?idp=admin');
                        exit();
                    }
                    echo $Admin->EditPage();
                    break;

                case -4:
                    if($Admin === null){
                        $Admin = new Admin();
                    }
                    if(!isset($_SESSION['loggedin'])){
                        header('Location: ?idp=admin');
                        exit();
                    }
                    echo $Admin->DeletePage();
                    break;
                
                case -5:
                    if($Admin === null){
                        $Admin = new Admin();
                    }
                    if(!isset($_SESSION['loggedin'])){
                        header('Location: ?idp=admin');
                        exit();
                    }
                    echo $Admin->StworzPodstronen();
                    break;

                case -6:
                    $contact = new Contact();
                    echo "<h2>Odzyskanie hasła</h2>";
                    echo $contact->PrzypomnijHaslo("169394@student.uwm.edu.pl");
                    echo"<br></br>";
                    break;

                case -7:
                    $contact = new Contact();
                    echo "<h2>Kontakt</h2>";
                    echo $contact->WyslijMailaKontakt("169394@student.uwm.edu.pl");
                    echo "<br></br>";
                    break;

                default:
                    echo PokazStrone($alias);
                    break;
            };
            ?>
        </div>

        <div class="container">
            <?php
                error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING); // Ustawianie raportowania błędów

                $nr_indeksu = '169394';
                $nrGrupy = '4';
                echo 'Autor: Paweł Wróbel ' . $nr_indeksu . ' grupa ' . $nrGrupy . ' <br /><br />';
            ?>
        </div>
    </body>
</html>