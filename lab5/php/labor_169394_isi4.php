<?php
$nr_indeksu = '169394';
$nrGrupy = '4';
echo 'Paweł Wróbel '.$nr_indeksu.' grupa '.$nrGrupy.' <br /><br />';
echo 'Zastosowanie metody include() <br />';

echo "<br /><b>a) Metoda include() oraz require_once()</b><br />";
echo "include() - ładuje zawartość pliku PHP w miejscu, w którym jest wywołana.<br />";
echo "require_once() - ładuje plik tylko raz. Jeśli został wcześniej załadowany, to ignoruje dalsze wywołania.<br />";

echo "<br /><b>b) Przykład warunków if, else, elseif, switch</b><br />";
$liczba = 5;
if ($liczba > 10) {
    echo "Liczba jest większa niż 10.<br />";
} elseif ($liczba == 5) {
    echo "Liczba jest równa 5.<br />";
} else {
    echo "Liczba jest mniejsza niż 5.<br />";
}

$kolor = 'zielony';
switch ($kolor) {
    case 'czerwony':
        echo "Kolor to czerwony.<br />";
        break;
    case 'zielony':
        echo "Kolor to zielony.<br />";
        break;
    default:
        echo "Kolor nieznany.<br />";
        break;
}

echo "<br /><b>c) Przykład pętli while i for</b><br />";
$i = 0;
while ($i < 3) {
    echo "Pętla while - iteracja $i <br />";
    $i++;
}

for ($j = 0; $j < 3; $j++) {
    echo "Pętla for - iteracja $j <br />";
}


echo "<br /><b>d) Typy zmiennych \$_GET, \$_POST, \$_SESSION</b><br />";
echo "\$_GET - umożliwia odbieranie danych przekazanych w URL, widocznych w przeglądarce.<br />";
echo "\$_POST - służy do przesyłania danych z formularzy, które są ukryte przed użytkownikiem.<br />";
echo "\$_SESSION - pozwala na przechowywanie danych użytkownika w sesji, dostępnych na różnych podstronach.<br />";
?>

