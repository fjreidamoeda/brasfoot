<?php
require_once 'autoload.php';
session_start();

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }

$saveModel = new Save();
$jogadorModel = new Jogador();
$save_ativo = $saveModel->buscarAtivo();

if (!$save_ativo) { header("Location: setup.php"); exit; }

$id_save = $save_ativo['id'];
$clube_id = $save_ativo['clube_id'];
$partida_id = $_GET['id'] ?? null;

if (!$partida_id) { header("Location: index.php"); exit; }

// Toggle titular via AJAX ou POST
if (isset($_POST['toggle_id'])) {
    $jid = $_POST['toggle_id'];
    $jogador = $jogadorModel->buscarPorId($jid);
    if ($jogador && $jogador['clube_id'] == $clube_id) {
        $novo_status = $jogador['titular'] ? 0 : 1;
        
        // Verificar limite de 11 se estiver tentando colocar como titular
        if ($novo_status == 1) {
            $titulares = $db = Database::getInstance()->getConnection()->query("SELECT count(*) FROM jogadores WHERE clube_id = $clube_id AND id_save = $id_save AND titular = 1")->fetchColumn();
            if ($titulares >= 11) {
                echo "<script>alert('Você já tem 11 titulares!');</script>";
            } else {
                $jogadorModel->atualizar($jid, ['titular' => 1]);
            }
        } else {
            $jogadorModel->atualizar($jid, ['titular' => 0]);
        }
    }
}

$elenco = $jogadorModel->listarPorClube($clube_id, $id_save);
$titularesCount = 0;
foreach($elenco as $j) if($j['titular']) $titularesCount++;

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Escalação - Fenix Foot</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --glass: rgba(255, 255, 255, 0.05);
            --primary: #00d2ff;
            --accent: #ff007a;
        }
        body { background: #0a0a0a; color: white; font-family: 'Outfit', sans-serif; margin: 0; padding: 20px; }
        .container { max-width: 1000px; margin: 0 auto; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .grid { display: grid; grid-template-columns: 1fr; gap: 15px; }
        .player-card { 
            background: var(--glass); 
            border: 1px solid rgba(255,255,255,0.1); 
            padding: 15px 25px; 
            border-radius: 20px; 
            display: flex; 
            align-items: center; 
            justify-content: space-between;
            transition: 0.3s;
        }
        .player-card.titular { border-left: 5px solid var(--primary); background: rgba(0, 210, 255, 0.1); }
        .player-info { display: flex; align-items: center; gap: 20px; }
        .overall { background: var(--primary); color: black; font-weight: 800; padding: 5px 10px; border-radius: 10px; min-width: 45px; text-align: center; }
        .pos { color: #888; font-weight: 600; font-size: 0.8em; text-transform: uppercase; width: 60px; }
        .stats { display: flex; gap: 15px; font-size: 0.8em; opacity: 0.6; }
        
        .btn-toggle { background: transparent; border: 1px solid rgba(255,255,255,0.3); color: white; padding: 8px 15px; border-radius: 15px; cursor: pointer; transition: 0.3s; }
        .btn-toggle:hover { background: rgba(255,255,255,0.1); }
        .titular .btn-toggle { background: var(--primary); color: black; border-color: var(--primary); }
        
        .btn-play { background: var(--accent); color: white; padding: 15px 40px; border-radius: 50px; text-decoration: none; font-weight: 800; text-transform: uppercase; box-shadow: 0 10px 30px rgba(255,0,122,0.3); transition: 0.3s; }
        .btn-play:hover { transform: scale(1.1); box-shadow: 0 15px 40px rgba(255,0,122,0.5); }
        .btn-play.disabled { opacity: 0.5; cursor: not-allowed; pointer-events: none; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <h1 style="margin:0">🏃 Escalação</h1>
                <p style="opacity:0.6"><?php echo $titularesCount; ?> / 11 selecionados</p>
            </div>
            <a href="partida_ao_vivo.php?id=<?php echo $partida_id; ?>" class="btn-play <?php echo ($titularesCount < 11) ? 'disabled' : ''; ?>">Confirmar e Jogar ⚽</a>
        </div>

        <div class="grid">
            <?php foreach ($elenco as $j): ?>
            <div class="player-card <?php echo $j['titular'] ? 'titular' : ''; ?>">
                <div class="player-info">
                    <div class="overall"><?php echo $j['overall']; ?></div>
                    <div>
                        <div class="pos"><?php echo $j['posicao']; ?></div>
                        <div style="font-weight: 800; font-size: 1.1em;"><?php echo $j['nome']; ?></div>
                    </div>
                    <div class="stats">
                        <span>⚡ Vel: <?php echo $j['velocidade']; ?></span>
                        <span>💪 Fis: <?php echo $j['fisico']; ?></span>
                        <span>🔋 Res: <?php echo $j['resistencia']; ?></span>
                    </div>
                </div>
                <form method="POST" style="margin:0">
                    <input type="hidden" name="toggle_id" value="<?php echo $j['id']; ?>">
                    <button type="submit" class="btn-toggle">
                        <?php echo $j['titular'] ? 'Remover ❌' : 'Escalar ✅'; ?>
                    </button>
                </form>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
