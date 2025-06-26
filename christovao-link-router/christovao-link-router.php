<?php
/**
 * Plugin Name:       Gerenciador de Links Avançado
 * Description:       Plugin completo com múltiplos modos de rotação, limite de cliques, fallback, cookie, fila de acesso e encaminhamento de queries.
 * Version:           2.7.0
 * Author:            Ricardo Christovão
 * Author URI:        https://christovao.com.br/wordpress/plugins/gerenciador-links
 * License:           GPL2
 */

if (!defined('ABSPATH')) exit;

global $wpdb;
define('GL_TABLE_SLUGS', $wpdb->prefix . 'gl_slugs');
define('GL_TABLE_LINKS', $wpdb->prefix . 'gl_links');

register_activation_hook(__FILE__, function() {
    global $wpdb; $charset_collate = $wpdb->get_charset_collate(); require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    $sql_slugs = "CREATE TABLE " . GL_TABLE_SLUGS . " ( id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT, slug_name VARCHAR(255) NOT NULL, fallback_page_id BIGINT(20) UNSIGNED DEFAULT 0, tracking_type VARCHAR(20) NOT NULL DEFAULT 'all_clicks', performance_mode VARCHAR(20) NOT NULL DEFAULT 'direct', forward_queries TINYINT(1) NOT NULL DEFAULT 0, rotation_mode VARCHAR(20) NOT NULL DEFAULT 'fill', last_link_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0, PRIMARY KEY (id), UNIQUE KEY slug_name (slug_name) ) $charset_collate;";
    dbDelta($sql_slugs);
    $sql_links = "CREATE TABLE " . GL_TABLE_LINKS . " ( id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT, slug_id BIGINT(20) UNSIGNED NOT NULL, url TEXT NOT NULL, max_clicks BIGINT(20) NOT NULL, clicks BIGINT(20) DEFAULT 0, PRIMARY KEY (id), KEY slug_id (slug_id) ) $charset_collate;";
    dbDelta($sql_links);
});

add_action('admin_menu', function() { add_menu_page('Gerenciador de Links', 'Gerenciador Links', 'manage_options', 'gerenciador_links', 'gl_render_admin_page', 'dashicons-admin-links'); });

