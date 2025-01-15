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
    // Natychmiastowa aktualizacja przy starcie
    updateClock();
    updateDate();
    // Aktualizacja zegara co sekundę
    setInterval(updateClock, 1000);
}

function updateClock() {
    const now = new Date();
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const seconds = String(now.getSeconds()).padStart(2, '0');
    
    document.getElementById('zegarek').innerHTML = `${hours}:${minutes}:${seconds}`;
}

function updateDate() {
    const now = new Date();
    const day = String(now.getDate()).padStart(2, '0');
    const month = String(now.getMonth() + 1).padStart(2, '0'); // Miesiące są od 0-11
    const year = now.getFullYear();
    
    document.getElementById('data').innerHTML = `${day}.${month}.${year}`;
}
