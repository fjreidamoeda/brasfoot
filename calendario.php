<?php
require_once 'autoload.php';
session_start();

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }

$db = Database::getInstance()->getConnection();
$user_id = $_SESSION['user_id'];

// Get active save
$save = $db->query("SELECT * FROM saves WHERE ativo = 1 LIMIT 1")->fetch(PDO::FETCH_ASSOC);
$id_save = $save ? $save['id'] : 1;

// Get championships
$stmt = $db->prepare("SELECT * FROM campeonatos WHERE id_save = ? ORDER BY pais, nome");
$stmt->execute([$id_save]);
$campeonatos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get selected championship
$id_camp_selecionado = $_GET['id_camp'] ?? null;

// If no championship selected, try to find user's championship
if (!$id_camp_selecionado) {
    $user = $db->prepare("SELECT clube_id FROM users WHERE id = ?")->execute([$user_id])->fetch(PDO::FETCH_ASSOC);
    if ($user && $user['clube_id']) {
        $stmt = $db->prepare("SELECT campeonato_id FROM classificacao WHERE time_id = ? AND id_save = ? LIMIT 1");
        $stmt->execute([$user['clube_id'], $id_save]);
        $id_camp_selecionado = $stmt->fetchColumn();
    }
}

// If still no championship, get first one
if (!$id_camp_selecionado && !empty($campeonatos)) {
    $id_camp_selecionado = $campeonatos[0]['id'];
}

