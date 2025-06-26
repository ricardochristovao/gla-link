# Registro de Alterações (Changelog)

Todas as alterações notáveis neste projeto serão documentadas neste arquivo.

O formato é baseado em [Keep a Changelog](https://keepachangelog.com/en/1.0.0/), e este projeto adere ao [Versionamento Semântico](https://semver.org/spec/v2.0.0.html).

## [2.7.0] - 2025-06-26

### Added
- **Modos de Rotação de Links:** Implementada a capacidade de escolher entre dois modos de rotação por slug.
  - **Por Limite de Clique (fill):** Comportamento padrão, onde um link é usado até se esgotar antes de passar para o próximo.
  - **Sequencial (round-robin):** Distribui os cliques um a um entre os links disponíveis em um ciclo, ideal para balanceamento de carga.
- Adicionada a coluna `rotation_mode` e `last_link_id` à tabela de slugs para gerenciar a nova funcionalidade.
- Novas opções de rádio nos formulários de criação e edição para configurar o modo de rotação.

## [2.6.0] - 2025-06-26

### Added
- **Encaminhamento de Parâmetros de URL (Queries):** Adicionada uma opção por slug para encaminhar automaticamente os parâmetros da URL (ex: `?utm_source=...`) para o link de destino final. Essencial para campanhas de marketing.
- Adicionada a coluna `forward_queries` (TINYINT) à tabela de slugs.
- Nova caixa de seleção nos formulários de criação e edição para ativar/desativar o recurso.

## [2.5.0] - 2025-06-26

### Added
- **Modo Performance - Fila de Acesso:** Implementado um modo de alta performance para slugs que receberão muitos cliques simultâneos.
- Utiliza a Transients API do WordPress para criar um cache de curta duração da URL de redirecionamento, reduzindo drasticamente a carga no banco de dados durante picos de tráfego.
- Adicionada a coluna `performance_mode` à tabela de slugs.
- Nova opção nos formulários para escolher entre o modo "Direto" (padrão) e "Fila de Acesso".

## [2.4.0] - 2025-06-26

### Added
- **Rastreamento Único por Cookie:** Adicionada a opção por slug de contar apenas o primeiro clique de um usuário.
- Utiliza um cookie no navegador do usuário para identificá-lo e redirecioná-lo para o mesmo link em visitas futuras, sem incrementar o contador.
- Adicionada a coluna `tracking_type` à tabela de slugs.

## [2.3.0] - 2025-06-26

### Added
- **Deletar Slug Inteiro:** Adicionado um botão para deletar um slug e todos os seus links associados de uma vez.
- **Limpar Todos os Dados:** Adicionada uma "Área de Risco" com um botão para apagar permanentemente todos os slugs e links do plugin, permitindo um recomeço limpo.

## [2.2.0] - 2025-06-26

### Fixed
- Melhorada a verificação de erros no banco de dados para garantir que as mensagens de sucesso/erro sejam precisas.

### Added
- Adicionado um modo de depuração (`define('GL_DEBUG', true);`) para facilitar a identificação de problemas.

## [2.1.0] - 2025-06-26

### Changed
- **Otimização de Performance:** A lógica de exibição dos slugs e links no painel de administração foi refatorada para usar uma única consulta SQL com `JOIN`, melhorando a performance e a confiabilidade da exibição.

### Fixed
- Corrigido o bug crítico onde links recém-criados não apareciam na lista, mesmo após a mensagem de sucesso.

## [2.0.1] - 2025-06-26

### Fixed
- Corrigido o processamento das ações de formulário (Adicionar, Deletar, Resetar) que falhava silenciosamente devido a uma verificação de segurança (nonce) incorreta.

### Changed
- Aprimorada a segurança dos links de ação (Deletar, Resetar) com o uso de `wp_nonce_url`.

## [2.0.0] - 2025-06-26

### Added
- **Arquitetura Multi-Tabelas:** Estrutura do banco de dados dividida em `gl_slugs` e `gl_links` para maior integridade e para permitir configurações por slug.
- **Gerenciamento Completo (CRUD):** Implementadas funcionalidades para Editar slugs, Resetar cliques e Deletar links individuais.
- **Segurança Reforçada:** Implementado o uso de nonces do WordPress (`wp_nonce_field`, `check_admin_referer`) em todas as ações para proteção contra CSRF.
- **Fallback por Slug:** A lógica de fallback foi movida do link individual para o slug, permitindo um fallback único por slug.

### Changed
- **Interface de Admin Modernizada:** A interface foi completamente refeita para usar as classes e padrões do WordPress (`wp-list-table`, `form-table`), resultando em um visual limpo e integrado.

## [1.1.3] - Versão Original
- Versão inicial do plugin com a funcionalidade básica de criar um link com slug, limite de cliques e um fallback geral.
