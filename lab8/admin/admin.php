<?php
include 'cfg.php';

function FormularzLogowania() {
    return '
    <div class="logowanie">
        <h3 class="heading">Panel CMS:</h3>
        <form method="post" name="LoginForm" enctype="multipart/form-data" action="' . $_SERVER['REQUEST_URI'] . '">
            <table class="logowanie">
                <tr><td class="log4_t">[login]</td><td><input type="text" name="login" class="logowanie" required /></td></tr>
                <tr><td class="log4_t">[haslo]</td><td><input type="password" name="login_pass" class="logowanie" required /></td></tr>
                <tr><td></td><td><input type="submit" name="x1_submit" class="logowanie" value="zaloguj" /></td></tr>
            </table>
        </form>
    </div>';
}

// Sprawdzenie połączenia
if ($conn->connect_error) {
    die("Błąd połączenia z bazą danych: " . $conn->connect_error);
}

// Funkcja wyświetlająca listę podstron
function ListaPodstron($conn) {
    $sql = "SELECT id, page_title FROM page_list";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        echo "<table border='1' cellpadding='10' cellspacing='0'>";
        echo "<tr>
                <th>ID</th>
                <th>Tytuł Podstrony</th>
                <th>Akcje</th>
              </tr>";

        while ($row = $result->fetch_assoc()) {
            $id = $row['id'];
            $title = htmlspecialchars($row['page_title']); // Bezpieczne wyświetlanie tytułu
            echo "<tr>
                    <td>{$id}</td>
                    <td>{$title}</td>
                    <td>
                        <a href='edit.php?id={$id}'>Edytuj</a> | 
                        <a href='delete.php?id={$id}' onclick='return confirm(\"Czy na pewno chcesz usunąć tę podstronę?\")'>Usuń</a>
                    </td>
                  </tr>";
        }

        echo "</table>";
    } else {
        echo "<p>Brak podstron w bazie danych.</p>";
    }
}

// Funkcja pozwala na edytowanie podstron
function EdytujPodstrone($conn) {
    // Sprawdzenie, czy przekazano id podstrony
    if (isset($_GET['id'])) {
        $id = $_GET['id'];

        // Pobranie danych podstrony z bazy
        $sql = "SELECT page_title, page_content, status FROM page_list WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $id); // Wiązanie parametru (id)
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $title = $row['page_title'];
            $content = $row['page_content'];
            $status = $row['status'];
        } else {
            echo "<p>Podstrona o podanym ID nie istnieje.</p>";
            return;
        }

        // Formularz edycji podstrony
        echo "<h2>Edytuj Podstronę: {$title}</h2>";
        echo "<form method='POST' action=''>
                <label for='title'>Tytuł Podstrony:</label><br>
                <input type='text' id='title' name='title' value='" . htmlspecialchars($title) . "' required><br><br>

                <label for='content'>Treść Podstrony:</label><br>
                <textarea id='content' name='content' rows='4' cols='50' required>{$content}</textarea><br><br>

                <label for='status'>Aktywna:</label>
                <input type='checkbox' id='status' name='status' value='1' " . ($status == 1 ? 'checked' : '') . "><br><br>

                <input type='submit' value='Zapisz zmiany'>
              </form>";

        // Obsługa wysyłania formularza
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $new_title = $_POST['title'];
            $new_content = $_POST['content'];
            $new_status = isset($_POST['status']) ? 1 : 0; // Jeżeli checkbox jest zaznaczony, status = 1, w przeciwnym razie = 0

            // Aktualizacja danych w bazie
            $update_sql = "UPDATE page_list SET page_title = ?, page_content = ?, status = ? WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param('ssii', $new_title, $new_content, $new_status, $id);
            if ($update_stmt->execute()) {
                echo "<p>Podstrona została zaktualizowana.</p>";
            } else {
                echo "<p>Wystąpił błąd podczas aktualizacji podstrony.</p>";
            }
        }

    } else {
        echo "<p>Nie podano ID podstrony do edycji.</p>";
    }
}

function DodajNowaPodstrone($conn) {
    // Obsługa wysyłania formularza
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $new_title = $_POST['title'];
        $new_content = $_POST['content'];
        $new_status = isset($_POST['status']) ? 1 : 0; // Checkbox: zaznaczony = 1, niezaznaczony = 0

        // Dodawanie danych do bazy
        $insert_sql = "INSERT INTO page_list (page_title, page_content, status) VALUES (?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param('ssi', $new_title, $new_content, $new_status);

        if ($insert_stmt->execute()) {
            echo "<p>Nowa podstrona została dodana.</p>";
        } else {
            echo "<p>Wystąpił błąd podczas dodawania nowej podstrony.</p>";
        }
    }

    // Formularz dodawania podstrony
    echo "<h2>Dodaj Nową Podstronę</h2>";
    echo "<form method='POST' action=''>
            <label for='title'>Tytuł Podstrony:</label><br>
            <input type='text' id='title' name='title' required><br><br>

            <label for='content'>Treść Podstrony:</label><br>
            <textarea id='content' name='content' rows='4' cols='50' required></textarea><br><br>

            <label for='status'>Aktywna:</label>
            <input type='checkbox' id='status' name='status' value='1'><br><br>

            <input type='submit' value='Dodaj Podstronę'>
          </form>";
}

function UsunPodstrone($conn, $id) {
    if (!is_numeric($id)) {
        echo "<p>Błąd: ID musi być liczbą.</p>";
        return;
    }

    // Usunięcie rekordu z bazy danych
    $delete_sql = "DELETE FROM page_list WHERE id = ? LIMIT 1";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param('i', $id);

    if ($delete_stmt->execute()) {
        if ($delete_stmt->affected_rows > 0) {
            echo "<p>Podstrona o ID $id została usunięta.</p>";
        } else {
            echo "<p>Podstrona o ID $id nie istnieje.</p>";
        }
    } else {
        echo "<p>Wystąpił błąd podczas usuwania podstrony.</p>";
    }
}

?>