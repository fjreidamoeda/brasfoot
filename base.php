<?php
require_once 'classes/Base.php';
require_once 'classes/Time.php';
require_once 'classes/Jogador.php';
require_once 'classes/Save.php';

$save = new Save();
$save_ativo = $save->buscarAtivo();
$id_save = $save_ativo['id'] ?? 1;

$baseModel = new Base();
$timeModel = new Time();
$jogadorModel = new Jogador();

$times = $timeModel->listar(['id_save' => $id_save]);
$time_selecionado = null;
$jovens = [];

if (isset($_GET['time_id'])) {
    $time_selecionado = $timeModel->buscarPorId($_GET['time_id']);
    if ($time_selecionado) {
        $jovens = $baseModel->listarPorTime($time_selecionado['id'], null, $id_save);
    }
}

$mensagem = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao'])) {
    if ($_POST['acao'] === 'gerar' && isset($_POST['time_id'])) {
        $jogador_id = $baseModel->gerarJovemPromissor($_POST['time_id'], $id_save);
        $baseModel->criar([
            'time_id' => $_POST['time_id'],
            'jogador_id' => $jogador_id,
            'categoria' => 'Sub-20',
            'id_save' => $id_save
        ]);
        $mensagem = 'Jovem promissor gerado com sucesso!';
        if ($time_selecionado) {
            $jovens = $baseModel->listarPorTime($time_selecionado['id'], null, $id_save);
        }
    } elseif ($_POST['acao'] === 'promover' && isset($_POST['base_id'])) {
        $resultado = $baseModel->promoverParaProfissional($_POST['base_id']);
        if ($resultado) {
            $mensagem = 'Jogador promovido à equipe profissional!';
        }
        if ($time_selecionado) {
            $jovens = $baseModel->listarPorTime($time_selecionado['id'], null, $id_save);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Base - Fenix Foot</title>
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
        .container { max-width:1200px; margin:20px auto; padding:0 20px; }
        .btn { background: var(--glass); border: 1px solid var(--glass-border); color:white; padding:10px 20px; text-decoration:none; border-radius:12px; cursor:pointer; display:inline-block; margin:5px; transition: all 0.3s; backdrop-filter: blur(5px); }
        .btn:hover { background: rgba(255,255,255,0.2); transform: translateY(-2px); border-color: var(--primary); }
        
        table { width:100%; background: var(--glass); backdrop-filter: blur(10px); border-radius:20px; overflow:hidden; border: 1px solid var(--glass-border); margin:20px 0; border-collapse:collapse; box-shadow: 0 10px 30px rgba(0,0,0,0.3); }
        th, td { padding:15px 20px; text-align:left; border-bottom:1px solid var(--glass-border); }
        th { background: rgba(255,255,255,0.05); color: var(--primary); font-weight: 600; text-transform: uppercase; font-size: 0.85em; letter-spacing: 1px; }
        tr:hover { background: rgba(255,255,255,0.05); }
        
        .form-container { background: var(--glass); backdrop-filter: blur(15px); border: 1px solid var(--glass-border); padding:30px; border-radius:20px; margin:20px 0; }
        select { 
            width:100%; padding:12px; border:1px solid var(--glass-border); border-radius:12px; 
            background: rgba(0,0,0,0.2); color: white; outline: none; transition: 0.3s;
        }
        .success { background: rgba(39, 174, 96, 0.2); border: 1px solid #27ae60; color:#2ecc71; padding:15px; border-radius:12px; margin:10px 0; }
        .stat { color: var(--primary); font-weight: 600; }
    </style>
    <script src="js/audio.js"></script>
</head>
<body>
    <div class="header">
        <h1>🌱 Base / Academia de Jovens</h1>
        <a href="index.php" class="btn">Voltar</a>
    </div>
    
    <div class="container">
        <?php if ($mensagem): ?>
            <div class="success"><?php echo htmlspecialchars($mensagem); ?></div>
        <?php endif; ?>
        
        <div class="form-container">
            <h3>Selecionar Time</h3>
            <form method="GET">
                <select name="time_id" onchange="this.form.submit()">
                    <option value="">-- Selecionar --</option>
                    <?php foreach ($times as $t): ?>
                        <option value="<?php echo $t['id']; ?>" <?php echo (isset($_GET['time_id']) && $_GET['time_id'] == $t['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($t['nome']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
        
        <?php if ($time_selecionado): ?>
            <div class="form-container">
                <h3>Gerar Novo Jovem Promissor</h3>
                <form method="POST">
                    <input type="hidden" name="acao" value="gerar">
                    <input type="hidden" name="time_id" value="<?php echo $time_selecionado['id']; ?>">
                    <button type="submit" class="btn">Gerar Jovem</button>
                </form>
            </div>
            
            <h3>Jovens da Base: <?php echo htmlspecialchars($time_selecionado['nome']); ?></h3>
            <table>
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Posição</th>
                        <th>Idade</th>
                        <th>Overall</th>
                        <th>Potencial</th>
                        <th>Categoria</th>
                        <th>Ação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($jovens as $j): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($j['nome']); ?></strong></td>
                        <td><?php echo htmlspecialchars($j['posicao']); ?></td>
                        <td><span class="stat"><?php echo $j['idade'] ?? 16; ?></span></td>
                        <td><span class="stat"><?php echo $j['overall']; ?></span></td>
                        <td><span class="stat"><?php echo $j['potencial']; ?></span></td>
                        <td><?php echo htmlspecialchars($j['categoria']); ?></td>
                        <td>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="acao" value="promover">
                                <input type="hidden" name="base_id" value="<?php echo $j['id']; ?>">
                                <button type="submit" class="btn" style="padding: 5px 12px; font-size: 0.8em;" onclick="return confirm('Promover para profissional?')">Promover</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>
