// ------------------------------------------------------------------------------
// Funkcja do pobrania daty i wyświetlenia jej w określonym elemencie HTML
// ------------------------------------------------------------------------------

function gettheDate() {
    Today = new Date(); // Utworzenie nowej daty
    TheDate = "" + (Today.getMonth() + 1) + " /" + Today.getDate() + " /" + (Today.getFullYear() - 100) // Funkcja do pobrania daty i wyświetlenia jej w określonym elemencie HTML
    document.getElementById("data").innerHTML = TheDate;     // Wyświetlenie daty w elemencie HTML o identyfikatorze "data"
}

// Zmienne do obsługi timera
var timerID = null;
var timerRunning = false;

// Funkcja do zatrzymania timera
function stopclock() {
    if (timerRunning) { // Sprawdzenie, czy timer jest uruchomiony
        clearTimeout(timerID); // Zatrzymanie timera
    }
    timerRunning = false; // Ustawienie flagi timerRunning na false
}

// Funkcja do uruchomienia timera
function startclock() {
    stopclock();
    gettheDate();
    showtime();
}

// Funkcja do wyświetlenia czasu i aktualizacji go co 1 sekundę
function showtime() {
    var now = new Date; // Utworzenie nowej daty

    // Pobranie godziny, minuty i sekundy
    var hours = now.getHours();
    var minutes = now.getMinutes();
    var seconds = now.getSeconds()

    // Pobranie godziny, minuty i sekundy
    timeValue += ((minutes < 10) ? ":0" : ":") + minutes
    timeValue += ((seconds < 10) ? ":0" : ":") + seconds
    timeValue += (hours >= 12) ? " P.M." : "A.M."

    document.getElementById("zegarek").innerHTML = timeValue; // Wyświetlenie czasu w elemencie HTML o identyfikatorze "zegarek"
    timerID = setTimeout("showtime()", 1000); // Uruchomienie funkcji showtime() po 1 sekundzie
    timerRunning = true; // Ustawienie flagi timerRunning na true
}
