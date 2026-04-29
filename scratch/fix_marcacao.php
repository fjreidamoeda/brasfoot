<?php
require_once 'classes/Database.class.php';
$db = Database::getInstance();
$conn = $db->getConnection();

try {
    $conn->exec("ALTER TABLE tacticas RENAME COLUMN marcação TO marcacao");
    echo "Coluna 'marcação' renomeada para 'marcacao' com sucesso!";
} catch (Exception $e) {
    echo "Erro ou coluna já renomeada: " . $e->getMessage();
}
?>
