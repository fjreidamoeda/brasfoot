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

$times = $timeModel->listar(['id_save' => $id_save]);
$jogadores_disponiveis = $jogadorModel->listarDisponiveis($id_save);

$mensagem = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao'])) {
    if ($_POST['acao'] === 'transferir') {
        $resultado = $jogadorModel->transferir(
            $_POST['jogador_id'],
            $_POST['clube_origem_id'] ?: null,
            $_POST['clube_destino_id'],
            $_POST['valor'],
            $_POST['tipo']
        );
        if ($resultado) {
            $mensagem = 'Transferência realizada com sucesso!';
        } else {
            $mensagem = 'Erro na transferência.';
        }
        $jogadores_disponiveis = $jogadorModel->listarDisponiveis($id_save);
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Transferências - Fenix Foot</title>
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
    </style>
    <script src="js/audio.js"></script>
</head>
</head>
<body>
    <div class="header">
        <h1>🔄 Transferências</h1>
        <a href="index.php" class="btn">Voltar</a>
    </div>
    
    <div class="container">
        <?php if ($mensagem): ?>
            <div class="success"><?php echo htmlspecialchars($mensagem); ?></div>
        <?php endif; ?>
        
        <?php 
        $todos_jogadores = $jogadorModel->listarPorClube(null, $id_save); // This might need a change in listarPorClube to allow null
        // Let's use a custom query for all players
        $sql = "SELECT j.*, t.nome as clube_nome FROM jogadores j LEFT JOIN times t ON j.clube_id = t.id WHERE j.id_save = :id_save ORDER BY j.overall DESC";
        $stmt = Database::getInstance()->getConnection()->prepare($sql);
        $stmt->execute([':id_save' => $id_save]);
        $todos_jogadores = $stmt->fetchAll(PDO::FETCH_ASSOC);
        ?>

        <div class="form-container">
            <h3>Nova Transferência</h3>
            <form method="POST">
                <input type="hidden" name="acao" value="transferir">
                <div class="form-group">
                    <label>Jogador:</label>
                    <select name="jogador_id" required id="select-jogador" onchange="updateOrigem()">
                        <option value="">-- Selecionar Jogador --</option>
                        <?php foreach ($todos_jogadores as $j): ?>
                            <option value="<?php echo $j['id']; ?>" data-clube-id="<?php echo $j['clube_id']; ?>">
                                <?php echo htmlspecialchars($j['nome']); ?> - OV: <?php echo $j['overall']; ?> (<?php echo $j['clube_nome'] ?? 'Sem Clube'; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <input type="hidden" name="clube_origem_id" id="clube_origem_id">
                
                <div class="form-group">
                    <label>Time de Destino:</label>
                    <select name="clube_destino_id" required>
                        <option value="">-- Selecionar Time --</option>
                        <?php foreach ($times as $t): ?>
                            <option value="<?php echo $t['id']; ?>"><?php echo htmlspecialchars($t['nome']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Valor da Transferência (R$):</label>
                    <input type="number" name="valor" step="1000" required placeholder="Ex: 5000000">
                </div>
                <div class="form-group">
                    <label>Tipo:</label>
                    <select name="tipo">
                        <option value="Compra">Compra</option>
                        <option value="Empréstimo">Empréstimo</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-success">Confirmar Negócio</button>
            </form>
        </div>

        <script>
            function updateOrigem() {
                const sel = document.getElementById('select-jogador');
                const opt = sel.options[sel.selectedIndex];
                document.getElementById('clube_origem_id').value = opt.getAttribute('data-clube-id') || '';
            }
        </script>
        
        <h3>Jogadores Disponíveis</h3>
        <table>
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Posição</th>
                    <th>Overall</th>
                    <th>Potencial</th>
                    <th>Valor</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($jogadores_disponiveis as $j): ?>
                <tr>
                    <td><?php echo htmlspecialchars($j['nome']); ?></td>
                    <td><?php echo htmlspecialchars($j['posicao']); ?></td>
                    <td><strong><?php echo $j['overall']; ?></strong></td>
                    <td><?php echo $j['potencial']; ?></td>
                    <td>R$ <?php echo number_format($j['valor_mercado'], 0, ',', '.'); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
