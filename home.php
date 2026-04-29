<?php
session_start();
require_once 'configuracoes/functions.php';
require_once 'autoload.php';

$session = new Sessao();
if (!$session->verificar_sessao()) {
    redirect('./index.php');
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StreamBlack - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="#">StreamBlack</a>
        <a href="?logout=1" class="btn btn-outline-light btn-sm">Sair</a>
    </div>
</nav>
<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">Dashboard</div>
                <div class="card-body">
                    <h5>Bem-vindo, <?php echo htmlspecialchars($_SESSION['admin_user'] ?? 'Admin'); ?>!</h5>
                    <p>Sistema funcionando corretamente.</p>
                    <a href="?logout=1" class="btn btn-danger">Sair</a>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
<?php
if (isset($_GET['logout'])) {
    $auth = new Auth();
    $auth->logout();
    redirect('./index.php');
}
