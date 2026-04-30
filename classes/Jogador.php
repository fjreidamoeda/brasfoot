<?php
require_once __DIR__ . '/Database.class.php';

class Jogador {
    private $db;
    
    public function __construct() {
        $database = Database::getInstance();
        $this->db = $database->getConnection();
    }
    
    public function criar($dados) {
        $sql = "INSERT INTO jogadores (nome, apelido, data_nascimento, nacionalidade, posicao, posicao_secundaria, pe_preferido, 
                overall, potencial, velocidade, finalizacao, passe, defesa, fisico, resistencia, goleiro, titular, valor_mercado, salario, 
                contrato_ate, clube_id, felicidade, forma, id_save) 
                VALUES (:nome, :apelido, :data_nascimento, :nacionalidade, :posicao, :posicao_secundaria, :pe_preferido,
                :overall, :potencial, :velocidade, :finalizacao, :passe, :defesa, :fisico, :resistencia, :goleiro, :titular, :valor_mercado, :salario,
                :contrato_ate, :clube_id, :felicidade, :forma, :id_save)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':nome' => $dados['nome'],
            ':apelido' => $dados['apelido'] ?? null,
            ':data_nascimento' => $dados['data_nascimento'] ?? null,
            ':nacionalidade' => $dados['nacionalidade'] ?? 'Brasil',
            ':posicao' => $dados['posicao'],
            ':posicao_secundaria' => $dados['posicao_secundaria'] ?? null,
            ':pe_preferido' => $dados['pe_preferido'] ?? 'Destro',
            ':overall' => $dados['overall'] ?? 60,
            ':potencial' => $dados['potencial'] ?? 70,
            ':velocidade' => $dados['velocidade'] ?? 60,
            ':finalizacao' => $dados['finalizacao'] ?? 60,
            ':passe' => $dados['passe'] ?? 60,
            ':defesa' => $dados['defesa'] ?? 60,
            ':fisico' => $dados['fisico'] ?? 60,
            ':resistencia' => $dados['resistencia'] ?? 60,
            ':goleiro' => $dados['goleiro'] ?? 60,
            ':titular' => $dados['titular'] ?? 0,
            ':valor_mercado' => $dados['valor_mercado'] ?? 100000.00,
            ':salario' => $dados['salario'] ?? 5000.00,
            ':contrato_ate' => $dados['contrato_ate'] ?? null,
            ':clube_id' => $dados['clube_id'] ?? null,
            ':felicidade' => $dados['felicidade'] ?? 70,
            ':forma' => $dados['forma'] ?? 80,
            ':id_save' => $dados['id_save'] ?? 1
        ]);
        return $this->db->lastInsertId();
    }
    
    public function buscarPorId($id) {
        $sql = "SELECT * FROM jogadores WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function listarPorClube($clube_id, $id_save = 1) {
        $sql = "SELECT * FROM jogadores WHERE clube_id = :clube_id AND id_save = :id_save ORDER BY posicao, overall DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':clube_id' => $clube_id, ':id_save' => $id_save]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function listarDisponiveis($id_save = 1) {
        $sql = "SELECT * FROM jogadores WHERE clube_id IS NULL AND id_save = :id_save ORDER BY overall DESC";
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
        
        $sql = "UPDATE jogadores SET " . implode(', ', $campos) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
    
    public function transferir($jogador_id, $clube_origem_id, $clube_destino_id, $valor, $tipo = 'Compra') {
        $this->db->beginTransaction();
        
        try {
            $jogador = $this->buscarPorId($jogador_id);
            if (!$jogador) throw new Exception("Jogador não encontrado.");

            // Atualizar clube do jogador
            $sql = "UPDATE jogadores SET clube_id = :clube_destino_id WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':clube_destino_id' => $clube_destino_id, ':id' => $jogador_id]);
            
            // Subtrair orçamento do destino
            if ($clube_destino_id) {
                $sql = "UPDATE times SET orcamento = orcamento - :valor WHERE id = :id";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([':valor' => $valor, ':id' => $clube_destino_id]);
            }

            // Somar orçamento da origem
            if ($clube_origem_id) {
                $sql = "UPDATE times SET orcamento = orcamento + :valor WHERE id = :id";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([':valor' => $valor, ':id' => $clube_origem_id]);
            }

            // Registrar transferência
            $sql = "INSERT INTO transferencias (jogador_id, clube_origem_id, clube_destino_id, tipo, valor, data_transferencia, id_save) 
                    VALUES (:jogador_id, :clube_origem_id, :clube_destino_id, :tipo, :valor, DATE('now'), :id_save)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':jogador_id' => $jogador_id,
                ':clube_origem_id' => $clube_origem_id,
                ':clube_destino_id' => $clube_destino_id,
                ':tipo' => $tipo,
                ':valor' => $valor,
                ':id_save' => $jogador['id_save']
            ]);
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
}
