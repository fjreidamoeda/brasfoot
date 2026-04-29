<?php
require_once 'autoload.php';
require_once 'classes/Partida.php';

$id = $_GET['id'] ?? null;
if (!$id) { header('Location: calendario.php'); exit; }

$partidaModel = new Partida();
$partida = $partidaModel->buscarPorId($id);

if (!$partida) { header('Location: calendario.php'); exit; }

// Se ainda não foi jogada, simula agora
if (!$partida['jogada']) {
    $resultado = $partidaModel->simular($id);
    $partida = $partidaModel->buscarPorId($id);
}

$eventos = json_decode($partida['eventos'], true);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title><?php echo $partida['time_casa_nome']; ?> vs <?php echo $partida['time_fora_nome']; ?> - Ao Vivo</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --glass: rgba(255, 255, 255, 0.1);
            --glass-border: rgba(255, 255, 255, 0.2);
            --primary: #00d2ff;
            --accent: #ff007a;
            --bg: #0f2027;
        }
        * { margin:0; padding:0; box-sizing:border-box; font-family: 'Outfit', sans-serif; }
        body { background: radial-gradient(circle at top, #1e3c72, #2a5298, #0f2027); min-height: 100vh; color: white; padding: 20px; overflow-x: hidden; }
        
        .match-card {
            max-width: 900px;
            margin: 40px auto;
            background: var(--glass);
            backdrop-filter: blur(20px);
            border-radius: 40px;
            border: 1px solid var(--glass-border);
            padding: 40px;
            text-align: center;
            box-shadow: 0 30px 60px rgba(0,0,0,0.5);
        }
        
        .league-name { font-size: 1.2em; text-transform: uppercase; letter-spacing: 4px; color: var(--primary); margin-bottom: 30px; opacity: 0.8; }
        
        .scoreboard {
            display: flex;
            justify-content: space-around;
            align-items: center;
            margin-bottom: 40px;
        }
        
        .team { flex: 1; }
        .team h2 { font-size: 2.5em; font-weight: 800; margin-bottom: 10px; }
        .score { font-size: 6em; font-weight: 800; background: linear-gradient(to bottom, #fff, #ccc); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        
        .timer {
            font-size: 2em;
            background: var(--accent);
            padding: 10px 30px;
            border-radius: 50px;
            font-weight: 800;
            box-shadow: 0 0 30px var(--accent);
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.05); opacity: 0.8; }
            100% { transform: scale(1); opacity: 1; }
        }
        
        .events-container {
            max-width: 700px;
            margin: 0 auto;
            height: 300px;
            overflow-y: auto;
            padding: 20px;
            background: rgba(0,0,0,0.3);
            border-radius: 20px;
            display: flex;
            flex-direction: column-reverse;
            gap: 10px;
        }
        
        .event-item {
            background: var(--glass);
            padding: 15px;
            border-radius: 15px;
            border-left: 5px solid var(--primary);
            text-align: left;
            animation: slideIn 0.5s ease-out;
            display: none; /* hidden by default, shown by JS */
        }
        
        .event-item.gol { border-left-color: #2ecc71; background: rgba(46, 204, 113, 0.1); }
        .event-item.vermelho { border-left-color: #e74c3c; background: rgba(231, 76, 60, 0.1); }
        .event-item.amarelo { border-left-color: #f1c40f; background: rgba(241, 196, 15, 0.1); }
        
        @keyframes slideIn {
            from { transform: translateX(-50px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        
        .btn { 
            display: inline-block; 
            margin-top: 30px; 
            background: white; 
            color: black; 
            padding: 15px 40px; 
            border-radius: 50px; 
            text-decoration: none; 
            font-weight: 800; 
            text-transform: uppercase; 
            transition: 0.3s;
        }
        .btn:hover { background: var(--primary); color: white; transform: scale(1.1); }
    </style>
    <script src="js/audio.js"></script>
</head>
<body>
    <div class="match-card">
        <div class="league-name"><?php echo $partida['campeonato_nome']; ?> - Rodada <?php echo $partida['rodada']; ?></div>
        
        <div class="scoreboard">
            <div class="team">
                <h2 id="home-name"><?php echo $partida['time_casa_nome']; ?></h2>
                <div class="score" id="home-score">0</div>
            </div>
            
            <div class="timer" id="match-timer">0'</div>
            
            <div class="team">
                <h2 id="away-name"><?php echo $partida['time_fora_nome']; ?></h2>
                <div class="score" id="away-score">0</div>
            </div>
        </div>
        
        <div class="events-container" id="events-log">
            <!-- Events will appear here -->
        </div>
        
        <div id="final-actions" style="display:none;">
            <a href="calendario.php" class="btn">Voltar ao Calendário</a>
        </div>
    </div>

    <script>
        const eventos = <?php echo json_encode($eventos); ?>;
        const totalGolsCasa = <?php echo $partida['gols_casa']; ?>;
        const totalGolsFora = <?php echo $partida['gols_fora']; ?>;
        
        let currentMinute = 0;
        let scoreCasa = 0;
        let scoreFora = 0;
        
        const timerEl = document.getElementById('match-timer');
        const homeScoreEl = document.getElementById('home-score');
        const awayScoreEl = document.getElementById('away-score');
        const eventsLog = document.getElementById('events-log');
        
        function narrate(text) {
            if ('speechSynthesis' in window) {
                const utterance = new SpeechSynthesisUtterance(text);
                utterance.lang = 'pt-BR';
                utterance.rate = 1.1;
                speechSynthesis.speak(utterance);
            }
        }

        function startMatch() {
            if (window.FenixAudio) FenixAudio.whistle();
            narrate("Inicia o jogo em " + "<?php echo $partida['campeonato_nome']; ?>");
            
            const interval = setInterval(() => {
                currentMinute++;
                timerEl.innerText = currentMinute + "'";
                
                // Procurar eventos neste minuto
                const evs = eventos.filter(e => e.minuto === currentMinute);
                evs.forEach(ev => {
                    displayEvent(ev);
                    let narrationText = "";
                    if (ev.tipo === 'gol') {
                        if (ev.time === 'casa') scoreCasa++;
                        else scoreFora++;
                        
                        homeScoreEl.innerText = scoreCasa;
                        awayScoreEl.innerText = scoreFora;
                        
                        narrationText = "GOOOOOL! do " + ev.time_nome + "! " + ev.jogador + " balança a rede!";
                        
                        if (window.FenixAudio) {
                            FenixAudio.goal();
                            FenixAudio.crowd();
                        }
                    } else if (ev.tipo === 'amarelo') {
                        narrationText = "Cartão amarelo para " + ev.jogador + " do " + ev.time_nome;
                    } else if (ev.tipo === 'vermelho') {
                        narrationText = "EXPULSO! Cartão vermelho para " + ev.jogador + " do " + ev.time_nome;
                    } else if (ev.tipo === 'lesao') {
                        narrationText = "Jogador caído! " + ev.jogador + " parece ter se lesionado.";
                    }
                    
                    if (narrationText) narrate(narrationText);
                });
                
                if (currentMinute >= 90) {
                    clearInterval(interval);
                    timerEl.innerText = "FIM";
                    narrate("Fim de jogo! Placar final: " + scoreCasa + " a " + scoreFora);
                    if (window.FenixAudio) FenixAudio.whistle();
                    document.getElementById('final-actions').style.display = 'block';
                }
            }, 500); // Slower interval to allow narration to breathe (500ms per minute)
        }
        
        function displayEvent(ev) {
            const div = document.createElement('div');
            div.className = 'event-item ' + ev.tipo;
            div.style.display = 'block';
            
            let icon = '⚽';
            if (ev.tipo === 'amarelo') icon = '🟨';
            if (ev.tipo === 'vermelho') icon = '🟥';
            if (ev.tipo === 'lesao') icon = '🚑';
            
            div.innerHTML = `<strong>${ev.minuto}'</strong> ${icon} <strong>${ev.jogador}</strong> (${ev.time_nome})`;
            eventsLog.appendChild(div);
            eventsLog.scrollTop = eventsLog.scrollHeight;
        }
        
        window.onload = startMatch;
    </script>
</body>
</html>
