<?php

function instagram_feed_bb_add_options_page() {
    add_options_page(
        'Paramètres du flux Instagram', // Le titre de la page
        'Instagram Feed', // Le titre du menu
        'manage_options', // La capacité requise pour accéder à cette page
        'instagram-feed-bb-settings', // Le slug de la page
        'instagram_feed_bb_options_page' // La fonction qui affichera le contenu de la page
    );
}

// Fonction qui affiche le contenu de la page de réglages
function instagram_feed_bb_options_page() {
    // Vérifier si le bouton "Reset Cache" a été cliqué
    if (isset($_POST['reset_cache'])) {
        check_admin_referer('instagram_feed_bb_reset_cache'); // Vérification de sécurité

        delete_transient('instagram_feed_bb_cache'); // Suppression du cache
        echo '<div id="message" class="notice notice-success is-dismissible"><p>Le cache a été réinitialisé avec succès.</p></div>';
    }
?>
    <div class="wrap">
        <h2>Paramètres du flux Instagram</h2>
        <p>Pour afficher le flux Instagram sur votre site, utilisez le shortcode <code>[instagram_feed_bb]</code>.</p>
        <form method="post" action="options.php">
            <?php
            settings_fields('instagram_feed_bb_options_group');
            do_settings_sections('instagram-feed-bb-settings');
            submit_button();
            ?>
        </form>

        <form method="post" action="" style="margin-top: 20px;">
            <?php wp_nonce_field('instagram_feed_bb_reset_cache'); ?>
            <input type="hidden" name="reset_cache" value="1">
            <?php submit_button('Réinitialiser le cache', 'secondary'); ?>
        </form>
    </div>
<?php
}

add_action('admin_menu', 'instagram_feed_bb_add_options_page');

// Enregistrement des options
function instagram_feed_bb_register_settings() {
    register_setting('instagram_feed_bb_options_group', 'instagram_username');
    register_setting('instagram_feed_bb_options_group', 'rapidapi_key');
    register_setting('instagram_feed_bb_options_group', 'overlay_background_color');
    register_setting('instagram_feed_bb_options_group', 'overlay_text_color');
    register_setting('instagram_feed_bb_options_group', 'hide_instagram_posts_in_backend');
    register_setting('instagram_feed_bb_options_group', 'cache_duration');
    register_setting('instagram_feed_bb_options_group', 'enable_debug');

    // Ajout de la section de réglages
    add_settings_section(
        'instagram_feed_bb_settings_section',
        'Paramètres du flux Instagram',
        null,
        'instagram-feed-bb-settings'
    );

    // Champ pour le nom d'utilisateur Instagram
    add_settings_field(
        'instagram_username',
        'Nom d\'utilisateur Instagram',
        'instagram_feed_bb_render_username_field',
        'instagram-feed-bb-settings',
        'instagram_feed_bb_settings_section'
    );

    // Champ pour la clé RapidAPI
    add_settings_field(
        'rapidapi_key',
        'Clé RapidAPI',
        'instagram_feed_bb_render_rapidapi_key_field',
        'instagram-feed-bb-settings',
        'instagram_feed_bb_settings_section'
    );

    // Champ pour la couleur de fond de l'overlay
    add_settings_field(
        'overlay_background_color',
        'Couleur de fond de l\'overlay',
        'instagram_feed_bb_render_overlay_background_color_field',
        'instagram-feed-bb-settings',
        'instagram_feed_bb_settings_section'
    );

    // Champ pour la couleur du texte et des icônes
    add_settings_field(
        'overlay_text_color',
        'Couleur du texte et des icônes',
        'instagram_feed_bb_render_overlay_text_color_field',
        'instagram-feed-bb-settings',
        'instagram_feed_bb_settings_section'
    );

    // Champ pour masquer/afficher les posts Instagram dans le back office
    add_settings_field(
        'hide_instagram_posts_in_backend',
        'Masquer les posts Instagram dans le back office',
        'instagram_feed_bb_render_hide_posts_field',
        'instagram-feed-bb-settings',
        'instagram_feed_bb_settings_section'
    );

    // Champ pour définir la durée du cache
    add_settings_field(
        'cache_duration',
        'Durée du cache (en secondes)',
        'instagram_feed_bb_render_cache_duration_field',
        'instagram-feed-bb-settings',
        'instagram_feed_bb_settings_section'
    );

    // Champ pour activer/désactiver le mode debug
    add_settings_field(
        'enable_debug',
        'Activer le mode debug',
        'instagram_feed_bb_render_debug_mode_field',
        'instagram-feed-bb-settings',
        'instagram_feed_bb_settings_section'
    );

}

