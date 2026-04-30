<?php
require_once 'autoload.php';
session_start();

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }

$db = Database::getInstance()->getConnection();
$user_id = $_SESSION['user_id'];

// Get active save
$save = $db->query("SELECT * FROM saves WHERE ativo = 1 LIMIT 1")->fetch(PDO::FETCH_ASSOC);
$id_save = $save ? $save['id'] : 1;

// Get user's club
$user = $db->prepare("SELECT u.*, t.nome as time_nome FROM users u LEFT JOIN times t ON u.clube_id = t.id WHERE u.id = ?")->execute([$user_id])->fetch(PDO::FETCH_ASSOC);
$clube_id = $user['clube_id'] ?? 0;

// Get championships with classification - optimized query
$stmt = $db->prepare("SELECT c.*, 
    (SELECT COUNT(*) FROM classificacao cl WHERE cl.campeonato_id = c.id AND cl.id_save = c.id_save) as has_classificacao
    FROM campeonatos c WHERE c.id_save = ? ORDER BY c.pais, c.nome");
$stmt->execute([$id_save]);
$campeonatos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Campeonatos - Fenix Foot 2026</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; font-family: Arial, sans-serif; }
        body { background: #050505; color: white; min-height:100vh; }
        .header { padding:30px; text-align:center; background: rgba(0,0,0,0.4); border-bottom:1px solid rgba(255,255,255,0.1); }
        .header h1 { color: #00d2ff; font-size:2.5em; margin:0; }
        .container { max-width:1200px; margin:20px auto; padding:0 20px; }
        .btn { background: rgba(255,255,255,0.1); border:1px solid rgba(255,255,255,0.2); color:white; padding:10px 20px; text-decoration:none; border-radius:12px; cursor:pointer; display:inline-block; margin:5px; }
        .btn:hover { background: rgba(255,255,255,0.2); }
        
        .champ-card { background: rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1); border-radius:20px; padding:30px; margin:20px 0; }
        .champ-card h3 { color: #00d2ff; font-size:1.8em; margin:0 0 10px 0; }
        .champ-info { color: rgba(255,255,255,0.7); margin-bottom:15px; }
        
        table { width:100%; border-collapse:collapse; margin-top:20px; }
        th, td { padding:12px 15px; text-align:left; border-bottom:1px solid rgba(255,255,255,0.1); }
        th { background: rgba(255,255,255,0.05); color: #00d2ff; font-weight:600; text-transform:uppercase; font-size:0.8em; letter-spacing:1px; }
        tr:hover { background: rgba(255,255,255,0.02); }
        .pos { font-weight:800; width:40px; color: rgba(255,255,255,0.5); }
        .my-league { border-color: #00d2ff; }
        .tag { background: #00d2ff; color:black; padding:5px 12px; border-radius:10px; font-size:0.7em; font-weight:800; }
    </style>
</head>
<body>
    <div class="header">
        <h1>🏆 Campeonatos</h1>
        <a href="index.php" class="btn">Voltar</a>
    </div>
    
    <div class="container">
        <?php foreach ($campeonatos as $camp): 
            // Check if it's user's league
            $is_my_league = false;
            if ($clube_id > 0) {
                $stmt = $db->prepare("SELECT 1 FROM classificacao WHERE campeonato_id = ? AND time_id = ? AND id_save = ?");
                $stmt->execute([$camp['id'], $clube_id, $id_save]);
                $is_my_league = (bool)$stmt->fetch();
            }
        ?>
            <div class="champ-card <?php echo $is_my_league ? 'my-league' : ''; ?>">
                <div style="display:flex; justify-content:space-between; align-items:start;">
                    <div>
                        <h3><?php echo htmlspecialchars($camp['nome']); ?></h3>
                        <div class="champ-info">
                            <strong><?php echo $camp['pais']; ?></strong> | <?php echo $camp['tipo']; ?> | <?php echo $camp['temporada']; ?>
                        </div>
                    </div>
                    <?php if ($is_my_league): ?>
                        <span class="tag">Minha Liga ⭐</span>
                    <?php endif; ?>
                    <a href="calendario.php?id_camp=<?php echo $camp['id']; ?>" class="btn">VER CALENDÁRIO 📅</a>
                </div>
                
                <?php if ($camp['has_classificacao'] > 0): ?>
                <div style="margin-top:20px;">
                    <h4 style="font-size:0.9em; text-transform:uppercase; color:rgba(255,255,255,0.4); letter-spacing:1px;">Classificação Parcial</h4>
                    <?php 
                    $stmt = $db->prepare("SELECT cl.*, t.nome as time_nome FROM classificacao cl JOIN times t ON cl.time_id = t.id WHERE cl.campeonato_id = ? AND cl.id_save = ? ORDER BY cl.pontos DESC, (cl.gols_pro - cl.gols_contra) DESC LIMIT 10");
                    $stmt->execute([$camp['id'], $id_save]);
                    $classificacao = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Pos</th>
                                <th>Time</th>
                                <th>P</th>
                                <th>J</th>
                                <th>V</th>
                                <th>E</th>
                                <th>D</th>
                                <th>GP</th>
                                <th>GC</th>
                                <th>SG</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $pos = 1; foreach ($classificacao as $c): ?>
                                <tr>
                                    <td class="pos"><?php echo $pos++; ?></td>
                                    <td><?php echo htmlspecialchars($c['time_nome']); ?></td>
                                    <td><strong><?php echo $c['pontos']; ?></strong></td>
                                    <td><?php echo $c['jogos']; ?></td>
                                    <td><?php echo $c['vitorias']; ?></td>
                                    <td><?php echo $c['empates']; ?></td>
                                    <td><?php echo $c['derrotas']; ?></td>
                                    <td><?php echo $c['gols_pro']; ?></td>
                                    <td><?php echo $c['gols_contra']; ?></td>
                                    <td><?php echo $c['gols_pro'] - $c['gols_contra']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div style="padding:20px; text-align:center; color:rgba(255,255,255,0.5);">
                        Nenhuma partida jogada ainda. <a href="calendario.php?id_camp=<?php echo $camp['id']; ?>" style="color:#00d2ff;">Ir para o calendário</a>.
                    </div>
                <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
        
        <?php if (empty($campeonatos)): ?>
            <div style="text-align:center; padding:40px; color:rgba(255,255,255,0.5);">
                <h3 style="color:#00d2ff;">Nenhum campeonato encontrado</h3>
                <p>Crie campeonatos primeiro no setup ou aguarde a geração automática.</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
