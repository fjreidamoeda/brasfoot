<?php
require_once 'autoload.php';
session_start();

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }

$saveModel = new Save();
$userModel = new User();
$db = Database::getInstance()->getConnection();

$save_ativo = $saveModel->buscarAtivo();
if (!$save_ativo) { header("Location: setup.php"); exit; }

$id_save = $save_ativo['id'];
$user_id = $_SESSION['user_id'];
$user = $userModel->buscarPorId($user_id);

// Executar simulação de partidas pendentes da rodada
$partidaModel = new Partida();
$partidas_simuladas = $partidaModel->simularRestanteRodada($id_save);

// Avançar o dia
$saveModel->avancarDia($id_save);

// Atualizar data após avanço
$save_ativo = $saveModel->buscarAtivo();
$dia_atual = $save_ativo['dia_atual'];
$mes_atual = $save_ativo['mes_atual'];
$temporada = $save_ativo['temporada_atual'];

// Ganhos diários
$ganho = rand(5, 15);
$userModel->adicionarMoedas($user_id, $ganho);

// Buscar informações para o resumo
$clube_id = $user['clube_id'];

// Jogadores convidados (sondagem)
$jogadores_sondagem = $db->prepare("SELECT j.nome, j.posicao FROM jogadores j WHERE j.time_id != ? AND j.id_save = ? ORDER BY j.overall DESC LIMIT 3");
$jogadores_sondagem->execute([$clube_id, $id_save]);
$sondagem = $jogadores_sondagem->fetchAll(PDO::FETCH_ASSOC);

// Jogadores chegando (novos contratos)
$jogadores_chegando = $db->prepare("SELECT j.nome, j.posicao FROM jogadores j WHERE j.time_id = ? AND j.id_save = ? AND j.created_at >= datetime('now', '-1 day') LIMIT 3");
$jogadores_chegando->execute([$clube_id, $id_save]);
$chegando = $jogadores_chegando->fetchAll(PDO::FETCH_ASSOC);

// Cartões amarelos na última partida
$cartoes_amarelos = $db->prepare("SELECT j.nome FROM eventos_partida ep JOIN jogadores j ON ep.jogador_id = j.id WHERE ep.tipo = 'cartao_amarelo' AND ep.id_save = ? ORDER BY ep.id DESC LIMIT 5");
$cartoes_amarelos->execute([$id_save]);
$amarelos = $cartoes_amarelos->fetchAll(PDO::FETCH_ASSOC);

// Jogadores afastados (suspensos)
$afastados = $db->prepare("SELECT j.nome, j.posicao FROM jogadores j WHERE j.time_id = ? AND j.suspenso = 1 AND j.id_save = ?");
$afastados->execute([$clube_id, $id_save]);
$suspensos = $afastados->fetchAll(PDO::FETCH_ASSOC);

// Jogadores contundidos
$contundidos = $db->prepare("SELECT j.nome, j.posicao, j.dias_contusao as dias_restantes FROM jogadores j WHERE j.time_id = ? AND j.contundido = 1 AND j.id_save = ?");
$contundidos->execute([$clube_id, $id_save]);
$lesionados = $contundidos->fetchAll(PDO::FETCH_ASSOC);

// Jogadores em tratamento
$tratamento = $db->prepare("SELECT j.nome, j.posicao FROM jogadores j WHERE j.time_id = ? AND j.em_tratamento = 1 AND j.id_save = ?");
$tratamento->execute([$clube_id, $id_save]);
$tratando = $tratamento->fetchAll(PDO::FETCH_ASSOC);