function gl_handle_actions() {
    if (!isset($_REQUEST['action'])) return;
    global $wpdb;
    switch ($_REQUEST['action']) {
        case 'add_link':
            check_admin_referer('gl_add_link_action', 'gl_add_link_nonce');
            $slug_data = [
                'slug_name' => sanitize_title($_POST['slug_name']), 'fallback_page_id' => isset($_POST['fallback_page_id']) ? intval($_POST['fallback_page_id']) : 0,
                'tracking_type' => in_array($_POST['tracking_type'], ['all_clicks', 'unique_by_cookie']) ? $_POST['tracking_type'] : 'all_clicks',
                'performance_mode' => in_array($_POST['performance_mode'], ['direct', 'queue']) ? $_POST['performance_mode'] : 'direct',
                'forward_queries' => isset($_POST['forward_queries']) ? 1 : 0, 'rotation_mode' => in_array($_POST['rotation_mode'], ['fill', 'sequential']) ? $_POST['rotation_mode'] : 'fill',
            ];
            $link_data = ['url' => esc_url_raw($_POST['url']), 'max_clicks' => intval($_POST['max_clicks'])];
            if (empty($slug_data['slug_name']) || empty($link_data['url']) || $link_data['max_clicks'] <= 0) { wp_redirect(admin_url('admin.php?page=gerenciador_links&gl_notice=add_error_fields')); exit; }
            $slug_id = $wpdb->get_var($wpdb->prepare("SELECT id FROM " . GL_TABLE_SLUGS . " WHERE slug_name = %s", $slug_data['slug_name']));
            if (!$slug_id) { $wpdb->insert(GL_TABLE_SLUGS, $slug_data); $slug_id = $wpdb->insert_id; }
            if ($slug_id) { $link_data['slug_id'] = $slug_id; $link_inserted = $wpdb->insert(GL_TABLE_LINKS, $link_data); wp_redirect(admin_url('admin.php?page=gerenciador_links&gl_notice=' . ($link_inserted ? 'add_success' : 'add_error_db_link')));
            } else { wp_redirect(admin_url('admin.php?page=gerenciador_links&gl_notice=add_error_db_slug')); }
            exit;
        case 'update_slug':
            check_admin_referer('gl_edit_slug_' . $_POST['slug_id']); $slug_id = intval($_POST['slug_id']);
            $update_data = [
                'fallback_page_id' => intval($_POST['fallback_page_id']),
                'tracking_type' => in_array($_POST['tracking_type'], ['all_clicks', 'unique_by_cookie']) ? $_POST['tracking_type'] : 'all_clicks',
                'performance_mode' => in_array($_POST['performance_mode'], ['direct', 'queue']) ? $_POST['performance_mode'] : 'direct',
                'forward_queries' => isset($_POST['forward_queries']) ? 1 : 0,
                'rotation_mode' => in_array($_POST['rotation_mode'], ['fill', 'sequential']) ? $_POST['rotation_mode'] : 'fill',
            ];
            if ($update_data['rotation_mode'] === 'fill') { $update_data['last_link_id'] = 0; }
            $wpdb->update(GL_TABLE_SLUGS, $update_data, ['id' => $slug_id]);
            wp_redirect(admin_url('admin.php?page=gerenciador_links&gl_notice=update_success'));
            exit;
        case 'delete_slug': check_admin_referer('gl_delete_slug_' . $_GET['slug_id']); $slug_id = intval($_GET['slug_id']); if ($slug_id > 0) { $wpdb->delete(GL_TABLE_LINKS, ['slug_id' => $slug_id]); $wpdb->delete(GL_TABLE_SLUGS, ['id' => $slug_id]); } wp_redirect(admin_url('admin.php?page=gerenciador_links&gl_notice=slug_deleted')); exit;
        case 'reset': check_admin_referer('gl_reset_link_' . $_GET['link_id']); $link_id = intval($_GET['link_id']); $wpdb->update(GL_TABLE_LINKS, ['clicks' => 0], ['id' => $link_id]); wp_redirect(admin_url('admin.php?page=gerenciador_links&gl_notice=reset_success')); exit;
        case 'delete': check_admin_referer('gl_delete_link_' . $_GET['link_id']); $link_id = intval($_GET['link_id']); $wpdb->delete(GL_TABLE_LINKS, ['id' => $link_id]); wp_redirect(admin_url('admin.php?page=gerenciador_links&gl_notice=delete_success')); exit;
        case 'wipe_all_data': check_admin_referer('gl_wipe_all_data_action'); $wpdb->query("TRUNCATE TABLE " . GL_TABLE_LINKS); $wpdb->query("TRUNCATE TABLE " . GL_TABLE_SLUGS); wp_redirect(admin_url('admin.php?page=gerenciador_links&gl_notice=wiped_success')); exit;
    }
}
add_action('admin_init', 'gl_handle_actions');

