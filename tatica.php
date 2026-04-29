<?php
require_once 'classes/Tatica.php';
require_once 'classes/Time.php';
require_once 'classes/Save.php';

$save = new Save();
$save_ativo = $save->buscarAtivo();
$id_save = $save_ativo['id'] ?? 1;

$taticaModel = new Tatica();
$timeModel = new Time();

$times = $timeModel->listar(['id_save' => $id_save]);
$time_selecionado = null;
$tatica = null;

if (isset($_GET['time_id'])) {
    $time_selecionado = $timeModel->buscarPorId($_GET['time_id']);
    if ($time_selecionado) {
        $tatica = $taticaModel->buscarPorTime($time_selecionado['id'], $id_save);
    }
}

$mensagem = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao'])) {
    if ($_POST['acao'] === 'salvar' && isset($_POST['time_id'])) {
        $taticaModel->atualizar($tatica['id'], [
            'formacao' => $_POST['formacao'],
            'estilo' => $_POST['estilo'],
            'marcacao' => $_POST['marcacao'],
            'controle' => $_POST['controle'],
            'ataque' => $_POST['ataque'],
            'laterais' => $_POST['laterais']
        ]);
        $mensagem = 'Tática salva com sucesso!';
        $tatica = $taticaModel->buscarPorTime($_POST['time_id'], $id_save);
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Tática - Fenix Foot</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:Arial,sans-serif; background:#f0f0f0; }
        .header { background:#2c3e50; color:white; padding:20px; text-align:center; }
        .container { max-width:1000px; margin:20px auto; padding:0 20px; }
        .btn { background:#3498db; color:white; padding:10px 20px; text-decoration:none; border:none; border-radius:5px; cursor:pointer; display:inline-block; margin:5px; }
        .form-container { background:white; padding:20px; border-radius:10px; margin:20px 0; box-shadow:0 2px 10px rgba(0,0,0,0.1); }
        .form-group { margin:15px 0; }
        .form-group label { display:block; margin-bottom:5px; font-weight:bold; }
        .form-group select, .form-group input { width:100%; padding:8px; border:1px solid #ddd; border-radius:5px; }
        .tatica-visual { background:#2c3e50; color:white; padding:20px; border-radius:10px; margin:20px 0; text-align:center; }
        .field { display:flex; flex-direction:column; gap:10px; }
        .line { display:flex; justify-content:center; gap:10px; }
        .player { background:#3498db; padding:5px 10px; border-radius:5px; font-size:0.9em; }
        .success { background:#2ecc71; color:white; padding:10px; border-radius:5px; margin:10px 0; }
    <script src="js/audio.js"></script>
</head>
<body>
    <div class="header">
        <h1>📋 Tática</h1>
        <a href="index.php" class="btn">Voltar</a>
    </div>
    
    <div class="container">
        <?php if ($mensagem): ?>
            <div class="success"><?php echo htmlspecialchars($mensagem); ?></div>
        <?php endif; ?>
        
        <div class="form-container">
            <h3>Selecionar Time</h3>
            <form method="GET">
                <div class="form-group">
                    <label>Time:</label>
                    <select name="time_id" onchange="this.form.submit()">
                        <option value="">-- Selecionar --</option>
                        <?php foreach ($times as $t): ?>
                            <option value="<?php echo $t['id']; ?>" <?php echo (isset($_GET['time_id']) && $_GET['time_id'] == $t['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($t['nome']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>
        </div>
        
        <?php if ($time_selecionado && $tatica): ?>
            <div class="tatica-visual">
                <h3><?php echo htmlspecialchars($time_selecionado['nome']); ?> - <?php echo $tatica['formacao']; ?></h3>
                <div class="field">
                    <?php
                    $formacao = $tatica['formacao'];
                    if ($formacao === '4-4-2') {
                        echo '<div class="line"><div class="player">GOL</div></div>';
                        echo '<div class="line"><div class="player">LAT</div><div class="player">ZAG</div><div class="player">ZAG</div><div class="player">LAT</div></div>';
                        echo '<div class="line"><div class="player">MEI</div><div class="player">MEI</div><div class="player">MEI</div><div class="player">MEI</div></div>';
                        echo '<div class="line"><div class="player">ATA</div><div class="player">ATA</div></div>';
                    }
                    ?>
                </div>
            </div>
            
            <div class="form-container">
                <form method="POST">
                    <input type="hidden" name="acao" value="salvar">
                    <input type="hidden" name="time_id" value="<?php echo $time_selecionado['id']; ?>">
                    
                    <div class="form-group">
                        <label>Formação:</label>
                        <select name="formacao">
                            <?php foreach ($taticaModel->listarFormacoes() as $key => $value): ?>
                                <option value="<?php echo $key; ?>" <?php echo $tatica['formacao'] == $key ? 'selected' : ''; ?>>
                                    <?php echo $value; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Estilo:</label>
                        <select name="estilo">
                            <?php foreach ($taticaModel->listarEstilos() as $key => $value): ?>
                                <option value="<?php echo $key; ?>" <?php echo $tatica['estilo'] == $key ? 'selected' : ''; ?>>
                                    <?php echo $value; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Marcação: <?php echo $tatica['marcacao']; ?></label>
                        <input type="range" name="marcacao" min="0" max="100" value="<?php echo $tatica['marcacao']; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Controle: <?php echo $tatica['controle']; ?></label>
                        <input type="range" name="controle" min="0" max="100" value="<?php echo $tatica['controle']; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Ataque: <?php echo $tatica['ataque']; ?></label>
                        <input type="range" name="ataque" min="0" max="100" value="<?php echo $tatica['ataque']; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Laterais: <?php echo $tatica['laterais']; ?></label>
                        <input type="range" name="laterais" min="0" max="100" value="<?php echo $tatica['laterais']; ?>">
                    </div>
                    
                    <button type="submit" class="btn">Salvar Tática</button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
