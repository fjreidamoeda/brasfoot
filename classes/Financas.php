<?php
require_once __DIR__ . '/Database.class.php';

class Financas {
    private $db;
    
    public function __construct() {
        $database = Database::getInstance();
        $this->db = $database->getConnection();
    }
    
    public function registrar($dados) {
        $sql = "INSERT INTO financas (time_id, tipo, descricao, valor, saldo_anterior, saldo_posterior, data_lancamento, id_save) 
                VALUES (:time_id, :tipo, :descricao, :valor, :saldo_anterior, :saldo_posterior, :data_lancamento, :id_save)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':time_id' => $dados['time_id'],
            ':tipo' => $dados['tipo'],
            ':descricao' => $dados['descricao'] ?? null,
            ':valor' => $dados['valor'],
            ':saldo_anterior' => $dados['saldo_anterior'] ?? 0,
            ':saldo_posterior' => $dados['saldo_posterior'] ?? 0,
            ':data_lancamento' => $dados['data_lancamento'] ?? date('Y-m-d'),
            ':id_save' => $dados['id_save'] ?? 1
        ]);
        return $this->db->lastInsertId();
    }
    
    public function listarPorTime($time_id, $limite = 50, $id_save = 1) {
        $sql = "SELECT * FROM financas WHERE time_id = :time_id AND id_save = :id_save 
                ORDER BY data_lancamento DESC, id DESC LIMIT :limite";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':time_id', $time_id, PDO::PARAM_INT);
        $stmt->bindValue(':id_save', $id_save, PDO::PARAM_INT);
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function calcularSaldo($time_id, $id_save = 1) {
        $sql = "SELECT 
                SUM(CASE WHEN valor > 0 THEN valor ELSE 0 END) as total_entradas,
                SUM(CASE WHEN valor < 0 THEN ABS(valor) ELSE 0 END) as total_saidas
                FROM financas WHERE time_id = :time_id AND id_save = :id_save";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':time_id' => $time_id, ':id_save' => $id_save]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function registrarReceita($time_id, $tipo, $valor, $descricao = null, $id_save = 1) {
        $saldo = $this->calcularSaldo($time_id, $id_save);
        $saldo_atual = ($saldo['total_entradas'] ?? 0) - ($saldo['total_saidas'] ?? 0);
        
        return $this->registrar([
            'time_id' => $time_id,
            'tipo' => $tipo,
            'descricao' => $descricao,
            'valor' => abs($valor),
            'saldo_anterior' => $saldo_atual,
            'saldo_posterior' => $saldo_atual + abs($valor),
            'id_save' => $id_save
        ]);
    }
    
    public function registrarDespesa($time_id, $tipo, $valor, $descricao = null, $id_save = 1) {
        $saldo = $this->calcularSaldo($time_id, $id_save);
        $saldo_atual = ($saldo['total_entradas'] ?? 0) - ($saldo['total_saidas'] ?? 0);
        
        return $this->registrar([
            'time_id' => $time_id,
            'tipo' => $tipo,
            'descricao' => $descricao,
            'valor' => -abs($valor),
            'saldo_anterior' => $saldo_atual,
            'saldo_posterior' => $saldo_atual - abs($valor),
            'id_save' => $id_save
        ]);
    }
}