function gl_render_admin_page() {
    global $wpdb;
    if (isset($_GET['gl_notice'])) { $messages = ['add_success'=>'Link adicionado!','add_error_fields'=>'Erro: Preencha todos os campos.','add_error_db_slug'=>'Erro de BD ao criar slug.','add_error_db_link'=>'Erro de BD ao salvar link.','update_success'=>'Slug atualizado!','reset_success'=>'Cliques resetados!','delete_success'=>'Link deletado!','slug_deleted'=>'Slug e links deletados!','wiped_success'=>'TODOS os dados foram apagados!']; $notice_type = strpos($_GET['gl_notice'], 'error') !== false ? 'error' : 'success'; echo '<div id="message" class="notice notice-'.esc_attr($notice_type).' is-dismissible"><p><strong>'.esc_html($messages[$_GET['gl_notice']]).'</strong></p></div>'; }
    $editing_slug_id = (isset($_GET['action']) && $_GET['action'] === 'edit_slug') ? intval($_GET['slug_id']) : 0;
    ?>
    <div class="wrap"><h1><span class="dashicons-before dashicons-admin-links"></span> Gerenciador de Links</h1>
        <?php if ($editing_slug_id) : $slug_to_edit = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . GL_TABLE_SLUGS . " WHERE id = %d", $editing_slug_id)); ?>
            <h2>Editando Slug: <?php echo esc_html($slug_to_edit->slug_name); ?></h2>
            <form method="POST" action="<?php echo admin_url('admin.php'); ?>"><input type="hidden" name="action" value="update_slug"><input type="hidden" name="slug_id" value="<?php echo $editing_slug_id; ?>"><?php wp_nonce_field('gl_edit_slug_' . $editing_slug_id); ?><table class="form-table">
                <tr valign="top"><th scope="row">Modo de Rotação</th><td><fieldset><label><input type="radio" name="rotation_mode" value="fill" <?php checked($slug_to_edit->rotation_mode, 'fill'); ?>> Por Limite de Clique (Encher)</label><br><label><input type="radio" name="rotation_mode" value="sequential" <?php checked($slug_to_edit->rotation_mode, 'sequential'); ?>> Sequencial (Round-Robin)</label></fieldset></td></tr>
                <tr valign="top"><th scope="row"><label for="fallback_page_id">Página de Fallback</label></th><td><?php wp_dropdown_pages(['name' => 'fallback_page_id', 'selected' => $slug_to_edit->fallback_page_id, 'show_option_none' => 'Nenhuma (Padrão)', 'option_none_value' => '0']); ?></td></tr>
                <tr valign="top"><th scope="row">Tipo de Rastreamento</th><td><fieldset><label><input type="radio" name="tracking_type" value="all_clicks" <?php checked($slug_to_edit->tracking_type, 'all_clicks'); ?>> Contar todos os cliques</label><br><label><input type="radio" name="tracking_type" value="unique_by_cookie" <?php checked($slug_to_edit->tracking_type, 'unique_by_cookie'); ?>> Contar apenas um clique por usuário (cookie)</label></fieldset></td></tr>
                <tr valign="top"><th scope="row">Modo de Performance</th><td><fieldset><label><input type="radio" name="performance_mode" value="direct" <?php checked($slug_to_edit->performance_mode, 'direct'); ?>> Direto (Padrão)</label><br><label><input type="radio" name="performance_mode" value="queue" <?php checked($slug_to_edit->performance_mode, 'queue'); ?>> Fila de Acesso (Alta Performance)</label><p class="description">Use "Fila" para slugs que receberão muitos cliques simultâneos.</p></fieldset></td></tr>
                <tr valign="top"><th scope="row">Parâmetros de URL</th><td><fieldset><label><input type="checkbox" name="forward_queries" value="1" <?php checked($slug_to_edit->forward_queries, 1); ?>> Encaminhar parâmetros da URL (ex: `?utm_source=...`)</label></fieldset></td></tr>
            </table><?php submit_button('Salvar Alterações'); ?><a href="?page=gerenciador_links" class="button">Voltar</a></form>
        <?php else : ?>
            <div id="col-container" class="wp-clearfix">
                <div id="col-left"><div class="col-wrap"><h2>Adicionar Novo Link</h2><form method="POST" action="<?php echo admin_url('admin.php'); ?>"><input type="hidden" name="action" value="add_link"><?php wp_nonce_field('gl_add_link_action', 'gl_add_link_nonce'); ?>
                    <div class="form-field"><label for="slug_name">Nome do Slug</label><input type="text" name="slug_name" id="slug_name" required><p>Se o slug não existir, será criado com as opções abaixo.</p></div>
                    <div class="form-field"><label for="url">URL de Destino</label><input type="url" name="url" id="url" required></div>
                    <div class="form-field"><label for="max_clicks">Limite de Cliques</label><input type="number" name="max_clicks" id="max_clicks" value="100" min="1" required></div>
                    <div class="form-field"><label>Opções do Slug (Apenas para slugs novos)</label>
                        <fieldset><legend class="screen-reader-text">Modo de Rotação</legend><label><input type="radio" name="rotation_mode" value="fill" checked> Rotação: Por Limite (Encher)</label><br><label><input type="radio" name="rotation_mode" value="sequential"> Rotação: Sequencial</label></fieldset><hr>
                        <fieldset><label for="fallback_page_id">Página de Fallback</label><?php wp_dropdown_pages(['name' => 'fallback_page_id', 'show_option_none' => 'Nenhuma', 'option_none_value' => '0']); ?></fieldset><hr>
                        <fieldset><label><input type="radio" name="tracking_type" value="all_clicks" checked> Rastreio: Contar todos</label><br><label><input type="radio" name="tracking_type" value="unique_by_cookie"> Rastreio: Clique único</label></fieldset><hr>
                        <fieldset><label><input type="radio" name="performance_mode" value="direct" checked> Performance: Direto</label><br><label><input type="radio" name="performance_mode" value="queue"> Performance: Fila</label></fieldset><hr>
                        <fieldset><label><input type="checkbox" name="forward_queries" value="1"> Encaminhar parâmetros da URL</label></fieldset>
                    </div>
                    <?php submit_button('Adicionar Link', 'primary'); ?></form></div></div>
                <div id="col-right"><div class="col-wrap"><h2>Slugs e Links Atuais</h2>
                    <?php $results = $wpdb->get_results("SELECT s.id AS slug_id, s.slug_name, s.fallback_page_id, s.tracking_type, s.performance_mode, s.forward_queries, s.rotation_mode, l.id AS link_id, l.url, l.clicks, l.max_clicks FROM " . GL_TABLE_SLUGS . " AS s LEFT JOIN " . GL_TABLE_LINKS . " AS l ON s.id = l.slug_id ORDER BY s.slug_name ASC, l.id ASC"); $slugs_data = []; if ($results) { foreach ($results as $row) { if (!isset($slugs_data[$row->slug_id])) { $slugs_data[$row->slug_id] = ['slug_name' => $row->slug_name, 'fallback_page_id' => $row->fallback_page_id, 'tracking_type' => $row->tracking_type, 'performance_mode' => $row->performance_mode, 'forward_queries' => $row->forward_queries, 'rotation_mode' => $row->rotation_mode, 'links' => []]; } if ($row->link_id) { $slugs_data[$row->slug_id]['links'][] = $row; } } } ?>
                    <?php if (!empty($slugs_data)) : foreach ($slugs_data as $slug_id => $slug) : ?>
                    <div class="slug-group"><h3>/<?php echo esc_html($slug['slug_name']); ?>/<div class="row-actions"><a href="<?php echo esc_url(admin_url('admin.php?page=gerenciador_links&action=edit_slug&slug_id=' . $slug_id)); ?>">Editar</a>|<span class="trash"><a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=gerenciador_links&action=delete_slug&slug_id=' . $slug_id), 'gl_delete_slug_' . $slug_id)); ?>" onclick="return confirm('Deletar este slug e TODOS os seus links?');" class="submitdelete">Deletar Slug</a></span></div></h3>
                    <p class="slug-meta"><strong>Rotação:</strong> <?php echo $slug['rotation_mode'] == 'sequential' ? 'Sequencial' : 'Por Limite'; ?> | <strong>Rastreio:</strong> <?php echo $slug['tracking_type'] == 'unique_by_cookie' ? 'Único' : 'Todos'; ?> | <strong>Perf.:</strong> <?php echo $slug['performance_mode'] == 'queue' ? 'Fila' : 'Direto'; ?> | <strong>Queries:</strong> <?php echo $slug['forward_queries'] == 1 ? 'Sim' : 'Não'; ?></p>
                    <table class="wp-list-table widefat striped"><thead><tr><th>URL</th><th>Progresso</th><th>Ações</th></tr></thead><tbody>
                    <?php if (!empty($slug['links'])) : foreach ($slug['links'] as $link) : ?><tr><td><?php echo esc_url($link->url); ?></td><td><progress value="<?php echo $link->clicks; ?>" max="<?php echo $link->max_clicks; ?>"></progress> <?php echo intval($link->clicks); ?>/<?php echo intval($link->max_clicks); ?></td><td><a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=gerenciador_links&action=reset&link_id=' . $link->link_id), 'gl_reset_link_' . $link->link_id)); ?>">Resetar</a>|<a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=gerenciador_links&action=delete&link_id=' . $link->link_id), 'gl_delete_link_' . $link->link_id)); ?>" style="color:#a00;">Deletar</a></td></tr><?php endforeach; else : ?><tr><td colspan="3">Nenhum link associado.</td></tr><?php endif; ?>
                    </tbody></table></div><?php endforeach; else : ?><p>Nenhum slug cadastrado ainda.</p><?php endif; ?>
                </div></div>
            </div><hr><div id="danger-zone"><h2>Área de Risco</h2><form method="POST" action="<?php echo admin_url('admin.php'); ?>" onsubmit="return confirm('TEM CERTEZA ABSOLUTA? Todos os slugs e links serão apagados permanentemente.');"><input type="hidden" name="action" value="wipe_all_data"><?php wp_nonce_field('gl_wipe_all_data_action'); ?><?php submit_button('Limpar Todos os Dados do Plugin', 'delete', 'submit', false, ['style' => 'background-color: #d63638; border-color: #d63638; color: #fff;']); ?></form></div>
        <?php endif; ?>
    </div>
    <style>.slug-meta { margin: 0.5em 0; font-style: italic; color: #555; font-size: 13px; } #danger-zone { border: 2px solid #d63638; background: #fff; padding: 1em 2em; margin-top: 2em; border-radius: 4px; } #danger-zone h2 { color: #d63638; } .row-actions { font-size: 13px; font-weight: normal; margin-left: auto; display: inline-flex; gap: 5px; vertical-align: middle;} .submitdelete { color: #d63638 !important; } .submitdelete:hover { text-decoration: underline !important; } .slug-group h3 { display: flex; align-items: center; gap: 5px; } .slug-group { margin-bottom: 2em; padding: 1em; background: #fff; border: 1px solid #ddd; border-radius: 4px;} .form-field { margin-bottom: 1rem; } .form-field label { display: inline-block; width: 100%; margin-bottom: 5px; font-weight: 600; } progress { width: 100%; }</style>
    <?php
}

