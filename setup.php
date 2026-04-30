<?php
require_once 'autoload.php';

$db = Database::getInstance();
$pdo = $db->getConnection();
$mensagem = '';
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';
    
    if ($acao === 'quick_setup') {
        $nome_tecnico = $_POST['nome_tecnico'] ?? 'Técnico';
        $time_favorito = $_POST['time_nome'] ?? 'Flamengo';
        $senha_tecnico = $_POST['senha_tecnico'] ?? '123456';
        $nacionalidade = $_POST['nacionalidade'] ?? 'Brasil';

        try {
            // 1. Criar Tabelas
            $db->createSchema();
            
            // Garantir colunas novas
            try { $pdo->exec("ALTER TABLE saves ADD COLUMN nome_tecnico VARCHAR(100)"); } catch(Exception $e){}
            try { $pdo->exec("ALTER TABLE saves ADD COLUMN clube_id INTEGER"); } catch(Exception $e){}

            // 2. Usuário Online (Cria se não existir, senão tenta logar)
            $userModel = new User();
            if (!$userModel->existe($nome_tecnico)) {
                $userModel->registrar($nome_tecnico, $senha_tecnico, null, null, $nacionalidade);
            }
            
            $user = $userModel->login($nome_tecnico, $senha_tecnico);
            if (!$user) {
                throw new Exception("O usuário '{$nome_tecnico}' já existe, mas a senha informada está incorreta.");
            }
            
            // Limpar tudo para um novo começo real
            $pdo->exec("DELETE FROM saves");
            $pdo->exec("DELETE FROM campeonatos");
            $pdo->exec("DELETE FROM times");
            $pdo->exec("DELETE FROM jogadores");
            $pdo->exec("DELETE FROM partidas");
            $pdo->exec("DELETE FROM calendario");
            $pdo->exec("DELETE FROM classificacao");
            
            // 2. Criar Save
            $saveModel = new Save();
            $id_save = $saveModel->criar('Brasfoot 2026', $nome_tecnico);
            $pdo->prepare("UPDATE saves SET nacionalidade = ? WHERE id = ?")->execute([$nacionalidade, $id_save]);
            $saveModel->ativar($id_save);
            
            // 3. Popular Completo (JSON -> DB)
            require_once 'popular_fenix.php';
            $res = popularFenix($id_save);

            // 4. Vincular o clube escolhido
            if ($res) {
                $stmt = $pdo->prepare("SELECT id FROM times WHERE nome LIKE ? AND id_save = ? LIMIT 1");
                $stmt->execute(["%$time_favorito%", $id_save]);
                $time = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($time) {
                    $pdo->prepare("UPDATE saves SET clube_id = ? WHERE id = ?")->execute([$time['id'], $id_save]);
                }
                header("Location: index.php");
                exit;
            } else {
                $erro = 'Erro ao gerar dados do mundo.';
            }
        } catch (Exception $e) {
            $erro = 'Erro crítico: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fenix Foot 2026 - Novo Jogo</title>
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
        body { 
            background: #050505;
            color: var(--text);
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }
        
        .bg-glow {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 800px;
            height: 800px;
            background: radial-gradient(circle, rgba(0, 210, 255, 0.15) 0%, transparent 70%);
            filter: blur(100px);
            z-index: -1;
        }

        .container { 
            width: 100%;
            max-width: 500px;
            padding: 40px; 
            background: var(--glass);
            backdrop-filter: blur(30px);
            border-radius: 40px;
            border: 1px solid var(--glass-border);
            text-align: center;
            box-shadow: 0 40px 100px rgba(0,0,0,0.8);
            position: relative;
            z-index: 10;
        }
        .logo-box {
            margin-bottom: 25px;
            animation: bounceIn 1.2s ease;
        }
        .logo-box img {
            width: 160px;
            filter: drop-shadow(0 0 30px var(--primary));
        }
        @keyframes bounceIn {
            0% { transform: scale(0); opacity: 0; }
            60% { transform: scale(1.1); }
            100% { transform: scale(1); opacity: 1; }
        }
            box-shadow: 0 40px 100px rgba(0,0,0,0.8);
            text-align: center;
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        h1 { font-size: 2.8em; font-weight: 800; margin-bottom: 10px; letter-spacing: -2px; }
        p.subtitle { color: rgba(255,255,255,0.5); margin-bottom: 40px; font-weight: 300; }

        .form-group { text-align: left; margin-bottom: 25px; }
        label { display: block; margin-bottom: 10px; font-size: 0.85em; text-transform: uppercase; letter-spacing: 2px; color: var(--primary); font-weight: 600; }
        
        input {
            width: 100%;
            background: rgba(255,255,255,0.03);
            border: 1px solid var(--glass-border);
            padding: 18px 25px;
            border-radius: 15px;
            color: white;
            font-size: 1.1em;
            transition: 0.3s;
            outline: none;
        }
        input:focus { border-color: var(--primary); background: rgba(255,255,255,0.07); box-shadow: 0 0 20px rgba(0, 210, 255, 0.2); }

        .btn-start {
            width: 100%;
            background: linear-gradient(45deg, var(--primary), var(--secondary));
            color: white;
            padding: 22px;
            border: none;
            border-radius: 20px;
            font-size: 1.2em;
            font-weight: 800;
            cursor: pointer;
            margin-top: 20px;
            transition: 0.4s;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 15px 30px rgba(0, 210, 255, 0.3);
        }
        .btn-start:hover { transform: translateY(-5px); box-shadow: 0 20px 40px rgba(0, 210, 255, 0.5); filter: brightness(1.1); }
        .btn-start:active { transform: translateY(0); }

        .loading-overlay {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: #000;
            z-index: 9999;
            display: none;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        .spinner {
            width: 60px; height: 60px;
            border: 5px solid rgba(255,255,255,0.1);
            border-top-color: var(--primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-bottom: 20px;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        .error { color: #ff4757; font-size: 0.9em; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="bg-glow"></div>
    
    <div class="container">
        <div class="logo-box">
            <img src="img/logo.png" alt="Fenix Foot">
        </div>
        <h1>FENIX FOOT 2026</h1>
        <p class="subtitle">The Ultimate Football Manager</p>

        <?php if ($erro): ?>
            <div class="error"><?php echo htmlspecialchars($erro); ?></div>
        <?php endif; ?>

        <form method="POST" id="setupForm">
            <input type="hidden" name="acao" value="quick_setup">
            
            <div class="form-group">
                <label>Nome do Treinador (Login)</label>
                <input type="text" name="nome_tecnico" placeholder="Seu nome..." required autocomplete="off">
            </div>

            <div class="form-group">
                <label>Senha da Conta</label>
                <input type="password" name="senha_tecnico" placeholder="Sua senha..." required>
            </div>

            <div class="form-group">
                <label>Nacionalidade</label>
                <select name="nacionalidade" id="nacionalidadeSelect" required>
                    <option value="Brasil">Brasil 🇧🇷</option>
                    <option value="Argentina">Argentina 🇦🇷</option>
                    <option value="Espanha">Espanha 🇪🇸</option>
                    <option value="Inglaterra">Inglaterra 🏴󠁧󠁢󠁥󠁮󠁧󠁿</option>
                    <option value="Itália">Itália 🇮🇹</option>
                    <option value="Alemanha">Alemanha 🇩🇪</option>
                    <option value="França">França 🇫🇷</option>
                    <option value="Portugal">Portugal 🇵🇹</option>
                </select>
            </div>

            <div class="form-group">
                <label>Time para Assumir</label>
                <select name="time_nome" id="timeSelect" required>
                    <option value="">Escolha seu país primeiro...</option>
                </select>
            </div>

            <button type="submit" class="btn-start">Começar Carreira 🚀</button>
        </form>
    </div>

    <div id="loader" class="loading-overlay">
        <div class="spinner"></div>
        <p>Configurando mundo Fenix...</p>
    </div>

    <script>
        const paisSelect = document.getElementById('nacionalidadeSelect');
        const teamSelect = document.getElementById('timeSelect');

        async function updateTeams() {
            const pais = paisSelect.value;
            teamSelect.innerHTML = '<option value="">Carregando clubes...</option>';
            
            try {
                const response = await fetch(`api_times.php?pais=${pais}`);
                const teams = await response.json();
                
                teamSelect.innerHTML = '<option value="">-- SELECIONE SEU CLUBE --</option>';
                
                // Agrupar por liga
                const groups = {};
                teams.forEach(t => {
                    if (!groups[t.liga]) groups[t.liga] = [];
                    groups[t.liga].push(t.nome);
                });

                // Criar optgroups
                for (const liga in groups) {
                    const group = document.createElement('optgroup');
                    group.label = liga;
                    
                    groups[liga].forEach(team => {
                        const opt = document.createElement('option');
                        opt.value = team;
                        opt.innerText = team;
                        group.appendChild(opt);
                    });
                    
                    teamSelect.appendChild(group);
                }
            } catch (e) {
                console.error("Erro ao carregar times:", e);
                teamSelect.innerHTML = '<option value="">Erro ao carregar times</option>';
            }
        }

        paisSelect.addEventListener('change', updateTeams);
        // Initial load
        updateTeams();

        document.getElementById('setupForm').onsubmit = function() {
            document.getElementById('loader').style.display = 'flex';
        };
    </script>
</body>
</html>
