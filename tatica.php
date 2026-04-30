<?php
require_once 'autoload.php';
session_start();

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }

$db = Database::getInstance()->getConnection();
$user_id = $_SESSION['user_id'];

// Get user's team with one query
$user = $db->prepare("SELECT u.*, t.nome as time_nome 
                      FROM users u 
                      LEFT JOIN times t ON u.clube_id = t.id 
                      WHERE u.id = ?")
             ->execute([$user_id])
             ->fetch(PDO::FETCH_ASSOC);

if (!$user || !$user['clube_id']) {
    header("Location: escolher_time.php");
    exit;
}

$clube_id = $user['clube_id'];
$id_save = $user['id_save'] ?? 1;

// Get players by position - single query
$stmt = $db->prepare("SELECT * FROM jogadores WHERE time_id = ? AND id_save = ? ORDER BY 
                      CASE posicao 
                        WHEN 'Goleiro' THEN 1
                        WHEN 'Defensor' THEN 2
                        WHEN 'Meio-campista' THEN 3
                        WHEN 'Atacante' THEN 4
                      END, overall DESC");
$stmt->execute([$clube_id, $id_save]);
$jogadores = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Group by position
$jogadores_por_posicao = [
    'Goleiro' => [],
    'Defensor' => [],
    'Meio-campista' => [],
    'Atacante' => []
];

foreach ($jogadores as $j) {
    if (isset($jogadores_por_posicao[$j['posicao']])) {
        $jogadores_por_posicao[$j['posicao']][] = $j;
    }
}

// Handle formation change
if (isset($_POST['formacao'])) {
    $formacao = $_POST['formacao'];
    $db->prepare("UPDATE saves SET formacao = ? WHERE user_id = ? AND ativo = 1")
       ->execute([$formacao, $user_id]);
    header("Location: tatica.php");
    exit;
}

// Get current formation
$formacao_atual = $db->prepare("SELECT formacao FROM saves WHERE user_id = ? AND ativo = 1")
                        ->execute([$user_id])
                        ->fetch(PDO::FETCH_ASSOC)['formacao'] ?? '4-4-2';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Tática - Fenix Foot 2026</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; font-family: Arial, sans-serif; }
        body { background: #050505; color: white; min-height:100vh; padding:20px; }
        .container { max-width:1400px; margin:0 auto; }
        h1 { color: #00d2ff; margin-bottom:20px; }
        .btn { background: #00d2ff; color: black; padding:10px 20px; border:none; border-radius:10px; cursor:pointer; font-weight:700; }
        .card { background: rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1); border-radius:20px; padding:20px; margin-bottom:20px; }
        .formation-selector { margin-bottom:20px; }
        .formation-selector select { background: rgba(255,255,255,0.1); color:white; padding:10px; border:1px solid rgba(255,255,255,0.2); border-radius:8px; font-size:1em; }
        .field { background: rgba(0,100,0,0.3); border:2px solid rgba(255,255,255,0.2); border-radius:10px; padding:40px 20px; margin:20px 0; min-height:600px; }
        .player-row { display:flex; justify-content:center; gap:20px; margin:30px 0; }
        .player { background: rgba(255,255,255,0.1); border:2px solid #00d2ff; border-radius:10px; padding:10px 15px; text-align:center; min-width:120px; }
        .player-name { font-weight:600; font-size:0.9em; }
        .player-rating { font-size:0.8em; color:#00d2ff; }
        .player-list { display:grid; grid-template-columns:repeat(auto-fill, minmax(150px,1fr)); gap:10px; margin-top:20px; }
        .player-card { background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1); border-radius:8px; padding:10px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>TÁTICA - <?php echo htmlspecialchars($user['time_nome'] ?? 'Time'); ?></h1>
        
        <div class="card">
            <form method="POST" class="formation-selector">
                <label style="color:#00d2ff; margin-right:10px;">Formação:</label>
                <select name="formacao" onchange="this.form.submit()">
                    <option value="4-4-2" <?php echo $formacao_atual == '4-4-2' ? 'selected' : ''; ?>>4-4-2</option>
                    <option value="4-3-3" <?php echo $formacao_atual == '4-3-3' ? 'selected' : ''; ?>>4-3-3</option>
                    <option value="3-5-2" <?php echo $formacao_atual == '3-5-2' ? 'selected' : ''; ?>>3-5-2</option>
                    <option value="4-5-1" <?php echo $formacao_atual == '4-5-1' ? 'selected' : ''; ?>>4-5-1</option>
                    <option value="5-3-2" <?php echo $formacao_atual == '5-3-2' ? 'selected' : ''; ?>>5-3-2</option>
                </select>
            </form>
        </div>
        
        <div class="card">
            <h3 style="color:#00d2ff; margin-bottom:20px;">Campo</h3>
            <div class="field">
                <?php 
                $lines = [
                    'Goleiro' => ['qtd'=>1, 'label'=>'Goleiro'],
                    'Defensor' => ['qtd'=>4, 'label'=>'Defensores'],
                    'Meio-campista' => ['qtd'=>4, 'label'=>'Meias'],
                    'Atacante' => ['qtd'=>2, 'label'=>'Atacantes']
                ];
                foreach($lines as $pos => $cfg): ?>
                    <div class="player-row">
                        <?php 
                        $players = array_slice($jogadores_por_posicao[$pos], 0, $cfg['qtd']);
                        foreach($players as $j): ?>
                            <div class="player">
                                <div class="player-name"><?php echo htmlspecialchars($j['nome']); ?></div>
                                <div class="player-rating"><?php echo $j['overall']; ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="card">
            <h3 style="color:#00d2ff;">Todos os Jogadores (<?php echo count($jogadores); ?>)</h3>
            <div class="player-list">
                <?php foreach($jogadores as $j): ?>
                    <div class="player-card">
                        <div style="font-weight:600;"><?php echo htmlspecialchars($j['nome']); ?></div>
                        <div style="font-size:0.8em; color:rgba(255,255,255,0.7);"><?php echo $j['posicao']; ?> - Overall: <?php echo $j['overall']; ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <a href="index.php" class="btn">Voltar</a>
    </div>
</body>
</html>