add_action('init', function() {
    if (is_admin()) return;
    global $wpdb;
    $request_uri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
    if (empty($request_uri)) return;
    $slug = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . GL_TABLE_SLUGS . " WHERE slug_name = %s", $request_uri));
    if (!$slug) return;
    $query_string = $_SERVER['QUERY_STRING'] ?? '';
    if ($slug->performance_mode === 'queue') {
        $transient_name = 'gl_redirect_cache_' . $slug->id; $cached_url = get_transient($transient_name);
        if ($cached_url) {
            if ($slug->forward_queries == 1 && !empty($query_string)) { $cached_url .= (strpos($cached_url, '?') === false) ? '?' : '&'; $cached_url .= $query_string; }
            wp_redirect($cached_url, 302); exit;
        }
    }
    if ($slug->tracking_type === 'unique_by_cookie') {
        $cookie_name = 'gl_unique_click_' . $slug->id;
        if (isset($_COOKIE[$cookie_name])) {
            $redirect_url = esc_url_raw($_COOKIE[$cookie_name]);
            if (!empty($redirect_url)) {
                if ($slug->forward_queries == 1 && !empty($query_string)) { $redirect_url .= (strpos($redirect_url, '?') === false) ? '?' : '&'; $redirect_url .= $query_string; }
                wp_redirect($redirect_url, 302); exit;
            }
        }
    }
    $active_link = null;
    if ($slug->rotation_mode === 'sequential') {
        $active_links = $wpdb->get_results($wpdb->prepare("SELECT id, url FROM " . GL_TABLE_LINKS . " WHERE slug_id = %d AND clicks < max_clicks ORDER BY id ASC", $slug->id));
        if (!empty($active_links)) {
            $next_link_index = 0; $last_link_id = $slug->last_link_id;
            if ($last_link_id > 0) {
                $ids_column = array_column($active_links, 'id');
                $last_link_index = array_search($last_link_id, $ids_column);
                if ($last_link_index !== false) { $next_link_index = ($last_link_index + 1) % count($active_links); }
            }
            $active_link = $active_links[$next_link_index];
        }
    } else {
        $active_link = $wpdb->get_row($wpdb->prepare("SELECT id, url FROM " . GL_TABLE_LINKS . " WHERE slug_id = %d AND clicks < max_clicks ORDER BY id ASC LIMIT 1", $slug->id));
    }
    $final_redirect_url = ''; $base_redirect_url = '';
    if ($active_link) {
        $base_redirect_url = $active_link->url;
        $wpdb->query($wpdb->prepare("UPDATE " . GL_TABLE_LINKS . " SET clicks = clicks + 1 WHERE id = %d", $active_link->id));
        if ($slug->rotation_mode === 'sequential') { $wpdb->update(GL_TABLE_SLUGS, ['last_link_id' => $active_link->id], ['id' => $slug->id]); }
        if ($slug->tracking_type === 'unique_by_cookie') { setcookie('gl_unique_click_' . $slug->id, $base_redirect_url, time() + (86400 * 365), COOKIEPATH, COOKIE_DOMAIN); }
    } else {
        if ($slug->fallback_page_id > 0 && ($fallback_status = get_post_status($slug->fallback_page_id)) && $fallback_status === 'publish') { $base_redirect_url = get_permalink($slug->fallback_page_id);
        } else { wp_die('Desculpe, todos os links para este destino foram esgotados e não há uma página de fallback configurada.', 'Links Esgotados', ['response' => 404]); }
    }
    $final_redirect_url = $base_redirect_url;
    if ($slug->forward_queries == 1 && !empty($query_string)) { $final_redirect_url .= (strpos($final_redirect_url, '?') === false) ? '?' : '&'; $final_redirect_url .= $query_string; }
    if ($slug->performance_mode === 'queue' && !empty($base_redirect_url)) { set_transient('gl_redirect_cache_' . $slug->id, $base_redirect_url, 5); }
    if (!empty($final_redirect_url)) { wp_redirect($final_redirect_url, 302); exit; }
});