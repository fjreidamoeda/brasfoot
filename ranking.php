<?php
require_once 'autoload.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userModel = new User();
$ranking = $userModel->listarRanking();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ranking Mundial - Fenix Foot 2026</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --glass: rgba(255, 255, 255, 0.05);
            --glass-border: rgba(255, 255, 255, 0.1);
            --primary: #00d2ff;
            --secondary: #3a7bd5;
            --accent: #ff007a;
            --text: #ffffff;
        }
        * { margin:0; padding:0; box-sizing:border-box; font-family: 'Outfit', sans-serif; }
        body { background: #050505; color: var(--text); padding: 40px 20px; }
        .container { max-width: 800px; margin: 0 auto; background: var(--glass); backdrop-filter: blur(30px); padding: 40px; border-radius: 40px; border: 1px solid var(--glass-border); box-shadow: 0 40px 100px rgba(0,0,0,0.8); }
        h1 { font-size: 2.5em; font-weight: 800; text-align: center; margin-bottom: 40px; letter-spacing: -1px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { text-align: left; padding: 15px; color: var(--primary); text-transform: uppercase; font-size: 0.8em; letter-spacing: 2px; border-bottom: 1px solid var(--glass-border); }
        td { padding: 20px 15px; border-bottom: 1px solid rgba(255,255,255,0.05); }
        tr:hover { background: rgba(255,255,255,0.02); }
        .rank-pos { font-weight: 800; font-size: 1.2em; color: rgba(255,255,255,0.3); }
        .rank-name { font-weight: 600; font-size: 1.1em; }
        .rank-points { color: var(--accent); font-weight: 800; }
        .rank-coins { color: #ffd700; font-size: 0.9em; }
        .btn-back { display: inline-block; margin-top: 30px; color: var(--primary); text-decoration: none; font-weight: 600; font-size: 0.9em; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🏆 HALL DA FAMA</h1>
        
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Técnico</th>
                    <th>Pontos</th>
                    <th>Moedas</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ranking as $index => $r): ?>
                <tr>
                    <td class="rank-pos"><?php echo $index + 1; ?></td>
                    <td class="rank-name"><?php echo htmlspecialchars($r['username']); ?></td>
                    <td class="rank-points"><?php echo $r['ranking_pontos']; ?> pts</td>
                    <td class="rank-coins">💰 <?php echo number_format($r['moedas'], 0, ',', '.'); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <a href="index.php" class="btn-back">← Voltar ao Dashboard</a>
    </div>
</body>
</html>
