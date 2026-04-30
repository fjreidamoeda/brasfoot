<?php
require_once "autoload.php";
session_start();

$auth = new Auth();
if (!isset($_SESSION["admin_id"]) || !$_SESSION["admin_logged"]) {
    header("Location: admin_login.php");
    exit;
}

$db = Database::getInstance()->getConnection();

// Logout
if (isset($_GET["logout"])) {
    $auth->logout();
    header("Location: admin_login.php");
    exit;
}

// Delete user
if (isset($_GET["delete"])) {
    $id = (int)$_GET["delete"];
    $db->prepare("DELETE FROM users WHERE id = ?")->execute([$id]);
    header("Location: admin.php");
    exit;
}

// Add coins
if (isset($_GET["add_moedas"])) {
    $id = (int)$_GET["add_moedas"];
    $db->prepare("UPDATE users SET moedas = moedas + 1000 WHERE id = ?")->execute([$id]);
    header("Location: admin.php");
    exit;
}

$users = $db->query("SELECT COUNT(*) as total FROM users")->fetch(PDO::FETCH_ASSOC)["total"];
$saves = $db->query("SELECT COUNT(*) as total FROM saves")->fetch(PDO::FETCH_ASSOC)["total"];
$times = $db->query("SELECT COUNT(*) as total FROM times")->fetch(PDO::FETCH_ASSOC)["total"];
$partidas = $db->query("SELECT COUNT(*) as total FROM partidas")->fetch(PDO::FETCH_ASSOC)["total"];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Admin - Fenix Foot 2026</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; font-family: 'Outfit', sans-serif; }
        body { background: #050505; color: white; min-height: 100vh; }
        .header { padding: 20px 40px; background: rgba(0,0,0,0.5); display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .header h1 { font-size: 1.8em; font-weight: 800; background: linear-gradient(to right, #fff, #00d2ff); -webkit-background-clip: text; -webkit-text-fill-color: transparent; margin: 0; }
        .header a { color: #ff4757; text-decoration: none; font-weight: 600; }
        .container { max-width: 1200px; margin: 0 auto; padding: 40px 20px; }
        .cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 40px; }
        .card { background: rgba(255,255,255,0.05); padding: 30px; border-radius: 20px; border: 1px solid rgba(255,255,255,0.1); backdrop-filter: blur(10px); }
        .card h3 { color: #00d2ff; font-size: 0.8em; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 10px; }
        .card .num { font-size: 3em; font-weight: 800; }
        .btn { display: inline-block; padding: 12px 24px; border-radius: 10px; text-decoration: none; font-weight: 600; margin: 5px; transition: 0.3s; }
        .btn:hover { transform: translateY(-2px); }
        .btn-primary { background: #00d2ff; color: black; }
        .btn-success { background: #2ecc71; color: white; }
        .btn-danger { background: #ff4757; color: white; }
        .btn-warning { background: #f39c12; color: white; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th { background: rgba(255,255,255,0.05); color: #00d2ff; font-size: 0.8em; text-transform: uppercase; padding: 15px; text-align: left; border-bottom: 2px solid rgba(255,255,255,0.1); }
        td { padding: 15px; border-bottom: 1px solid rgba(255,255,255,0.05); }
        tr:hover { background: rgba(255,255,255,0.02); }
        h2 { margin: 40px 0 20px 0; font-weight: 600; }
    </style>
</head>
<body>
    <div class="header">
        <h1>FENIX FOOT 2026 - ADMIN</h1>
        <a href="?logout=1">SAIR</a>
    </div>
    
    <div class="container">
        <div class="cards">
            <div class="card">
                <h3>Usuários</h3>
                <div class="num"><?php echo $users; ?></div>
            </div>
            <div class="card">
                <h3>Saves</h3>
                <div class="num"><?php echo $saves; ?></div>
            </div>
            <div class="card">
                <h3>Times</h3>
                <div class="num"><?php echo $times; ?></div>
            </div>
            <div class="card">
                <h3>Partidas</h3>
                <div class="num"><?php echo $partidas; ?></div>
            </div>
        </div>
        
        <div>
            <a href="register.php" class="btn btn-success">Novo Usuário</a>
            <a href="setup.php" class="btn btn-primary">Setup</a>
            <a href="index.php" class="btn btn-warning">Ir para o Jogo</a>
        </div>
        
        <h2>Usuários Recentes</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>Usuário</th>
                <th>Moedas</th>
                <th>Pontos</th>
                <th>Ações</th>
            </tr>
            <?php
            $stmt = $db->query("SELECT * FROM users ORDER BY id DESC LIMIT 20");
            while ($u = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
            <tr>
                <td><?php echo $u["id"]; ?></td>
                <td><?php echo htmlspecialchars($u["username"]); ?></td>
                <td><?php echo $u["moedas"] ?? 0; ?></td>
                <td><?php echo $u["ranking_pontos"] ?? 0; ?></td>
                <td>
                    <a href="?add_moedas=<?php echo $u["id"]; ?>" class="btn btn-success" style="padding: 5px 10px; font-size: 0.8em;">+Moedas</a>
                    <a href="?delete=<?php echo $u["id"]; ?>" class="btn btn-danger" style="padding: 5px 10px; font-size: 0.8em;" onclick="return confirm('Tem certeza?')">Excluir</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</body>
</html>
