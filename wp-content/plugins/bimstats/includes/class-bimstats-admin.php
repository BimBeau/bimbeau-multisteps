<?php

// Ce fichier gère toutes les actions liées à l'administration : ajout de colonnes, affichage des vues et du temps, filtrage, etc.

if (!defined('ABSPATH')) {
    exit;
}

class BimStats_Admin {

    public static function register_admin_actions() {
        add_filter('manage_posts_columns', array(__CLASS__, 'add_views_and_time_columns'));
        add_filter('manage_pages_columns', array(__CLASS__, 'add_views_and_time_columns'));
        add_action('manage_posts_custom_column', array(__CLASS__, 'show_views_and_time_columns'), 10, 2);
        add_action('manage_pages_custom_column', array(__CLASS__, 'show_views_and_time_columns'), 10, 2);
        add_filter('manage_edit-post_sortable_columns', array(__CLASS__, 'make_columns_sortable'));
        add_filter('manage_edit-page_sortable_columns', array(__CLASS__, 'make_columns_sortable'));
        add_action('pre_get_posts', array(__CLASS__, 'apply_column_sorting')); // Ajout du tri personnalisé

        // Ajouter la page de réglages et les actions de réinitialisation
        add_action('admin_menu', array(__CLASS__, 'add_settings_page')); // Ajouter la page de réglages
        add_action('admin_post_bimstats_reset_views', array(__CLASS__, 'reset_views')); // Action pour réinitialiser les vues
        add_action('admin_post_bimstats_reset_time', array(__CLASS__, 'reset_time'));  // Action pour réinitialiser le temps
        add_action('admin_post_bimstats_increment_views', function() { BimStats_Admin::update_all_views('increment'); }); // Incrémenter toutes les vues
        add_action('admin_post_bimstats_increment_time', function() { BimStats_Admin::update_all_time('increment'); });  // Incrémenter tous les temps
        add_action('admin_post_bimstats_decrement_views', function() { BimStats_Admin::update_all_views('decrement'); }); // Soustraire des vues
        add_action('admin_post_bimstats_decrement_time', function() { BimStats_Admin::update_all_time('decrement'); });  // Soustraire du temps
        
    }

    public static function add_settings_page() {
        // Ajouter une nouvelle page dans le menu "Réglages"
        add_options_page(
            'Réglages BimStats', 
            'BimStats', 
            'manage_options', 
            'bimstats-settings', 
            array(__CLASS__, 'render_settings_page')
        );
    }

    public static function render_settings_page() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Réglages BimStats', 'bimstats'); ?></h1>
            
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin-bottom: 20px;">
                <?php wp_nonce_field('bimstats_reset_views_nonce'); ?>
                <input type="hidden" name="action" value="bimstats_reset_views">
                <button type="submit" class="button button-primary"><?php esc_html_e('Réinitialiser les vues', 'bimstats'); ?></button>
            </form>
    
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <?php wp_nonce_field('bimstats_reset_time_nonce'); ?>
                <input type="hidden" name="action" value="bimstats_reset_time">
                <button type="submit" class="button button-primary"><?php esc_html_e('Réinitialiser le temps passé', 'bimstats'); ?></button>
            </form>

            <h2><?php esc_html_e('Incrémenter les vues et le temps passé', 'bimstats'); ?></h2>

            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin-bottom: 20px;">
                <?php wp_nonce_field('bimstats_increment_views_nonce'); ?>
                <input type="hidden" name="action" value="bimstats_increment_views">
                <label for="views_increment"><?php esc_html_e('Incrémenter les vues de', 'bimstats'); ?></label>
                <input type="number" name="views_value" id="views_increment" value="1" min="1">
                <button type="submit" class="button button-primary"><?php esc_html_e('Appliquer', 'bimstats'); ?></button>
            </form>

            <div style="margin-bottom: 20px;"></div>

            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <?php wp_nonce_field('bimstats_increment_time_nonce'); ?>
                <input type="hidden" name="action" value="bimstats_increment_time">
                <label for="time_increment"><?php esc_html_e('Incrémenter le temps passé de (en secondes)', 'bimstats'); ?></label>
                <input type="number" name="time_value" id="time_increment" value="60" min="1">
                <button type="submit" class="button button-primary"><?php esc_html_e('Appliquer', 'bimstats'); ?></button>
            </form>

