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

// Chargement du coeur du formulaire
require_once BIMBEAU_MS_DIR . 'includes/estimation-core.php';

// Création de la page d'options
add_action('admin_menu', function() {
    add_menu_page(
        'BimBeau MultiSteps',
        'BimBeau MultiSteps',
        'manage_options',
        'bimbeau-multisteps',
        'bimbeau_ms_options_page'
    );
});

function bimbeau_ms_options_page() {
    if (!current_user_can('manage_options')) {
        return;
    }
    if (isset($_POST['bimbeau_ms_save'])) {
        update_option('bimbeau_ms_mode', sanitize_text_field($_POST['mode']));
        update_option('bimbeau_ms_payment_link', sanitize_text_field($_POST['payment_link']));
        update_option('bimbeau_ms_secret_key', sanitize_text_field($_POST['secret_key']));
        update_option('bimbeau_ms_admin_email', sanitize_email($_POST['admin_email']));
        echo '<div class="updated"><p>Options enregistrées.</p></div>';
    }
    $mode = get_option('bimbeau_ms_mode', 'PROD');
    $payment = get_option('bimbeau_ms_payment_link', '');
    $secret = get_option('bimbeau_ms_secret_key', '');
    $admin = get_option('bimbeau_ms_admin_email', '');
    ?>
    <div class="wrap">
        <h1>BimBeau MultiSteps</h1>
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
                    <th scope="row"><label for="payment_link">Payment Link</label></th>
                    <td><input type="text" id="payment_link" name="payment_link" value="<?php echo esc_attr($payment); ?>" class="regular-text" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="secret_key">Secret Key</label></th>
                    <td><input type="text" id="secret_key" name="secret_key" value="<?php echo esc_attr($secret); ?>" class="regular-text" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="admin_email">Email admin</label></th>
                    <td><input type="email" id="admin_email" name="admin_email" value="<?php echo esc_attr($admin); ?>" class="regular-text" /></td>
                </tr>
            </table>
            <p class="submit"><input type="submit" name="bimbeau_ms_save" id="submit" class="button button-primary" value="Enregistrer" /></p>
        </form>
    </div>
    <?php
}

// Définition des options par défaut à l'activation
register_activation_hook(__FILE__, function() {
    add_option('bimbeau_ms_mode', 'PROD');
    add_option('bimbeau_ms_payment_link', 'https://buy.stripe.com/14k5mzfDf86f7U4cO6');
    add_option('bimbeau_ms_secret_key', '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');
    add_option('bimbeau_ms_admin_email', 'hello@secretdeco.fr');
});

