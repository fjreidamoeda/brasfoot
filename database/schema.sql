-- Fenix Foot Database Schema
-- SQLite Database

-- Times (Teams)
CREATE TABLE IF NOT EXISTS times (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nome VARCHAR(100) NOT NULL,
    sigla VARCHAR(10),
    cidade VARCHAR(100),
    estado VARCHAR(50),
    pais VARCHAR(50) DEFAULT 'Brasil',
    liga VARCHAR(50),
    divisao INTEGER DEFAULT 1,
    reputacao INTEGER DEFAULT 50,
    orcamento DECIMAL(15,2) DEFAULT 1000000.00,
    id_save INTEGER DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Jogadores (Players)
CREATE TABLE IF NOT EXISTS jogadores (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nome VARCHAR(100) NOT NULL,
    apelido VARCHAR(50),
    data_nascimento DATE,
    nacionalidade VARCHAR(50) DEFAULT 'Brasil',
    posicao VARCHAR(20) NOT NULL,
    posicao_secundaria VARCHAR(20),
    pe_preferido VARCHAR(10) DEFAULT 'Destro',
    overall INTEGER DEFAULT 60,
    potencial INTEGER DEFAULT 70,
    velocidade INTEGER DEFAULT 60,
    finalizacao INTEGER DEFAULT 60,
    passe INTEGER DEFAULT 60,
    defesa INTEGER DEFAULT 60,
    fisico INTEGER DEFAULT 60,
    goleiro INTEGER DEFAULT 60,
    valor_mercado DECIMAL(15,2) DEFAULT 100000.00,
    salario DECIMAL(10,2) DEFAULT 5000.00,
    contrato_ate DATE,
    clube_id INTEGER,
    felicidade INTEGER DEFAULT 70,
    forma INTEGER DEFAULT 80,
    id_save INTEGER DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (clube_id) REFERENCES times(id)
);

-- Campeonatos (Championships)
CREATE TABLE IF NOT EXISTS campeonatos (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nome VARCHAR(100) NOT NULL,
    tipo VARCHAR(50) DEFAULT 'Liga',
    pais VARCHAR(50) DEFAULT 'Brasil',
    temporada VARCHAR(10) DEFAULT '2026',
    num_times INTEGER DEFAULT 20,
    rodadas INTEGER DEFAULT 38,
    ativo INTEGER DEFAULT 1,
    id_save INTEGER DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Partidas (Matches)
CREATE TABLE IF NOT EXISTS partidas (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    campeonato_id INTEGER NOT NULL,
    rodada INTEGER DEFAULT 1,
    time_casa_id INTEGER NOT NULL,
    time_fora_id INTEGER NOT NULL,
    gols_casa INTEGER DEFAULT 0,
    gols_fora INTEGER DEFAULT 0,
    data_partida DATE,
    hora VARCHAR(10) DEFAULT '16:00',
    estadio VARCHAR(100),
    jogada INTEGER DEFAULT 0,
    eventos TEXT,
    id_save INTEGER DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (campeonato_id) REFERENCES campeonatos(id),
    FOREIGN KEY (time_casa_id) REFERENCES times(id),
    FOREIGN KEY (time_fora_id) REFERENCES times(id)
);

-- Classificacao (Standings)
CREATE TABLE IF NOT EXISTS classificacao (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    campeonato_id INTEGER NOT NULL,
    time_id INTEGER NOT NULL,
    pontos INTEGER DEFAULT 0,
    jogos INTEGER DEFAULT 0,
    vitorias INTEGER DEFAULT 0,
    empates INTEGER DEFAULT 0,
    derrotas INTEGER DEFAULT 0,
    gols_pro INTEGER DEFAULT 0,
    gols_contra INTEGER DEFAULT 0,
    saldo_gols INTEGER DEFAULT 0,
    aproveitamento DECIMAL(5,2) DEFAULT 0.00,
    id_save INTEGER DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (campeonato_id) REFERENCES campeonatos(id),
    FOREIGN KEY (time_id) REFERENCES times(id)
);

-- Transferencias (Transfers)
CREATE TABLE IF NOT EXISTS transferencias (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    jogador_id INTEGER NOT NULL,
    clube_origem_id INTEGER,
    clube_destino_id INTEGER NOT NULL,
    tipo VARCHAR(20) DEFAULT 'Compra',
    valor DECIMAL(15,2),
    salario DECIMAL(10,2),
    data_transferencia DATE DEFAULT CURRENT_DATE,
    id_save INTEGER DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (jogador_id) REFERENCES jogadores(id),
    FOREIGN KEY (clube_origem_id) REFERENCES times(id),
    FOREIGN KEY (clube_destino_id) REFERENCES times(id)
);

-- Taticas (Tactics)
CREATE TABLE IF NOT EXISTS tacticas (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    time_id INTEGER NOT NULL,
    nome VARCHAR(50) DEFAULT 'Padrão',
    formacao VARCHAR(10) DEFAULT '4-4-2',
    estilo VARCHAR(20) DEFAULT 'Equilibrado',
    marcação INTEGER DEFAULT 50,
    controle INTEGER DEFAULT 50,
    ataque INTEGER DEFAULT 50,
    laterais INTEGER DEFAULT 50,
    id_save INTEGER DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (time_id) REFERENCES times(id)
);

-- Financas (Finances)
CREATE TABLE IF NOT EXISTS financas (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    time_id INTEGER NOT NULL,
    tipo VARCHAR(50) NOT NULL,
    descricao VARCHAR(200),
    valor DECIMAL(15,2) NOT NULL,
    saldo_anterior DECIMAL(15,2),
    saldo_posterior DECIMAL(15,2),
    data_lancamento DATE DEFAULT CURRENT_DATE,
    id_save INTEGER DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (time_id) REFERENCES times(id)
);

-- Patrocinios (Sponsors)
CREATE TABLE IF NOT EXISTS patrocinios (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    time_id INTEGER NOT NULL,
    empresa VARCHAR(100),
    valor_mensal DECIMAL(10,2),
    duracao_meses INTEGER DEFAULT 12,
    data_inicio DATE DEFAULT CURRENT_DATE,
    ativo INTEGER DEFAULT 1,
    id_save INTEGER DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (time_id) REFERENCES times(id)
);

-- Base Jovens (Youth Academy)
CREATE TABLE IF NOT EXISTS base_jovens (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    time_id INTEGER NOT NULL,
    jogador_id INTEGER NOT NULL,
    categoria VARCHAR(20) DEFAULT 'Sub-20',
    potencial INTEGER DEFAULT 70,
    id_save INTEGER DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (time_id) REFERENCES times(id),
    FOREIGN KEY (jogador_id) REFERENCES jogadores(id)
);

-- Calendario (Calendar)
CREATE TABLE IF NOT EXISTS calendario (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    campeonato_id INTEGER,
    partida_id INTEGER,
    data DATE NOT NULL,
    tipo_evento VARCHAR(50) DEFAULT 'Partida',
    descricao VARCHAR(200),
    id_save INTEGER DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (campeonato_id) REFERENCES campeonatos(id),
    FOREIGN KEY (partida_id) REFERENCES partidas(id)
);

-- Estatisticas Jogador (Player Stats)
CREATE TABLE IF NOT EXISTS estatisticas_jogador (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    jogador_id INTEGER NOT NULL,
    partida_id INTEGER,
    campeonato_id INTEGER,
    gols INTEGER DEFAULT 0,
    assistencias INTEGER DEFAULT 0,
    passes INTEGER DEFAULT 0,
    finalizacoes INTEGER DEFAULT 0,
    desarmes INTEGER DEFAULT 0,
    faltas INTEGER DEFAULT 0,
    cartoes_amarelos INTEGER DEFAULT 0,
    cartoes_vermelhos INTEGER DEFAULT 0,
    nota DECIMAL(3,1) DEFAULT 6.0,
    id_save INTEGER DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (jogador_id) REFERENCES jogadores(id),
    FOREIGN KEY (partida_id) REFERENCES partidas(id),
    FOREIGN KEY (campeonato_id) REFERENCES campeonatos(id)
);

-- Premios (Awards)
CREATE TABLE IF NOT EXISTS premios (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nome VARCHAR(100) NOT NULL,
    tipo VARCHAR(50),
    campeonato_id INTEGER,
    jogador_id INTEGER,
    time_id INTEGER,
    temporada VARCHAR(10) DEFAULT '2026',
    id_save INTEGER DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (campeonato_id) REFERENCES campeonatos(id),
    FOREIGN KEY (jogador_id) REFERENCES jogadores(id),
    FOREIGN KEY (time_id) REFERENCES times(id)
);

-- Noticias (News)
CREATE TABLE IF NOT EXISTS noticias (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    titulo VARCHAR(200) NOT NULL,
    conteudo TEXT,
    categoria VARCHAR(50),
    data_publicacao DATE DEFAULT CURRENT_DATE,
    id_save INTEGER DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Configuracoes (Settings)
CREATE TABLE IF NOT EXISTS configuracoes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    chave VARCHAR(50) NOT NULL UNIQUE,
    valor TEXT,
    id_save INTEGER DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Saves (Save Games)
CREATE TABLE IF NOT EXISTS saves (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nome VARCHAR(100) NOT NULL,
    data_inicio DATE DEFAULT CURRENT_DATE,
    temporada_atual VARCHAR(10) DEFAULT '2026',
    dia_atual INTEGER DEFAULT 1,
    mes_atual INTEGER DEFAULT 1,
    ativo INTEGER DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Indexes for performance
CREATE INDEX IF NOT EXISTS idx_jogadores_clube ON jogadores(clube_id);
CREATE INDEX IF NOT EXISTS idx_jogadores_save ON jogadores(id_save);
CREATE INDEX IF NOT EXISTS idx_partidas_campeonato ON partidas(campeonato_id);
CREATE INDEX IF NOT EXISTS idx_partidas_times ON partidas(time_casa_id, time_fora_id);
CREATE INDEX IF NOT EXISTS idx_classificacao_campeonato ON classificacao(campeonato_id);
CREATE INDEX IF NOT EXISTS idx_transferencias_jogador ON transferencias(jogador_id);
CREATE INDEX IF NOT EXISTS idx_financas_time ON financas(time_id);
CREATE INDEX IF NOT EXISTS idx_estatisticas_jogador ON estatisticas_jogador(jogador_id);
CREATE INDEX IF NOT EXISTS idx_times_save ON times(id_save);
CREATE INDEX IF NOT EXISTS idx_campeonatos_save ON campeonatos(id_save);
