<?php
require_once "autoload.php";
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$partida_id = $_GET["id"] ?? 0;
if (!$partida_id) {
    header("Location: index.php");
    exit;
}

$partidaModel = new Partida();
$partida = $partidaModel->buscarPorId($partida_id);

if (!$partida) {
    echo "Partida não encontrada.";
    exit;
}

// Simular se ainda não foi jogada
if (!$partida["jogada"]) {
    $partidaModel->simular($partida_id);
    $partida = $partidaModel->buscarPorId($partida_id);
}

$eventos = json_decode($partida["eventos"] ?? "[]", true) ?: [];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($partida["time_casa_nome"]); ?> vs <?php echo htmlspecialchars($partida["time_fora_nome"]); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; font-family: "Outfit", sans-serif; }
        body { background: #050505; color: white; }
        .header { padding: 20px; text-align: center; background: rgba(0,0,0,0.5); }
        .placar { font-size: 4em; font-weight: 800; text-align: center; padding: 40px; }
        .time { display: inline-block; width: 40%; }
        .gols { display: inline-block; font-size: 1.5em; color: #00d2ff; }
        .eventos { max-width: 600px; margin: 20px auto; padding: 20px; background: rgba(255,255,255,0.05); border-radius: 20px; }
        .evento { padding: 10px; border-bottom: 1px solid rgba(255,255,255,0.1); }
    </style>
</head>
<body>
    <div class="header">
        <h1><?php echo htmlspecialchars($partida["campeonato_nome"] ?? "Amistoso"); ?></h1>
    </div>
    
    <div class="placar">
        <div class="time"><?php echo htmlspecialchars($partida["time_casa_nome"]); ?></div>
        <span class="gols"><?php echo $partida["gols_casa"]; ?></span>
        <span style="font-size:0.5em; margin: 0 20px;">vs</span>
        <span class="gols"><?php echo $partida["gols_fora"]; ?></span>
        <div class="time"><?php echo htmlspecialchars($partida["time_fora_nome"]); ?></div>
    </div>
    
    <div class="eventos">
        <h3>Eventos da Partida</h3>
        <?php if (empty($eventos)): ?>
            <p>Nenhum evento registrado.</p>
        <?php else: ?>
            <?php foreach ($eventos as $ev): ?>
                <div class="evento">
                    <?php echo $ev["minuto"]; ?>' - 
                    <?php if ($ev["tipo"] === "gol"): ?>
                        ⚽ Gol de <?php echo htmlspecialchars($ev["jogador"]); ?> (<?php echo htmlspecialchars($ev["time_nome"]); ?>)
                    <?php elseif ($ev["tipo"] === "amarelo"): ?>
                        🟨 Cartão Amarelo - <?php echo htmlspecialchars($ev["jogador"]); ?>
                    <?php elseif ($ev["tipo"] === "vermelho"): ?>
                        🟥 Cartão Vermelho - <?php echo htmlspecialchars($ev["jogador"]); ?>
                    <?php elseif ($ev["tipo"] === "lesao"): ?>
                        🏥 Lesão - <?php echo htmlspecialchars($ev["jogador"]); ?>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
    <div style="text-align: center; margin: 30px;">
        <a href="index.php" style="color: #00d2ff; font-size: 1.2em;">← Voltar ao Jogo</a>
    </div>
</body>
</html>
