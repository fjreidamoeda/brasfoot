<?php
class User {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function existe($username) {
        $stmt = $this->db->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        return (bool)$stmt->fetch();
    }

    public function registrar($username, $password, $clube_id = null, $id_save = null, $nacionalidade = 'Brasil') {
        if ($this->existe($username)) return false;
        
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("INSERT INTO users (username, password, clube_id, id_save, nacionalidade) VALUES (?, ?, ?, ?, ?)");
        return $stmt->execute([$username, $hash, $clube_id, $id_save, $nacionalidade]);
    }
    
    public function login($username, $password) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['clube_id'] = $user['clube_id'];
            $_SESSION['id_save'] = $user['id_save'];
            $this->atualizarAtividade($user['id']);
            return $user;
        }
        return false;
    }
    
    public function atualizarAtividade($id) {
        $stmt = $this->db->prepare("UPDATE users SET last_activity = CURRENT_TIMESTAMP WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    public function listarOnline() {
        // Considera online quem teve atividade nos últimos 5 minutos
        $stmt = $this->db->prepare("SELECT u.*, t.nome as time_nome 
                                    FROM users u 
                                    LEFT JOIN times t ON u.clube_id = t.id 
                                    WHERE u.last_activity > datetime('now', '-5 minutes')");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function buscarPorId($id) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function adicionarMoedas($id, $quantidade) {
        $stmt = $this->db->prepare("UPDATE users SET moedas = moedas + ? WHERE id = ?");
        return $stmt->execute([$quantidade, $id]);
    }

    public function adicionarPontos($id, $quantidade) {
        $stmt = $this->db->prepare("UPDATE users SET ranking_pontos = ranking_pontos + ? WHERE id = ?");
        return $stmt->execute([$quantidade, $id]);
    }

    public function listarRanking() {
        $stmt = $this->db->query("SELECT username, ranking_pontos, moedas FROM users ORDER BY ranking_pontos DESC LIMIT 10");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
