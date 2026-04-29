<?php
require_once __DIR__ . '/Database.class.php';

class Save {
    private $db;
    
    public function __construct() {
        $database = Database::getInstance();
        $this->db = $database->getConnection();
    }
    
    public function criar($nome) {
        $sql = "INSERT INTO saves (nome, data_inicio, temporada_atual, dia_atual, mes_atual, ativo) 
                VALUES (:nome, DATE('now'), '2026', 1, 1, 1)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':nome' => $nome]);
        return $this->db->lastInsertId();
    }
    
    public function listar() {
        try {
            $sql = "SELECT * FROM saves ORDER BY data_inicio DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
    
    public function ativar($id) {
        $sql = "UPDATE saves SET ativo = 0";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        
        $sql = "UPDATE saves SET ativo = 1 WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return true;
    }
    
    public function buscarAtivo() {
        try {
            $sql = "SELECT * FROM saves WHERE ativo = 1 LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return null;
        }
    }
    
    public function avancarDia($id_save = 1) {
        $sql = "SELECT * FROM saves WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id_save]);
        $save = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$save) return false;
        
        $dia = $save['dia_atual'] + 1;
        $mes = $save['mes_atual'];
        
        if ($mes == 1 || $mes == 3 || $mes == 5 || $mes == 7 || $mes == 8 || $mes == 10 || $mes == 12) {
            if ($dia > 31) { $dia = 1; $mes++; }
        } elseif ($mes == 2) {
            if ($dia > 28) { $dia = 1; $mes++; }
        } else {
            if ($dia > 30) { $dia = 1; $mes++; }
        }
        
        if ($mes > 12) {
            $mes = 1;
            $sql = "UPDATE saves SET temporada_atual = temporada_atual + 1 WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $id_save]);
        }
        
        $sql = "UPDATE saves SET dia_atual = :dia, mes_atual = :mes WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':dia' => $dia, ':mes' => $mes, ':id' => $id_save]);
    }
}