// Get matches for selected championship
$partidas = [];
$camp_atual = null;
if ($id_camp_selecionado) {
    $stmt = $db->prepare("SELECT p.*, 
                           tc.nome as time_casa_nome, 
                           tf.nome as time_fora_nome 
                           FROM partidas p 
                           LEFT JOIN times tc ON p.time_casa_id = tc.id 
                           LEFT JOIN times tf ON p.time_fora_id = tf.id 
                           WHERE p.campeonato_id = ? AND p.id_save = ? 
                           ORDER BY p.rodada, p.data_partida, p.hora");
    $stmt->execute([$id_camp_selecionado, $id_save]);
    $partidas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get championship info
    $stmt = $db->prepare("SELECT * FROM campeonatos WHERE id = ?");
    $stmt->execute([$id_camp_selecionado]);
    $camp_atual = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Simulate match if requested
$mensagem = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simular'])) {
    $partida_id = $_POST['partida_id'];
    // Simulate match logic here (simplified)
    $stmt = $db->prepare("UPDATE partidas SET jogada = 1, gols_casa = ?, gols_fora = ? WHERE id = ?");
    $gols_casa = rand(0, 4);
    $gols_fora = rand(0, 4);
    $stmt->execute([$gols_casa, $gols_fora, $partida_id]);
    $mensagem = "Partida simulada: $gols_casa x $gols_fora";
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Calendário - Fenix Foot 2026</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; font-family: Arial, sans-serif; }
        body { background: #050505; color: white; min-height:100vh; padding:20px; }
        .header { text-align:center; margin-bottom:40px; }
        .header h1 { color:#00d2ff; font-size:2.5em; margin:0; }
        .container { max-width:1000px; margin:0 auto; }
        .btn { background:#00d2ff; color:black; padding:10px 20px; text-decoration:none; border-radius:12px; font-weight:700; border:none; cursor:pointer; }
        .btn:hover { background:white; }
        .btn-back { background:transparent; color:#00d2ff; border:1px solid #00d2ff; }
        .selector-box { background:rgba(255,255,255,0.05); padding:20px; border-radius:20px; border:1px solid rgba(255,255,255,0.1); margin-bottom:30px; display:flex; align-items:center; justify-content:center; gap:20px; }
        select { background:#111; color:white; padding:12px 20px; border-radius:12px; border:1px solid rgba(255,255,255,0.2); font-size:1em; min-width:300px; }
        table { width:100%; border-collapse:collapse; margin-top:20px; background:rgba(255,255,255,0.05); border-radius:25px; overflow:hidden; border:1px solid rgba(255,255,255,0.1); }
        th, td { padding:15px; text-align:left; border-bottom:1px solid rgba(255,255,255,0.1); }
        th { background:rgba(255,255,255,0.05); color:#00d2ff; font-weight:600; text-transform:uppercase; font-size:0.8em; letter-spacing:2px; }
        .match-row:hover { background:rgba(255,255,255,0.02); }
        .team-name { font-weight:600; font-size:1.1em; }
        .score { font-weight:800; font-size:1.2em; color:#ff007a; background:rgba(255,0,122,0.1); padding:5px 15px; border-radius:10px; min-width:60px; text-align:center; display:inline-block; }
        .rodada-badge { background:#3a7bd5; color:white; padding:4px 10px; border-radius:8px; font-size:0.8em; font-weight:700; }
        .alert { background:rgba(46,204,113,0.2); border:1px solid #2ecc71; color:#2ecc71; padding:15px; border-radius:15px; margin-bottom:20px; text-align:center; }
        .empty-msg { padding:40px; text-align:center; color:rgba(255,255,255,0.5); }
    </style>
</head>
<body>
    <div class="header">
        <h1>📅 Calendário Oficial</h1>
        <p style="opacity:0.6; font-weight:300;">Temporada 2026</p>
    </div>
    
    <div class="container">
        <?php if (isset($mensagem)): ?>
            <div class="alert"><?php echo htmlspecialchars($mensagem); ?></div>
        <?php endif; ?>
        
        <div class="selector-box">
            <label style="font-weight:600; color:#00d2ff;">CAMPEONATO:</label>
            <?php if (!empty($campeonatos)): ?>
            <form method="GET" style="display:flex; gap:10px;">
                <select name="id_camp" onchange="this.form.submit()">
                    <?php foreach ($campeonatos as $c): ?>
                        <option value="<?php echo $c['id']; ?>" <?php echo $id_camp_selecionado == $c['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($c['nome']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
            <?php endif; ?>
            <a href="index.php" class="btn btn-back">Painel Inicial</a>
        </div>
        
        <?php if ($camp_atual): ?>
            <h2 style="margin-bottom:20px; color:#00d2ff;"><?php echo htmlspecialchars($camp_atual['nome']); ?></h2>
            
            <?php if (empty($partidas)): ?>
                <div class="empty-msg">
                    <p>Nenhuma partida agendada para este campeonato.</p>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Rodada</th>
                            <th>Data/Hora</th>
                            <th>Confronto</th>
                            <th>Resultado</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($partidas as $p): ?>
                            <tr class="match-row">
                                <td><span class="rodada-badge"><?php echo $p['rodada']; ?>ª</span></td>
                                <td style="font-size:0.9em; opacity:0.7;">
                                    <?php echo date('d/m/Y', strtotime($p['data_partida'])); ?><br>
                                    <?php echo $p['hora']; ?>
                                </td>
                                <td>
                                    <span class="team-name"><?php echo htmlspecialchars($p['time_casa_nome']); ?></span>
                                    <span style="margin:0 10px; opacity:0.3;">vs</span>
                                    <span class="team-name"><?php echo htmlspecialchars($p['time_fora_nome']); ?></span>
                                </td>
                                <td style="text-align:center;">
                                    <?php if ($p['jogada']): ?>
                                        <span class="score"><?php echo $p['gols_casa']; ?> x <?php echo $p['gols_fora']; ?></span>
                                    <?php else: ?>
                                        <span style="opacity:0.3;">Aguardando</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!$p['jogada']): ?>
                                        <a href="partida_ao_vivo.php?id=<?php echo $p['id']; ?>" class="btn" style="padding:8px 15px; font-size:0.8em;">JOGAR ⚽</a>
                                    <?php else: ?>
                                        <a href="partida_ao_vivo.php?id=<?php echo $p['id']; ?>" class="btn btn-back" style="padding:8px 15px; font-size:0.8em;">DETALHES</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        <?php endif; ?>
        
        <?php if (empty($campeonatos)): ?>
            <div class="empty-msg">
                <h3 style="color:#00d2ff;">Nenhum campeonato encontrado</h3>
                <p>Crie campeonatos primeiro no setup.</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
