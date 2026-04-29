<?php
require_once 'classes/Database.class.php';
require_once 'classes/Jogador.php';
require_once 'classes/Time.php';
require_once 'classes/Save.php';

$save = new Save();
$save_ativo = $save->buscarAtivo();
$id_save = $save_ativo['id'] ?? 1;

$jogadorModel = new Jogador();
$timeModel = new Time();

$clube_id = $_GET['clube_id'] ?? null;
$disponiveis = isset($_GET['disponiveis']);

if ($clube_id) {
    $jogadores = $jogadorModel->listarPorClube($clube_id, $id_save);
    $time_selecionado = $timeModel->buscarPorId($clube_id);
} elseif ($disponiveis) {
    $jogadores = $jogadorModel->listarDisponiveis($id_save);
    $time_selecionado = null;
} else {
    $jogadores = [];
    $time_selecionado = null;
}

$times = $timeModel->listar(['id_save' => $id_save]);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jogadores - Fenix Foot</title>
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
        }
        .header { 
            padding: 30px; 
            text-align: center; 
            background: rgba(0,0,0,0.4);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--glass-border);
        }
        .header h1 { font-size: 2.5em; font-weight: 800; letter-spacing: -1px; }
        .container { max-width:1400px; margin:20px auto; padding:0 20px; }
        .btn { background: var(--glass); border: 1px solid var(--glass-border); color:white; padding:10px 20px; text-decoration:none; border-radius:12px; cursor:pointer; display:inline-block; margin:5px; transition: all 0.3s; backdrop-filter: blur(5px); }
        .btn:hover { background: rgba(255,255,255,0.2); transform: translateY(-2px); border-color: var(--primary); }
        
        table { width:100%; background: var(--glass); backdrop-filter: blur(10px); border-radius:20px; overflow:hidden; border: 1px solid var(--glass-border); margin:20px 0; border-collapse:collapse; box-shadow: 0 10px 30px rgba(0,0,0,0.3); }
        th, td { padding:12px 15px; text-align:left; border-bottom:1px solid var(--glass-border); font-size: 0.9em; }
        th { background: rgba(255,255,255,0.05); color: var(--primary); font-weight: 600; text-transform: uppercase; font-size: 0.8em; letter-spacing: 1px; }
        tr:hover { background: rgba(255,255,255,0.05); }
        
        .filter { background: var(--glass); backdrop-filter: blur(15px); border: 1px solid var(--glass-border); padding:20px; border-radius:20px; margin:20px 0; display: flex; align-items: center; gap: 15px; flex-wrap: wrap; }
        .filter select { 
            padding:10px; border:1px solid var(--glass-border); border-radius:12px; 
            background: rgba(0,0,0,0.2); color: white; outline: none; transition: 0.3s;
        }
        .filter select:focus { border-color: var(--primary); }
        h2 { margin: 20px 0; font-weight: 600; letter-spacing: -1px; }
        .stat { color: var(--primary); font-weight: 600; }
    </style>
    <script src="js/audio.js"></script>
</head>
<body>
    <div class="header">
        <h1>👤 Jogadores</h1>
        <a href="index.php" class="btn">Voltar</a>
    </div>
    
    <div class="container">
        <div class="filter">
            <form method="GET">
                <label>Filtrar por Time:</label>
                <select name="clube_id" onchange="this.form.submit()">
                    <option value="">-- Selecionar --</option>
                    <?php foreach ($times as $t): ?>
                        <option value="<?php echo $t['id']; ?>" <?php echo ($clube_id == $t['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($t['nome']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <a href="jogadores.php?disponiveis=1" class="btn">Livres (Sem Clube)</a>
                <a href="jogadores.php" class="btn">Limpar Filtro</a>
            </form>
        </div>
        
        <?php if ($time_selecionado): ?>
            <h2>Elenco: <?php echo htmlspecialchars($time_selecionado['nome']); ?></h2>
        <?php elseif ($disponiveis): ?>
            <h2>Jogadores Disponíveis (Sem Clube)</h2>
        <?php endif; ?>
        
        <table>
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Posição</th>
                    <th>Overall</th>
                    <th>Potencial</th>
                    <th>Velocidade</th>
                    <th>Finalização</th>
                    <th>Passe</th>
                    <th>Defesa</th>
                    <th>Físico</th>
                    <th>Valor</th>
                    <th>Salário</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($jogadores as $j): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($j['nome']); ?></strong></td>
                    <td><?php echo htmlspecialchars($j['posicao']); ?></td>
                    <td><span class="stat"><?php echo $j['overall']; ?></span></td>
                    <td><span class="stat"><?php echo $j['potencial']; ?></span></td>
                    <td><span class="stat"><?php echo $j['velocidade']; ?></span></td>
                    <td><span class="stat"><?php echo $j['finalizacao']; ?></span></td>
                    <td><span class="stat"><?php echo $j['passe']; ?></span></td>
                    <td><span class="stat"><?php echo $j['defesa']; ?></span></td>
                    <td><span class="stat"><?php echo $j['fisico']; ?></span></td>
                    <td><span style="color:#2ecc71">R$ <?php echo number_format($j['valor_mercado'], 0, ',', '.'); ?></span></td>
                    <td>R$ <?php echo number_format($j['salario'], 0, ',', '.'); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
