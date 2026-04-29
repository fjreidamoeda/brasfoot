<?php
session_start();
require_once __DIR__ . '/../../configuracoes/functions.php';

$response = ['status' => false, 'msg' => 'Erro ao processar login'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $_POST['user'] ?? '';
    $pass = $_POST['pass'] ?? '';
    
    if (empty($user) || empty($pass)) {
        $response['msg'] = 'Preencha todos os campos';
        echo json_encode($response);
        exit;
    }
    
    $auth = new Auth();
    if ($auth->login($user, $pass)) {
        $response['status'] = true;
        $response['msg'] = 'Login realizado com sucesso';
    } else {
        $response['msg'] = 'Usuário ou senha inválidos';
    }
}

echo json_encode($response);
