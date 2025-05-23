<?php
/*
Plugin Name: BimBeau MultiSteps
Description: Convertit le formulaire d'estimation multi-étapes en plugin administrable.
Version: 1.0.0
Author: BimBeau
*/

if (!defined('ABSPATH')) {
    exit;
}

// Démarrage de la session si nécessaire
add_action('init', function() {
    if (!session_id()) {
        session_start();
    }
});

// Définition des constantes du plugin
define('BIMBEAU_MS_DIR', plugin_dir_path(__FILE__));
define('BIMBEAU_MS_URL', plugin_dir_url(__FILE__));

// Enqueue front-end assets
function bimbeau_ms_enqueue_assets() {
    wp_enqueue_style(
        'bimbeau-ms-style',
        BIMBEAU_MS_URL . 'assets/css/estimation-form.css',
        [],
        '1.0.0'
    );

    wp_enqueue_script(
        'bimbeau-ms-form-interactions',
        BIMBEAU_MS_URL . 'assets/js/form-interactions.js',
        ['jquery'],
        '1.0.0',
        true
    );
}
add_action('wp_enqueue_scripts', 'bimbeau_ms_enqueue_assets');

// Chargement du coeur du formulaire après l'initialisation de WordPress
function bimbeau_ms_load_core() {
    require_once BIMBEAU_MS_DIR . 'includes/estimation-core.php';
}
add_action('init', 'bimbeau_ms_load_core');

// Création de la page d'options
add_action('admin_menu', function() {
    $label = get_option('bimbeau_ms_menu_label', 'BimBeau MultiSteps');
    $icon  = get_option('bimbeau_ms_menu_icon', 'dashicons-admin-generic');
    add_menu_page(
        'Réglages',
        $label,
        'manage_options',
        'bimbeau-multisteps',
        'bimbeau_ms_options_page',
        $icon
    );

    add_submenu_page(
        'bimbeau-multisteps',
        'Réglages',
        'Réglages',
        'manage_options',
        'bimbeau-ms-settings',
        'bimbeau_ms_options_page'
    );

    add_submenu_page(
        'bimbeau-multisteps',
        'Gérer les étapes',
        'Gérer les étapes',
        'manage_options',
        'bimbeau-ms-steps',
        'bimbeau_ms_steps_page'
    );
});

function bimbeau_ms_dashboard_page() {
    if (!current_user_can('manage_options')) {
        return;
    }
    echo '<div class="wrap">';
    echo '<h1>Tableau de bord</h1>';
    echo '<p>Bienvenue dans le tableau de bord du plugin.</p>';
    echo '</div>';
}

