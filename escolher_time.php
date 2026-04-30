<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "autoload.php";
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$db = Database::getInstance()->getConnection();

// Get countries
$stmt = $db->query("SELECT DISTINCT pais FROM times ORDER BY pais");
$paises = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Handle nationality selection
$nacionalidade = "";
if (isset($_GET["nacionalidade"]) && !empty($_GET["nacionalidade"])) {
    $nacionalidade = $_GET["nacionalidade"];
    $stmt = $db->prepare("UPDATE users SET nacionalidade = ? WHERE id = ?");
    $stmt->execute([$nacionalidade, $_SESSION["user_id"]]);
}

// Handle club selection
if (isset($_POST["clube_id"])) {
    $clube_id = (int)$_POST["clube_id"];
    $stmt = $db->prepare("UPDATE users SET clube_id = ? WHERE id = ?");
    $stmt->execute([$clube_id, $_SESSION["user_id"]]);
    
    // Check if user_id column exists in saves
    $cols = $db->query("PRAGMA table_info(saves)")->fetchAll(PDO::FETCH_ASSOC);
    $has_user_id = false;
    foreach ($cols as $col) {
        if ($col['name'] == 'user_id') {
            $has_user_id = true;
            break;
        }
    }
    
    // Add user_id column if needed
    if (!$has_user_id) {
        $db->exec("ALTER TABLE saves ADD COLUMN user_id INTEGER DEFAULT NULL");
    }
    
    // Create save
    if ($has_user_id) {
        $stmt = $db->prepare("INSERT OR IGNORE INTO saves (user_id, ativo, nome_tecnico, temporada_atual) VALUES (?, 1, 'Técnico', '2026')");
        $stmt->execute([$_SESSION["user_id"]]);
    } else {
        $stmt = $db->prepare("INSERT OR IGNORE INTO saves (ativo, nome_tecnico, temporada_atual) VALUES (1, 'Técnico', '2026')");
        $stmt->execute();
    }
    
    header("Location: index.php");
    exit;
}

// Get clubs
$times = [];
if ($nacionalidade) {
    $stmt = $db->prepare("SELECT id, nome, pais FROM times WHERE pais = ? ORDER BY nome");
    $stmt->execute([$nacionalidade]);
    $times = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Escolher Clube - Fenix Foot 2026</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; font-family: Arial, sans-serif; }
        body { background: #050505; color: white; min-height: 100vh; padding: 40px 20px; }
        .container { max-width: 1200px; margin: 0 auto; }
        h1 { font-size: 2.5em; font-weight: 800; margin-bottom: 10px; color: #00d2ff; }
        .subtitle { color: rgba(255,255,255,0.5); margin-bottom: 40px; }
        .card { background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 20px; padding: 30px; margin-bottom: 30px; }
        label { display: block; margin-bottom: 8px; font-size: 0.75em; text-transform: uppercase; letter-spacing: 2px; color: #00d2ff; font-weight: 600; }
        select { width: 100%; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.1); padding: 15px 20px; border-radius: 12px; color: white; font-size: 1em; outline: none; }
        option { background: #050505; color: white; }
        .btn { width: 100%; background: linear-gradient(45deg, #00d2ff, #3a7bd5); color: white; padding: 15px; border: none; border-radius: 15px; font-size: 1.1em; font-weight: 700; cursor: pointer; margin-top: 10px; }
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px; margin-top: 20px; }
        .clube { background: rgba(255,255,255,0.03); border: 2px solid rgba(255,255,255,0.1); border-radius: 12px; padding: 20px; text-align: center; cursor: pointer; transition: 0.3s; }
        .clube:hover { border-color: #00d2ff; background: rgba(0, 210, 255, 0.1); }
        .clube.selected { border-color: #00d2ff; background: rgba(0, 210, 255, 0.2); }
        .clube-nome { font-weight: 600; margin-top: 10px; color: white; font-size: 1.1em; }
        .clube-pais { font-size: 0.8em; color: rgba(255,255,255,0.7); margin-top: 5px; }
        h3 { color: white; }
    </style>
</head>
<body>
    <div class="container">
        <h1>FENIX FOOT 2026</h1>
        <p class="subtitle">Escolha seu clube</p>
        
        <div class="card">
            <form method="GET">
                <label>Sua Nacionalidade</label>
                <select name="nacionalidade" onchange="this.form.submit()">
                    <option value="">Aguardando escolha...</option>
                    <?php foreach ($paises as $pais): ?>
                        <option value="<?php echo htmlspecialchars($pais); ?>" <?php echo ($nacionalidade == $pais) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($pais); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
        
        <?php if ($nacionalidade && count($times) > 0): ?>
        <div class="card">
            <h3 style="margin-bottom: 20px; color: white;">Clubes de <?php echo htmlspecialchars($nacionalidade); ?></h3>
            <form method="POST" id="clubeForm">
                <div class="grid">
                    <?php foreach ($times as $time): ?>
                        <div class="clube" onclick="selectClube(<?php echo $time['id']; ?>, this)">
                            <div class="clube-nome"><?php echo htmlspecialchars($time['nome']); ?></div>
                            <div class="clube-pais"><?php echo htmlspecialchars($time['pais']); ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <input type="hidden" name="clube_id" id="clube_id">
                <button type="submit" class="btn" id="btnConfirm" style="display:none;">CONFIRMAR CLUBE</button>
            </form>
        </div>
        <?php elseif ($nacionalidade): ?>
            <div class="card">
                <p style="color: #ff4757;">Nenhum clube encontrado para <?php echo htmlspecialchars($nacionalidade); ?></p>
            </div>
        <?php elseif (!$nacionalidade): ?>
            <div class="card" style="text-align: center; padding: 60px 20px;">
                <p style="color: rgba(255,255,255,0.5); font-size: 1.1em;">Selecione sua nacionalidade acima para ver os clubes disponíveis</p>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        function selectClube(id, element) {
            document.querySelectorAll('.clube').forEach(el => el.classList.remove('selected'));
            element.classList.add('selected');
            document.getElementById('clube_id').value = id;
            document.getElementById('btnConfirm').style.display = 'block';
        }
    </script>
</body>
</html>
