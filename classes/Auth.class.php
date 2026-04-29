<?php
class Auth {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function login($user, $pass) {
        $stmt = $this->db->prepare("SELECT * FROM admin WHERE user = ? LIMIT 1");
        $stmt->execute([$user]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($admin && password_verify($pass, $admin['pass'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_logged'] = true;
            $_SESSION['admin_user'] = $admin['user'];
            return true;
        }
        return false;
    }
    
    public function logout() {
        session_destroy();
        return true;
    }
}
