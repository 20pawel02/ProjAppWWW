<?php
$nr_indeksu = '169394';
$nrGrupy = '4';
echo 'Paweł Wróbel ' . $nr_indeksu . ' grupa ' . $nrGrupy . '<br /><br />';
echo 'Zastosowanie metody include() <br />';

echo "<strong>Zadanie 2:</strong><br><br>";

// a) Metoda include(), require_once()
echo "a) Metoda include() i require_once(): <br>";
include('plik_z_informacja.php'); // Wstaw plik o nazwie "plik_z_informacja.php"
require_once('plik_z_informacja.php'); // Wstaw plik raz, ignorując kolejne próby
echo "<br>";

// b) Warunki if, else, elseif, switch
echo "b) Warunki if, else, elseif, switch: <br>";
$liczba = 5;
if ($liczba < 3) {
    echo "Liczba jest mniejsza niż 3.<br>";
} elseif ($liczba == 5) {
    echo "Liczba wynosi dokładnie 5.<br>";
} else {
    echo "Liczba jest większa niż 5.<br>";
}

switch ($liczba) {
    case 3:
        echo "Liczba wynosi 3.<br>";
        break;
    case 5:
        echo "Liczba wynosi 5.<br>";
        break;
    default:
        echo "Liczba jest inna niż 3 i 5.<br>";
}
echo "<br>";

// c) Pętla while() i for()
echo "c) Pętla while() i for(): <br>";
$i = 0;
while ($i < 3) {
    echo "while: Iteracja $i<br>";
    $i++;
}

for ($j = 0; $j < 3; $j++) {
    echo "for: Iteracja $j<br>";
}
echo "<br>";

// d) Typy zmiennych $_GET, $_POST, $_SESSION
echo "d) Typy zmiennych \$_GET, \$_POST, \$_SESSION: <br>";
echo 'Przykład $_GET: <a href="labor_169394_X.php?zmienna=wartosc">Kliknij tutaj</a><br>';

session_start();
$_SESSION['sesja'] = 'Wartość sesji';
echo 'Wartość $_SESSION["sesja"]: ' . $_SESSION['sesja'] . "<br>";

?>
