<?php
require_once 'classes/Database.class.php';
require_once 'classes/Time.php';
require_once 'classes/Save.php';

$save = new Save();
$save_ativo = $save->buscarAtivo();
$id_save = $save_ativo['id'] ?? 1;

$timeModel = new Time();
$times = $timeModel->listar(['id_save' => $id_save]);

$mensagem = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao'])) {
    if ($_POST['acao'] === 'criar') {
        $timeModel->criar([
            'nome' => $_POST['nome'],
            'sigla' => $_POST['sigla'],
            'cidade' => $_POST['cidade'],
            'estado' => $_POST['estado'],
            'pais' => $_POST['pais'],
            'liga' => $_POST['liga'],
            'divisao' => $_POST['divisao'],
            'id_save' => $id_save
        ]);
        $mensagem = 'Time criado com sucesso!';
        $times = $timeModel->listar(['id_save' => $id_save]);
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Times - Fenix Foot</title>
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
        .btn-success { background: var(--primary); color: #000; font-weight: 600; border: none; }
        .btn-success:hover { background: #fff; transform: scale(1.05); }
        
        table { width:100%; background: var(--glass); backdrop-filter: blur(10px); border-radius:20px; overflow:hidden; border: 1px solid var(--glass-border); margin:20px 0; border-collapse:collapse; box-shadow: 0 10px 30px rgba(0,0,0,0.3); }
        th, td { padding:15px 20px; text-align:left; border-bottom:1px solid var(--glass-border); }
        th { background: rgba(255,255,255,0.05); color: var(--primary); font-weight: 600; text-transform: uppercase; font-size: 0.85em; letter-spacing: 1px; }
        tr:hover { background: rgba(255,255,255,0.05); }
        
        .form-container { background: var(--glass); backdrop-filter: blur(15px); border: 1px solid var(--glass-border); padding:30px; border-radius:20px; margin:20px 0; }
        .form-group { margin:15px 0; }
        .form-group label { display:block; margin-bottom:8px; font-weight:400; font-size: 0.9em; color: rgba(255,255,255,0.7); }
        .form-group input, .form-group select { 
            width:100%; padding:12px; border:1px solid var(--glass-border); border-radius:12px; 
            background: rgba(0,0,0,0.2); color: white; outline: none; transition: 0.3s;
        }
        .form-group input:focus, .form-group select:focus { border-color: var(--primary); background: rgba(0,0,0,0.4); }
        
        .success { background: rgba(39, 174, 96, 0.2); border: 1px solid #27ae60; color:#2ecc71; padding:15px; border-radius:12px; margin:10px 0; }
        .filter { margin:30px 0; text-align: center; }
    </style>
    <script src="js/audio.js"></script>
</head>
<body>
    <div class="header">
        <h1>🏟️ Times</h1>
        <a href="index.php" class="btn">Voltar</a>
    </div>
    
    <div class="container">
        <?php if ($mensagem): ?>
            <div class="success"><?php echo htmlspecialchars($mensagem); ?></div>
        <?php endif; ?>
        
        <div class="filter">
            <a href="times.php" class="btn">Todos</a>
            <a href="times.php?liga=Brasileirão Série A 2026" class="btn">Série A</a>
            <a href="times.php?liga=Brasileirão Série B 2026" class="btn">Série B</a>
            <a href="times.php?liga=Premier League 2026/27" class="btn">Premier League</a>
            <a href="times.php?liga=La Liga 2026/27" class="btn">La Liga</a>
            <a href="times.php?liga=Champions League 2026" class="btn">Champions</a>
        </div>
        
        <div class="form-container">
            <h3>Novo Time</h3>
            <form method="POST">
                <input type="hidden" name="acao" value="criar">
                <div class="form-group">
                    <label>Nome:</label>
                    <input type="text" name="nome" required placeholder="Ex: Fenix FC">
                </div>
                <div class="form-group">
                    <label>Sigla:</label>
                    <input type="text" name="sigla" maxlength="10" placeholder="Ex: FFC">
                </div>
                <div class="form-group">
                    <label>Cidade:</label>
                    <input type="text" name="cidade" placeholder="Ex: Rio de Janeiro">
                </div>
                <div class="form-group">
                    <label>País:</label>
                    <input type="text" name="pais" value="Brasil">
                </div>
                <div class="form-group">
                    <label>Liga:</label>
                    <select name="liga">
                        <option value="Brasileirão Série A 2026">Brasileirão Série A 2026</option>
                        <option value="Brasileirão Série B 2026">Brasileirão Série B 2026</option>
                        <option value="Premier League 2026/27">Premier League 2026/27</option>
                        <option value="La Liga 2026/27">La Liga 2026/27</option>
                        <option value="Champions League 2026">Champions League 2026</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Divisão:</label>
                    <input type="number" name="divisao" value="1" min="1" max="2">
                </div>
                <button type="submit" class="btn btn-success">Criar Time</button>
            </form>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Sigla</th>
                    <th>Cidade</th>
                    <th>Liga</th>
                    <th>Divisão</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($times as $t): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($t['nome']); ?></strong></td>
                    <td><span style="opacity:0.7"><?php echo htmlspecialchars($t['sigla'] ?? ''); ?></span></td>
                    <td><?php echo htmlspecialchars($t['cidade'] ?? ''); ?></td>
                    <td><span style="color:var(--primary)"><?php echo htmlspecialchars($t['liga'] ?? ''); ?></span></td>
                    <td><?php echo $t['divisao']; ?>ª</td>
                    <td>
                        <a href="jogadores.php?clube_id=<?php echo $t['id']; ?>" class="btn" style="padding: 5px 15px; font-size: 0.8em;">Elenco</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