            <h2><?php esc_html_e('Soustraire les vues et le temps passé', 'bimstats'); ?></h2>

            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin-bottom: 20px;">
                <?php wp_nonce_field('bimstats_decrement_views_nonce'); ?>
                <input type="hidden" name="action" value="bimstats_decrement_views">
                <label for="views_decrement"><?php esc_html_e('Soustraire les vues de', 'bimstats'); ?></label>
                <input type="number" name="views_value" id="views_decrement" value="1" min="1">
                <button type="submit" class="button button-primary"><?php esc_html_e('Appliquer', 'bimstats'); ?></button>
            </form>

            <div style="margin-bottom: 20px;"></div>

            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <?php wp_nonce_field('bimstats_decrement_time_nonce'); ?>
                <input type="hidden" name="action" value="bimstats_decrement_time">
                <label for="time_decrement"><?php esc_html_e('Soustraire le temps passé de (en secondes)', 'bimstats'); ?></label>
                <input type="number" name="time_value" id="time_decrement" value="60" min="1">
                <button type="submit" class="button button-primary"><?php esc_html_e('Appliquer', 'bimstats'); ?></button>
            </form>

        </div>
        <?php
    }

    public static function reset_views() {
        if (!current_user_can('manage_options') || !check_admin_referer('bimstats_reset_views_nonce')) {
            wp_die(__('Vous n\'avez pas l\'autorisation d\'accéder à cette page.', 'bimstats'));
        }

        // Logique pour réinitialiser les vues
        $posts = get_posts(array('numberposts' => -1, 'post_type' => 'any'));
        foreach ($posts as $post) {
            delete_post_meta($post->ID, 'bimstats_post_views');
        }

        wp_redirect(admin_url('options-general.php?page=bimstats-settings&reset=views'));
        exit;
    }

    public static function reset_time() {
        if (!current_user_can('manage_options') || !check_admin_referer('bimstats_reset_time_nonce')) {
            wp_die(__('Vous n\'avez pas l\'autorisation d\'accéder à cette page.', 'bimstats'));
        }

        // Logique pour réinitialiser le temps passé
        $posts = get_posts(array('numberposts' => -1, 'post_type' => 'any'));
        foreach ($posts as $post) {
            delete_post_meta($post->ID, 'bimstats_total_time');
            delete_post_meta($post->ID, 'bimstats_avg_time');
        }

        wp_redirect(admin_url('options-general.php?page=bimstats-settings&reset=time'));
        exit;
    }

    public static function update_all_views($operation = 'increment') {
        if (!current_user_can('manage_options')) {
            wp_die(__('Vous n\'avez pas l\'autorisation d\'accéder à cette page.', 'bimstats'));
        }
    
        // Vérification du nonce pour chaque action
        if ($operation === 'increment' && !check_admin_referer('bimstats_increment_views_nonce')) {
            wp_die(__('Nonce non valide pour l\'incrémentation des vues.', 'bimstats'));
        } elseif ($operation === 'decrement' && !check_admin_referer('bimstats_decrement_views_nonce')) {
            wp_die(__('Nonce non valide pour la décrémentation des vues.', 'bimstats'));
        }
    
        $value = intval($_POST['views_value']); // Récupère la valeur du formulaire
    
        // Logique pour incrémenter ou décrémenter les vues de tous les posts
        $posts = get_posts(array('numberposts' => -1, 'post_type' => 'any'));
        foreach ($posts as $post) {
            $views = get_post_meta($post->ID, 'bimstats_post_views', true) ?: 0;
    
            // Appliquer l'opération en fonction du paramètre
            if ($operation === 'increment') {
                update_post_meta($post->ID, 'bimstats_post_views', $views + $value);
            } elseif ($operation === 'decrement') {
                $new_views = max(0, $views - $value); // S'assurer que les vues ne soient pas négatives
                update_post_meta($post->ID, 'bimstats_post_views', $new_views);
            }
        }
    
        wp_redirect(admin_url('options-general.php?page=bimstats-settings&update=views'));
        exit;
    }
            
    public static function update_all_time($operation = 'increment') {
        if (!current_user_can('manage_options')) {
            wp_die(__('Vous n\'avez pas l\'autorisation d\'accéder à cette page.', 'bimstats'));
        }
    
        // Vérification du nonce pour chaque action
        if ($operation === 'increment' && !check_admin_referer('bimstats_increment_time_nonce')) {
            wp_die(__('Nonce non valide pour l\'incrémentation du temps.', 'bimstats'));
        } elseif ($operation === 'decrement' && !check_admin_referer('bimstats_decrement_time_nonce')) {
            wp_die(__('Nonce non valide pour la décrémentation du temps.', 'bimstats'));
        }
    
        $value = intval($_POST['time_value']); // Récupère la valeur du formulaire
    
        // Logique pour incrémenter ou décrémenter le temps passé de tous les posts
        $posts = get_posts(array('numberposts' => -1, 'post_type' => 'any'));
        foreach ($posts as $post) {
            $avg_time = get_post_meta($post->ID, 'bimstats_avg_time', true) ?: 0;
    
            // Appliquer l'opération en fonction du paramètre
            if ($operation === 'increment') {
                update_post_meta($post->ID, 'bimstats_avg_time', $avg_time + $value);
            } elseif ($operation === 'decrement') {
                $new_time = max(0, $avg_time - $value); // S'assurer que le temps ne devienne pas négatif
                update_post_meta($post->ID, 'bimstats_avg_time', $new_time);
            }
        }
    
        wp_redirect(admin_url('options-general.php?page=bimstats-settings&update=time'));
        exit;
    }
                
    public static function register_admin_filters() {
        add_filter('manage_edit-post_sortable_columns', array(__CLASS__, 'make_columns_sortable'));
        add_filter('manage_edit-page_sortable_columns', array(__CLASS__, 'make_columns_sortable'));
        add_action('pre_get_posts', array(__CLASS__, 'filter_posts_by_views'));
        add_action('restrict_manage_posts', array(__CLASS__, 'add_views_filter'));
    }

    public static function add_views_and_time_columns($columns) {
        // Vérifie si le type de publication est parmi les types spécifiés
        $screen = get_current_screen();
        if (in_array($screen->post_type, ['post', 'page', 'e-landing-page', 'news', 'projet', 'realisation'])) {
            $columns['bimstats_post_views'] = 'Vues';
            $columns['bimstats_avg_time'] = 'Temps moyen (s)';
        }
        return $columns;
    }
    
    public static function show_views_and_time_columns($column, $postID) {
        // Vérifie si le type de publication est parmi les types spécifiés
        $post_type = get_post_type($postID);
        if (in_array($post_type, ['post', 'page', 'e-landing-page', 'news', 'projet', 'realisation'])) {
            if ($column === 'bimstats_post_views') {
                $views = get_post_meta($postID, 'bimstats_post_views', true);
                echo $views ? $views : '0';
            }
    
            if ($column === 'bimstats_avg_time') {
                $avg_time = get_post_meta($postID, 'bimstats_avg_time', true);
                echo $avg_time ? round($avg_time, 2) . ' s' : '0 s';
            }
        }
    }   

    public static function make_columns_sortable($columns) {
        $columns['bimstats_post_views'] = 'bimstats_post_views';
        $columns['bimstats_avg_time'] = 'bimstats_avg_time';
        return $columns;
    }

    public static function apply_column_sorting($query) {
        if (!is_admin() || !$query->is_main_query()) {
            return;
        }

        // Vérifier si le tri est demandé sur les colonnes 'bimstats_post_views' ou 'bimstats_avg_time'
        if ($query->get('orderby') === 'bimstats_post_views') {
            $query->set('meta_key', 'bimstats_post_views');
            $query->set('orderby', 'meta_value_num'); // Préciser que c'est un nombre entier
        }

        if ($query->get('orderby') === 'bimstats_avg_time') {
            $query->set('meta_key', 'bimstats_avg_time');
            $query->set('orderby', 'meta_value_num'); // Préciser que c'est un nombre décimal
        }
    }

    public static function filter_posts_by_views($query) {
        if (is_admin() && $query->is_main_query() && isset($_GET['post_views']) && $query->get('post_type') === 'post') {
            $meta_query = array(
                array(
                    'key' => 'bimstats_post_views',
                    'value' => sanitize_text_field($_GET['post_views']),
                    'compare' => '>='
                )
            );
            $query->set('meta_query', $meta_query);
        }
    }

    public static function add_views_filter() {
        global $typenow;
        if ($typenow == 'post' || $typenow == 'page') {
            $value = isset($_GET['post_views']) ? esc_attr($_GET['post_views']) : '';
            echo '<input type="number" name="post_views" placeholder="Filtrer par vues" value="' . $value . '" />';
        }
    }
}
