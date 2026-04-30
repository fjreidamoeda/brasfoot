<?php
require_once "autoload.php";
session_start();

$erro = "";

// If already logged in, redirect
if (isset($_SESSION["admin_id"]) && !empty($_SESSION["admin_id"])) {
    header("Location: admin.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user = $_POST["username"] ?? "";
    $pass = $_POST["password"] ?? "";
    
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT * FROM admin WHERE user = ? LIMIT 1");
    $stmt->execute([$user]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($admin && password_verify($pass, $admin['pass'])) {
        $_SESSION["admin_id"] = $admin['id'];
        $_SESSION["admin_logged"] = true;
        $_SESSION["admin_user"] = $admin['user'];
        header("Location: admin.php");
        exit;
    } else {
        $erro = "Usuário ou senha incorretos.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Admin - Fenix Foot 2026</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; font-family: Arial, sans-serif; }
        body { background: #050505; color: white; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
        .container { width: 100%; max-width: 400px; padding: 40px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); text-align: center; }
        h1 { font-size: 2.5em; font-weight: 800; margin-bottom: 30px; color: #00d2ff; }
        .form-group { text-align: left; margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-size: 0.75em; text-transform: uppercase; letter-spacing: 2px; color: #00d2ff; font-weight: 600; }
        input { width: 100%; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.1); padding: 15px 20px; border-radius: 12px; color: white; font-size: 1em; outline: none; }
        .btn { width: 100%; background: linear-gradient(45deg, #00d2ff, #3a7bd5); color: white; padding: 15px; border: none; border-radius: 15px; font-size: 1.1em; font-weight: 700; cursor: pointer; margin-top: 10px; }
        .error { color: #ff4757; font-size: 0.85em; margin-bottom: 15px; padding: 10px; background: rgba(255,71,87,0.1); border-radius: 8px; }
        .link { margin-top: 20px; font-size: 0.9em; color: rgba(255,255,255,0.5); }
        .link a { color: #00d2ff; text-decoration: none; font-weight: 600; }
    </style>
</head>
<body>
    <div class="container">
        <h1>FENIX FOOT 2026</h1>
        <p style="color: rgba(255,255,255,0.5); margin-bottom: 30px;">Painel Administrativo</p>
        <?php if ($erro): ?>
            <div class="error"><?php echo htmlspecialchars($erro); ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label>Usuário</label>
                <input type="text" name="username" required autocomplete="off">
            </div>
            <div class="form-group">
                <label>Senha</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="btn">ENTRAR</button>
        </form>
        <div class="link">
            <a href="login.php">← Voltar para Login de Jogador</a>
        </div>
    </div>
</body>
</html>