// Próximo dia
$proximo_dia = $dia_atual + 1;
$proximo_mes = $mes_atual;
if ($proximo_dia > 30) {
    $proximo_dia = 1;
    $proximo_mes++;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Novo Dia - Fenix Foot 2026</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; font-family: Arial, sans-serif; }
        body { background: #050505; color: white; min-height: 100vh; padding: 20px; }
        .container { max-width: 1000px; margin: 0 auto; }
        h1 { color: #00d2ff; font-size: 2.5em; margin-bottom: 10px; }
        .date { color: rgba(255,255,255,0.5); margin-bottom: 30px; }
        .card { background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 20px; padding: 30px; margin-bottom: 20px; }
        h2 { color: #00d2ff; margin-bottom: 15px; font-size: 1.3em; }
        h3 { color: rgba(255,255,255,0.7); margin: 15px 0 10px 0; font-size: 1em; }
        .info-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px; margin-top: 15px; }
        .info-item { background: rgba(255,255,255,0.03); padding: 15px; border-radius: 10px; border-left: 3px solid #00d2ff; }
        .info-item strong { display: block; color: #00d2ff; font-size: 0.8em; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 5px; }
        .btn { display: inline-block; background: linear-gradient(45deg, #00d2ff, #3a7bd5); color: white; padding: 15px 40px; border: none; border-radius: 15px; font-size: 1.1em; font-weight: 700; cursor: pointer; text-decoration: none; margin-top: 30px; transition: 0.3s; }
        .btn:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(0, 210, 255, 0.3); }
        .event-list { list-style: none; padding: 0; }
        .event-list li { padding: 8px 0; border-bottom: 1px solid rgba(255,255,255,0.05); }
        .tag { display: inline-block; padding: 3px 8px; border-radius: 5px; font-size: 0.8em; font-weight: 600; margin-right: 5px; }
        .tag-yellow { background: #f1c40f; color: black; }
        .tag-injured { background: #e74c3c; color: white; }
        .tag-suspended { background: #95a5a6; color: white; }
        .tag-new { background: #2ecc71; color: white; }
        .tag-scout { background: #9b59b6; color: white; }
    </style>
</head>
<body>
    <div class="container">
        <h1>RESUMO DO DIA</h1>
        <div class="date">📅 <?php echo str_pad($dia_atual, 2, '0', STR_PAD_LEFT) . '/' . str_pad($mes_atual, 2, '0', STR_PAD_LEFT) . '/' . $temporada; ?></div>
        
        <div class="card">
            <h2>⚽ Partidas Simuladas</h2>
            <p style="font-size: 2em; font-weight: 800; color: #00d2ff;"><?php echo $partidas_simuladas; ?></p>
            <p style="color: rgba(255,255,255,0.5);">partidas da rodada atual foram simuladas</p>
        </div>
        
        <div class="card">
            <h2>📋 Situação do Elenco</h2>
            
            <?php if (!empty($amarelos)): ?>
            <h3>🟨 Jogadores com Cartão Amarelo</h3>
            <ul class="event-list">
                <?php foreach ($amarelos as $j): ?>
                    <li><span class="tag tag-yellow">AM</span> <?php echo htmlspecialchars($j['nome']); ?></li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
            
            <?php if (!empty($suspensos)): ?>
            <h3>🚫 Jogadores Afastados (Suspensos)</h3>
            <ul class="event-list">
                <?php foreach ($suspensos as $j): ?>
                    <li><span class="tag tag-suspended">AFAST</span> <?php echo htmlspecialchars($j['nome'] . ' (' . $j['posicao'] . ')'); ?></li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
            
            <?php if (!empty($lesionados)): ?>
            <h3>🏥 Jogadores Contundidos</h3>
            <ul class="event-list">
                <?php foreach ($lesionados as $j): ?>
                    <li><span class="tag tag-injured">LESÃO</span> <?php echo htmlspecialchars($j['nome'] . ' (' . $j['posicao'] . ') - ' . $j['dias_restantes'] . ' dias'); ?></li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
            
            <?php if (!empty($tratando)): ?>
            <h3>💊 Jogadores em Tratamento</h3>
            <ul class="event-list">
                <?php foreach ($tratando as $j): ?>
                    <li><span class="tag tag-injured">TRAT</span> <?php echo htmlspecialchars($j['nome'] . ' (' . $j['posicao'] . ')'); ?></li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
        </div>
        
        <div class="card">
            <h2>🔄 Movimentações</h2>
            
            <?php if (!empty($sondagem)): ?>
            <h3>🔍 Sendo Sondados (Poderiam sair)</h3>
            <ul class="event-list">
                <?php foreach ($sondagem as $j): ?>
                    <li><span class="tag tag-scout">SONDA</span> <?php echo htmlspecialchars($j['nome'] . ' (' . $j['posicao'] . ')'); ?></li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
            
            <?php if (!empty($chegando)): ?>
            <h3>🆕 Jogadores Chegando</h3>
            <ul class="event-list">
                <?php foreach ($chegando as $j): ?>
                    <li><span class="tag tag-new">NOVO</span> <?php echo htmlspecialchars($j['nome'] . ' (' . $j['posicao'] . ')'); ?></li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
        </div>
        
        <div class="card">
            <h2>💰 Finanças</h2>
            <p>Receita diária: <strong style="color: #2ecc71;">+<?php echo $ganho; ?> moedas</strong></p>
        </div>
        
        <div class="card">
            <h2>📅 Próximo Dia</h2>
            <p style="color: rgba(255,255,255,0.7);">
                O próximo dia será: <strong style="color: #00d2ff;"><?php echo str_pad($proximo_dia, 2, '0', STR_PAD_LEFT) . '/' . str_pad($proximo_mes, 2, '0', STR_PAD_LEFT); ?></strong>
            </p>
            <p style="color: rgba(255,255,255,0.5); font-size: 0.9em; margin-top: 10px;">
                Novas partidas serão simuladas, jogadores podem chegar ou sair, e novos eventos ocorrerão.
            </p>
        </div>
        
        <a href="index.php" class="btn">▶ INICIAR NOVO DIA</a>
    </div>
</body>
</html>
