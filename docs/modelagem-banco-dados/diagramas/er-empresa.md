# Diagrama ER - Domínio de Empresa (Multitenant)

## 📊 Visão Geral

Este diagrama representa o núcleo do multitenancy, onde cada empresa é um tenant isolado com configurações específicas, filiais, usuários e sistema RBAC integrado para controle granular de permissões.

## 🗂️ Diagrama Completo

```mermaid
erDiagram
    EMPRESA ||--o{ FILIAL : "possui filiais"
    EMPRESA ||--o{ PARAMETRO_OPERACIONAL : "configura"
    EMPRESA ||--o{ CONFIGURACAO_FISCAL : "define fiscalidade"
    EMPRESA ||--o{ USUARIO_EMPRESA_PAPEL : "autoriza acesso"
    FILIAL ||--o{ CONFIGURACAO_FISCAL : "configura específica"
    FILIAL }o--|| ENDERECO : "localizada em"
    USUARIO }o--|| PESSOA : "representa"
    USUARIO ||--o{ USUARIO_EMPRESA_PAPEL : "acessa empresas"
    PAPEL ||--o{ USUARIO_EMPRESA_PAPEL : "define contexto"
    PERMISSAO ||--o{ USUARIO_EMPRESA_PAPEL : "concede direitos"
    CERTIFICADO_DIGITAL }o--|| EMPRESA : "pertence à"
    
    EMPRESA {
        uuid id PK "Identificador único do tenant"
        varchar_255 nome "Nome da empresa"
        varchar_18 cnpj "CNPJ formatado (único)"
        varchar_20 ie "Inscrição Estadual"
        varchar_20 im "Inscrição Municipal"
        enum regime_tributario "SIMPLES_NACIONAL | LUCRO_PRESUMIDO | LUCRO_REAL"
        date data_constituicao "Data de constituição"
        enum status "ATIVO | INATIVO | SUSPENSO"
        timestamp data_criacao "Data de criação do tenant"
    }
    
    FILIAL {
        uuid id PK "Identificador único"
        uuid empresa_id FK "Referência à empresa"
        varchar_255 nome "Nome da filial"
        varchar_18 cnpj_filial "CNPJ específico (opcional)"
        uuid endereco_id FK "Endereço da filial"
        enum status "ATIVO | INATIVO"
    }
    
    PARAMETRO_OPERACIONAL {
        uuid id PK "Identificador único"
        uuid empresa_id FK "Referência à empresa"
        varchar_100 chave "Nome do parâmetro"
        jsonb valor "Valor flexível em JSON"
        timestamp data_atualizacao "Última atualização"
    }
    
    CONFIGURACAO_FISCAL {
        uuid id PK "Identificador único"
        uuid empresa_id FK "Referência à empresa"
        uuid filial_id FK "Filial específica (opcional)"
        decimal_5_2 aliquota_iss_default "Alíquota ISS padrão"
        varchar_7 codigo_municipio_ibge "Código IBGE do município"
        uuid certificado_digital_id FK "Certificado para assinatura"
        varchar_255 webservice_url "URL do webservice municipal"
        enum ambiente "PRODUCAO | HOMOLOGACAO"
    }
    
    USUARIO {
        uuid id PK "Identificador único"
        uuid pessoa_id FK "Referência à pessoa"
        varchar_255 email "Email de login (único)"
        varchar_255 senha_hash "Hash da senha"
        boolean mfa_ativo "Multi-factor auth ativo"
        timestamp data_ultimo_login "Último acesso"
    }
    
    PERMISSAO {
        uuid id PK "Identificador único"
        varchar_100 nome "Nome da permissão (único)"
        varchar_255 descricao "Descrição da permissão"
        enum modulo "FATURAMENTO | FISCAL | FINANCEIRO | ORDEM"
    }
    
    USUARIO_EMPRESA_PAPEL {
        uuid id PK "Identificador único"
        uuid usuario_id FK "Referência ao usuário"
        uuid empresa_id FK "Empresa de acesso"
        uuid papel_id FK "Papel na empresa"
        uuid_array permissao_ids "Array de permissões"
    }
    
    CERTIFICADO_DIGITAL {
        uuid id PK "Identificador único"
        uuid empresa_id FK "Referência à empresa"
        bytea arquivo_certificado "Arquivo do certificado"
        varchar_255 senha "Senha do certificado"
        timestamp data_validade_inicio "Início da validade"
        timestamp data_validade_fim "Fim da validade"
        varchar_255 emissor "Emissor do certificado"
    }
    
    ENDERECO {
        uuid id PK "Referência externa"
        varchar_255 logradouro "Endereço completo (referência)"
    }
    
    PESSOA {
        uuid id PK "Referência externa"
        varchar_255 nome_razao_social "Nome da pessoa (referência)"
    }
    
    PAPEL {
        uuid id PK "Referência externa"
        enum tipo_papel "Tipo do papel (referência)"
    }
```

