<?php
require_once 'autoload.php';

// Classes will be autoloaded when first used
$db = Database::getInstance();
$mensagem = '';
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';
    
    if ($acao === 'criar_tabelas') {
        $resultado = $db->createSchema();
        if ($resultado) {
            $mensagem = 'Tabelas criadas com sucesso!';
        } else {
            $erro = 'Erro ao criar tabelas. Verifique se o arquivo database/schema.sql existe.';
        }
    }
    
    elseif ($acao === 'criar_save') {
        $save = new Save();
        $id_save = $save->criar('Save 1');
        $mensagem = 'Novo jogo criado com sucesso! ID: ' . $id_save;
    }
    
    elseif ($acao === 'popular_basico') {
        $id_save = $_POST['id_save'] ?? 1;
        popularTimesBasicos($id_save);
        $mensagem = 'Times básicos populados com sucesso!';
    }
    
    elseif ($acao === 'popular_completo') {
        $id_save = $_POST['id_save'] ?? 1;
        require_once 'popular_fenix.php';
        $res = popularFenix($id_save);
        if ($res) {
            $mensagem = 'Todos os times e jogadores de 2026 (Fenix Foot) foram populados com sucesso!';
        } else {
            $erro = 'Erro ao popular o banco de dados.';
        }
    }
}

function popularTimesBasicos($id_save) {
    $time = new Time();
    
    $times_brasil = [
        ['Flamengo', 'FLA', 'Rio de Janeiro', 'RJ', 'Brasil', 'Brasileirão A', 1],
        ['Palmeiras', 'PAL', 'São Paulo', 'SP', 'Brasil', 'Brasileirão A', 1],
        ['Corinthians', 'COR', 'São Paulo', 'SP', 'Brasil', 'Brasileirão A', 1],
        ['São Paulo', 'SP', 'São Paulo', 'SP', 'Brasil', 'Brasileirão A', 1],
        ['Vasco', 'VAS', 'Rio de Janeiro', 'RJ', 'Brasil', 'Brasileirão A', 1],
        ['Fluminense', 'FLU', 'Rio de Janeiro', 'RJ', 'Brasil', 'Brasileirão A', 1],
        ['Botafogo', 'BOT', 'Rio de Janeiro', 'RJ', 'Brasil', 'Brasileirão A', 1],
        ['Grêmio', 'GRE', 'Porto Alegre', 'RS', 'Brasil', 'Brasileirão A', 1],
        ['Internacional', 'INT', 'Porto Alegre', 'RS', 'Brasil', 'Brasileirão A', 1],
        ['Atlético-MG', 'CAM', 'Belo Horizonte', 'MG', 'Brasil', 'Brasileirão A', 1]
    ];
    
    foreach ($times_brasil as $t) {
        $time->criar([
            'nome' => $t[0],
            'sigla' => $t[1],
            'cidade' => $t[2],
            'estado' => $t[3],
            'pais' => $t[4],
            'liga' => $t[5],
            'divisao' => $t[6],
            'id_save' => $id_save
        ]);
    }
}

