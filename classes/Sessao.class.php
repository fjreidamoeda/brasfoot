<?php
class Sessao {
    public function verificar_sessao() {
        return isset($_SESSION['admin_id']) && $_SESSION['admin_logged'] === true;
    }
    
    public function getUserId() {
        return $_SESSION['admin_id'] ?? 0;
    }
}
