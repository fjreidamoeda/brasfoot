<?php
require_once 'autoload.php';
session_start();

$userModel = new User();
$desafioModel = new Desafio();

// Exigir login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Logout
if (isset($_GET['logout']) && $_GET['logout'] == '1') {
    session_destroy();
    header("Location: login.php");
    exit;
}

$db = Database::getInstance();
$conn = $db->getConnection();
$user_id = $_SESSION['user_id'];
$userModel->atualizarAtividade($user_id);

// Check if saves table exists by querying sqlite_master
$stmt = $conn->prepare("SELECT name FROM sqlite_master WHERE type='table' AND name='saves'");
$stmt->execute();
$tableExists = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$tableExists) {
    header('Location: setup.php');
    exit;
}

// Now we can safely use Save class
$save = new Save();
$save_ativo = $save->buscarAtivo();

if (!$save_ativo || empty($save_ativo['nome_tecnico'])) {
    header('Location: setup.php');
    exit;
}

$id_save = $save_ativo['id'];

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';
    
    if ($acao === 'avancar_dia') {
        header("Location: avancar_dia.php");
        exit;
    }
    elseif ($acao === 'desafiar') {
        $desafiado_id = $_POST['desafiado_id'];
        $desafioModel->criar($user_id, $desafiado_id);
        $mensagem = "Desafio enviado!";
    }
    elseif ($acao === 'aceitar_desafio') {
        $desafio_id = $_POST['desafio_id'];
        $partida_id = $desafioModel->aceitar($desafio_id);
        if ($partida_id) {
            header("Location: escalacao.php?id=" . $partida_id);
            exit;
        }
        $mensagem = "Desafio aceito! Iniciando simulação...";
    }
    elseif ($acao === 'recusar_desafio') {
        $desafio_id = $_POST['desafio_id'];
        $desafioModel->recusar($desafio_id);
    }
}

// Buscar dados online
$playersOnline = $userModel->listarOnline();
$desafiosPendentes = $desafioModel->buscarPendentes($user_id);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fenix Foot</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --glass: rgba(255, 255, 255, 0.1);
            --glass-border: rgba(255, 255, 255, 0.2);
            --primary: #00d2ff;
            --secondary: #3a7bd5;
            --accent: #ff007a;
            --text: #ffffff;
        }
        * { margin:0; padding:0; box-sizing:border-box; font-family: 'Outfit', sans-serif; }
        body { 
            background: radial-gradient(circle at top right, #1e3c72, #2a5298, #0f2027); 
            min-height: 100vh; 
            color: var(--text);
            overflow-x: hidden;
        }
        .header { 
            padding: 40px 20px; 
            text-align: center; 
            background: linear-gradient(to bottom, rgba(0,0,0,0.5), transparent);
        }
        .logo-container {
            margin-bottom: 20px;
            animation: fadeInDown 1s ease-out;
        }
        .logo-container img {
            width: 180px;
            filter: drop-shadow(0 0 20px var(--primary));
        }
        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .header h1 { 
            font-size: 4em; 
            font-weight: 800; 
            letter-spacing: -3px;
            text-transform: uppercase;
            background: linear-gradient(to right, #ffffff, var(--primary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: 0 10px 30px rgba(0,0,0,0.5);
            margin-bottom: 5px;
        }
        .header p { 
            font-size: 1.2em; 
            opacity: 0.8; 
            font-weight: 300; 
            letter-spacing: 4px;
            text-transform: uppercase;
        }
        .container { max-width:1100px; margin:0 auto; padding:20px; }
        .info-bar { 
            background: var(--glass);
            backdrop-filter: blur(10px);
            border: 1px solid var(--glass-border);
            padding: 15px 25px; 
            margin-bottom: 40px; 
            border-radius: 20px; 
            text-align: center; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            display: flex;
            justify-content: center;
            gap: 20px;
            font-size: 0.95em;
        }
        .info-bar span { color: var(--primary); font-weight: 600; }
        .menu { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); 
            gap: 25px; 
        }
        .menu-item { 
            background: var(--glass);
            backdrop-filter: blur(15px);
            border: 1px solid var(--glass-border);
            padding: 40px 20px; 
            border-radius: 30px; 
            text-align: center; 
            text-decoration: none; 
            color: white; 
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); 
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            overflow: hidden;
        }
        .menu-item::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            opacity: 0;
            transition: opacity 0.4s;
        }
        .menu-item:hover { 
            transform: translateY(-15px) scale(1.02); 
            background: rgba(255,255,255,0.15);
            border-color: var(--primary);
            box-shadow: 0 20px 40px rgba(0,0,0,0.4);
        }
        .menu-item:hover::before { opacity: 1; }
        .menu-item .icon { 
            font-size: 3.5em; 
            margin-bottom: 20px; 
            filter: drop-shadow(0 5px 15px rgba(0,0,0,0.3));
            transition: transform 0.4s;
        }
        .menu-item:hover .icon { transform: scale(1.2) rotate(5deg); }
        .menu-item h3 { 
            font-size: 1.5em; 
            font-weight: 600; 
            margin-bottom: 8px; 
            letter-spacing: -0.5px;
        }
        .menu-item p { 
            color: rgba(255,255,255,0.6); 
            font-size: 0.9em; 
            font-weight: 300;
        }
        /* Mobile adjustments */
        @media (max-width: 600px) {
            .header h1 { font-size: 2.5em; }
            .info-bar { flex-direction: column; gap: 5px; }
        }
    </style>
    <script src="js/audio.js"></script>
