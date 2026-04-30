<?php
require_once 'autoload.php';
session_start();

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }

$saveModel = new Save();
$campeonatoModel = new Campeonato();
$save_ativo = $saveModel->buscarAtivo();

if (!$save_ativo) { header("Location: setup.php"); exit; }

$id_save = $save_ativo['id'];
$clube_id = $save_ativo['clube_id'];

// Obter o campeonato da liga do usuário
$db = Database::getInstance()->getConnection();
$stmt = $db->prepare("SELECT c.* FROM campeonatos c 
                      JOIN times t ON c.nome = t.liga 
                      WHERE t.id = ? AND t.id_save = ?");
$stmt->execute([$clube_id, $id_save]);
$campeonato = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$campeonato) {
    // Fallback: pegar o primeiro campeonato disponível no save
    $stmt = $db->prepare("SELECT * FROM campeonatos WHERE id_save = ? LIMIT 1");
    $stmt->execute([$id_save]);
    $campeonato = $stmt->fetch(PDO::FETCH_ASSOC);
}

$campeonato_id = $_GET['id'] ?? ($campeonato['id'] ?? 0);
$classificacao = $campeonatoModel->listarClassificacao($campeonato_id, $id_save);
$infoCamp = $campeonatoModel->buscarPorId($campeonato_id);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Classificação - <?php echo $infoCamp['nome'] ?? 'Brasfoot'; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --glass: rgba(255, 255, 255, 0.05);
            --glass-border: rgba(255, 255, 255, 0.1);
            --primary: #00d2ff;
            --accent: #ff007a;
            --bg: #0a0a0a;
        }
        body { background: var(--bg); color: white; font-family: 'Outfit', sans-serif; margin: 0; padding: 20px; }
        .container { max-width: 1000px; margin: 0 auto; }
        
        .header { text-align: center; margin-bottom: 40px; }
        .header h1 { font-size: 3em; font-weight: 800; margin-bottom: 10px; background: linear-gradient(to right, #fff, var(--primary)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        
        .table-card { background: var(--glass); backdrop-filter: blur(20px); border: 1px solid var(--glass-border); border-radius: 30px; padding: 20px; overflow: hidden; box-shadow: 0 20px 50px rgba(0,0,0,0.5); }
        
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th, td { padding: 15px 10px; border-bottom: 1px solid var(--glass-border); }
        th { font-size: 0.8em; text-transform: uppercase; letter-spacing: 1px; color: var(--primary); }
        
        .pos { width: 40px; font-weight: 800; color: #888; }
        .time-cell { display: flex; align-items: center; gap: 10px; }
        .time-cell strong { font-size: 1.1em; }
        .is-user { background: rgba(0, 210, 255, 0.15); }
        .is-user td { border-bottom-color: var(--primary); }
        
        .badge { padding: 4px 10px; border-radius: 10px; font-size: 0.7em; font-weight: 800; text-transform: uppercase; }
        .badge-g { background: #2ecc71; color: #000; }
        .badge-l { background: var(--primary); color: #000; }
        .badge-z { background: #e74c3c; color: white; }
        
        .back-btn { display: inline-block; margin-bottom: 20px; color: white; text-decoration: none; opacity: 0.6; transition: 0.3s; }
        .back-btn:hover { opacity: 1; transform: translateX(-5px); }
        
        .stats-col { text-align: center; width: 40px; font-weight: 600; }
        .pts-col { font-weight: 800; color: white; font-size: 1.2em; width: 50px; }

        /* Estilos de zona */
        tr:nth-child(-n+4) .pos { color: #2ecc71; } /* G4 */
        tr:nth-last-child(-n+4) .pos { color: #e74c3c; } /* Z4 */
    </style>
</head>
<body>
    <div class="container">
        <a href="index.php" class="back-btn">← Voltar ao Painel</a>
        
        <div class="header">
            <h1>📊 Classificação</h1>
            <p style="opacity: 0.6;"><?php echo $infoCamp['nome'] ?? 'Liga Desconhecida'; ?> - Temporada 2026</p>
        </div>
        
        <div class="table-card">
            <table>
                <thead>
                    <tr>
                        <th class="pos">#</th>
                        <th>Clube</th>
                        <th class="stats-col">P</th>
                        <th class="stats-col">J</th>
                        <th class="stats-col">V</th>
                        <th class="stats-col">E</th>
                        <th class="stats-col">D</th>
                        <th class="stats-col">GP</th>
                        <th class="stats-col">GC</th>
                        <th class="stats-col">SG</th>
                        <th class="pts-col">PTS</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($classificacao as $i => $row): 
                        $isUserTeam = ($row['id'] == $clube_id);
                    ?>
                    <tr class="<?php echo $isUserTeam ? 'is-user' : ''; ?>">
                        <td class="pos"><?php echo $i + 1; ?></td>
                        <td>
                            <div class="time-cell">
                                <strong><?php echo htmlspecialchars($row['nome']); ?></strong>
                                <?php if ($isUserTeam): ?>
                                    <span class="badge badge-l">Você</span>
                                <?php endif; ?>
                            </div>
                        </td>

                        <td class="stats-col"><?php echo $row['vitorias']; ?></td>
                        <td class="stats-col"><?php echo $row['empates']; ?></td>
                        <td class="stats-col"><?php echo $row['derrotas']; ?></td>
                        <td class="stats-col"><?php echo $row['gols_pro']; ?></td>
                        <td class="stats-col"><?php echo $row['gols_contra']; ?></td>
                        <td class="stats-col"><?php echo $row['saldo_gols']; ?></td>
                        <td class="pts-col"><?php echo $row['pontos']; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
