<?php

include('cfg.php');

function PokazStrone($alias) {
    global $conn;
    $alias_clear = htmlspecialchars($alias);

    $query = "SELECT * FROM page_list WHERE alias = ? LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $alias_clear);

    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $stmt->close();
    return empty($row['id']) ? '[nie_znaleziono_strony]' : $row['page_content'];
}

if (isset($_GET['idp'])) {
    $alias = $_GET['idp'];
    echo PokazStrone($alias);
} else {
    echo '[nie_znaleziono_strony]';
}
?>