function bimbeau_ms_options_page() {
    if (!current_user_can('manage_options')) {
        return;
    }
    if (isset($_POST['bimbeau_ms_save'])) {
        update_option('bimbeau_ms_mode', sanitize_text_field($_POST['mode']));
        update_option('bimbeau_ms_payment_link', sanitize_text_field($_POST['payment_link_prod']));
        update_option('bimbeau_ms_payment_link_test', sanitize_text_field($_POST['payment_link_test']));
        update_option('bimbeau_ms_secret_key', sanitize_text_field($_POST['secret_key']));
        update_option('bimbeau_ms_admin_email', sanitize_email($_POST['admin_email']));
        update_option('bimbeau_ms_menu_label', sanitize_text_field($_POST['menu_label']));
        update_option('bimbeau_ms_menu_icon', sanitize_text_field($_POST['menu_icon']));
        echo '<div class="updated"><p>Options enregistrées.</p></div>';
    }
    $mode = get_option('bimbeau_ms_mode', 'PROD');
    $payment_prod = get_option('bimbeau_ms_payment_link', '');
    $payment_test = get_option('bimbeau_ms_payment_link_test', '');
    $secret = get_option('bimbeau_ms_secret_key', '');
    $admin = get_option('bimbeau_ms_admin_email', '');
    $menu_label = get_option('bimbeau_ms_menu_label', 'BimBeau MultiSteps');
    $menu_icon  = get_option('bimbeau_ms_menu_icon', 'dashicons-admin-generic');
    ?>
    <div class="wrap">
        <h1>Réglages</h1>
        <form method="post">
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="mode">Mode Stripe</label></th>
                    <td>
                        <select name="mode" id="mode">
                            <option value="PROD" <?php selected($mode, 'PROD'); ?>>PROD</option>
                            <option value="TEST" <?php selected($mode, 'TEST'); ?>>TEST</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="payment_link_prod">Payment Link PROD</label></th>
                    <td><input type="text" id="payment_link_prod" name="payment_link_prod" value="<?php echo esc_attr($payment_prod); ?>" class="regular-text" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="payment_link_test">Payment Link TEST</label></th>
                    <td><input type="text" id="payment_link_test" name="payment_link_test" value="<?php echo esc_attr($payment_test); ?>" class="regular-text" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="secret_key">Secret Key</label></th>
                    <td><input type="text" id="secret_key" name="secret_key" value="<?php echo esc_attr($secret); ?>" class="regular-text" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="admin_email">Email admin</label></th>
                    <td><input type="email" id="admin_email" name="admin_email" value="<?php echo esc_attr($admin); ?>" class="regular-text" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="menu_label">Nom du menu</label></th>
                    <td><input type="text" id="menu_label" name="menu_label" value="<?php echo esc_attr($menu_label); ?>" class="regular-text" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="menu_icon">Icône du menu</label></th>
                    <td>
                        <input type="text" id="menu_icon" name="menu_icon" value="<?php echo esc_attr($menu_icon); ?>" class="regular-text" />
                        <p class="description">URL ou Dashicon (ex. dashicons-admin-generic)</p>
                    </td>
                </tr>
            </table>
            <p class="submit"><input type="submit" name="bimbeau_ms_save" id="submit" class="button button-primary" value="Enregistrer" /></p>
        </form>
    </div>
    <?php
}

function bimbeau_ms_steps_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    $custom_steps = get_option('bimbeau_ms_custom_steps', []);

    if (isset($_POST['bimbeau_ms_add_step']) && !empty($_POST['step_name'])) {
        $step_name = sanitize_text_field($_POST['step_name']);
        $custom_steps[] = $step_name;
        update_option('bimbeau_ms_custom_steps', $custom_steps);
        echo '<div class="updated"><p>Étape ajoutée.</p></div>';
    }

    $default_steps = [
        'Mon profil',
        'Mon projet',
        'Mon accompagnement',
        'Mes besoins',
        'Informations complémentaires',
        'Superficie',
        'Démarrage du projet',
        'Mon budget',
        'Mes coordonnées',
        'Envoyer ma demande',
        'Remerciement'
    ];

    $all_steps = array_merge($default_steps, $custom_steps);

    echo '<div class="wrap">';
    echo '<h1>Gestion des étapes</h1>';
    echo '<table class="widefat">';
    echo '<thead><tr><th>#</th><th>Nom de l\'étape</th></tr></thead>';
    echo '<tbody>';
    $index = 1;
    foreach ($all_steps as $step) {
        echo '<tr><td>' . $index . '</td><td>' . esc_html($step) . '</td></tr>';
        $index++;
    }
    echo '</tbody></table>';

    echo '<h2>Ajouter une étape</h2>';
    echo '<form method="post">';
    echo '<input type="text" name="step_name" class="regular-text" required />';
    echo '<p class="submit"><input type="submit" name="bimbeau_ms_add_step" class="button button-primary" value="Ajouter" /></p>';
    echo '</form>';
    echo '</div>';
}

// Définition des options par défaut à l'activation
register_activation_hook(__FILE__, function() {
    add_option('bimbeau_ms_mode', 'PROD');
    add_option('bimbeau_ms_payment_link', 'https://buy.stripe.com/14k5mzfDf86f7U4cO6');
    add_option('bimbeau_ms_payment_link_test', 'https://buy.stripe.com/test_bIY2bbckteyjgbm4gg');
    add_option('bimbeau_ms_secret_key', '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');
    add_option('bimbeau_ms_admin_email', 'hello@secretdeco.fr');
    add_option('bimbeau_ms_menu_label', 'BimBeau MultiSteps');
    add_option('bimbeau_ms_menu_icon', 'dashicons-admin-generic');
});

