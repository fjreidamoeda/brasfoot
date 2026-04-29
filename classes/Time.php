<?php
require_once __DIR__ . '/Database.class.php';

class Time {
    private $db;
    
    public function __construct() {
        $database = Database::getInstance();
        $this->db = $database->getConnection();
    }
    
    public function criar($dados) {
        $sql = "INSERT INTO times (nome, sigla, cidade, estado, pais, liga, divisao, reputacao, orcamento, id_save) 
                VALUES (:nome, :sigla, :cidade, :estado, :pais, :liga, :divisao, :reputacao, :orcamento, :id_save)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':nome' => $dados['nome'],
            ':sigla' => $dados['sigla'] ?? null,
            ':cidade' => $dados['cidade'] ?? null,
            ':estado' => $dados['estado'] ?? null,
            ':pais' => $dados['pais'] ?? 'Brasil',
            ':liga' => $dados['liga'] ?? null,
            ':divisao' => $dados['divisao'] ?? 1,
            ':reputacao' => $dados['reputacao'] ?? 50,
            ':orcamento' => $dados['orcamento'] ?? 1000000.00,
            ':id_save' => $dados['id_save'] ?? 1
        ]);
        return $this->db->lastInsertId();
    }
    
    public function buscarPorId($id) {
        $sql = "SELECT * FROM times WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function listar($filtros = []) {
        $sql = "SELECT * FROM times WHERE 1=1";
        $params = [];
        
        if (isset($filtros['liga'])) {
            $sql .= " AND liga = :liga";
            $params[':liga'] = $filtros['liga'];
        }
        
        if (isset($filtros['divisao'])) {
            $sql .= " AND divisao = :divisao";
            $params[':divisao'] = $filtros['divisao'];
        }
        
        if (isset($filtros['id_save'])) {
            $sql .= " AND id_save = :id_save";
            $params[':id_save'] = $filtros['id_save'];
        }
        
        $sql .= " ORDER BY nome";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function atualizar($id, $dados) {
        $campos = [];
        $params = [':id' => $id];
        
        foreach ($dados as $campo => $valor) {
            $campos[] = "{$campo} = :{$campo}";
            $params[":{$campo}"] = $valor;
        }
        
        $sql = "UPDATE times SET " . implode(', ', $campos) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
    
    public function deletar($id) {
        $sql = "DELETE FROM times WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
    
    public function buscarPorLiga($liga, $id_save = 1) {
        $sql = "SELECT * FROM times WHERE liga = :liga AND id_save = :id_save ORDER BY nome";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':liga' => $liga, ':id_save' => $id_save]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
