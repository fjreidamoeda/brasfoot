<?php
require_once __DIR__ . '/Database.class.php';

class Tatica {
    private $db;
    
    public function __construct() {
        $database = Database::getInstance();
        $this->db = $database->getConnection();
    }
    
    public function criar($dados) {
        $sql = "INSERT INTO tacticas (time_id, nome, formacao, estilo, marcacao, controle, ataque, laterais, id_save) 
                VALUES (:time_id, :nome, :formacao, :estilo, :marcacao, :controle, :ataque, :laterais, :id_save)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':time_id' => $dados['time_id'],
            ':nome' => $dados['nome'] ?? 'Padrão',
            ':formacao' => $dados['formacao'] ?? '4-4-2',
            ':estilo' => $dados['estilo'] ?? 'Equilibrado',
            ':marcacao' => $dados['marcacao'] ?? 50,
            ':controle' => $dados['controle'] ?? 50,
            ':ataque' => $dados['ataque'] ?? 50,
            ':laterais' => $dados['laterais'] ?? 50,
            ':id_save' => $dados['id_save'] ?? 1
        ]);
        return $this->db->lastInsertId();
    }
    
    public function buscarPorTime($time_id, $id_save = 1) {
        $sql = "SELECT * FROM tacticas WHERE time_id = :time_id AND id_save = :id_save ORDER BY id DESC LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':time_id' => $time_id, ':id_save' => $id_save]);
        $tatica = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$tatica) {
            return $this->criar(['time_id' => $time_id, 'id_save' => $id_save]);
        }
        return $tatica;
    }
    
    public function atualizar($id, $dados) {
        $campos = [];
        $params = [':id' => $id];
        
        foreach ($dados as $campo => $valor) {
            $campos[] = "{$campo} = :{$campo}";
            $params[":{$campo}"] = $valor;
        }
        
        $sql = "UPDATE tacticas SET " . implode(', ', $campos) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
    
    public function listarFormacoes() {
        return [
            '4-4-2' => '4-4-2 (Clássico)',
            '4-3-3' => '4-3-3 (Ofensivo)',
            '3-5-2' => '3-5-2 (Meias)',
            '4-5-1' => '4-5-1 (Defensivo)',
            '4-2-4' => '4-2-4 (Ultraofensivo)',
            '5-3-2' => '5-3-2 (Defesa sólida)',
            '4-1-4-1' => '4-1-4-1 (Controle)',
            '3-4-3' => '3-4-3 (Libero)'
        ];
    }
    
    public function listarEstilos() {
        return [
            'Defensivo' => 'Defensivo (Foco em marcação)',
            'Equilibrado' => 'Equilibrado (Meio termo)',
            'Ofensivo' => 'Ofensivo (Foco em ataque)',
            'Pressionante' => 'Pressionante (Pressão alta)',
            'Possesso' => 'Possessão (Toque de bola)'
        ];
    }
}
