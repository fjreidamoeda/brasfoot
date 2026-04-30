<?php
class Desafio {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function criar($desafiante_id, $desafiado_id) {
        $stmt = $this->db->prepare("SELECT id FROM desafios WHERE desafiante_id = ? AND desafiado_id = ? AND status = 'pendente'");
        $stmt->execute([$desafiante_id, $desafiado_id]);
        if ($stmt->fetch()) return false;

        $stmt = $this->db->prepare("INSERT INTO desafios (desafiante_id, desafiado_id) VALUES (?, ?)");
        return $stmt->execute([$desafiante_id, $desafiado_id]);
    }
    
    public function buscarPendentes($user_id) {
        $stmt = $this->db->prepare("SELECT d.*, u.username as desafiante_nome 
                                    FROM desafios d 
                                    JOIN users u ON d.desafiante_id = u.id 
                                    WHERE d.desafiado_id = ? AND d.status = 'pendente' 
                                    ORDER BY d.created_at DESC");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function aceitar($desafio_id) {
        // Buscar desafio
        $stmt = $this->db->prepare("SELECT d.*, 
                                    u1.clube_id as desafiante_clube, 
                                    u2.clube_id as desafiado_clube 
                                    FROM desafios d 
                                    JOIN users u1 ON d.desafiante_id = u1.id 
                                    JOIN users u2 ON d.desafiado_id = u2.id 
                                    WHERE d.id = ?");
        $stmt->execute([$desafio_id]);
        $desafio = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$desafio) return false;
        
        // Buscar save ativo
        $save = $this->db->query("SELECT id FROM saves WHERE ativo = 1 LIMIT 1")->fetch(PDO::FETCH_ASSOC);
        $id_save = $save ? $save['id'] : 1;
        
        // Garantir que os clubes existem (pegar o primeiro time disponível se clube_id estiver vazio)
        if (empty($desafio['desafiante_clube']) || empty($desafio['desafiado_clube'])) {
            $times = $this->db->query("SELECT id FROM times LIMIT 2")->fetchAll(PDO::FETCH_ASSOC);
            if (count($times) < 2) return false;
            
            $time_casa = $desafio['desafiante_clube'] ?: $times[0]['id'];
            $time_fora = $desafio['desafiado_clube'] ?: $times[1]['id'];
            
            // Atualizar clube_id dos usuários se necessário
            if (empty($desafio['desafiante_clube'])) {
                $this->db->prepare("UPDATE users SET clube_id = ? WHERE id = ?")->execute([$times[0]['id'], $desafio['desafiante_id']]);
            }
            if (empty($desafio['desafiado_clube'])) {
                $this->db->prepare("UPDATE users SET clube_id = ? WHERE id = ?")->execute([$times[1]['id'], $desafio['desafiado_id']]);
            }
        } else {
            $time_casa = $desafio['desafiante_clube'];
            $time_fora = $desafio['desafiado_clube'];
        }
        
        // Criar partida
        $stmt = $this->db->prepare("INSERT INTO partidas (campeonato_id, rodada, time_casa_id, time_fora_id, data_partida, hora, id_save) 
                                  VALUES (0, 0, ?, ?, DATE('now'), '16:00', ?)");
        $stmt->execute([$time_casa, $time_fora, $id_save]);
        $partida_id = $this->db->lastInsertId();
        
        // Atualizar desafio
        $stmt = $this->db->prepare("UPDATE desafios SET status = 'aceito', partida_id = ? WHERE id = ?");
        $stmt->execute([$partida_id, $desafio_id]);
        
        return $partida_id;
    }
    
    public function recusar($desafio_id) {
        $stmt = $this->db->prepare("UPDATE desafios SET status = 'recusado' WHERE id = ?");
        return $stmt->execute([$desafio_id]);
    }
    
    public function verificarStatus($desafiante_id) {
        $stmt = $this->db->prepare("SELECT d.*, u.username as desafiado_nome 
                                    FROM desafios d 
                                    JOIN users u ON d.desafiado_id = u.id 
                                    WHERE d.desafiante_id = ? AND d.status != 'pendente' 
                                    ORDER BY d.created_at DESC LIMIT 1");
        $stmt->execute([$desafiante_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
