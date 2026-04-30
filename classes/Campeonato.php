<?php
require_once __DIR__ . '/Database.class.php';

class Campeonato {
    private $db;
    
    public function __construct() {
        $database = Database::getInstance();
        $this->db = $database->getConnection();
    }
    
    public function criar($dados) {
        $sql = "INSERT INTO campeonatos (nome, tipo, pais, temporada, num_times, rodadas, ativo, id_save) 
                VALUES (:nome, :tipo, :pais, :temporada, :num_times, :rodadas, :ativo, :id_save)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':nome' => $dados['nome'],
            ':tipo' => $dados['tipo'] ?? 'Liga',
            ':pais' => $dados['pais'] ?? 'Brasil',
            ':temporada' => $dados['temporada'] ?? '2026',
            ':num_times' => $dados['num_times'] ?? 20,
            ':rodadas' => $dados['rodadas'] ?? 38,
            ':ativo' => $dados['ativo'] ?? 1,
            ':id_save' => $dados['id_save'] ?? 1
        ]);
        return $this->db->lastInsertId();
    }
    
    public function buscarPorId($id) {
        $sql = "SELECT * FROM campeonatos WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function listar($id_save = 1) {
        $sql = "SELECT * FROM campeonatos WHERE id_save = :id_save AND ativo = 1 ORDER BY pais, nome";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_save' => $id_save]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function atualizar($id, $dados) {
        $campos = [];
        $params = [':id' => $id];
        
        foreach ($dados as $campo => $valor) {
            $campos[] = "{$campo} = :{$campo},";
            $params[":{$campo},"] = $valor;
        }
        
        $sql = "UPDATE campeonatos SET " . implode(', ', $campos) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
    
    public function listarClassificacao($campeonato_id, $id_save = 1) {
        $sql = "SELECT t.id, t.nome, 
                c.pontos, c.jogos, c.vitorias, c.empates, c.derrotas, 
                c.gols_pro, c.gols_contra, (c.gols_pro - c.gols_contra) as saldo_gols
                FROM classificacao c
                JOIN times t ON c.time_id = t.id AND t.id_save = c.id_save
                WHERE c.campeonato_id = :campeonato_id AND c.id_save = :id_save
                ORDER BY c.pontos DESC, (c.gols_pro - c.gols_contra) DESC, c.gols_pro DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':campeonato_id' => $campeonato_id, ':id_save' => $id_save]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
