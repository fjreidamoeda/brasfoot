<?php
require_once 'autoload.php';
header('Content-Type: application/json');

$pais = $_GET['pais'] ?? 'Brasil';
$db = Database::getInstance()->getConnection();

// Buscar times do banco de dados para o país selecionado
$stmt = $db->prepare("SELECT id, nome, liga, divisao FROM times WHERE pais = ? AND id_save = (SELECT id FROM saves WHERE ativo = 1 LIMIT 1) ORDER BY divisao, nome");
$stmt->execute([$pais]);
$times = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Agrupar por divisão
$resultado = [];
foreach ($times as $t) {
    $divisao = 'Divisão ' . ($t['divisao'] ?? 1);
    $resultado[] = [
        'id' => $t['id'],
        'nome' => $t['nome'],
        'liga' => $t['liga'] ?: $pais,
        'divisao' => $divisao
    ];
}

echo json_encode($resultado);
?>
