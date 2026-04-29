<?php
/**
 * Fenix Foot - Script de População Completo 2026
 */
require_once __DIR__ . '/autoload.php';

set_time_limit(600);
ini_set('memory_limit', '512M');

function generateRealisticPlayer($pos, $rep, $country) {
    $firstNames = ['João', 'Gabriel', 'Lucas', 'Mateus', 'Pedro', 'Felipe', 'Rafael', 'Thiago', 'Bruno', 'Rodrigo', 'Thomas', 'Marc', 'Alessandro', 'Luca', 'Pietro', 'Hansi', 'Klaus', 'Antoine', 'Pierre', 'Léo', 'Arrasca', 'Estêvão', 'Endrick', 'Lamine', 'Kylian', 'Jude', 'Erling', 'Vinícius', 'Rodrygo', 'Kevin', 'Mohamed', 'Alisson', 'Ederson'];
    $lastNames = ['Silva', 'Santos', 'Oliveira', 'Souza', 'Pereira', 'Alves', 'Ferreira', 'Lima', 'Gomes', 'Costa', 'Müller', 'Schmidt', 'Rossi', 'Bianchi', 'Garcia', 'Rodriguez', 'Mbappé', 'Griezmann', 'Yamal', 'Haaland', 'Bellingham', 'Junior', 'De Bruyne', 'Salah', 'Van Dijk', 'Kane', 'Musiala'];
    
    $name = $firstNames[array_rand($firstNames)] . ' ' . $lastNames[array_rand($lastNames)];
    
    $baseOverall = $rep - 10 + rand(0, 10);
    $baseOverall = max(40, min(95, $baseOverall));
    
    $potencial = $baseOverall + rand(2, 10);
    $vel = rand($baseOverall - 10, $baseOverall + 10);
    $fin = rand($baseOverall - 10, $baseOverall + 10);
    $pas = rand($baseOverall - 10, $baseOverall + 10);
    $def = rand($baseOverall - 10, $baseOverall + 10);
    $fis = rand($baseOverall - 10, $baseOverall + 10);
    $gol = ($pos === 'Goleiro') ? rand($baseOverall - 5, $baseOverall + 5) : rand(10, 30);
    
    $valor = ($baseOverall * $baseOverall * 1000) * (1 + (rand(0, 50) / 100));
    $salario = ($baseOverall * 1000);
    
    $idade = rand(17, 36);
    $anoNasc = 2026 - $idade;
    $dataNasc = $anoNasc . "-" . sprintf("%02d", rand(1, 12)) . "-" . sprintf("%02d", rand(1, 28));
    
    return [$name, $country, $pos, 'Destro', $baseOverall, $potencial, $vel, $fin, $pas, $def, $fis, $gol, $valor, $salario, $dataNasc, $idade];
}

