<?php
require_once __DIR__ . '/Database.class.php';
require_once __DIR__ . '/Jogador.php';
require_once __DIR__ . '/Tatica.php';

class Partida {
    private $db;
    
    public function __construct() {
        $database = Database::getInstance();
        $this->db = $database->getConnection();
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
    
    public function buscarPorId($id) {
        $sql = "SELECT p.*, 
                tc.nome as time_casa_nome, tf.nome as time_fora_nome,
                c.nome as campeonato_nome, c.tipo as campeonato_tipo
                FROM partidas p
                JOIN times tc ON p.time_casa_id = tc.id
                JOIN times tf ON p.time_fora_id = tf.id
                JOIN campeonatos c ON p.campeonato_id = c.id
                WHERE p.id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function listarPorCampeonato($campeonato_id, $rodada = null, $id_save = 1) {
        $sql = "SELECT p.*, 
                tc.nome as time_casa_nome, tf.nome as time_fora_nome
                FROM partidas p
                JOIN times tc ON p.time_casa_id = tc.id
                JOIN times tf ON p.time_fora_id = tf.id
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
    
    public function simular($partida_id) {
        $partida = $this->buscarPorId($partida_id);
        if (!$partida || $partida['jogada']) return $partida;
        
        $jogadorModel = new Jogador();
        $taticaModel = new Tatica();
        
        $jogadores_casa = $jogadorModel->listarPorClube($partida['time_casa_id'], $partida['id_save']);
        $jogadores_fora = $jogadorModel->listarPorClube($partida['time_fora_id'], $partida['id_save']);
        
        $tatica_casa = $taticaModel->buscarPorTime($partida['time_casa_id'], $partida['id_save']);
        $tatica_fora = $taticaModel->buscarPorTime($partida['time_fora_id'], $partida['id_save']);
        
        // Calcular médias com influência tática
        $media_casa = $this->calcularMediaTime($jogadores_casa, $tatica_casa) + 5; // +5 vantagem casa
        $media_fora = $this->calcularMediaTime($jogadores_fora, $tatica_fora);
        
        // Simular gols baseados na força relativa
        $gols_casa = $this->calcularGols($media_casa, $media_fora);
        $gols_fora = $this->calcularGols($media_fora, $media_casa);
        
        // Gerar eventos realistas (Gols, Cartões, Lesões)
        $eventos = $this->gerarEventos($partida, $gols_casa, $gols_fora, $jogadores_casa, $jogadores_fora);
        
        // Salvar resultado
        $sql = "UPDATE partidas SET gols_casa = :gols_casa, gols_fora = :gols_fora, jogada = 1, eventos = :eventos 
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':gols_casa' => $gols_casa,
            ':gols_fora' => $gols_fora,
            ':eventos' => json_encode($eventos),
            ':id' => $partida_id
        ]);
        
        // Atualizar classificação se for liga
        if ($partida['campeonato_tipo'] !== 'Copa' || $partida['rodada'] > 0) {
            $this->atualizarClassificacao($partida['campeonato_id'], $partida['time_casa_id'], $partida['time_fora_id'], 
                                         $gols_casa, $gols_fora, $partida['id_save']);
        }
        
        return [
            'id' => $partida_id,
            'gols_casa' => $gols_casa,
            'gols_fora' => $gols_fora,
            'eventos' => $eventos
        ];
    }
    
    private function calcularMediaTime($jogadores, $tatica) {
        if (empty($jogadores)) return 60;
        $total = 0;
        foreach ($jogadores as $j) {
            $total += $j['overall'];
        }
        $base = $total / count($jogadores);
        
        // Bônus tático
        if ($tatica['estilo'] === 'Ofensivo') $base += 2;
        if ($tatica['estilo'] === 'Pressionante') $base += 3;
        if ($tatica['ataque'] > 70) $base += 1;
        
        return $base;
    }
    
    private function calcularGols($forca_ataque, $forca_defesa) {
        $diff = $forca_ataque - $forca_defesa;
        $chance_base = 5 + ($diff / 5);
        $gols = 0;
        for ($i = 0; $i < 10; $i++) {
            if (mt_rand(1, 100) < $chance_base) $gols++;
        }
        return min($gols, 9);
    }
    
    private function gerarEventos($partida, $gols_casa, $gols_fora, $jogadores_casa, $jogadores_fora) {
        $eventos = [];
        
        // Gols Casa
        for ($i = 0; $i < $gols_casa; $i++) {
            $jogador = $jogadores_casa[array_rand($jogadores_casa)];
            $eventos[] = ['minuto' => mt_rand(1, 90), 'tipo' => 'gol', 'jogador' => $jogador['nome'], 'time' => 'casa', 'time_nome' => $partida['time_casa_nome']];
        }
        // Gols Fora
        for ($i = 0; $i < $gols_fora; $i++) {
            $jogador = $jogadores_fora[array_rand($jogadores_fora)];
            $eventos[] = ['minuto' => mt_rand(1, 90), 'tipo' => 'gol', 'jogador' => $jogador['nome'], 'time' => 'fora', 'time_nome' => $partida['time_fora_nome']];
        }
        
        // Cartões e Lesões (Realismo)
        for ($m = 1; $m <= 90; $m++) {
            if (mt_rand(1, 500) < 5) { // Cartão Amarelo
                $t = mt_rand(0,1) ? 'casa' : 'fora';
                $jogs = ($t === 'casa') ? $jogadores_casa : $jogadores_fora;
                $j = $jogs[array_rand($jogs)];
                $eventos[] = ['minuto' => $m, 'tipo' => 'amarelo', 'jogador' => $j['nome'], 'time' => $t, 'time_nome' => ($t === 'casa' ? $partida['time_casa_nome'] : $partida['time_fora_nome'])];
            }
            if (mt_rand(1, 2000) < 2) { // Cartão Vermelho
                $t = mt_rand(0,1) ? 'casa' : 'fora';
                $jogs = ($t === 'casa') ? $jogadores_casa : $jogadores_fora;
                $j = $jogs[array_rand($jogs)];
                $eventos[] = ['minuto' => $m, 'tipo' => 'vermelho', 'jogador' => $j['nome'], 'time' => $t, 'time_nome' => ($t === 'casa' ? $partida['time_casa_nome'] : $partida['time_fora_nome'])];
            }
            if (mt_rand(1, 3000) < 1) { // Lesão
                $t = mt_rand(0,1) ? 'casa' : 'fora';
                $jogs = ($t === 'casa') ? $jogadores_casa : $jogadores_fora;
                $j = $jogs[array_rand($jogs)];
                $eventos[] = ['minuto' => $m, 'tipo' => 'lesao', 'jogador' => $j['nome'], 'time' => $t, 'time_nome' => ($t === 'casa' ? $partida['time_casa_nome'] : $partida['time_fora_nome'])];
            }
        }
        
        usort($eventos, function($a, $b) { return $a['minuto'] - $b['minuto']; });
        return $eventos;
    }
    
    private function atualizarClassificacao($campeonato_id, $time_casa_id, $time_fora_id, $gols_casa, $gols_fora, $id_save) {
        $this->atualizarTimeClassificacao($campeonato_id, $time_casa_id, $gols_casa, $gols_fora, $id_save);
        $this->atualizarTimeClassificacao($campeonato_id, $time_fora_id, $gols_fora, $gols_casa, $id_save);
    }
    
    private function atualizarTimeClassificacao($campeonato_id, $time_id, $gols_pro, $gols_contra, $id_save) {
        $sql = "SELECT * FROM classificacao WHERE campeonato_id = :campeonato_id AND time_id = :time_id AND id_save = :id_save";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':campeonato_id' => $campeonato_id, ':time_id' => $time_id, ':id_save' => $id_save]);
        $classificacao = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $pontos = ($gols_pro > $gols_contra) ? 3 : (($gols_pro == $gols_contra) ? 1 : 0);
        $vitoria = ($gols_pro > $gols_contra) ? 1 : 0;
        $empate = ($gols_pro == $gols_contra) ? 1 : 0;
        $derrota = ($gols_pro < $gols_contra) ? 1 : 0;
        
        if ($classificacao) {
            $sql = "UPDATE classificacao SET 
                    pontos = pontos + :pontos, jogos = jogos + 1, 
                    vitorias = vitorias + :v, empates = empates + :e, derrotas = derrotas + :d,
                    gols_pro = gols_pro + :gp, gols_contra = gols_contra + :gc, 
                    saldo_gols = (gols_pro + :gp) - (gols_contra + :gc)
                    WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':pontos' => $pontos, ':v' => $vitoria, ':e' => $empate, ':d' => $derrota, ':gp' => $gols_pro, ':gc' => $gols_contra, ':id' => $classificacao['id']]);
        } else {
            $this->db->prepare("INSERT INTO classificacao (campeonato_id, time_id, pontos, jogos, vitorias, empates, derrotas, gols_pro, gols_contra, saldo_gols, id_save) 
                               VALUES (?, ?, ?, 1, ?, ?, ?, ?, ?, ?, ?)")
                     ->execute([$campeonato_id, $time_id, $pontos, $vitoria, $empate, $derrota, $gols_pro, $gols_contra, $gols_pro - $gols_contra, $id_save]);
        }
    }
}