$save = new Save();
try {
    $saves = $save->listar();
} catch (Exception $e) {
    $saves = [];
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fenix Foot - Setup</title>
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
            padding: 40px 20px;
        }
        .container { 
            max-width:800px; 
            margin:0 auto; 
            background: var(--glass); 
            backdrop-filter: blur(15px);
            padding:40px; 
            border-radius:30px; 
            border: 1px solid var(--glass-border);
            box-shadow: 0 20px 50px rgba(0,0,0,0.4);
        }
        h1 { 
            color: white; 
            text-align:center; 
            font-weight: 800; 
            font-size: 2.5em; 
            margin-bottom: 30px;
            letter-spacing: -1px;
        }
        .step { 
            background: rgba(255,255,255,0.05); 
            padding:25px; 
            margin:20px 0; 
            border-radius:20px; 
            border: 1px solid var(--glass-border);
            transition: 0.3s;
        }
        .step:hover { background: rgba(255,255,255,0.08); border-color: var(--primary); }
        .step h3 { margin-top:0; color: var(--primary); font-weight: 600; margin-bottom: 10px; }
        .step p { color: rgba(255,255,255,0.7); font-size: 0.9em; margin-bottom: 15px; }
        
        button { 
            background: var(--primary); 
            color: #000; 
            border: none; 
            padding: 12px 25px; 
            border-radius: 12px; 
            cursor: pointer; 
            font-size: 16px; 
            font-weight: 600;
            transition: 0.3s;
        }
        button:hover { background: white; transform: scale(1.05); }
        
        .success { background: rgba(46, 204, 113, 0.2); border: 1px solid #2ecc71; color:#2ecc71; padding:15px; border-radius:15px; margin:10px 0; }
        .error { background: rgba(231, 76, 60, 0.2); border: 1px solid #e74c3c; color:#e74c3c; padding:15px; border-radius:15px; margin:10px 0; }
        
        select { 
            padding:10px; 
            border-radius:12px; 
            background: rgba(0,0,0,0.2); 
            color: white; 
            border: 1px solid var(--glass-border);
            margin:10px 0;
            outline: none;
        }
    </style>
    <script src="js/audio.js"></script>
</head>
</head>
<body>
    <div class="container">
        <h1>⚽ Fenix Foot - Configuração Inicial</h1>
        
        <?php if ($mensagem): ?>
            <div class="success"><?php echo htmlspecialchars($mensagem); ?></div>
        <?php endif; ?>
        
        <?php if ($erro): ?>
            <div class="error"><?php echo htmlspecialchars($erro); ?></div>
        <?php endif; ?>
        
        <div class="step">
            <h3>Passo 1: Criar Tabelas do Banco de Dados</h3>
            <p>Cria todas as tabelas necessárias para o jogo.</p>
            <form method="POST">
                <input type="hidden" name="acao" value="criar_tabelas">
                <button type="submit">Criar Tabelas</button>
            </form>
        </div>
        
        <div class="step">
            <h3>Passo 2: Criar Novo Jogo</h3>
            <p>Cria um novo save game.</p>
            <form method="POST">
                <input type="hidden" name="acao" value="criar_save">
                <button type="submit">Criar Novo Jogo</button>
            </form>
        </div>
        
        <div class="step">
            <h3>Passo 3: Popular com Times Básicos</h3>
            <p>Adiciona 10 times brasileiros principais.</p>
            <form method="POST">
                <input type="hidden" name="acao" value="popular_basico">
                <label>Selecione o Save:</label>
                <select name="id_save">
                    <?php foreach ($saves as $s): ?>
                        <option value="<?php echo $s['id']; ?>"><?php echo htmlspecialchars($s['nome']); ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit">Popular Times Básicos</button>
            </form>
        </div>
        
        <div class="step">
            <h3>Passo 4: Popular Completo (2026)</h3>
            <p>Adiciona todos os times e jogadores reais de 2026: Brasileirão A/B/C, Premier League, La Liga, Serie A, Bundesliga, Ligue 1, Paulistão, Cariocão, Libertadores, Champions League, Copa do Mundo e mais.</p>
            <form method="POST">
                <input type="hidden" name="acao" value="popular_completo">
                <label>Selecione o Save:</label>
                <select name="id_save">
                    <?php foreach ($saves as $s): ?>
                        <option value="<?php echo $s['id']; ?>"><?php echo htmlspecialchars($s['nome']); ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit">Popular Completo 2026</button>
            </form>
        </div>
        
        <div class="step">
            <h3>Finalizar</h3>
            <p>Após completar os passos acima, acesse o jogo:</p>
            <a href="index.php"><button type="button">Ir para o Jogo</button></a>
        </div>
    </div>
</body>
</html>
