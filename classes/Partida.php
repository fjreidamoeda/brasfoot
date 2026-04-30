<?php
class Partida {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function buscarPorId($id) {
        $sql = "SELECT p.*, 
                tc.nome as time_casa_nome, tf.nome as time_fora_nome,
                tc.reputacao as time_casa_reputacao, tf.reputacao as time_fora_reputacao,
                c.nome as campeonato_nome, c.tipo as campeonato_tipo
                FROM partidas p
                JOIN times tc ON p.time_casa_id = tc.id AND tc.id_save = p.id_save
                JOIN times tf ON p.time_fora_id = tf.id AND tf.id_save = p.id_save
                JOIN campeonatos c ON p.campeonato_id = c.id
                WHERE p.id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function simular($partida_id) {
        $partida = $this->buscarPorId($partida_id);
        if (!$partida || !isset($partida['jogada']) || $partida['jogada']) {
            return $partida;
        }
        
        $jogadorModel = new Jogador();
        $taticaModel = new Tatica();
        
        $jogadores_casa = $jogadorModel->listarPorClube($partida['time_casa_id'], $partida['id_save']);
        $jogadores_fora = $jogadorModel->listarPorClube($partida['time_fora_id'], $partida['id_save']);
        
        $tCasa = $taticaModel->buscarPorTime($partida['time_casa_id'], $partida['id_save']);
        if (!$tCasa) {
            $tCasa = ['estilo' => 'Equilibrado', 'formacao' => '4-4-2', 'ataque' => 50];
        }
        
        $tFora = $taticaModel->buscarPorTime($partida['time_fora_id'], $partida['id_save']);
        if (!$tFora) {
            $tFora = ['estilo' => 'Equilibrado', 'formacao' => '4-4-2', 'ataque' => 50];
        }
        
        $forcaCasa = isset($partida['time_casa_reputacao']) ? $partida['time_casa_reputacao'] : 50;
        $forcaFora = isset($partida['time_fora_reputacao']) ? $partida['time_fora_reputacao'] : 50;
        
        if ($tCasa['estilo'] === 'Ofensivo') $forcaCasa += 5;
        if ($tFora['estilo'] === 'Ofensivo') $forcaFora += 5;
        
        $gols_casa = max(0, floor(($forcaCasa / 25) + rand(0, 4) - ($forcaFora / 50)));
        $gols_fora = max(0, floor(($forcaFora / 25) + rand(0, 4) - ($forcaCasa / 50)));
        
        $stmt = $this->db->prepare("UPDATE partidas SET gols_casa = :gols_casa, gols_fora = :gols_fora, jogada = 1 WHERE id = :id");
        $stmt->execute([
            ':gols_casa' => $gols_casa,
            ':gols_fora' => $gols_fora,
            ':id' => $partida_id
        ]);
        
        return ['gols_casa' => $gols_casa, 'gols_fora' => $gols_fora];
    }
    
    public function simularRestanteRodada($id_save) {
        $stmt = $this->db->prepare("SELECT id FROM partidas WHERE id_save = :id_save AND jogada = 0");
        $stmt->execute([':id_save' => $id_save]);
        $partidas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($partidas as $p) {
            $this->simular($p['id']);
        }
        return count($partidas);
    }
    
    public function criar($dados) {
        $sql = "INSERT INTO partidas (campeonato_id, rodada, time_casa_id, time_fora_id, data_partida, hora, estadio, id_save) 
                VALUES (:campeonato_id, :rodada, :time_casa_id, :time_fora_id, :data_partida, :hora, :estadio, :id_save)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':campeonato_id' => $dados['campeonato_id'],
            ':rodada' => $dados['rodada'] ?? 1,
            ':time_casa_id' => $dados['time_casa_id'],
            ':time_fora_id' => $dados['time_fora_id'],
            ':data_partida' => $dados['data_partida'] ?? date('Y-m-d'),
            ':hora' => $dados['hora'] ?? '16:00',
            ':estadio' => $dados['estadio'] ?? null,
            ':id_save' => $dados['id_save'] ?? 1
        ]);
        return $this->db->lastInsertId();
    }
    
    public function listarPorCampeonato($campeonato_id, $rodada = null, $id_save = 1) {
        $sql = "SELECT p.*, tc.nome as time_casa_nome, tf.nome as time_fora_nome
                FROM partidas p
                JOIN times tc ON p.time_casa_id = tc.id AND tc.id_save = p.id_save
                JOIN times tf ON p.time_fora_id = tf.id AND tf.id_save = p.id_save
                WHERE p.campeonato_id = :campeonato_id AND p.id_save = :id_save";
        
        $params = [':campeonato_id' => $campeonato_id, ':id_save' => $id_save];
        
        if ($rodada !== null) {
            $sql .= " AND p.rodada = :rodada";
            $params[':rodada'] = $rodada;
        }
        
        $sql .= " ORDER BY p.rodada, p.data_partida, p.hora";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
