# Gerenciador de Links Avançado para WordPress

![Autor](https://img.shields.io/badge/Autor-ricardochristovao-blue.svg)
![WordPress](https://img.shields.io/badge/WordPress-6.0%2B-blue.svg)
![License](https://img.shields.io/badge/License-GPLv2-brightgreen.svg)
![Version](https://img.shields.io/badge/Versão-2.8.0-orange.svg)

Um poderoso plugin para WordPress que transforma links simples em ferramentas de marketing e gerenciamento de tráfego. Crie slugs personalizados com múltiplos links de destino, rotação inteligente, limites de cliques, e muito mais.

## Visão Geral

Este plugin foi projetado para resolver um problema comum: como gerenciar e distribuir tráfego de forma inteligente a partir de um único link? Seja para campanhas de marketing, links de afiliados, distribuição de acesso a webinars, ou testes A/B, o Gerenciador de Links Avançado oferece um conjunto completo de ferramentas para controlar exatamente para onde seus usuários vão, como eles são contados e como proteger seu servidor de picos de acesso.

De uma simples rotação de links, evoluímos para uma solução completa com funcionalidades de alta performance e rastreamento granular.

## Principais Funcionalidades

O plugin oferece um controle sem precedentes sobre cada slug que você cria:

#### Rotação de Links
* **🔗 Múltiplos Destinos:** Associe um ou mais links de destino a um único slug.
* **🔄 Modos de Rotação:**
    * **Por Limite de Clique (Encher):** Usa um link até seu limite de cliques se esgotar, passando para o próximo da lista. Ideal para links primários com backups.
    * **Sequencial (Round-Robin):** Distribui cada clique para o próximo link da lista em um ciclo contínuo, garantindo um balanceamento de carga uniforme.

#### Rastreamento e Contagem de Cliques
* **🎯 Limite de Cliques:** Defina um número máximo de cliques para cada link individual.
* **🍪 Rastreamento Único por Cookie:** Opção para contar apenas o primeiro clique de um usuário, impedindo que múltiplos cliques da mesma pessoa consumam o seu limite.
* **📊 Contador de Progresso:** Uma barra de progresso visual mostra quantos cliques cada link já recebeu.

#### Performance e Segurança
* **⚡ Fila de Acesso (Alta Performance):** Um modo especial que usa cache de curta duração (transients) para proteger seu servidor de picos de tráfego. Em vez de centenas de acessos simultâneos ao banco de dados, apenas um é processado a cada poucos segundos, enquanto os outros usuários são redirecionados instantaneamente.
* **🛡️ Segurança WordPress:** Utiliza nonces e as melhores práticas de sanitização e validação de dados para manter seu site seguro.

#### Marketing e Flexibilidade
* **쿼 Encaminhamento de Queries:** Encaminhe automaticamente parâmetros de URL (como `?utm_source=...`) do seu slug para o link de destino final. Essencial para rastreamento de campanhas.
* **📄 Página de Fallback:** Defina uma página de fallback para cada slug. Quando todos os links atingirem seus limites, os usuários serão enviados para esta página, garantindo que ninguém encontre um link quebrado.

#### Ferramentas de Administração
* **✨ Interface Moderna:** Painel de controle integrado ao WordPress, limpo e fácil de usar.
* **✏️ Gerenciamento Completo:** Adicione, edite e delete links ou slugs inteiros com facilidade.
* **🗑️ Área de Risco:** Ferramenta para limpar completamente todos os dados do plugin, permitindo um recomeço rápido e limpo.

## Instalação

1.  Baixe o arquivo `.zip` do plugin a partir do repositório.
2.  No seu painel do WordPress, vá para `Plugins` > `Adicionar Novo`.
3.  Clique em `Enviar plugin` no topo da página.
4.  Selecione o arquivo `.zip` que você baixou e clique em `Instalar agora`.
5.  Após a instalação, clique em `Ativar plugin`.
6.  As tabelas necessárias no banco de dados (`wp_gl_slugs` e `wp_gl_links`) serão criadas automaticamente.

## Como Usar

1.  Após a ativação, um novo item de menu chamado **"Gerenciador Links"** aparecerá no seu painel.
2.  A página é dividida em duas colunas:
    * **À esquerda:** Formulário para adicionar um novo link a um slug. Se o slug não existir, ele será criado com as opções definidas neste formulário.
    * **À direita:** Lista de todos os seus slugs e os links associados.
3.  **Para editar um slug:** Clique no link "Editar" ao lado do nome do slug. Você será levado a uma página dedicada onde poderá alterar todas as suas configurações (Fallback, Modo de Rotação, Rastreamento, etc.).
4.  **Ações Rápidas:** Você pode resetar os cliques de um link, deletar um link individual, ou deletar um slug inteiro (junto com todos os seus links) diretamente da lista.

## Apoie o Projeto

Se você achou este plugin útil e ele te ajudou em seus projetos, considere apoiar seu desenvolvimento. Sua contribuição ajuda a garantir a manutenção contínua e a criação de novas funcionalidades.

[![Sponsor](https://img.shields.io/badge/Sponsor-%E2%9D%A4-%23db61a2.svg?style=for-the-badge&logo=github)](https://github.com/sponsors/ricardochristovao)

## Como Contribuir

Contribuições são muito bem-vindas! Se você tem uma sugestão de melhoria ou encontrou um bug, por favor:

1.  **Abra uma Issue:** Descreva o bug ou a sua ideia na [página de Issues](https://github.com/ricardochristovao/gla-link/issues) do repositório.
2.  **Envie um Pull Request:** Se você mesmo fez uma correção ou melhoria, sinta-se à vontade para enviar um [Pull Request](https://github.com/ricardochristovao/gla-link/pulls).


## Registro de Alterações

A lista detalhada de todas as alterações por versão pode ser encontrada no arquivo [`CHANGELOG.md`](CHANGELOG.md).

## Licença

Este plugin é distribuído sob a licença GPLv2 ou posterior.

## Autor

* **Autor Original:** [Ricardo Christovão](https://github.com/ricardochristovao)
* **Desenvolvimento e Melhorias:** Colaboração com a IA para issues pontuais.
