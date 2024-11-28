<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Strona poświęcona lotom w kosmos">
    <title>Loty w Kosmos</title>
    <link rel="stylesheet" href="css/style.css">

    <?php 
        include('cfg.php'); 
        include('admin/admin.php'); 
        include('php/contact.php'); 
    ?>

</head>
<body>

<?php
if ($_GET['idp'] == 'glowna') {
    $strona = 'html/glowna.html';
} elseif ($_GET['idp'] == 'historia') {
    $strona = 'html/historia.html';
} elseif ($_GET['idp'] == 'misje') {
    $strona = 'html/misje.html';
} elseif ($_GET['idp'] == 'zwierzeta') {
    $strona = 'html/zwierzeta.html';
} elseif ($_GET['idp'] == 'kontakt') {
    $strona = 'html/kontakt.html';
} elseif ($_GET['idp'] == 'filmy') {
    $strona = 'html/filmy.html';
} elseif ($_GET['idp'] == 'poligon') {
    $strona = 'html/poligon.html';
} else {
    $strona = 'html/404.html';  // Plik z informacją o błędzie
}
?>

<header>
    <h1>Loty w Kosmos</h1>
    <nav>
        <ul>
            <li><a href="index.php?idp=glowna">Strona Główna</a></li>
            <li><a href="index.php?idp=historia">Historia Lotów</a></li>
            <li><a href="index.php?idp=misje">Misje Kosmiczne</a></li>
            <li><a href="index.php?idp=zwierzeta">Zwierzęta w kosmosie</a></li>
            <li><a href="index.php?idp=filmy">Filmy</a></li>
            <li><a href="index.php?idp=kontakt">Kontakt</a></li>
            <li><a href="index.php?idp=poligon">poligon</a></li>
        </ul>
    </nav>
</header>

<div class="content">
    <?php
    if (file_exists($strona)) {
        include($strona);
    } else {
        echo 'Zabłądziłeś w lesie, zalecamy wracanie się po swoich śladach.';
    }
    ?>
</div>
<div class="container">
<?php
error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);

$nr_indeksu = '169394';
$nrGrupy = '4';
echo 'Autor: Paweł Wróbel ' . $nr_indeksu . ' grupa ' . $nrGrupy . ' <br /><br />';
?>
</div>

</body>
</html>
