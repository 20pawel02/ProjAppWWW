<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Strona poświęcona lotom w kosmos">
    <title>Loty w Kosmos</title>

    <?php
    // Ładowanie odpowiednich arkuszy stylów
    if ($_GET['idp'] == 'podstrona5') {
        echo '<link rel="stylesheet" href="css/style2.css" />'; // Załaduj style2.css dla lab2
    } else {
        echo '<link rel="stylesheet" href="css/style.css" />'; // Domyślnie ładuj style.css
    }
    ?>

    <link rel="stylesheet" href="../css/style.css">
    <script src="js/jquery-3.7.1.min.js"></script>
</head>
<body>

<?php
error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);

if ($_GET['idp'] == '') {
    $strona = '/html/index.html';
} elseif ($_GET['idp'] == 'podstrona1') {
    $strona = '/html/historia.html';
} elseif ($_GET['idp'] == 'podstrona2') {
    $strona = '/html/misje.html';
} elseif ($_GET['idp'] == 'podstrona3') {
    $strona = '/html/zwierzeta.html';
} elseif ($_GET['idp'] == 'podstrona4') {
    $strona = '/html/kontakt.html';
} elseif ($_GET['idp'] == 'podstrona5') {
    $strona = '/html/poligon.html';
} elseif ($_GET['idp'] == 'filmy') {
    $strona = '/html/filmy.html';
} else {
    $strona = '/html/404.html';
}

/* Autor i informacje o projekcie */
$nr_indeksu = '169394';
$nrGrupy = '4';
echo 'Autor: Paweł Wróbel ' . $nr_indeksu . ' grupa ' . $nrGrupy . '<br /><br />';
?>

<header>
    <h1>Loty w Kosmos</h1>
    <nav>
        <ul>
            <li><a href="index.html?idp">Strona Główna</a></li>
            <li><a href="index.html?idp=podstrona1">Historia Lotów</a></li>
            <li><a href="index.html?idp=podstrona2">Misje Kosmiczne</a></li>
            <li><a href="index.html?idp=podstrona3">Zwierzęta w kosmosie</a></li>
            <li><a href="index.html?idp=podstrona4">Kontakt</a></li>
            <li><a href="index.html?idp=podstrona5">poligon</a></li>
            <li><a href="index.html?idp=filmy">Filmy</a></li>
        </ul>
    </nav>
</header>

<main>
    <div class="container">
        <?php
        if (file_exists($strona)) {
            include($strona);
        } else {
            echo "Strona nie istnieje.";
        }
        ?>
    </div>
</main>
</body>
</html>