function popularFenix($id_save = 1) {
    $db    = Database::getInstance();
    $pdo   = $db->getConnection();
    $pdo->beginTransaction();

    try {
        // Limpar dados
        foreach (['estatisticas_jogador','transferencias','classificacao','partidas','calendario',
                  'tacticas','financas','patrocinios','base_jovens','jogadores','times','campeonatos'] as $t) {
            $pdo->exec("DELETE FROM {$t} WHERE id_save = {$id_save}");
        }

        require_once __DIR__ . '/data/liga_brasil_a.php';
        require_once __DIR__ . '/data/liga_brasil_b.php';
        require_once __DIR__ . '/data/liga_brasil_c.php';
        require_once __DIR__ . '/data/liga_premier.php';
        require_once __DIR__ . '/data/liga_laliga.php';
        require_once __DIR__ . '/data/liga_seriea.php';
        require_once __DIR__ . '/data/liga_bundesliga.php';
        require_once __DIR__ . '/data/liga_franca.php';
        require_once __DIR__ . '/data/estaduais.php';
        require_once __DIR__ . '/data/internacionais.php';
        require_once __DIR__ . '/data/copa_brasil.php';
        require_once __DIR__ . '/data/libertadores.php';
        require_once __DIR__ . '/data/europa_league.php';
        require_once __DIR__ . '/data/eurocopa.php';

        $ligasData = [
            ['Brasileirão Série A 2026', 'Liga', 'Brasil', getBrasilA()],
            ['Brasileirão Série B 2026', 'Liga', 'Brasil', getBrasilB()],
            ['Brasileirão Série C 2026', 'Liga', 'Brasil', getBrasilC()],
            ['Premier League 2026/27', 'Liga', 'Inglaterra', getPremierLeague()],
            ['La Liga 2026/27', 'Liga', 'Espanha', getLaLiga()],
            ['Serie A 2026/27', 'Liga', 'Itália', getSerieA()],
            ['Bundesliga 2026/27', 'Liga', 'Alemanha', getBundesliga()],
            ['Ligue 1 2026/27', 'Liga', 'França', getLigue1()],
            ['Paulistão 2026', 'Estadual', 'Brasil', getPaulistao()],
            ['Cariocão 2026', 'Estadual', 'Brasil', getCariocao()],
            ['Taça Rio 2026', 'Copa', 'Brasil', getCariocao()], // Reutilizando times do Cariocão
            ['Copa do Brasil 2026', 'Copa', 'Brasil', getCopaBrasil()],
            ['Libertadores 2026', 'Copa', 'América', getLibertadores()],
            ['Copa do Mundo 2026', 'Copa', 'Mundo', getMundial()],
            ['Champions League 2026', 'Copa', 'Europa', getChampions()],
            ['Europa League 2026', 'Copa', 'Europa', getEuropaLeague()],
            ['Eurocopa 2026', 'Copa', 'Europa', getEurocopa()]
        ];

        foreach ($ligasData as $l) {
            $nomeCamp = $l[0];
            $tipoCamp = $l[1];
            $paisCamp = $l[2];
            $timesList = $l[3];
            $numTimes = count($timesList) > 0 ? count($timesList) : 20;

            $stmt = $pdo->prepare("INSERT INTO campeonatos (nome,tipo,pais,temporada,num_times,ativo,id_save) VALUES (?,?,?,'2026',?,1,?)");
            $stmt->execute([$nomeCamp, $tipoCamp, $paisCamp, $numTimes, $id_save]);
            $camp_id = $pdo->lastInsertId();

            if (empty($timesList)) {
                // Fallback fictício
                for ($i = 1; $i <= $numTimes; $i++) {
                    $timesList[] = ['nome' => $nomeCamp . " Time " . $i, 'sigla' => substr($nomeCamp, 0, 1) . $i, 'cidade' => 'Cidade', 'pais' => $paisCamp, 'rep' => rand(60, 90), 'orca' => 10000000];
                }
            }

            foreach ($timesList as $t) {
                $stmtT = $pdo->prepare("INSERT INTO times (nome,sigla,cidade,pais,liga,reputacao,orcamento,id_save) VALUES (?,?,?,?,?,?,?,?)");
                $stmtT->execute([$t['nome'], $t['sigla'], $t['cidade'] ?? 'Cidade', $t['pais'], $nomeCamp, $t['rep'], $t['orca'], $id_save]);
                $time_id = $pdo->lastInsertId();

                $pdo->prepare("INSERT INTO classificacao (campeonato_id,time_id,id_save) VALUES (?,?,?)")->execute([$camp_id, $time_id, $id_save]);

                // Gerar 22 jogadores por time
                $posicoes = ['Goleiro', 'Goleiro', 'Zagueiro', 'Zagueiro', 'Zagueiro', 'Zagueiro', 'Lateral', 'Lateral', 'Lateral', 'Lateral', 'Meia', 'Meia', 'Meia', 'Meia', 'Meia', 'Meia', 'Atacante', 'Atacante', 'Atacante', 'Atacante', 'Atacante', 'Atacante'];
                foreach ($posicoes as $pos) {
                    $j = generateRealisticPlayer($pos, $t['rep'], $t['pais']);
                    $stmtJ = $pdo->prepare("INSERT INTO jogadores (nome,nacionalidade,posicao,pe_preferido,overall,potencial,velocidade,finalizacao,passe,defesa,fisico,goleiro,valor_mercado,salario,data_nascimento,idade,clube_id,id_save) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
                    $stmtJ->execute(array_merge($j, [$time_id, $id_save]));
                }
            }
        }

        $pdo->commit();
        return true;
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log($e->getMessage());
        return false;
    }
}
