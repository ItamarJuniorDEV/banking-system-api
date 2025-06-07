# 🏦 Sistema Bancário - API REST

Um sistema bancário completo desenvolvido em PHP puro, com arquitetura MVC e API REST para gerenciamento de clientes e contas bancárias.

## 📋 Índice

- [Características](#características)
- [Requisitos](#requisitos)
- [Instalação](#instalação)
- [Estrutura do Projeto](#estrutura-do-projeto)
- [Configuração](#configuração)
- [API Endpoints](#api-endpoints)
- [Exemplos de Uso](#exemplos-de-uso)
- [Testes](#testes)
- [Arquitetura](#arquitetura)
- [Contribuição](#contribuição)

## ✨ Características

- **API REST** completa para operações bancárias
- **Arquitetura MVC** bem estruturada
- **Banco de dados SQLite** para persistência
- **Validações** robustas de dados
- **Múltiplos tipos de conta** (Corrente e Poupança)
- **Query Builder** customizado
- **Sistema de rotas** dinâmico
- **Tratamento de erros** padronizado
- **Respostas JSON** estruturadas

## 🔧 Requisitos

- **PHP 8.1+**
- **SQLite 3**
- **Extensões PHP:**
  - PDO
  - SQLite
  - JSON

## 🚀 Instalação

### 1. Clone ou baixe o projeto
```bash
git clone https://github.com/ItamarJuniorDEV/banking-system-api
cd sistema-bancario
```

### 2. Configure o banco de dados
```bash
cd tests
php teste_conexao.php
```

### 3. Inicie o servidor de desenvolvimento
```bash
cd public
php -S localhost:8000
```

### 4. Teste a API
```bash
cd tests
php teste_api.php
```

## 📁 Estrutura do Projeto

```
sistema-bancario/
├── config/
│   └── database.php          # Configurações do banco
├── database/
│   ├── banco.sqlite          # Banco SQLite (criado automaticamente)
│   └── create_tables.sql     # Script de criação das tabelas
├── public/
│   └── index.php            # Ponto de entrada da API
├── src/
│   ├── Controllers/
│   │   ├── BaseController.php
│   │   ├── ClienteController.php
│   │   └── ContaController.php
│   ├── Database/
│   │   ├── Connection.php    # Conexão com banco
│   │   └── QueryBuilder.php  # Construtor de queries
│   ├── Models/
│   │   ├── Model.php         # Model base
│   │   ├── Cliente.php
│   │   ├── Conta.php         # Classe abstrata
│   │   ├── ContaCorrente.php
│   │   └── ContaPoupanca.php
│   ├── Utils/
│   │   ├── Response.php      # Padronização de respostas
│   │   └── ValidationException.php
│   └── Router.php            # Sistema de rotas
└── tests/
    ├── teste_conexao.php     # Teste de conexão
    ├── teste_api.php         # Teste da API
    └── teste_direto.php      # Testes diretos dos models
```

## ⚙️ Configuração

### Banco de Dados

O sistema usa SQLite por padrão. A configuração está em `config/database.php`:

```php
return [
    'driver' => 'sqlite',
    'database' => __DIR__ . '/../database/banco.sqlite',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]
];
```

### Inicialização

Execute o script de inicialização para criar o banco e tabelas:

```bash
php tests/teste_conexao.php
```

## 🌐 API Endpoints

### Base URL
```
http://localhost:8000
```

### Clientes

#### Criar Cliente
```http
POST /clientes
Content-Type: application/json

{
    "nome": "João Silva",
    "cpf": "12345678901",
    "email": "joao@email.com",
    "telefone": "11999999999"
}
```

#### Buscar Cliente
```http
GET /clientes/{id}
```

### Contas

#### Abrir Conta Corrente
```http
POST /contas/corrente
Content-Type: application/json

{
    "cliente_id": 1,
    "limite_cheque_especial": 2000,
    "limite_diario": 5000
}
```

#### Abrir Conta Poupança
```http
POST /contas/poupanca
Content-Type: application/json

{
    "cliente_id": 1,
    "limite_diario": 3000
}
```

#### Consultar Saldo
```http
GET /contas/{numero}/saldo
```

### Estrutura de Resposta

#### Sucesso
```json
{
    "status": "success",
    "data": {
        "id": 1,
        "nome": "João Silva",
        "email": "joao@email.com"
    },
    "timestamp": "2025-06-07 18:30:45"
}
```

#### Erro
```json
{
    "status": "error",
    "data": {
        "error": "Cliente não encontrado"
    },
    "timestamp": "2025-06-07 18:30:45"
}
```

## 📝 Exemplos de Uso

### 1. Criar um Cliente
```bash
curl -X POST http://localhost:8000/clientes \
  -H "Content-Type: application/json" \
  -d '{
    "nome": "Maria Santos",
    "cpf": "98765432100",
    "email": "maria@email.com",
    "telefone": "11888888888"
  }'
```

### 2. Abrir Conta Corrente
```bash
curl -X POST http://localhost:8000/contas/corrente \
  -H "Content-Type: application/json" \
  -d '{
    "cliente_id": 1,
    "limite_cheque_especial": 1500
  }'
```

### 3. Consultar Saldo
```bash
curl http://localhost:8000/contas/12345-6/saldo
```

## 🧪 Testes

### Executar Todos os Testes
```bash
cd tests
php teste_api.php
```

### Testes Individuais

#### Teste de Conexão
```bash
php teste_conexao.php
```

#### Teste dos Models
```bash
php teste_direto.php
```

### Exemplo de Saída dos Testes
```
=== TESTE DEBUG DA API ===
1. Testando rota inexistente...
✅ Retorna erro 404

2. Testando busca de cliente inexistente...
✅ Cliente não encontrado

3. Testando criação de cliente...
✅ Cliente criado com ID: 5

4. Testando abertura de conta corrente...
✅ Conta criada: 19968-0

5. Testando consulta de saldo...
✅ Saldo consultado: R$ 0,00
```

## 🏗️ Arquitetura

### Padrões Utilizados

- **MVC (Model-View-Controller)**
- **Active Record** para Models
- **Repository Pattern** nos Controllers
- **Dependency Injection** básica
- **RESTful API** design

### Componentes Principais

#### Models
- **Model.php**: Classe base com CRUD genérico
- **Cliente.php**: Gerenciamento de clientes
- **Conta.php**: Classe abstrata para contas
- **ContaCorrente.php**: Conta com limite especial
- **ContaPoupanca.php**: Conta poupança

#### Controllers
- **BaseController.php**: Funcionalidades comuns
- **ClienteController.php**: Operações de clientes
- **ContaController.php**: Operações de contas

#### Database
- **Connection.php**: Singleton para conexão
- **QueryBuilder.php**: Construtor de queries SQL

#### Utils
- **Response.php**: Padronização de respostas JSON
- **ValidationException.php**: Exceções customizadas

### Fluxo de Requisição

1. **Router** recebe a requisição HTTP
2. **Controller** específico é chamado
3. **Controller** valida dados de entrada
4. **Model** executa operações no banco
5. **Response** padronizada é retornada

## 🔒 Validações

### Clientes
- Nome: obrigatório, string
- CPF: obrigatório, único, 11 dígitos
- Email: obrigatório, único, formato válido
- Telefone: obrigatório

### Contas
- Cliente deve existir
- Não pode ter conta corrente duplicada
- Limites devem ser numéricos positivos
- Número da conta é gerado automaticamente

## 🐛 Tratamento de Erros

### Códigos de Status HTTP
- **200**: Sucesso
- **400**: Erro de validação/dados inválidos
- **404**: Recurso não encontrado
- **500**: Erro interno do servidor

### Tipos de Erro
- Validação de campos obrigatórios
- Violação de constraints do banco
- Recursos não encontrados
- Erros internos com log detalhado

## 🚧 Melhorias Futuras

- [ ] Operações de depósito e saque
- [ ] Sistema de transferências
- [ ] Histórico de transações
- [ ] Autenticação e autorização
- [ ] Testes unitários com PHPUnit
- [ ] Interface web frontend
- [ ] Documentação Swagger/OpenAPI
- [ ] Docker para containerização
- [ ] Logging estruturado
- [ ] Cache de consultas

## 📄 Licença

Este projeto está sob a licença MIT.

## 🤝 Contribuição

1. Fork o projeto
2. Crie uma branch para sua feature (`git checkout -b feature/nova-feature`)
3. Commit suas mudanças (`git commit -am 'Adiciona nova feature'`)
4. Push para a branch (`git push origin feature/nova-feature`)
5. Abra um Pull Request

## 📞 Suporte

Para dúvidas ou suporte:
- Abra uma [issue](https://github.com/ItamarJuniorDEV/banking-system-api)
- Email: cdajuniorf@gmail.com

---

**Desenvolvido por Itamar Junior**