## 🔍 Detalhamento dos Relacionamentos

### Empresa → Filial (1:N)
- **Cardinalidade**: Uma empresa pode ter múltiplas filiais
- **Finalidade**: Suporte a operação multi-localização
- **Fiscalidade**: Cada filial pode ter configurações fiscais específicas
- **Endereçamento**: Cada filial tem endereço próprio

### Empresa → ParametroOperacional (1:N)
- **Pattern EAV**: Configurações extensíveis sem mudança de schema
- **Flexibilidade**: Permite personalização por tenant
- **Versionamento**: Controle de histórico de mudanças
- **Performance**: Índice GIN para busca em JSONB

### Empresa → ConfiguracaoFiscal (1:N)
- **Multimunicipal**: Suporte a múltiplas prefeituras
- **Por Filial**: Configurações específicas por localização
- **Certificados**: Vinculação de certificados digitais
- **Ambientes**: Separação produção/homologação

### Usuario → UsuarioEmpresaPapel (1:N)
- **Multitenant**: Um usuário acessa múltiplas empresas
- **Contexto**: Papel específico em cada empresa
- **RBAC**: Permissões granulares por contexto
- **Auditoria**: Rastreamento de acessos

### Papel → UsuarioEmpresaPapel (1:N)
- **Integração**: Reutiliza papéis do domínio de identidade
- **Contextualização**: Papel específico para acesso ao sistema
- **Flexibilidade**: Permite múltiplos papéis por usuário/empresa

## 📋 Constraints e Índices Importantes

### Unique Constraints
```sql
-- CNPJ único globalmente
UNIQUE (cnpj) ON empresa

-- Email único globalmente
UNIQUE (email) ON usuario

-- Usuário único por pessoa
UNIQUE (pessoa_id) ON usuario

-- Nome de permissão único
UNIQUE (nome) ON permissao

-- Parâmetro único por empresa
UNIQUE (empresa_id, chave) ON parametro_operacional

-- Usuário/empresa/papel único
UNIQUE (usuario_id, empresa_id, papel_id) ON usuario_empresa_papel
```

### Índices de Performance
```sql
-- Isolation multitenant
CREATE INDEX idx_filial_empresa_id ON filial (empresa_id);
CREATE INDEX idx_parametro_empresa_id ON parametro_operacional (empresa_id);
CREATE INDEX idx_config_fiscal_empresa_id ON configuracao_fiscal (empresa_id);
CREATE INDEX idx_uep_empresa_id ON usuario_empresa_papel (empresa_id);

-- Busca por usuário
CREATE INDEX idx_usuario_email ON usuario (email);
CREATE INDEX idx_usuario_pessoa_id ON usuario (pessoa_id);
CREATE INDEX idx_uep_usuario_id ON usuario_empresa_papel (usuario_id);

-- Busca por localização
CREATE INDEX idx_config_fiscal_municipio ON configuracao_fiscal (codigo_municipio_ibge);
CREATE INDEX idx_filial_cnpj ON filial (cnpj_filial);

-- RBAC performance
CREATE INDEX idx_permissao_modulo ON permissao (modulo);
CREATE INDEX gin_uep_permissoes ON usuario_empresa_papel USING GIN (permissao_ids);

-- Configurações
CREATE INDEX idx_parametro_chave ON parametro_operacional (chave);
CREATE INDEX gin_parametro_valor ON parametro_operacional USING GIN (valor);
```

## 🔐 Row Level Security (RLS)

### Políticas de Isolamento
```sql
-- Isolamento por tenant em todas as tabelas
CREATE POLICY tenant_isolation_filial ON filial
FOR ALL TO app_role
USING (empresa_id = current_setting('app.tenant_id')::uuid);

CREATE POLICY tenant_isolation_parametro ON parametro_operacional
FOR ALL TO app_role  
USING (empresa_id = current_setting('app.tenant_id')::uuid);

CREATE POLICY tenant_isolation_config_fiscal ON configuracao_fiscal
FOR ALL TO app_role
USING (empresa_id = current_setting('app.tenant_id')::uuid);

-- Isolamento de usuário para seus próprios dados
CREATE POLICY user_isolation_usuario ON usuario
FOR ALL TO app_role
USING (id = current_setting('app.user_id')::uuid);

-- Acesso apenas a empresas autorizadas
CREATE POLICY authorized_companies_uep ON usuario_empresa_papel
FOR ALL TO app_role
USING (usuario_id = current_setting('app.user_id')::uuid);
```

### Context Setting
```sql
-- Definir contexto da sessão
SELECT set_config('app.tenant_id', @empresa_id::text, true);
SELECT set_config('app.user_id', @usuario_id::text, true);
```

