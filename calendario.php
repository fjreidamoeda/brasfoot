<?php
require_once 'classes/Database.class.php';
require_once 'classes/Partida.php';
require_once 'classes/Campeonato.php';
require_once 'classes/Save.php';

$save = new Save();
$save_ativo = $save->buscarAtivo();
$id_save = $save_ativo['id'] ?? 1;

$partidaModel = new Partida();
$campeonatoModel = new Campeonato();

$campeonatos = $campeonatoModel->listar($id_save);
$partidas_hoje = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simular'])) {
    $partida_id = $_POST['partida_id'];
    $resultado = $partidaModel->simular($partida_id);
    if ($resultado) {
        $mensagem = "Partida simulada: {$resultado['gols_casa']} x {$resultado['gols_fora']}";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendário - Fenix Foot</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:Arial,sans-serif; background:#f0f0f0; }
        .header { background:#2c3e50; color:white; padding:20px; text-align:center; }
        .container { max-width:1200px; margin:20px auto; padding:0 20px; }
        .btn { background:#3498db; color:white; padding:8px 16px; text-decoration:none; border:none; border-radius:5px; cursor:pointer; margin:5px; }
        .btn:hover { background:#2980b9; }
        .btn-success { background:#27ae60; }
        table { width:100%; background:white; border-radius:10px; overflow:hidden; box-shadow:0 2px 10px rgba(0,0,0,0.1); margin:20px 0; border-collapse:collapse; }
        th, td { padding:12px 15px; text-align:left; border-bottom:1px solid #ddd; }
        th { background:#34495e; color:white; }
        .match { font-size:1.1em; }
        .match strong { color:#2c3e50; }
        .eventos { background:#ecf0f1; padding:10px; border-radius:5px; margin:10px 0; max-height:200px; overflow-y:auto; }
        .evento { padding:5px; border-bottom:1px solid #ddd; }
        .gol { color:#27ae60; font-weight:bold; }
    </style>
    <script src="js/audio.js"></script>
    <script>
        function simularComSom(partidaId) {
            if (window.FenixAudio) {
                FenixAudio.whistle();
                setTimeout(() => {
                    window.location.href = 'partida_ao_vivo.php?id=' + partidaId;
                }, 300);
            } else {
                window.location.href = 'partida_ao_vivo.php?id=' + partidaId;
            }
            return false;
        }
    </script>
</head>
<body>
    <div class="header">
        <h1>📅 Calendário de Jogos</h1>
        <a href="index.php" class="btn">Voltar</a>
    </div>
    
    <div class="container">
        <?php if (isset($mensagem)): ?>
            <div style="background:#2ecc71; color:white; padding:10px; border-radius:5px; margin:10px 0;">
                <?php echo htmlspecialchars($mensagem); ?>
                <script>setTimeout(() => { if(window.FenixAudio) FenixAudio.goal(); }, 100);</script>
            </div>
        <?php endif; ?>
        
        <?php foreach ($campeonatos as $campeonato): ?>
            <h2><?php echo htmlspecialchars($campeonato['nome']); ?></h2>
            <?php $partidas = $partidaModel->listarPorCampeonato($campeonato['id'], null, $id_save); ?>
            
            <?php if (empty($partidas)): ?>
                <p style="text-align:center; padding:20px; background:white; border-radius:10px;">
                    Nenhuma partida agendada. <a href="setup.php">Criar partidas no setup</a>.
                </p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Rodada</th>
                            <th>Data</th>
                            <th>Jogo</th>
                            <th>Placar</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($partidas as $p): ?>
                        <tr>
                            <td><?php echo $p['rodada']; ?></td>
                            <td><?php echo date('d/m/Y', strtotime($p['data_partida'])); ?> <?php echo $p['hora']; ?></td>
                            <td class="match">
                                <strong><?php echo htmlspecialchars($p['time_casa_nome']); ?></strong> vs 
                                <strong><?php echo htmlspecialchars($p['time_fora_nome']); ?></strong>
                            </td>
                            <td>
                                <?php if ($p['jogada']): ?>
                                    <strong><?php echo $p['gols_casa']; ?> x <?php echo $p['gols_fora']; ?></strong>
                                <?php else: ?>
                                    - x -
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!$p['jogada']): ?>
                                    <form id="form-<?php echo $p['id']; ?>" method="POST" style="display:inline;">
                                        <input type="hidden" name="partida_id" value="<?php echo $p['id']; ?>">
                                        <input type="hidden" name="simular" value="1">
                                        <button type="button" class="btn btn-success" onclick="simularComSom(<?php echo $p['id']; ?>)">
                                            Simular
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <a href="partida_ao_vivo.php?id=<?php echo $p['id']; ?>" class="btn">🔊 Ver Eventos</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php if ($p['jogada'] && $p['eventos']): ?>
                        <tr>
                            <td colspan="5">
                                <div class="eventos">
                                    <strong>Eventos da partida:</strong><br>
                                    <?php 
                                    $eventos = json_decode($p['eventos'], true);
                                    if (is_array($eventos)) {
                                        foreach ($eventos as $e) {
                                            if ($e['tipo'] === 'gol') {
                                                echo '<div class="evento gol">⚽ ' . $e['minuto'] . "' " . htmlspecialchars($e['jogador']) . " (" . htmlspecialchars($e['time_nome']) . ")</div>";
                                            }
                                        }
                                    }
                                    ?>
                                </div>
                            </td>
                        </tr>
                        <?php endif; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</body>
</html>
