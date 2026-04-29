<?php
require_once 'classes/Financas.php';
require_once 'classes/Time.php';
require_once 'classes/Save.php';

$save = new Save();
$save_ativo = $save->buscarAtivo();
$id_save = $save_ativo['id'] ?? 1;

$financasModel = new Financas();
$timeModel = new Time();

$times = $timeModel->listar(['id_save' => $id_save]);
$time_selecionado = null;
$lancamentos = [];
$saldo = null;

if (isset($_GET['time_id'])) {
    $time_selecionado = $timeModel->buscarPorId($_GET['time_id']);
    if ($time_selecionado) {
        $lancamentos = $financasModel->listarPorTime($time_selecionado['id'], 100, $id_save);
        $saldo = $financasModel->calcularSaldo($time_selecionado['id'], $id_save);
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Finanças - Fenix Foot</title>
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
        .form-group select { 
            width:100%; padding:12px; border:1px solid var(--glass-border); border-radius:12px; 
            background: rgba(0,0,0,0.2); color: white; outline: none; transition: 0.3s;
        }
        .receita { color:#2ecc71; font-weight: 600; }
        .despesa { color:#ff007a; font-weight: 600; }
        .saldo { background: var(--glass); padding:25px; border-radius:20px; margin:20px 0; text-align:center; font-size:1.5em; border: 1px solid var(--glass-border); }
        .saldo span { color: var(--primary); font-weight: 800; }
    </style>
</head>
<body>
    <div class="header">
        <h1>💰 Finanças</h1>
        <a href="index.php" class="btn">Voltar</a>
    </div>
    
    <div class="container">
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
        
        <?php if ($time_selecionado && $saldo): ?>
            <div class="saldo">
                <strong><?php echo htmlspecialchars($time_selecionado['nome']); ?></strong><br>
                Saldo Atual: R$ <?php echo number_format(($saldo['total_entradas'] ?? 0) - ($saldo['total_saidas'] ?? 0), 2, ',', '.'); ?>
            </div>
            
            <h3>Lançamentos Recentes</h3>
            <table>
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Tipo</th>
                        <th>Descrição</th>
                        <th>Valor</th>
                        <th>Saldo Anterior</th>
                        <th>Saldo Posterior</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($lancamentos as $l): ?>
                    <tr>
                        <td><?php echo date('d/m/Y', strtotime($l['data_lancamento'])); ?></td>
                        <td><?php echo htmlspecialchars($l['tipo']); ?></td>
                        <td><?php echo htmlspecialchars($l['descricao'] ?? ''); ?></td>
                        <td class="<?php echo $l['valor'] > 0 ? 'receita' : 'despesa'; ?>">
                            R$ <?php echo number_format($l['valor'], 2, ',', '.'); ?>
                        </td>
                        <td>R$ <?php echo number_format($l['saldo_anterior'], 2, ',', '.'); ?></td>
                        <td>R$ <?php echo number_format($l['saldo_posterior'], 2, ',', '.'); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>
