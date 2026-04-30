<?php
require_once 'autoload.php';
session_start();

$db = Database::getInstance()->getConnection();
$id_save = 1;

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $user = $db->prepare("SELECT id_save FROM users WHERE id = ?")->execute([$user_id])->fetch(PDO::FETCH_ASSOC);
    if ($user && $user['id_save']) {
        $id_save = $user['id_save'];
    }
}

$id_camp = $_GET['id_camp'] ?? 0;

if (!$id_camp) {
    echo json_encode(['error' => 'ID do campeonato não informado']);
    exit;
}

// Get matches
$stmt = $db->prepare("SELECT p.*, 
                           tc.nome as time_casa_nome, 
                           tf.nome as time_fora_nome 
                           FROM partidas p 
                           LEFT JOIN times tc ON p.time_casa_id = tc.id 
                           LEFT JOIN times tf ON p.time_fora_id = tf.id 
                           WHERE p.campeonato_id = ? AND p.id_save = ? 
                           ORDER BY p.rodada, p.data_partida, p.hora");
$stmt->execute([$id_camp, $id_save]);
$partidas = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($partidas);
?>
