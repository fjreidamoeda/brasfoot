<?php
require_once __DIR__ . '/classes/Database.class.php';
require_once __DIR__ . '/classes/Time.php';
require_once __DIR__ . '/classes/Jogador.php';
require_once __DIR__ . '/classes/Campeonato.php';

function popularCompleto($id_save = 1) {
    $time = new Time();
    $jogador = new Jogador();
    $campeonato = new Campeonato();
    
    // Brasileirão Série A 2026 (20 times)
    $brasileirao_a = [
        ['Flamengo', 'FLA', 'Rio de Janeiro', 'RJ', 'Brasil', 'Brasileirão A', 1],
        ['Palmeiras', 'PAL', 'São Paulo', 'SP', 'Brasil', 'Brasileirão A', 1],
        ['Corinthians', 'COR', 'São Paulo', 'SP', 'Brasil', 'Brasileirão A', 1],
        ['São Paulo', 'SPFC', 'São Paulo', 'SP', 'Brasil', 'Brasileirão A', 1],
        ['Vasco', 'VAS', 'Rio de Janeiro', 'RJ', 'Brasil', 'Brasileirão A', 1],
        ['Fluminense', 'FLU', 'Rio de Janeiro', 'RJ', 'Brasil', 'Brasileirão A', 1],
        ['Botafogo', 'BOT', 'Rio de Janeiro', 'RJ', 'Brasil', 'Brasileirão A', 1],
        ['Grêmio', 'GRE', 'Porto Alegre', 'RS', 'Brasil', 'Brasileirão A', 1],
        ['Internacional', 'INT', 'Porto Alegre', 'RS', 'Brasil', 'Brasileirão A', 1],
        ['Atlético-MG', 'CAM', 'Belo Horizonte', 'MG', 'Brasil', 'Brasileirão A', 1],
        ['Cruzeiro', 'CRU', 'Belo Horizonte', 'MG', 'Brasil', 'Brasileirão A', 1],
        ['América-MG', 'AME', 'Belo Horizonte', 'MG', 'Brasil', 'Brasileirão A', 1],
        ['Bahia', 'BAH', 'Salvador', 'BA', 'Brasil', 'Brasileirão A', 1],
        ['Fortaleza', 'FOR', 'Fortaleza', 'CE', 'Brasil', 'Brasileirão A', 1],
        ['Sport', 'SPO', 'Recife', 'PE', 'Brasil', 'Brasileirão A', 1],
        ['Ceará', 'CEA', 'Fortaleza', 'CE', 'Brasil', 'Brasileirão A', 1],
        ['Coritiba', 'COR', 'Curitiba', 'PR', 'Brasil', 'Brasileirão A', 1],
        ['Goiás', 'GOI', 'Goiânia', 'GO', 'Brasil', 'Brasileirão A', 1],
        ['Atlético-PR', 'ATP', 'Curitiba', 'PR', 'Brasil', 'Brasileirão A', 1],
        ['Santos', 'SAN', 'Santos', 'SP', 'Brasil', 'Brasileirão A', 1]
    ];
    
    foreach ($brasileirao_a as $t) {
        $time->criar([
            'nome' => $t[0], 'sigla' => $t[1], 'cidade' => $t[2], 'estado' => $t[3],
            'pais' => $t[4], 'liga' => $t[5], 'divisao' => $t[6], 'id_save' => $id_save
        ]);
    }
    
    // Brasileirão Série B 2026 (20 times)
    $brasileirao_b = [
        ['Vila Nova', 'VIL', 'Goiânia', 'GO', 'Brasil', 'Brasileirão B', 2],
        ['CRB', 'CRB', 'Maceió', 'AL', 'Brasil', 'Brasileirão B', 2],
        ['Criciúma', 'CRI', 'Criciúma', 'SC', 'Brasil', 'Brasileirão B', 2],
        ['Guarani', 'GUA', 'Campinas', 'SP', 'Brasil', 'Brasileirão B', 2],
        ['Ponte Preta', 'PON', 'Campinas', 'SP', 'Brasil', 'Brasileirão B', 2],
        ['Bragantino', 'BRA', 'Bragança Paulista', 'SP', 'Brasil', 'Brasileirão B', 2],
        ['Novorizontino', 'NOV', 'Novo Horizonte', 'SP', 'Brasil', 'Brasileirão B', 2],
        ['Ituano', 'ITU', 'Itu', 'SP', 'Brasil', 'Brasileirão B', 2],
        ['São Bernardo', 'SBE', 'São Bernardo', 'SP', 'Brasil', 'Brasileirão B', 2],
        ['Mirassol', 'MIR', 'Mirassol', 'SP', 'Brasil', 'Brasileirão B', 2],
        ['Chapecoense', 'CHA', 'Chapecó', 'SC', 'Brasil', 'Brasileirão B', 2],
        ['Avaí', 'AVA', 'Florianópolis', 'SC', 'Brasil', 'Brasileirão B', 2],
        ['Cuiabá', 'CUI', 'Cuiabá', 'MT', 'Brasil', 'Brasileirão B', 2],
        ['Operário', 'OPE', 'Ponta Grossa', 'PR', 'Brasil', 'Brasileirão B', 2],
        ['Londrina', 'LON', 'Londrina', 'PR', 'Brasil', 'Brasileirão B', 2],
        ['Brusque', 'BRU', 'Brusque', 'SC', 'Brasil', 'Brasileirão B', 2],
        ['ABC', 'ABC', 'Natal', 'RN', 'Brasil', 'Brasileirão B', 2],
        ['Sampaio Corrêa', 'SAM', 'São Luís', 'MA', 'Brasil', 'Brasileirão B', 2],
        ['Vitória', 'VIT', 'Salvador', 'BA', 'Brasil', 'Brasileirão B', 2],
        ['Náutico', 'NAU', 'Recife', 'PE', 'Brasil', 'Brasileirão B', 2]
    ];
    
    foreach ($brasileirao_b as $t) {
        $time->criar([
            'nome' => $t[0], 'sigla' => $t[1], 'cidade' => $t[2], 'estado' => $t[3],
            'pais' => $t[4], 'liga' => $t[5], 'divisao' => $t[6], 'id_save' => $id_save
        ]);
    }
    
    // Premier League 2026 (20 times)
    $premier_league = [
        ['Manchester City', 'MCI', 'Manchester', '', 'Inglaterra', 'Premier League', 1],
        ['Arsenal', 'ARS', 'Londres', '', 'Inglaterra', 'Premier League', 1],
        ['Liverpool', 'LIV', 'Liverpool', '', 'Inglaterra', 'Premier League', 1],
        ['Manchester United', 'MUN', 'Manchester', '', 'Inglaterra', 'Premier League', 1],
        ['Chelsea', 'CHE', 'Londres', '', 'Inglaterra', 'Premier League', 1],
        ['Newcastle', 'NEW', 'Newcastle', '', 'Inglaterra', 'Premier League', 1],
        ['Tottenham', 'TOT', 'Londres', '', 'Inglaterra', 'Premier League', 1],
        ['Aston Villa', 'AVL', 'Birmingham', '', 'Inglaterra', 'Premier League', 1],
        ['Brighton', 'BHA', 'Brighton', '', 'Inglaterra', 'Premier League', 1],
        ['West Ham', 'WHU', 'Londres', '', 'Inglaterra', 'Premier League', 1],
        ['Crystal Palace', 'CRY', 'Londres', '', 'Inglaterra', 'Premier League', 1],
        ['Fulham', 'FUL', 'Londres', '', 'Inglaterra', 'Premier League', 1],
        ['Wolverhampton', 'WOL', 'Wolverhampton', '', 'Inglaterra', 'Premier League', 1],
        ['Everton', 'EVE', 'Liverpool', '', 'Inglaterra', 'Premier League', 1],
        ['Brentford', 'BRE', 'Brentford', '', 'Inglaterra', 'Premier League', 1],
        ['Nottingham Forest', 'NFO', 'Nottingham', '', 'Inglaterra', 'Premier League', 1],
        ['Luton Town', 'LUT', 'Luton', '', 'Inglaterra', 'Premier League', 1],
        ['Burnley', 'BUR', 'Burnley', '', 'Inglaterra', 'Premier League', 1],
        ['Sheffield United', 'SHU', 'Sheffield', '', 'Inglaterra', 'Premier League', 1],
        ['AFC Bournemouth', 'BOU', 'Bournemouth', '', 'Inglaterra', 'Premier League', 1]
    ];
    
    foreach ($premier_league as $t) {
        $time->criar([
            'nome' => $t[0], 'sigla' => $t[1], 'cidade' => $t[2], 'estado' => $t[3],
            'pais' => $t[4], 'liga' => $t[5], 'divisao' => $t[6], 'id_save' => $id_save
        ]);
    }
    
    // La Liga 2026 (20 times)
    $la_liga = [
        ['Real Madrid', 'RMA', 'Madrid', '', 'Espanha', 'La Liga', 1],
        ['Barcelona', 'BAR', 'Barcelona', '', 'Espanha', 'La Liga', 1],
        ['Atlético Madrid', 'ATM', 'Madrid', '', 'Espanha', 'La Liga', 1],
        ['Girona', 'GIR', 'Girona', '', 'Espanha', 'La Liga', 1],
        ['Athletic Bilbao', 'ATH', 'Bilbao', '', 'Espanha', 'La Liga', 1],
        ['Real Sociedad', 'RSO', 'San Sebastián', '', 'Espanha', 'La Liga', 1],
        ['Valencia', 'VAL', 'Valência', '', 'Espanha', 'La Liga', 1],
        ['Villarreal', 'VIL', 'Villarreal', '', 'Espanha', 'La Liga', 1],
        ['Betis', 'BET', 'Sevilha', '', 'Espanha', 'La Liga', 1],
        ['Sevilla', 'SEV', 'Sevilha', '', 'Espanha', 'La Liga', 1],
        ['Celta Vigo', 'CEL', 'Vigo', '', 'Espanha', 'La Liga', 1],
        ['Osasuna', 'OSA', 'Pamplona', '', 'Espanha', 'La Liga', 1],
        ['Mallorca', 'MAL', 'Palma', '', 'Espanha', 'La Liga', 1],
        ['Las Palmas', 'LPA', 'Las Palmas', '', 'Espanha', 'La Liga', 1],
        ['Rayo Vallecano', 'RAY', 'Madrid', '', 'Espanha', 'La Liga', 1],
        ['Alavés', 'ALA', 'Vitoria', '', 'Espanha', 'La Liga', 1],
        ['Espanyol', 'ESP', 'Barcelona', '', 'Espanha', 'La Liga', 1],
        ['Getafe', 'GET', 'Getafe', '', 'Espanha', 'La Liga', 1],
        ['Leganés', 'LEG', 'Leganés', '', 'Espanha', 'La Liga', 1],
        ['Valladolid', 'VAD', 'Valladolid', '', 'Espanha', 'La Liga', 1]
    ];
    
    foreach ($la_liga as $t) {
        $time->criar([
            'nome' => $t[0], 'sigla' => $t[1], 'cidade' => $t[2], 'estado' => $t[3],
            'pais' => $t[4], 'liga' => $t[5], 'divisao' => $t[6], 'id_save' => $id_save
        ]);
    }
    
    // Criar campeonatos
    $campeonato->criar(['nome' => 'Brasileirão Série A 2026', 'tipo' => 'Liga', 'pais' => 'Brasil', 'temporada' => '2026', 'num_times' => 20, 'rodadas' => 38, 'id_save' => $id_save]);
    $campeonato->criar(['nome' => 'Brasileirão Série B 2026', 'tipo' => 'Liga', 'pais' => 'Brasil', 'temporada' => '2026', 'num_times' => 20, 'rodadas' => 38, 'id_save' => $id_save]);
    $campeonato->criar(['nome' => 'Premier League 2026', 'tipo' => 'Liga', 'pais' => 'Inglaterra', 'temporada' => '2026', 'num_times' => 20, 'rodadas' => 38, 'id_save' => $id_save]);
    $campeonato->criar(['nome' => 'La Liga 2026', 'tipo' => 'Liga', 'pais' => 'Espanha', 'temporada' => '2026', 'num_times' => 20, 'rodadas' => 38, 'id_save' => $id_save]);
    $campeonato->criar(['nome' => 'Copa do Brasil 2026', 'tipo' => 'Copa', 'pais' => 'Brasil', 'temporada' => '2026', 'num_times' => 40, 'rodadas' => 7, 'id_save' => $id_save]);
    $campeonato->criar(['nome' => 'Champions League 2026', 'tipo' => 'Copa', 'pais' => 'Europa', 'temporada' => '2026', 'num_times' => 32, 'rodadas' => 13, 'id_save' => $id_save]);
    
    // Adicionar jogadores para times principais (Brasil)
    $times_brasil = $time->buscarPorLiga('Brasileirão A', $id_save);
    
    $jogadores_exemplo = [
        // Goleiros
        ['Gabriel', 'Goleiro', 82, 85, 75, 70, 60, 60, 78, 85],
        ['Weverton', 'Goleiro', 80, 82, 72, 68, 58, 55, 76, 83],
        ['Rossi', 'Goleiro', 78, 80, 70, 65, 55, 52, 74, 80],
        
        // Defensores
        ['David Luiz', 'Zagueiro', 78, 80, 60, 65, 72, 80, 78, 70],
        ['Marquinhos', 'Zagueiro', 85, 87, 65, 70, 75, 82, 80, 72],
        ['Thiago Silva', 'Zagueiro', 84, 85, 62, 68, 73, 81, 79, 71],
        ['Dani Alves', 'Lateral', 82, 83, 75, 78, 70, 72, 76, 68],
        ['Alex Sandro', 'Lateral', 80, 82, 73, 76, 68, 70, 74, 66],
        ['Danilo', 'Lateral', 81, 83, 72, 75, 69, 71, 73, 65],
        
        // Meias
        ['Casemiro', 'Meia', 86, 87, 70, 75, 82, 85, 80, 72],
        ['Philippe Coutinho', 'Meia', 84, 86, 78, 85, 68, 70, 75, 65],
        ['Paquetá', 'Meia', 85, 88, 80, 82, 70, 72, 76, 68],
        ['Gerson', 'Meia', 82, 85, 76, 80, 75, 78, 77, 70],
        ['Arrascaeta', 'Meia', 83, 85, 78, 84, 65, 68, 72, 62],
        
        // Atacantes
        ['Gabigol', 'Atacante', 86, 88, 85, 88, 70, 65, 82, 72],
        ['Neymar', 'Atacante', 92, 93, 90, 92, 60, 55, 70, 60],
        ['Vini Jr', 'Atacante', 90, 92, 92, 85, 58, 52, 68, 58],
        ['Rodrygo', 'Atacante', 86, 89, 84, 80, 55, 50, 65, 55],
        ['Martinelli', 'Atacante', 84, 88, 82, 78, 52, 48, 62, 52]
    ];
    
    foreach ($times_brasil as $t) {
        foreach ($jogadores_exemplo as $j) {
            $jogador->criar([
                'nome' => $j[0] . ' (' . $t['sigla'] . ')',
                'posicao' => $j[1],
                'overall' => $j[2],
                'potencial' => $j[3],
                'velocidade' => $j[4],
                'finalizacao' => $j[5],
                'passe' => $j[6],
                'defesa' => $j[7],
                'fisico' => $j[8],
                'goleiro' => $j[9],
                'clube_id' => $t['id'],
                'salario' => mt_rand(5000, 50000),
                'valor_mercado' => mt_rand(100000, 5000000),
                'id_save' => $id_save
            ]);
        }
    }
    
    echo "População completa realizada com sucesso! 60 times e jogadores adicionados.\n";
}
