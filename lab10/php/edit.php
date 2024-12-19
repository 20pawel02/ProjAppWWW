<?php
session_start();
include '../cfg.php';

// Zabezpieczenie przed nieuprawnionym dostępem
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: admin.php");
    exit;
}

// Komunikaty błędów
$error_message = '';
$success_message = '';

// Sprawdzenie, czy podano ID strony do edycji
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $error_message = "Nieprawidłowe ID strony.";
    header("Location: admin.php?error=" . urlencode($error_message));
    exit;
}

$page_id = intval($_GET['id']);

// Obsługa wysłania formularza
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_page'])) {
    // Walidacja danych
    $title = trim($_POST['page_title']);
    $content = trim($_POST['page_content']);
    $status = isset($_POST['page_status']) ? 1 : 0;

    // Zabezpieczenie przed SQL Injection
    $title = $conn->real_escape_string($title);
    $content = $conn->real_escape_string($content);

    // Aktualizacja strony
    $update_query = "UPDATE page_list SET 
                     page_title = '$title', 
                     page_content = '$content', 
                     status = '$status' 
                     WHERE id = '$page_id'";
    
    if ($conn->query($update_query) === TRUE) {
        $success_message = "Strona została pomyślnie zaktualizowana.";
        header("Location: admin.php?message=" . urlencode($success_message));
        exit;
    } else {
        $error_message = "Błąd podczas aktualizacji strony: " . $conn->error;
    }
}

// Pobranie danych strony do edycji
$query = "SELECT * FROM page_list WHERE id = '$page_id'";
$result = $conn->query($query);

if ($result->num_rows == 0) {
    $error_message = "Nie znaleziono strony o podanym ID.";
    header("Location: admin.php?error=" . urlencode($error_message));
    exit;
}

$page = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Edycja Strony</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; }
        .error { color: red; background-color: #ffeeee; padding: 10px; margin-bottom: 15px; }
        .success { color: green; background-color: #eeffee; padding: 10px; margin-bottom: 15px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; }
        .form-group input[type="text"], .form-group textarea { width: 100%; padding: 8px; }
        .form-actions { display: flex; justify-content: space-between; }
    </style>
</head>
<body>
    <h1>Edycja Strony</h1>
    
    <?php if ($error_message): ?>
        <div class="error"><?php echo htmlspecialchars($error_message); ?></div>
    <?php endif; ?>
    
    <form method="post" action="">
        <div class="form-group">
            <label for="page_title">Tytuł strony:</label>
            <input type="text" id="page_title" name="page_title" 
                   value="<?php echo htmlspecialchars($page['page_title']); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="page_content">Zawartość strony:</label>
            <textarea id="page_content" name="page_content" rows="10" required><?php 
                echo htmlspecialchars($page['page_content']); 
            ?></textarea>
        </div>
        
        <div class="form-group">
            <label>
                <input type="checkbox" name="page_status" 
                       <?php echo $page['status'] ? 'checked' : ''; ?>>
                Strona aktywna
            </label>
        </div>
        
        <div class="form-actions">
            <input type="submit" name="edit_page" value="Zapisz zmiany">
            <a href="admin.php" style="color: red; text-decoration: none;">Anuluj</a>
        </div>
    </form>
</body>
</html>
