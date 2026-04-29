<?php
require_once 'classes/Campeonato.php';
require_once 'classes/Save.php';
require_once 'classes/Partida.php';

$save = new Save();
$save_ativo = $save->buscarAtivo();
$id_save = $save_ativo['id'] ?? 1;

$campeonatoModel = new Campeonato();
$partidaModel = new Partida();

$campeonatos = $campeonatoModel->listar($id_save);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Campeonatos - Fenix Foot</title>
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
        
        .champ-card { 
            background: var(--glass); 
            backdrop-filter: blur(15px); 
            padding:30px; 
            border-radius:30px; 
            margin:30px 0; 
            border: 1px solid var(--glass-border);
            box-shadow: 0 15px 35px rgba(0,0,0,0.3);
        }
        .champ-card h3 { color: var(--primary); font-size: 1.8em; margin-bottom:15px; font-weight: 800; }
        
        table { width:100%; border-collapse:collapse; margin-top:20px; background: rgba(0,0,0,0.2); border-radius: 20px; overflow: hidden; }
        th, td { padding:15px; text-align:left; border-bottom:1px solid var(--glass-border); }
        th { background: rgba(255,255,255,0.05); color: var(--primary); font-weight: 600; text-transform: uppercase; font-size: 0.8em; letter-spacing: 1px; }
        tr:hover { background: rgba(255,255,255,0.05); }
        .pos { font-weight:800; width:40px; color: rgba(255,255,255,0.5); }
        tr:nth-child(-n+4) .pos { color: #2ecc71; } /* G4 */
        tr:nth-child(n+17) .pos { color: #ff007a; } /* Z4 */
        .points { font-weight: 800; color: white; }
    </style>
</head>
<body>
    <div class="header">
        <h1>🏆 Campeonatos</h1>
        <a href="index.php" class="btn">Voltar</a>
    </div>
    
    <div class="container">
        <?php foreach ($campeonatos as $camp): ?>
            <div class="champ-card">
                <h3><?php echo htmlspecialchars($camp['nome']); ?></h3>
                <p><strong>Tipo:</strong> <?php echo $camp['tipo']; ?> | 
                   <strong>País:</strong> <?php echo $camp['pais']; ?> | 
                   <strong>Temporada:</strong> <?php echo $camp['temporada']; ?></p>
                
                <div class="standings">
                    <h4>Classificação</h4>
                    <?php $classificacao = $campeonatoModel->gerarClassificacao($camp['id'], $id_save); ?>
                    
                    <?php if (empty($classificacao) || $classificacao[0]['pontos'] == 0): ?>
                        <p style="padding:10px; background:#ecf0f1; border-radius:5px;">
                            Nenhuma partida jogada ainda. <a href="calendario.php">Ir para o calendário</a>.
                        </p>
                    <?php else: ?>
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
                                        <td><?php echo htmlspecialchars($c['nome']); ?></td>
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
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>
