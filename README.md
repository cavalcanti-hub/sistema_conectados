# 📱 Sistema Conectados - Gestão de Assistência Técnica, PDV e Financeiro

![PHP 8.1+](https://img.shields.io/badge/PHP-8.1%2B-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0%2B-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![Architecture](https://img.shields.io/badge/Architecture-MVC%20Custom-2d2dff?style=for-the-badge)
![Security](https://img.shields.io/badge/Security-Sodium%20Crypto-059669?style=for-the-badge)

O **Sistema Conectados** é uma solução ERP/SaaS completa, moderna e de alta performance desenvolvida sob medida para a gestão de **assistências técnicas de celulares, notebooks, eletrônicos e lojas de informática**. 

O sistema une agilidade no atendimento de balcão, controle rigoroso de ordens de serviço, gestão de estoque, caixa PDV e comunicação em tempo real com o cliente final.

---

## 🌟 Destaques e Principais Recursos

### 1. 🎛️ Dashboard Executivo Inteligente (Carrossel Clean & Modais)
- **Carrossel Horizontal de KPIs**: Fila contínua e deslizável em estilo minimalista com navegação por setas (`‹` e `›`) e suporte a gestos *touch/drag* com *scroll-snap*.
- **Visualização 100% Clean**: Eliminação de poluição e textos redundantes na tela principal.
- **Modais Interativos por Etapa**: Clique em qualquer card de status (`OS Abertas`, `Aprovação`, `Em Reparo`, `Aguardando Peça`, `Prontas`) para abrir instantaneamente um **Modal com Backdrop Blur** listando todas as OS ativas daquela etapa, com atalho direto para abertura e WhatsApp do cliente.

### 2. 🔍 Busca Global (Command Palette - `Ctrl + K` / `Cmd + K`)
- **Atalho Universal de Teclado**: Pressione `Ctrl + K` em qualquer tela ou clique no botão de busca da barra superior para abrir o modal de comando estilo *Spotlight / Linear*.
- **Pesquisa Unificada em Tempo Real**:
  - **Ordens de Serviço**: por número da OS, nome do cliente, modelo do aparelho ou IMEI.
  - **Clientes**: por nome, WhatsApp ou CPF/CNPJ.
  - **Produtos/Peças**: por nome do item ou código de barras (com exibição imediata de estoque e preço).
  - **Ações Rápidas**: atalhos para *Nova OS*, *Novo Cliente*, *PDV Balcão*, *Módulo Financeiro*, etc.
- **Navegação por Teclado**: Navegue pelos resultados usando as setas `↑` e `↓` e pressione `Enter` para abrir o registro selecionado.

### 3. 🛠️ Gestão Completa de Ordens de Serviço (OS)
- **Ciclo de Vida Transparente**: Fluxo padronizado (`Recebido` ➔ `Em Análise` ➔ `Aguardando Aprovação` ➔ `Em Reparo` ➔ `Aguardando Peça` ➔ `Pronto` ➔ `Entregue`).
- **Segurança de Aparelhos (Criptografia Sodium)**: Senhas de padrão e PINs de aparelhos de clientes são criptografadas em nível de banco com a biblioteca criptográfica `libsodium` (`v1:` secretbox), prevenindo vazamento de dados.
- **Impressão Versátil**: Emissão de comprovantes para impressoras térmicas (80mm) ou relatórios em papel A4 (resumido ou laudo detalhado).
- **Disparo de Orçamento via WhatsApp**: Geração automática de texto formatado para envio direto ao cliente no WhatsApp com 1 clique.

### 4. 📱 Página Pública de Acompanhamento (Para o Cliente)
- **Acesso Público sem Login**: Rota dedicada (`/consulta?os=NUMERO`) permitindo que o cliente acompanhe o status do conserto pelo próprio smartphone.
- **Linha do Tempo Visual (Timeline)**: Exibição gráfica intuitiva da etapa atual do reparo.
- **Botão Direto de Contato**: Atalho para o cliente chamar a assistência no WhatsApp tirando dúvidas sobre a OS específica.

### 5. 🛒 Frente de Caixa (PDV Balcão) & Estoque
- **Vendas de Balcão Rápida**: Baixa de estoque automática, leitor de código de barras e emissão de comprovantes.
- **Integração Mercado Pago Point**: Suporte a cobranças presenciais na maquininha Point com reconciliação de taxas e conciliação bancária.
- **Alertas de Estoque Crítico**: Identificação visual automática de peças e produtos abaixo do limite mínimo.

### 6. 💰 Módulo Financeiro & DRE
- Controle completo de receitas, despesas fixas/variáveis, fluxo de caixa e extrato detalhado por forma de pagamento (Dinheiro, PIX, Cartão de Crédito/Débito).

---

## 🛠️ Tecnologias Utilizadas

- **Backend**: PHP 8.1+ (Arquitetura MVC pura, sem frameworks pesados, garantindo carregamento ultrarrápido).
- **Criptografia**: Extensão PHP `libsodium` (Secretbox com Nonce aleatório para segredos de aparelhos).
- **Banco de Dados**: MySQL 8.0+ / MariaDB com suporte a transações PDO.
- **Frontend**: HTML5 Semântico, CSS3 Vanilla (Design System baseado em CSS Variables, superfícies neutras e glassmorphism refinado) e JavaScript ES6+.
- **Iconografia**: Lucide Icons.
- **Fontes**: Google Fonts (*Inter* para dados e *Outfit* para títulos).

---

## 🚀 Requisitos de Infraestrutura

- Servidor Web (Apache 2.4+ com módulo `mod_rewrite` ativado).
- PHP 8.1 ou superior.
- Extensão `php_sodium` habilitada no `php.ini`.
- Extensões PHP recomendadas: `pdo_mysql`, `curl`, `mbstring`, `json`, `iconv`.
- MySQL 8.0+ ou MariaDB 10.4+ (Porta padrão: `3306`).

---

## 📦 Instalação e Configuração

### 1. Clonar o Repositório
```bash
git clone https://github.com/cavalcanti-hub/sistema_conectados.git
cd sistema_conectados
```

### 2. Configurar as Variáveis de Ambiente (`.env`)
Crie um arquivo `.env` na raiz do projeto baseado no `.env.example`:

```ini
APP_NAME="Conectados"
APP_ENV=development
APP_URL="http://localhost/sistema_conectados"
APP_TIMEZONE="America/Sao_Paulo"

# Conexao com Banco de Dados
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=conectados_db
DB_USERNAME=root
DB_PASSWORD=

# Chave Criptografica Sodium (32 bytes base64)
DEVICE_SECRET_KEY=u240xb2vC853FidhULU8KZXV+0F9HNwt25eSFpZdRYs=

SESSION_NAME=conectados_session
SESSION_LIFETIME=120
```

### 3. Habilitar a Extensão Sodium no PHP (`php.ini`)
No seu arquivo `php.ini` (ex: `C:\xampp\php\php.ini`), certifique-se de descomentar a linha:
```ini
extension=sodium
```
*(Após alterar o `php.ini`, reinicie o Apache).*

### 4. Importar o Banco de Dados
Crie a base de dados no MySQL e importe o arquivo de estrutura atual:
```bash
mysql -u root -p conectados_db < database/schema_current.sql
```

---

## 🔑 Credenciais de Acesso Inicial

Ao importar o banco de dados inicial, o acesso de administração está configurado como:

- **URL de Login**: `http://localhost/sistema_conectados/login`
- **Usuário**: `Marcelo` (ou `marcelo@conectados.local`)
- **Senha**: `180207`
- **Perfil**: `Administrador`

---

## 📁 Estrutura do Projeto (MVC)

```text
sistema_conectados/
├── app/
│   ├── Config/          # Configuracoes da aplicacao, banco de dados e rotas
│   ├── Controllers/     # Controllers (Dashboard, Os, Clientes, Search, Consulta, etc.)
│   ├── Core/            # Framework central (Router, Controller)
│   ├── Models/          # Regras de negocio e persistencia PDO (OsModel, AuthModel, etc.)
│   ├── Services/        # Servicos (DeviceSecretService com Sodium)
│   ├── Support/         # Helpers globais (helpers.php)
│   └── Views/           # Interfaces HTML/PHP divididas por modulo
├── database/
│   ├── schema_current.sql   # Schema completo do banco de dados
│   └── migrations/          # Migracoes estruturais
├── public/
│   ├── assets/
│   │   ├── css/         # Design system index.css
│   │   └── img/         # Logotipos e favicons
│   ├── .htaccess        # Reescrita de URLs para o front-controller
│   └── index.php        # Ponto de entrada (Front Controller)
├── scripts/             # Scripts CLI de manutencao e migracao
├── tests/               # Suite automatizada de testes (Security, HTTP, Concorrencia)
├── .env.example         # Exemplo de arquivo de configuracao
├── .gitignore           # Protecao contra vazamento de credenciais
└── README.md            # Documentacao oficial do sistema
```

---

## ⌨️ Guia de Atalhos de Teclado

| Atalho | Ação |
| :--- | :--- |
| <kbd>Ctrl</kbd> + <kbd>K</kbd> / <kbd>Cmd</kbd> + <kbd>K</kbd> | Abrir **Command Palette (Busca Global)** em qualquer tela |
| <kbd>↑</kbd> / <kbd>↓</kbd> | Navegar entre os resultados da busca |
| <kbd>Enter</kbd> | Selecionar/abrir o item destacado |
| <kbd>ESC</kbd> | Fechar Command Palette ou Modais abertos |

---

## 🔒 Segurança e Privacidade

- **Visibilidade do Repositório**: Este projeto contém segredos comerciais e regras de negócio de assistência técnica. **Mantenha o repositório marcado como PRIVADO no GitHub**.
- **Arquivos Protegidos**: O arquivo `.gitignore` vem pré-configurado para **nunca** enviar arquivos `.env`, chaves privadas, logs, dumps de banco ou credenciais de FTP para o GitHub.
- **Proteção CSRF**: Todas as requisições `POST` exigem validação de token CSRF (`_csrf_token`).
- **SQL Injection**: Todas as consultas ao banco utilizam *Prepared Statements* com `PDO`.

---

## 📝 Licença e Suporte

Desenvolvido para uso exclusivo em assistências técnicas e comércios parceiros. Todos os direitos reservados.
