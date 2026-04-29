<?php
require_once __DIR__ . '/Database.class.php';

class Base {
    private $db;
    
    public function __construct() {
        $database = Database::getInstance();
        $this->db = $database->getConnection();
    }
    
    public function criar($dados) {
        $sql = "INSERT INTO base_jovens (time_id, jogador_id, categoria, potencial, id_save) 
                VALUES (:time_id, :jogador_id, :categoria, :potencial, :id_save)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':time_id' => $dados['time_id'],
            ':jogador_id' => $dados['jogador_id'],
            ':categoria' => $dados['categoria'] ?? 'Sub-20',
            ':potencial' => $dados['potencial'] ?? 70,
            ':id_save' => $dados['id_save'] ?? 1
        ]);
        return $this->db->lastInsertId();
    }
    
    public function listarPorTime($time_id, $categoria = null, $id_save = 1) {
        $sql = "SELECT bj.*, j.nome, j.posicao, j.overall, j.idade
                FROM base_jovens bj
                JOIN jogadores j ON bj.jogador_id = j.id
                WHERE bj.time_id = :time_id AND bj.id_save = :id_save";
        $params = [':time_id' => $time_id, ':id_save' => $id_save];
        
        if ($categoria) {
            $sql .= " AND bj.categoria = :categoria";
            $params[':categoria'] = $categoria;
        }
        
        $sql .= " ORDER BY bj.potencial DESC, j.overall DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function promoverParaProfissional($base_id) {
        $sql = "SELECT bj.*, j.clube_id FROM base_jovens bj JOIN jogadores j ON bj.jogador_id = j.id WHERE bj.id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $base_id]);
        $registro = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$registro || $registro['clube_id']) return false;
        
        $sql = "UPDATE jogadores SET clube_id = :time_id WHERE id = :jogador_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':time_id' => $registro['time_id'],
            ':jogador_id' => $registro['jogador_id']
        ]);
        
        $sql = "DELETE FROM base_jovens WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $base_id]);
    }
    
    public function gerarJovemPromissor($time_id, $id_save = 1) {
        $posicoes = ['Goleiro', 'Zagueiro', 'Lateral', 'Meia', 'Atacante'];
        $posicao = $posicoes[array_rand($posicoes)];
        
        $nomes = ['Miguel', 'Arthur', 'Heitor', 'Theo', 'Davi', 'Gabriel', 'Pedro', 'Lucas', 'Matheus', 'Rafael'];
        $sobrenomes = ['Silva', 'Santos', 'Oliveira', 'Souza', 'Lima', 'Pereira', 'Costa', 'Rodrigues', 'Almeida', 'Ferreira'];
        
        $jogador = new Jogador();
        return $jogador->criar([
            'nome' => $nomes[array_rand($nomes)] . ' ' . $sobrenomes[array_rand($sobrenomes)],
            'posicao' => $posicao,
            'overall' => mt_rand(55, 65),
            'potencial' => mt_rand(75, 90),
            'velocidade' => mt_rand(60, 75),
            'finalizacao' => mt_rand(55, 70),
            'passe' => mt_rand(55, 70),
            'defesa' => mt_rand(55, 70),
            'fisico' => mt_rand(55, 70),
            'goleiro' => $posicao == 'Goleiro' ? mt_rand(65, 80) : mt_rand(50, 60),
            'clube_id' => null,
            'id_save' => $id_save
        ]);
    }
}