// Fonctions pour afficher les champs
function instagram_feed_bb_render_username_field() {
    $username = get_option('instagram_username');
    echo '<input type="text" name="instagram_username" value="' . esc_attr($username) . '">';
    echo '<p>Entrez le nom d\'utilisateur ou l\'ID Instagram pour récupérer les posts.</p>';
}

function instagram_feed_bb_render_rapidapi_key_field() {
    $rapidapi_key = get_option('rapidapi_key');
    echo '<input type="text" name="rapidapi_key" value="' . esc_attr($rapidapi_key) . '">';
    echo '<p>Entrez votre clé RapidAPI pour l\'API Instagram Scraper.</p>';
}

function instagram_feed_bb_render_overlay_background_color_field() {
    $color = get_option('overlay_background_color', '#000000');
    echo '<input type="text" name="overlay_background_color" value="' . esc_attr($color) . '" class="color-field">';
    echo '<p>Choisissez la couleur de fond de l\'overlay (format hexadécimal, par ex : #000000 pour noir).</p>';
}

function instagram_feed_bb_render_overlay_text_color_field() {
    $color = get_option('overlay_text_color', '#FFFFFF');
    echo '<input type="text" name="overlay_text_color" value="' . esc_attr($color) . '" class="color-field">';
    echo '<p>Choisissez la couleur du texte et des icônes dans l\'overlay (format hexadécimal, par ex : #FFFFFF pour blanc).</p>';
}

function instagram_feed_bb_render_hide_posts_field() {
    $hide_posts = get_option('hide_instagram_posts_in_backend', false);
    echo '<input type="checkbox" name="hide_instagram_posts_in_backend" value="1"' . checked(1, $hide_posts, false) . '>';
    echo '<p>Cocher pour masquer les posts Instagram dans le menu du back office.</p>';
}

function instagram_feed_bb_render_cache_duration_field() {
    $duration = get_option('cache_duration', 43200); // 43200 secondes par défaut (12 heures)
    echo '<input type="number" name="cache_duration" value="' . esc_attr($duration) . '">';
    echo '<p>Entrez la durée du cache en secondes. Par exemple, 43200 pour 12 heures.</p>';
}

function instagram_feed_bb_render_debug_mode_field() {
    $debug_enabled = get_option('enable_debug', false);
    echo '<input type="checkbox" name="enable_debug" value="1"' . checked(1, $debug_enabled, false) . '>';
    echo '<p>Cocher pour activer l\'enregistrement des logs dans debug.log.</p>';
}

add_action('admin_init', 'instagram_feed_bb_register_settings');

// Enqueue les scripts et styles nécessaires
function instagram_feed_bb_enqueue_scripts($hook_suffix) {
    if ('settings_page_instagram-feed-bb-settings' !== $hook_suffix) {
        return;
    }
    wp_enqueue_style('wp-color-picker');
    wp_enqueue_script(
        'instagram-feed-bb-main-js',
        plugin_dir_url(__FILE__) . 'assets/js/main.js',
        array('wp-color-picker', 'jquery'),
        false,
        true
    );
}
add_action('admin_enqueue_scripts', 'instagram_feed_bb_enqueue_scripts');

/**
 * Écrit des logs dans un fichier.
 */
function write_insta_log($label, $data = null, $level = 'notice') {
    // Vérifie si le mode debug est activé
    if (!get_option('enable_debug', false)) {
        return; // Si le debug n'est pas activé, on ne logge rien
    }

    $log_file = plugin_dir_path(__FILE__) . 'debug.log';

    if (!file_exists($log_file)) {
        touch($log_file);
    }

    $max_size = 10 * 1024 * 1024; // 10 Mo

    if (filesize($log_file) >= $max_size) {
        file_put_contents($log_file, '');
    }

    $current_date = date('Y-m-d H:i:s');
    $log_message = $current_date . ' [' . strtoupper($level) . '] - ' . $label;
    if ($data !== null) {
        $log_message .= "\n" . print_r($data, true);
    }
    $log_message .= "\n";
    file_put_contents($log_file, $log_message, FILE_APPEND);
}