</head>
<body>
    <?php $currentUser = $userModel->buscarPorId($user_id); ?>
    <div class="header">
        <div class="logo-container">
            <img src="img/logo.png" alt="Fenix Foot 2026">
        </div>
        <h1>FENIX FOOT 2026</h1>
        <div style="display:flex; justify-content:center; gap:30px; font-size:1em; opacity:0.9; margin-top:15px; font-weight:600;">
            <span>Manager: <strong style="color:var(--primary);"><?php echo htmlspecialchars($currentUser['username']); ?></strong></span>
            <span>💰 Moedas: <strong style="color:#ffd700;"><?php echo number_format($currentUser['moedas'] ?? 0, 0, ',', '.'); ?></strong></span>
            <span>🏆 Ranking: <strong style="color:var(--accent);"><?php echo $currentUser['ranking_pontos'] ?? 0; ?> pts</strong></span>
            <span><a href="?logout=1" style="color:var(--accent); text-decoration:none; font-weight:600;">🚪 Sair</a></span>
        </div>
    </div>
    
    <div class="container">
        <div class="info-bar">
            <div><strong>🎮 Save:</strong> <span><?php echo htmlspecialchars($save_ativo['nome']); ?></span></div>
            <div><strong>🏆 Temporada:</strong> <span><?php echo $save_ativo['temporada_atual']; ?></span></div>
            <div><strong>📅 Data:</strong> <span><?php echo sprintf("%02d/%02d", $save_ativo['dia_atual'], $save_ativo['mes_atual']); ?></span></div>
            
            <?php 
            $time_id = $save_ativo['clube_id'] ?? null;
            if ($time_id) {
                $managed_team = $conn->query("SELECT nome FROM times WHERE id = $time_id")->fetch(PDO::FETCH_ASSOC);
                if ($managed_team) {
                    echo "<div><strong>🚩 Seu Clube:</strong> <span style='color:var(--primary);'>" . htmlspecialchars($managed_team['nome']) . "</span></div>";
                }
            }
            ?>
            
            <?php 
            $time_id = $save_ativo['clube_id'] ?? 0;
            $proxima = $conn->query("SELECT p.*, tc.nome as casa, tf.nome as fora 
                                    FROM partidas p 
                                    JOIN times tc ON p.time_casa_id = tc.id 
                                    JOIN times tf ON p.time_fora_id = tf.id 
                                    WHERE p.id_save = $id_save AND p.jogada = 0 
                                    AND (p.time_casa_id = $time_id OR p.time_fora_id = $time_id)
                                    ORDER BY p.data_partida, p.hora LIMIT 1")->fetch(PDO::FETCH_ASSOC);
            if ($proxima): ?>
                <div style="margin-left: 20px; border-left: 1px solid var(--glass-border); padding-left: 20px;">
                    <strong>Próximo Jogo:</strong> 
                    <span style="color:var(--accent)"><?php echo $proxima['casa']; ?> vs <?php echo $proxima['fora']; ?></span>
                    <a href="escalacao.php?id=<?php echo $proxima['id']; ?>" style="text-decoration:none; margin-left:10px; font-size:0.8em; background:var(--primary); color:black; padding:2px 8px; border-radius:5px;">JOGAR ⚽</a>
                </div>
            <?php endif; ?>

            <form method="POST" style="margin-left:auto; display: flex; gap: 10px; align-items: center;">
                <input type="hidden" name="acao" value="avancar_dia">
                <button type="submit" class="btn" style="margin:0; padding:8px 20px; font-size:0.9em; background:var(--accent); box-shadow: 0 0 15px var(--accent);">PRÓXIMO DIA ⏩</button>
            </form>
        </div>
        
        <div class="menu">
            <a href="times.php" class="menu-item">
                <span class="icon">🏟️</span>
                <span>Times</span>
            </a>
            <a href="tatica.php" class="menu-item">
                <span class="icon">📋</span>
                <span>Táticas</span>
            </a>
            <a href="tabela.php" class="menu-item">
                <span class="icon">📊</span>
                <span>Tabela</span>
            </a>
            
            <a href="jogadores.php" class="menu-item">
                <div class="icon">👤</div>
                <h3>Jogadores</h3>
                <p>Ver e transferir jogadores</p>
            </a>
            
            <a href="campeonatos.php" class="menu-item">
                <div class="icon">🏆</div>
                <h3>Campeonatos</h3>
                <p>Brasileirão, Europa, Mundo e mais</p>
            </a>
            
            <?php 
            $my_camp_id = 0;
            if ($save_ativo['clube_id'] ?? null) {
                $stmt = $conn->prepare("SELECT campeonato_id FROM classificacao WHERE time_id = ? AND id_save = ? LIMIT 1");
                $stmt->execute([$save_ativo['clube_id'], $id_save]);
                $my_camp_id = $stmt->fetchColumn();
            }
            ?>
            <a href="calendario.php?id_camp=<?php echo $my_camp_id; ?>" class="menu-item">
                <div class="icon">📅</div>
                <h3>Calendário</h3>
                <p>Partidas e resultados</p>
            </a>
            
            <a href="transferencias.php" class="menu-item">
                <div class="icon">🔄</div>
                <h3>Transferências</h3>
                <p>Mercado de jogadores</p>
            </a>
            
            <a href="tatica.php" class="menu-item">
                <div class="icon">📋</div>
                <h3>Tática</h3>
                <p>Esquema tático e formação</p>
            </a>
            
            <a href="financas.php" class="menu-item">
                <div class="icon">💰</div>
                <h3>Finanças</h3>
                <p>Controle financeiro</p>
            </a>
            
            <a href="#online" class="menu-item" style="border-color: var(--accent);">
                <div class="icon">🌐</div>
                <h3>Online</h3>
                <p>Desafie outros técnicos</p>
            </a>

            <a href="ranking.php" class="menu-item">
                <div class="icon">🎖️</div>
                <h3>Ranking</h3>
                <p>Melhores do Mundo</p>
            </a>
            
            <a href="base.php" class="menu-item">
                <div class="icon">🌱</div>
                <h3>Base</h3>
                <p>Academia de jovens</p>
            </a>
        </div>

        <div id="online" style="margin-top:50px; padding-top:50px; border-top: 1px solid var(--glass-border);">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                <h2 style="font-weight:800; letter-spacing:-1px;">👥 CENTRAL MULTIPLAYER</h2>
                <span style="color:var(--primary); font-size:0.8em;">ONLINE AGORA</span>
            </div>
            
            <div style="display:grid; grid-template-columns: 2fr 1fr; gap:30px;">
                <!-- Feed de Notificações e Desafios -->
                <div style="background:var(--glass); padding:30px; border-radius:25px; border:1px solid var(--glass-border);">
                    <h3 style="font-size:1.1em; margin-bottom:20px; color:rgba(255,255,255,0.7);">📬 Notificações</h3>
                    <?php if (empty($desafiosPendentes)): ?>
                        <p style="opacity:0.4; font-size:0.9em;">Nenhum desafio pendente no momento.</p>
                    <?php else: ?>
                        <?php foreach ($desafiosPendentes as $dp): ?>
                            <div style="background:rgba(255, 165, 0, 0.1); border:1px solid rgba(255, 165, 0, 0.3); padding:20px; border-radius:20px; margin-bottom:15px; display:flex; justify-content:space-between; align-items:center;">
                                <div>
                                    <strong style="color:orange;">⚔️ DESAFIO!</strong>
                                    <div style="font-size:0.9em;"><?php echo htmlspecialchars($dp['desafiante_nome']); ?> te desafiou!</div>
                                </div>
                                <div style="display:flex; gap:10px;">
                                    <form method="POST">
                                        <input type="hidden" name="acao" value="aceitar_desafio">
                                        <input type="hidden" name="desafio_id" value="<?php echo $dp['id']; ?>">
                                        <button type="submit" style="background:var(--primary); color:black; padding:8px 15px; font-size:0.8em; font-weight:700;">ACEITAR</button>
                                    </form>
                                    <form method="POST">
                                        <input type="hidden" name="acao" value="recusar_desafio">
                                        <input type="hidden" name="desafio_id" value="<?php echo $dp['id']; ?>">
                                        <button type="submit" style="background:rgba(255,255,255,0.1); color:white; border:1px solid var(--glass-border); padding:8px 15px; font-size:0.8em;">RECUSAR</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Lista de Jogadores -->
                <div style="background:var(--glass); padding:30px; border-radius:25px; border:1px solid var(--glass-border);">
                    <h3 style="font-size:1.1em; margin-bottom:20px; color:rgba(255,255,255,0.7);">👥 Outros Técnicos</h3>
                    <?php if (empty($playersOnline)): ?>
                        <p style="opacity:0.4; font-size:0.9em;">Ninguém online agora.</p>
                    <?php else: ?>
                        <?php foreach ($playersOnline as $p): ?>
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; padding-bottom:15px; border-bottom:1px solid rgba(255,255,255,0.05);">
                                <div>
                                    <div style="font-weight:600;"><?php echo htmlspecialchars($p['username']); ?></div>
                                    <div style="font-size:0.75em; color:var(--primary);"><?php echo htmlspecialchars($p['time_nome'] ?? 'Sem Clube'); ?></div>
                                </div>
                                <form method="POST">
                                    <input type="hidden" name="acao" value="desafiar">
                                    <input type="hidden" name="desafiado_id" value="<?php echo $p['id']; ?>">
                                    <button type="submit" style="background:var(--accent); color:white; padding:5px 12px; font-size:0.7em; font-weight:700; border-radius:10px;">DESAFIAR</button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
