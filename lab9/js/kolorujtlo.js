// Zmienne do przechowywania stanu
var computed = false;
var decimal = 0;

// Funkcja do konwersji jednostek miary
function convert(entryform, from, to) {
    // Pobieranie indeksu wybranej jednostki miary
    convertfrom = from.selectedIndex;
    convertto = to.selectedIndex;

    // Obliczenie wartości w nowej jednostce miary i wyświetlenie wyniku
    entryform.display.value = (entryform.input.value * from[convertfrom].value / to[convertto].value);
}

// Funkcja do dodawania znaków do pola wejściowego
function addChar(input, character) {
    // Sprawdzenie, czy dodawany znak jest kropką i czy nie dodano jeszcze kropki
    if ((character == '.' && decimal == '0') || character != '.') {
        // Jeśli pole wejściowe jest puste lub zawiera '0', dodaj znak
        (input.value == "" || input.value == '0') ? input.value = character : input.value += character;

        // Wywołanie funkcji konwersji
        convert(input.from, input.from.measure1, input.from.measure2);

        // Ustawienie flagi computed na true
        computed = true;

        // Jeśli dodano kropkę, ustaw flagę decimal na 1
        if (character == '.') {
            decimal = 1;
        }
    }
}

// Funkcja do otwierania nowego okna przeglądarki
function openVothcom() {
    window.open("", "Display window", "toolbar=no, menubar=no");
}

// Funkcja do czyszczenia pola wejściowego
function clear(from) {
    // Ustawienie wartości pola wejściowego na 0
    from.input.value = 0;

    // Ustawienie wartości pola wyświetlania na 0
    from.display.value = 0;

    // Ustawienie flagi decimal na 0
    decimal = 0;
}

// Funkcja do zmiany koloru tła strony
function changeBackground(hexNumber) {
    // Ustawienie koloru tła strony na podany w argumencie hexNumber
    document.bgColor = hexNumber;
}