<?php
require_once 'autoload.php';
session_start();

$userModel = new User();
$erro = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    
    if ($password !== $confirm) {
        $erro = 'As senhas não coincidem.';
    } else {
        if ($userModel->registrar($username, $password)) {
            // Login automático após cadastro
            $user = $userModel->login($username, $password);
            if ($user) {
                header("Location: escolher_time.php");
                exit;
            }
        } else {
            $erro = 'Usuário já existe ou erro no cadastro.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - Fenix Foot 2026</title>
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
        body { background: #050505; color: var(--text); overflow: hidden; display: flex; align-items: center; justify-content: center; height: 100vh; }
        .bg-glow { position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 800px; height: 800px; background: radial-gradient(circle, rgba(0, 210, 255, 0.1) 0%, transparent 70%); filter: blur(100px); z-index: -1; }
        .container { width: 100%; max-width: 400px; padding: 40px; background: var(--glass); backdrop-filter: blur(30px); border-radius: 40px; border: 1px solid var(--glass-border); box-shadow: 0 40px 100px rgba(0,0,0,0.8); text-align: center; }
        h1 { font-size: 2.5em; font-weight: 800; margin-bottom: 30px; letter-spacing: -2px; }
        .form-group { text-align: left; margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-size: 0.75em; text-transform: uppercase; letter-spacing: 2px; color: var(--primary); font-weight: 600; }
        input { width: 100%; background: rgba(255,255,255,0.03); border: 1px solid var(--glass-border); padding: 15px 20px; border-radius: 12px; color: white; font-size: 1em; outline: none; transition: 0.3s; }
        input:focus { border-color: var(--primary); box-shadow: 0 0 15px rgba(0, 210, 255, 0.2); }
        .btn-reg { width: 100%; background: linear-gradient(45deg, var(--primary), var(--secondary)); color: white; padding: 15px; border: none; border-radius: 15px; font-size: 1.1em; font-weight: 700; cursor: pointer; margin-top: 10px; transition: 0.4s; }
        .btn-reg:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(0, 210, 255, 0.3); }
        .error { color: #ff4757; font-size: 0.85em; margin-bottom: 15px; }
        .success { color: #2ecc71; font-size: 0.85em; margin-bottom: 15px; }
        .link { margin-top: 20px; font-size: 0.9em; color: rgba(255,255,255,0.5); }
        .link a { color: var(--primary); text-decoration: none; font-weight: 600; }
    </style>
</head>
<body>
    <div class="bg-glow"></div>
    <div class="container">
        <h1>CADASTRO</h1>
        <?php if ($erro): ?>
            <div class="error"><?php echo htmlspecialchars($erro); ?></div>
        <?php endif; ?>
        <?php if ($sucesso): ?>
            <div class="success"><?php echo htmlspecialchars($sucesso); ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label>Usuário (Nome do Técnico)</label>
                <input type="text" name="username" required autocomplete="off">
            </div>
            <div class="form-group">
                <label>Senha</label>
                <input type="password" name="password" required>
            </div>
            <div class="form-group">
                <label>Confirmar Senha</label>
                <input type="password" name="confirm_password" required>
            </div>
            <button type="submit" class="btn-reg">CRIAR CONTA 🛡️</button>
        </form>
        <div class="link">
            Já tem uma conta? <a href="login.php">Faça Login</a>
        </div>
    </div>
</body>
</html>
