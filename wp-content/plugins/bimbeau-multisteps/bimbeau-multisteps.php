<?php
/*
Plugin Name: BimBeau MultiSteps
Description: Convertit le formulaire multi step multi-étapes en plugin administrable.
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

if (is_admin()) require_once BIMBEAU_MS_DIR . 'includes/admin/admin-settings.php';
// Enqueue front-end assets
function bimbeau_ms_enqueue_assets() {
    wp_enqueue_style(
        'bimbeau-ms-style',
        BIMBEAU_MS_URL . 'assets/css/multi_step-form.css',
        [],
        '1.0.0'
    );

    // Force an absolute path for the checkbox tick image
    wp_add_inline_style(
        'bimbeau-ms-style',
        '.multi_step_form_step div[data-elementor-type="section"].active .custom-input::before{' .
        'background-image:url("' . BIMBEAU_MS_URL . 'assets/img/check_form.svg");'
        .'}'
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
    require_once BIMBEAU_MS_DIR . 'includes/forms/multi_step-form.php';
}
add_action('init', 'bimbeau_ms_load_core');


// Définition des options par défaut à l'activation
register_activation_hook(__FILE__, function() {
    add_option('bimbeau_ms_mode', 'PROD');
    add_option('bimbeau_ms_payment_link', 'https://buy.stripe.com/14k5mzfDf86f7U4cO6');
    add_option('bimbeau_ms_payment_link_test', 'https://buy.stripe.com/test_bIY2bbckteyjgbm4gg');
    $prodKey = getenv('BIMBEAU_MS_SECRET_KEY') ?: '';
    $testKey = getenv('BIMBEAU_MS_SECRET_KEY_TEST') ?: '';
    add_option('bimbeau_ms_secret_key', $prodKey);
    add_option('bimbeau_ms_secret_key_test', $testKey);
    add_option('bimbeau_ms_admin_email', 'hello@secretdeco.fr');
    add_option('bimbeau_ms_menu_label', 'BimBeau MultiSteps');
    add_option('bimbeau_ms_menu_icon', 'dashicons-admin-generic');

    // Create steps table
    global $wpdb;
    $table_name = $wpdb->prefix . 'bimbeau_ms_steps';
    $charset_collate = $wpdb->get_charset_collate();
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    $sql = "CREATE TABLE {$table_name} (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        step_order int NOT NULL,
        step_key varchar(100) NOT NULL,
        label varchar(200) NOT NULL,
        question_type varchar(20) NOT NULL,
        choices text,
        PRIMARY KEY  (id)
    ) {$charset_collate};";
    dbDelta($sql);

    // Insert default steps if table empty
    if (!$wpdb->get_var("SELECT COUNT(*) FROM {$table_name}")) {
        $defaults = [
            [
                'profil',
                'Mon profil',
                'radio',
                json_encode([
                    'proprietaire'  => 'Propriétaire',
                    'compromis'     => 'En train de signer mon compromis',
                    'renseignements' => [
                        'label'  => 'Juste à la recherche de renseignements',
                        'extras' => [
                            [
                                'name'     => 'precisions',
                                'label'    => 'Précisez vos besoins',
                                'type'     => 'text',
                                'required' => false
                            ]
                        ]
                    ]
                ])
            ],
            ['projet', 'Mon projet', 'radio', json_encode(['maison' => 'Maison', 'appartement' => 'Appartement'])],
            ['accompagnement', 'Mon accompagnement', 'radio', json_encode(['renovation' => 'Rénovation', 'construction' => 'Construction'])],
            [
                'besoins',
                'Mes besoins',
                'checkbox',
                json_encode([
                    'restructurer' => 'Restructurer',
                    'decorer'      => [
                        'label'  => 'Décorer',
                        'extras' => [
                            [
                                'name'  => 'zones',
                                'label' => 'Zones à décorer',
                                'type'  => 'text'
                            ]
                        ]
                    ]
                ])
            ],
            ['coordonnees', 'Mes coordonnées', 'text', '']
        ];
        $order = 1;
        foreach ($defaults as $def) {
            $wpdb->insert($table_name, [
                'step_order' => $order++,
                'step_key' => $def[0],
                'label' => $def[1],
                'question_type' => $def[2],
                'choices' => $def[3]
            ]);
        }
    }

    // Default email templates
    add_option('bimbeau_ms_confirm_client_subject', '[Secret Déco] Confirmation de votre demande');
    add_option('bimbeau_ms_confirm_client_body', "<h2>Bonjour {prenom},</h2><p>Nous avons bien reçu votre demande. Voici un récapitulatif :</p>{details}<p>Nous reviendrons vers vous avant le {date}.</p>");
    add_option('bimbeau_ms_confirm_admin_subject', '[Secret Déco] Nouvelle demande de travaux à traiter pour le {date}');
    add_option('bimbeau_ms_confirm_admin_body', "<h2>Bonjour !</h2><p>Voici les détails de la demande :</p>{details}<p>Cette personne attend un retour pour le {date}. Vous recevrez un rappel 24h avant cette date.</p>");
    add_option('bimbeau_ms_reminder_admin_subject', '[Secret Déco] Rappel : demande de {prenom} {nom} pour le {date}');
    add_option('bimbeau_ms_reminder_admin_body', "<h2>Bonjour !</h2><p>Voici un rappel de la demande :</p>{details}<p>Cette personne attend un retour le {date}.</p>");

    // Options de délai pour le rappel
    add_option('bimbeau_ms_reminder_days_before', 1);
    add_option('bimbeau_ms_reminder_time', '10:00');
    // Enable delay selection step by default
    add_option('bimbeau_ms_enable_delay_step', 1);

    // Default interface labels
    add_option('bimbeau_ms_label_required', 'Ce champ est requis.');
    add_option('bimbeau_ms_label_select_option', 'Veuillez sélectionner au moins une option.');
    add_option('bimbeau_ms_label_continue', 'Continuer');
    add_option('bimbeau_ms_label_unknown_step', 'Étape inconnue.');

    // Capability for email management (editor and above)
    $role = get_role('editor');
    if ($role && !$role->has_cap('bimbeau_ms_manage_emails')) {
        $role->add_cap('bimbeau_ms_manage_emails');
    }
    $role = get_role('shop_manager');
    if ($role && !$role->has_cap('bimbeau_ms_manage_emails')) {
        $role->add_cap('bimbeau_ms_manage_emails');
    }
    $role = get_role('administrator');
    if ($role && !$role->has_cap('bimbeau_ms_manage_emails')) {
        $role->add_cap('bimbeau_ms_manage_emails');
    }

    // Capability for advanced settings (administrator only)
    if ($role && !$role->has_cap('bimbeau_ms_manage_advanced')) {
        $role->add_cap('bimbeau_ms_manage_advanced');
    }
});

function bimbeau_ms_elementor_missing_notice_main() {
    echo '<div class="notice notice-error"><p>' .
        'BimBeau MultiSteps requiert le plugin Elementor pour fonctionner.' .
        '</p></div>';
}

add_action('admin_init', function () {
    if (!class_exists('\\Elementor\\Plugin')) {
        add_action('admin_notices', 'bimbeau_ms_elementor_missing_notice_main');
    }
});

