<?php
require_once 'autoload.php';

$db = Database::getInstance()->getConnection();
$id_save = 1;

// Get active save
$save = $db->query("SELECT id FROM saves WHERE ativo = 1 LIMIT 1")->fetch(PDO::FETCH_ASSOC);
if ($save) {
    $id_save = $save['id'];
}

// Get championships
$stmt = $db->prepare("SELECT c.*, 
    (SELECT COUNT(*) FROM classificacao cl WHERE cl.campeonato_id = c.id AND cl.id_save = c.id_save) as has_class
    FROM campeonatos c WHERE c.id_save = ? ORDER BY c.pais, c.nome");
$stmt->execute([$id_save]);
$campeonatos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get classification for each
foreach ($campeonatos as &$camp) {
    if ($camp['has_class'] > 0) {
        $stmt = $db->prepare("SELECT cl.*, t.nome as time_nome 
                                   FROM classificacao cl 
                                   JOIN times t ON cl.time_id = t.id 
                                   WHERE cl.campeonato_id = ? AND cl.id_save = ? 
                                   ORDER BY cl.pontos DESC, (cl.gols_pro - cl.gols_contra) DESC 
                                   LIMIT 10");
        $stmt->execute([$camp['id'], $id_save]);
        $camp['classificacao'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $camp['classificacao'] = [];
    }
}

header('Content-Type: application/json');
echo json_encode($campeonatos);
?>
