<?php
$nr_indeksu = '169394'; // Student index number
$nrGrupy = '4'; // Group number
echo 'Paweł Wróbel ' . $nr_indeksu . ' grupa ' . $nrGrupy . '<br /><br />'; // Display student information
echo 'Zastosowanie metody include() <br />'; // Introduction to include method

echo "<strong>Zadanie 2:</strong><br><br>";

// a) Metoda include(), require_once()
echo "a) Metoda include() i require_once(): <br>";
include('plik_z_informacja.php'); // Include file "plik_z_informacja.php"
require_once('plik_z_informacja.php'); // Include file once, ignoring subsequent attempts
echo "<br>";

// b) Warunki if, else, elseif, switch
echo "b) Warunki if, else, elseif, switch: <br>";
$liczba = 5; // Initialize a variable
if ($liczba < 3) {
    echo "Liczba jest mniejsza niż 3.<br>"; // Condition for less than 3
} elseif ($liczba == 5) {
    echo "Liczba wynosi dokładnie 5.<br>"; // Condition for equal to 5
} else {
    echo "Liczba jest większa niż 5.<br>"; // Condition for greater than 5
}

// Switch statement to check the value of $liczba
switch ($liczba) {
    case 3:
        echo "Liczba wynosi 3.<br>"; // Case for 3
        break;
    case 5:
        echo "Liczba wynosi 5.<br>"; // Case for 5
        break;
    default:
        echo "Liczba jest inna niż 3 i 5.<br>"; // Default case
}
echo "<br>";

// c) Pętla while() i for()
echo "c) Pętla while() i for(): <br>";
$i = 0; // Initialize counter for while loop
while ($i < 3) {
    echo "while: Iteracja $i<br>"; // Display iteration number
    $i++; // Increment counter
}

for ($j = 0; $j < 3; $j++) {
    echo "for: Iteracja $j<br>"; // Display iteration number
}
echo "<br>";

// d) Typy zmiennych $_GET, $_POST, $_SESSION
echo "d) Typy zmiennych \$_GET, \$_POST, \$_SESSION: <br>";
echo 'Przykład $_GET: <a href="labor_169394_X.php?zmienna=wartosc">Kliknij tutaj</a><br>'; // Example of $_GET usage

session_start(); // Start session
$_SESSION['sesja'] = 'Wartość sesji'; // Set session variable
echo 'Wartość $_SESSION["sesja"]: ' . $_SESSION['sesja'] . "<br>"; // Display session value
?>