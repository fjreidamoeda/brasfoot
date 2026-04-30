<?php
require_once "autoload.php";
$db = Database::getInstance()->getConnection();

echo "<h2>Verificação do Sistema</h2>";

// Check championships
$total_camps = $db->query("SELECT COUNT(*) as total FROM campeonatos")->fetch(PDO::FETCH_ASSOC)['total'];
echo "Campeonatos: $total_camps<br>";

// Check if classification exists
$total_class = $db->query("SELECT COUNT(*) as total FROM classificacao")->fetch(PDO::FETCH_ASSOC)['total'];
echo "Classificação entries: $total_class<br>";

// Check matches
$total_partidas = $db->query("SELECT COUNT(*) as total FROM partidas")->fetch(PDO::FETCH_ASSOC)['total'];
echo "Partidas: $total_partidas<br>";

// Check if calendar is empty, create matches if needed
if ($total_partidas == 0 && $total_camps > 0) {
    echo "<br><strong>Criando partidas de exemplo...</strong><br>";
    
    $times = $db->query("SELECT id FROM times LIMIT 20")->fetchAll(PDO::FETCH_ASSOC);
    if (count($times) >= 2) {
        $campeonatos = $db->query("SELECT id FROM campeonatos LIMIT 1")->fetch(PDO::FETCH_ASSOC);
        if ($campeonatos) {
            $camp_id = $campeonatos['id'];
            $stmt = $db->prepare("INSERT INTO partidas (campeonato_id, rodada, time_casa_id, time_fora_id, data_partida, hora, id_save) VALUES (?, ?, ?, ?, DATE('now'), '16:00', 1)");
            
            for ($rodada = 1; $rodada <= 5; $rodada++) {
                for ($i = 0; $i < count($times); $i += 2) {
                    if ($i + 1 < count($times)) {
                        $stmt->execute([$camp_id, $rodada, $times[$i]['id'], $times[$i+1]['id']]);
                    }
                }
            }
            echo "Partidas criadas!<br>";
        }
    }
}

echo "<br><a href='campeonatos.php'>Ir para Campeonatos</a><br>";
echo "<a href='calendario.php'>Ir para Calendário</a><br>";
echo "<a href='tatica.php'>Ir para Tática</a><br>";
?>
