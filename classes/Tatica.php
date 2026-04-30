<?php
class Tatica {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function buscarPorTime($time_id, $id_save = 1) {
        $stmt = $this->db->prepare("SELECT * FROM tacticas WHERE time_id = :time_id AND id_save = :id_save");
        $stmt->execute([':time_id' => $time_id, ':id_save' => $id_save]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$result) {
            return [
                'estilo' => 'Equilibrado',
                'formacao' => '4-4-2',
                'marcacao' => 50,
                'controle' => 50,
                'ataque' => 50,
                'laterais' => 50
            ];
        }
        return $result;
    }
    
    public function salvar($time_id, $dados, $id_save = 1) {
        // Verificar se já existe
        $stmt = $this->db->prepare("SELECT id FROM tacticas WHERE time_id = :time_id AND id_save = :id_save");
        $stmt->execute([':time_id' => $time_id, ':id_save' => $id_save]);
        $existe = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existe) {
            $sql = "UPDATE tacticas SET estilo = :estilo, formacao = :formacao, marcacao = :marcacao, 
                    controle = :controle, ataque = :ataque, laterais = :laterais 
                    WHERE time_id = :time_id AND id_save = :id_save";
        } else {
            $sql = "INSERT INTO tacticas (time_id, estilo, formacao, marcacao, controle, ataque, laterais, id_save) 
                    VALUES (:time_id, :estilo, :formacao, :marcacao, :controle, :ataque, :laterais, :id_save)";
        }
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':time_id' => $time_id,
            ':estilo' => $dados['estilo'] ?? 'Equilibrado',
            ':formacao' => $dados['formacao'] ?? '4-4-2',
            ':marcacao' => $dados['marcacao'] ?? 50,
            ':controle' => $dados['controle'] ?? 50,
            ':ataque' => $dados['ataque'] ?? 50,
            ':laterais' => $dados['laterais'] ?? 50,
            ':id_save' => $id_save
        ]);
    }
}
?>