## 🔄 Fluxos de Dados Principais

### 1. Onboarding de Nova Empresa
```sql
-- 1. Criar empresa (tenant)
INSERT INTO empresa (id, nome, cnpj, regime_tributario, status)
VALUES (uuid_generate_v4(), 'Empresa ABC Ltda', '12.345.678/0001-99', 'SIMPLES_NACIONAL', 'ATIVO');

-- 2. Configurar parâmetros operacionais
INSERT INTO parametro_operacional (id, empresa_id, chave, valor)
VALUES 
    (uuid_generate_v4(), @empresa_id, 'moeda_padrao', '"BRL"'),
    (uuid_generate_v4(), @empresa_id, 'timezone', '"America/Sao_Paulo"'),
    (uuid_generate_v4(), @empresa_id, 'casas_decimais', '2');

-- 3. Configurar fiscalidade
INSERT INTO configuracao_fiscal (id, empresa_id, codigo_municipio_ibge, aliquota_iss_default, webservice_url, ambiente)
VALUES (uuid_generate_v4(), @empresa_id, '3550308', 2.00, 'https://nfse.prefeitura.sp.gov.br/ws', 'PRODUCAO');
```

### 2. Cadastro de Usuário
```sql
-- 1. Criar usuário vinculado à pessoa
INSERT INTO usuario (id, pessoa_id, email, senha_hash, mfa_ativo)
VALUES (uuid_generate_v4(), @pessoa_id, 'admin@empresa.com', @senha_hash, false);

-- 2. Vincular usuário à empresa com papel
INSERT INTO usuario_empresa_papel (id, usuario_id, empresa_id, papel_id, permissao_ids)
VALUES (uuid_generate_v4(), @usuario_id, @empresa_id, @papel_admin_id, ARRAY[@perm1, @perm2, @perm3]);
```

### 3. Verificação de Permissões
```sql
-- Verificar se usuário tem permissão específica na empresa
SELECT EXISTS (
    SELECT 1 FROM usuario_empresa_papel uep
    WHERE uep.usuario_id = @usuario_id
    AND uep.empresa_id = @empresa_id
    AND @permissao_id = ANY(uep.permissao_ids)
) as tem_permissao;

-- Listar todas as permissões do usuário na empresa
SELECT p.nome, p.descricao, p.modulo
FROM usuario_empresa_papel uep
JOIN permissao p ON p.id = ANY(uep.permissao_ids)
WHERE uep.usuario_id = @usuario_id
AND uep.empresa_id = @empresa_id;
```

## ⚙️ Cache e Performance

### Cache de Permissões (Redis)
```json
{
  "key": "user_permissions:{user_id}:{empresa_id}",
  "value": {
    "user_id": "uuid",
    "empresa_id": "uuid",
    "papel": "ADMIN",
    "permissions": [
      "emitir_nfse",
      "cancelar_nfse", 
      "criar_ordem",
      "aprovar_fatura"
    ],
    "ttl": 3600
  }
}
```

### Cache de Configurações (Redis)
```json
{
  "key": "tenant_config:{empresa_id}",
  "value": {
    "nome": "Empresa ABC",
    "regime_tributario": "SIMPLES_NACIONAL",
    "parametros": {
      "moeda_padrao": "BRL",
      "timezone": "America/Sao_Paulo",
      "casas_decimais": 2
    },
    "fiscal": {
      "municipio_principal": "3550308",
      "aliquota_iss_default": 2.0,
      "ambiente": "PRODUCAO"
    },
    "ttl": 7200
  }
}
```

## 📊 Métricas e Monitoramento

### KPIs por Tenant
```sql
-- Usuários ativos por empresa nos últimos 30 dias
SELECT 
    e.nome as empresa,
    COUNT(DISTINCT u.id) as usuarios_ativos
FROM empresa e
JOIN usuario_empresa_papel uep ON uep.empresa_id = e.id
JOIN usuario u ON u.id = uep.usuario_id
WHERE u.data_ultimo_login >= CURRENT_DATE - INTERVAL '30 days'
GROUP BY e.id, e.nome;

-- Distribuição de papéis por empresa
SELECT 
    e.nome as empresa,
    pa.tipo_papel,
    COUNT(*) as quantidade
FROM empresa e
JOIN usuario_empresa_papel uep ON uep.empresa_id = e.id
JOIN papel pa ON pa.id = uep.papel_id
GROUP BY e.id, e.nome, pa.tipo_papel;
```

### Alertas de Segurança
- **Tentativas de Login Falhadas**: > 5 em 15 minutos
- **Certificados Vencendo**: < 30 dias para expirar
- **Usuários Inativos**: > 90 dias sem login
- **Permissões Órfãs**: Permissões não utilizadas

---

*Última atualização: {{data_atual}}*
