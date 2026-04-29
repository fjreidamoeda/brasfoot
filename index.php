<?php
require_once 'autoload.php';

$db = Database::getInstance();
$conn = $db->getConnection();

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

if (!$save_ativo) {
    header('Location: setup.php');
    exit;
}

$id_save = $save_ativo['id'];

// Handle POST actions before any HTML output
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'avancar_dia') {
    $save->avancarDia($id_save);
    header("Location: index.php");
    exit;
}
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
        .header h1 { 
            font-size: 4em; 
            font-weight: 800; 
            letter-spacing: -2px;
            text-transform: uppercase;
            background: linear-gradient(to bottom, #ffffff, #a5a5a5);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: 0 10px 20px rgba(0,0,0,0.3);
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
    <div class="header">
        <h1>⚽ Fenix Foot</h1>
        <p>Jogo de Futebol em PHP + SQLite</p>
    </div>
    
    <div class="container">
        <div class="info-bar">
            <div><strong>Save Ativo:</strong> <span><?php echo htmlspecialchars($save_ativo['nome']); ?></span></div>
            <div><strong>Temporada:</strong> <span><?php echo $save_ativo['temporada_atual']; ?></span></div>
            <div><strong>Dia:</strong> <span><?php echo $save_ativo['dia_atual']; ?>/<?php echo $save_ativo['mes_atual']; ?></span></div>
            <form method="POST" style="margin-left:auto;">
                <input type="hidden" name="acao" value="avancar_dia">
                <button type="submit" class="btn" style="margin:0; padding:5px 15px; font-size:0.8em; background:var(--accent);">Próximo Dia ⏩</button>
            </form>
        </div>
        
        <div class="menu">
            <a href="times.php" class="menu-item">
                <div class="icon">🏟️</div>
                <h3>Times</h3>
                <p>Gerenciar times e elencos</p>
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
            
            <a href="calendario.php" class="menu-item">
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
            
            <a href="base.php" class="menu-item">
                <div class="icon">🌱</div>
                <h3>Base</h3>
                <p>Academia de jovens</p>
            </a>
        </div>
    </div>
</body>
</html>
