<?php
require_once 'classes/Database.class.php';
$db = Database::getInstance();
$conn = $db->getConnection();

try {
    $conn->exec("ALTER TABLE jogadores ADD COLUMN idade INTEGER DEFAULT 20");
    echo "Coluna 'idade' adicionada com sucesso!";
} catch (Exception $e) {
    echo "Erro ou coluna já existe: " . $e->getMessage();
}
?>
