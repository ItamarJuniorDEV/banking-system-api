CREATE TABLE IF NOT EXISTS clientes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nome VARCHAR(100) NOT NULL,
    cpf VARCHAR(11) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    telefone VARCHAR(15) NOT NULL,
    endereco TEXT,
    data_nascimento DATE,
    ativo BOOLEAN DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS contas (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    numero_conta VARCHAR(10) UNIQUE NOT NULL,
    cliente_id INTEGER NOT NULL,
    tipo_conta VARCHAR(20) NOT NULL,
    saldo DECIMAL(15,2) DEFAULT 0.00,
    limite_cheque_especial DECIMAL(15,2) DEFAULT 0.00,
    bloqueada BOOLEAN DEFAULT 0,
    limite_diario DECIMAL(15,2) DEFAULT 5000.00,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id)
);

CREATE TABLE IF NOT EXISTS transacoes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    conta_origem_id INTEGER,
    conta_destino_id INTEGER,
    tipo_operacao VARCHAR(50) NOT NULL,
    valor DECIMAL(15,2) NOT NULL,
    tarifa DECIMAL(15,2) DEFAULT 0.00,
    descricao TEXT,
    saldo_anterior DECIMAL(15,2),
    saldo_posterior DECIMAL(15,2),
    data_transacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    status VARCHAR(20) DEFAULT 'concluida',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (conta_origem_id) REFERENCES contas(id),
    FOREIGN KEY (conta_destino_id) REFERENCES contas(id)
);

CREATE INDEX IF NOT EXISTS idx_clientes_cpf ON clientes(cpf);
CREATE INDEX IF NOT EXISTS idx_clientes_email ON clientes(email);
CREATE INDEX IF NOT EXISTS idx_contas_numero ON contas(numero_conta);
CREATE INDEX IF NOT EXISTS idx_contas_cliente ON contas(cliente_id);
CREATE INDEX IF NOT EXISTS idx_transacoes_conta_origem ON transacoes(conta_origem_id);
CREATE INDEX IF NOT EXISTS idx_transacoes_data ON transacoes(data_transacao);