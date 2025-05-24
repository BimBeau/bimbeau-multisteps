<?php
if (!defined('ABSPATH')) {
    exit;
}

function bimbeau_ms_register_admin_menu() {
    $label = get_option('bimbeau_ms_menu_label', 'BimBeau MultiSteps');
    $icon  = get_option('bimbeau_ms_menu_icon', 'dashicons-admin-generic');

    add_menu_page(
        'Dashboard',
        $label,
        'edit_others_posts',
        'bimbeau-ms-dashboard',
        'bimbeau_ms_dashboard_page',
        $icon
    );

    add_submenu_page(
        'bimbeau-ms-dashboard',
        'Dashboard',
        'Dashboard',
        'edit_others_posts',
        'bimbeau-ms-dashboard',
        'bimbeau_ms_dashboard_page'
    );

    add_submenu_page(
        'bimbeau-ms-dashboard',
        'Emails',
        'Emails',
        'edit_others_posts',
        'bimbeau-ms-emails',
        'bimbeau_ms_email_page'
    );

    add_submenu_page(
        'bimbeau-ms-dashboard',
        'Réglages avancés',
        'Réglages avancés',
        'manage_options',
        'bimbeau-ms-settings',
        'bimbeau_ms_options_page'
    );

    add_submenu_page(
        'bimbeau-ms-dashboard',
        'Gérer les étapes',
        'Gérer les étapes',
        'edit_others_posts',
        'bimbeau-ms-steps',
        'bimbeau_ms_steps_page'
    );

    add_submenu_page(
        'bimbeau-ms-dashboard',
        'Messages personnalisés',
        'Messages personnalisés',
        'edit_others_posts',
        'bimbeau-ms-labels',
        'bimbeau_ms_labels_page'
    );
}

add_action('admin_menu', 'bimbeau_ms_register_admin_menu');

/**
 * Display navigation tabs across all plugin pages.
 */
function bimbeau_ms_admin_tabs($current) {
    $tabs = [
        'bimbeau-ms-dashboard' => [ 'label' => 'Dashboard',          'cap' => 'edit_others_posts' ],
        'bimbeau-ms-emails'    => [ 'label' => 'Emails',             'cap' => 'edit_others_posts' ],
        'bimbeau-ms-settings'  => [ 'label' => 'Réglages avancés',    'cap' => 'manage_options' ],
        'bimbeau-ms-steps'     => [ 'label' => 'Gérer les étapes',    'cap' => 'edit_others_posts' ],
        'bimbeau-ms-labels'    => [ 'label' => 'Messages personnalisés', 'cap' => 'edit_others_posts' ],
    ];

    $data = [];
    echo '<h2 id="bimbeau-ms-admin-tabs-fallback" class="nav-tab-wrapper">';
    foreach ($tabs as $slug => $tab) {
        if ( ! current_user_can( $tab['cap'] ) ) {
            continue;
        }
        $label = $tab['label'];
        $class = 'nav-tab' . ( $current === $slug ? ' nav-tab-active' : '' );
        $url   = admin_url( 'admin.php?page=' . $slug );
        echo '<a href="' . esc_url( $url ) . '" class="' . esc_attr( $class ) . '">' . esc_html( $label ) . '</a>';
        $data[] = [
            'slug'  => $slug,
            'label' => $label,
            'url'   => $url,
        ];
    }
    echo '</h2>';
    echo '<div id="bimbeau-ms-admin-tabs" data-current="' . esc_attr($current) . '" data-tabs="' . esc_attr(wp_json_encode($data)) . '"></div>';
}

/**
 * Enqueue the React settings application on the options page.
 */
function bimbeau_ms_enqueue_settings_app() {
    wp_enqueue_script(
        'bimbeau-ms-settings-app',
        BIMBEAU_MS_URL . 'assets/js/settings-app.js',
        [ 'wp-element', 'wp-components', 'wp-api-fetch' ],
        '1.0.0',
        true
    );
    wp_enqueue_style( 'wp-components' );
}

/**

 * Enqueue the React steps application on the steps page.
 */
function bimbeau_ms_enqueue_steps_app() {
    wp_enqueue_script(
        'bimbeau-ms-steps-app',
        BIMBEAU_MS_URL . 'assets/js/steps-app.js',
        [ 'wp-element', 'wp-components', 'wp-api-fetch' ],
        '1.0.0',
        true
    );
    wp_enqueue_style( 'wp-components' );
}

/**
 * Enqueue the React labels application on the labels page.
 */
function bimbeau_ms_enqueue_labels_app() {
    wp_enqueue_script(
        'bimbeau-ms-labels-app',
        BIMBEAU_MS_URL . 'assets/js/labels-app.js',
        [ 'wp-element', 'wp-components', 'wp-api-fetch' ],
        '1.0.0',
        true
    );
    wp_enqueue_style( 'wp-components' );
}
add_action( 'admin_enqueue_scripts', function( $hook ) {
    // Apply the wp-components style and admin tabs script to every plugin page
    if ( strpos( $hook, 'bimbeau-ms-' ) !== false ) {
        wp_enqueue_style( 'wp-components' );
        wp_enqueue_script(
            'bimbeau-ms-admin-tabs',
            BIMBEAU_MS_URL . 'assets/js/admin-tabs.js',
            [ 'wp-element', 'wp-components' ],
            '1.0.0',
            true
        );
    }


    // Load the React settings application only on the advanced settings page
    if ( strpos( $hook, 'bimbeau-ms-settings' ) !== false ) {
        bimbeau_ms_enqueue_settings_app();
    }
    // Load the React steps application only on the steps page
    if ( strpos( $hook, 'bimbeau-ms-steps' ) !== false ) {
        bimbeau_ms_enqueue_steps_app();
    }
    // Load the React labels application only on the labels page
    if ( strpos( $hook, 'bimbeau-ms-labels' ) !== false ) {
        bimbeau_ms_enqueue_labels_app();
    }
} );

/**
 * Register REST routes used by the React settings page.
 */
function bimbeau_ms_register_rest_routes() {
    register_rest_route( 'bimbeau-ms/v1', '/options', [
        'methods'  => 'GET',
        'callback' => function() {
            return [
                'mode'              => get_option( 'bimbeau_ms_mode', 'PROD' ),
                'payment_link_prod' => get_option( 'bimbeau_ms_payment_link', '' ),
                'payment_link_test' => get_option( 'bimbeau_ms_payment_link_test', '' ),
                'admin_email'       => get_option( 'bimbeau_ms_admin_email', '' ),
                'enable_delay_step' => (bool) get_option( 'bimbeau_ms_enable_delay_step', 1 ),
                'menu_label'        => get_option( 'bimbeau_ms_menu_label', 'BimBeau MultiSteps' ),
                'menu_icon'         => get_option( 'bimbeau_ms_menu_icon', 'dashicons-admin-generic' ),
            ];
        },
        'permission_callback' => function() {
            return current_user_can( 'manage_options' );
        },
    ] );

    register_rest_route( 'bimbeau-ms/v1', '/options', [
        'methods'  => 'POST',
        'callback' => function( WP_REST_Request $request ) {
            $data = $request->get_json_params();
            if ( isset( $data['mode'] ) ) {
                update_option( 'bimbeau_ms_mode', sanitize_text_field( $data['mode'] ) );
            }
            if ( isset( $data['payment_link_prod'] ) ) {
                update_option( 'bimbeau_ms_payment_link', sanitize_text_field( $data['payment_link_prod'] ) );
            }
            if ( isset( $data['payment_link_test'] ) ) {
                update_option( 'bimbeau_ms_payment_link_test', sanitize_text_field( $data['payment_link_test'] ) );
            }
            if ( isset( $data['admin_email'] ) ) {
                update_option( 'bimbeau_ms_admin_email', sanitize_email( $data['admin_email'] ) );
            }
            if ( isset( $data['enable_delay_step'] ) ) {
                update_option( 'bimbeau_ms_enable_delay_step', $data['enable_delay_step'] ? 1 : 0 );
            }
            if ( isset( $data['menu_label'] ) ) {
                update_option( 'bimbeau_ms_menu_label', sanitize_text_field( $data['menu_label'] ) );
            }
            if ( isset( $data['menu_icon'] ) ) {
                update_option( 'bimbeau_ms_menu_icon', sanitize_text_field( $data['menu_icon'] ) );
            }
            return [ 'success' => true ];
        },
        'permission_callback' => function() {
            return current_user_can( 'manage_options' );
        },
    ] );

    // Routes for the React steps page
    register_rest_route( 'bimbeau-ms/v1', '/steps', [
        'methods'  => 'GET',
        'callback' => function() {
            global $wpdb;
            $table = $wpdb->prefix . 'bimbeau_ms_steps';
            return $wpdb->get_results( "SELECT * FROM {$table} ORDER BY step_order ASC", ARRAY_A );
        },
        'permission_callback' => function() {
            return current_user_can( 'edit_others_posts' );
        },
    ] );

    register_rest_route( 'bimbeau-ms/v1', '/steps', [
        'methods'  => 'POST',
        'callback' => function( WP_REST_Request $request ) {
            global $wpdb;
            $table = $wpdb->prefix . 'bimbeau_ms_steps';
            $data = $request->get_json_params();
            if ( isset( $data['action'] ) && 'create' === $data['action'] ) {
                $order = (int) $wpdb->get_var( "SELECT MAX(step_order) FROM {$table}" ) + 1;
                $wpdb->insert( $table, [
                    'step_order'    => $order,
                    'step_key'      => sanitize_title( $data['label'] ),
                    'label'         => sanitize_text_field( $data['label'] ),
                    'question_type' => sanitize_text_field( $data['question_type'] ),
                    'choices'       => isset( $data['choices'] ) ? sanitize_textarea_field( $data['choices'] ) : ''
                ] );
                return [ 'success' => true ];
            }
            if ( isset( $data['action'] ) && 'delete' === $data['action'] && isset( $data['id'] ) ) {
                $wpdb->delete( $table, [ 'id' => intval( $data['id'] ) ] );
                return [ 'success' => true ];
            }
            if ( isset( $data['action'] ) && 'update_order' === $data['action'] && ! empty( $data['order'] ) ) {
                $ids = array_map( 'intval', (array) $data['order'] );
                $pos = 1;
                foreach ( $ids as $id ) {
                    $wpdb->update( $table, [ 'step_order' => $pos++ ], [ 'id' => $id ] );
                }
                return [ 'success' => true ];
            }
            return new WP_Error( 'invalid_request', 'Invalid request', [ 'status' => 400 ] );
        },
        'permission_callback' => function() {
            return current_user_can( 'edit_others_posts' );
        },
    ] );

    // Routes for the React labels page
    register_rest_route( 'bimbeau-ms/v1', '/labels', [
        'methods'  => 'GET',
        'callback' => function() {
            return [
                'label_required'        => get_option( 'bimbeau_ms_label_required', 'Ce champ est requis.' ),
                'label_select_option'   => get_option( 'bimbeau_ms_label_select_option', 'Veuillez sélectionner au moins une option.' ),
                'label_continue'        => get_option( 'bimbeau_ms_label_continue', 'Continuer' ),
                'label_unknown_step'    => get_option( 'bimbeau_ms_label_unknown_step', 'Étape inconnue.' ),
                'msg_saved'             => get_option( 'bimbeau_ms_msg_saved', 'Options enregistrées.' ),
                'msg_elementor_missing' => get_option( 'bimbeau_ms_msg_elementor_missing', 'BimBeau MultiSteps requiert le plugin Elementor pour fonctionner.' ),
                'msg_reminder_disabled' => get_option( 'bimbeau_ms_msg_reminder_disabled', 'La fonctionnalité de rappel est désactivée.' ),
                'msg_dashboard_welcome' => get_option( 'bimbeau_ms_msg_dashboard_welcome', 'Bienvenue dans le tableau de bord du plugin.' ),
            ];
        },
        'permission_callback' => function() {
            return current_user_can( 'edit_others_posts' );
        },
    ] );

    register_rest_route( 'bimbeau-ms/v1', '/labels', [
        'methods'  => 'POST',
        'callback' => function( WP_REST_Request $request ) {
            $data = $request->get_json_params();
            if ( isset( $data['label_required'] ) ) {
                update_option( 'bimbeau_ms_label_required', sanitize_text_field( $data['label_required'] ) );
            }
            if ( isset( $data['label_select_option'] ) ) {
                update_option( 'bimbeau_ms_label_select_option', sanitize_text_field( $data['label_select_option'] ) );
            }
            if ( isset( $data['label_continue'] ) ) {
                update_option( 'bimbeau_ms_label_continue', sanitize_text_field( $data['label_continue'] ) );
            }
            if ( isset( $data['label_unknown_step'] ) ) {
                update_option( 'bimbeau_ms_label_unknown_step', sanitize_text_field( $data['label_unknown_step'] ) );
            }
            if ( isset( $data['msg_saved'] ) ) {
                update_option( 'bimbeau_ms_msg_saved', sanitize_text_field( $data['msg_saved'] ) );
            }
            if ( isset( $data['msg_elementor_missing'] ) ) {
                update_option( 'bimbeau_ms_msg_elementor_missing', sanitize_text_field( $data['msg_elementor_missing'] ) );
            }
            if ( isset( $data['msg_reminder_disabled'] ) ) {
                update_option( 'bimbeau_ms_msg_reminder_disabled', sanitize_text_field( $data['msg_reminder_disabled'] ) );
            }
            if ( isset( $data['msg_dashboard_welcome'] ) ) {
                update_option( 'bimbeau_ms_msg_dashboard_welcome', sanitize_text_field( $data['msg_dashboard_welcome'] ) );
            }
            return [ 'success' => true ];
        },
        'permission_callback' => function() {
            return current_user_can( 'edit_others_posts' );
        },
    ] );
}
add_action( 'rest_api_init', 'bimbeau_ms_register_rest_routes' );

function bimbeau_ms_dashboard_page() {
    if (!current_user_can('edit_others_posts')) {
        return;
    }
    echo '<div class="wrap">';
    echo '<h1>Tableau de bord</h1>';
    bimbeau_ms_admin_tabs('bimbeau-ms-dashboard');
    echo '<p>' . esc_html(get_option(
        'bimbeau_ms_msg_dashboard_welcome',
        'Bienvenue dans le tableau de bord du plugin.'
    )) . '</p>';
    echo '</div>';
}

function bimbeau_ms_options_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    bimbeau_ms_enqueue_settings_app();

    echo '<div class="wrap">';
    echo '<h1>Réglages avancés</h1>';
    bimbeau_ms_admin_tabs('bimbeau-ms-settings');
    echo '<div id="bimbeau-ms-settings-app"></div>';
    echo '</div>';
}

function bimbeau_ms_steps_page() {
    if (!current_user_can('edit_others_posts')) {
        return;
    }

    bimbeau_ms_enqueue_steps_app();

    echo '<div class="wrap">';
    echo '<h1>Gestion des étapes</h1>';
    bimbeau_ms_admin_tabs('bimbeau-ms-steps');
    echo '<div id="bimbeau-ms-steps-app"></div>';
    echo '</div>';
}

function bimbeau_ms_email_page() {
    if (!current_user_can('edit_others_posts')) {
        return;
    }

    if (isset($_POST['bimbeau_ms_save_emails'])) {
        if (isset($_POST['confirm_client_subject'])) {
            update_option('bimbeau_ms_confirm_client_subject', wp_kses_post(wp_unslash($_POST['confirm_client_subject'])));
        }
        if (isset($_POST['confirm_client_body'])) {
            update_option('bimbeau_ms_confirm_client_body', wp_kses_post(wp_unslash($_POST['confirm_client_body'])));
        }
        if (isset($_POST['confirm_admin_subject'])) {
            update_option('bimbeau_ms_confirm_admin_subject', wp_kses_post(wp_unslash($_POST['confirm_admin_subject'])));
        }
        if (isset($_POST['confirm_admin_body'])) {
            update_option('bimbeau_ms_confirm_admin_body', wp_kses_post(wp_unslash($_POST['confirm_admin_body'])));
        }
        if (isset($_POST['reminder_admin_subject'])) {
            update_option('bimbeau_ms_reminder_admin_subject', wp_kses_post(wp_unslash($_POST['reminder_admin_subject'])));
        }
        if (isset($_POST['reminder_admin_body'])) {
            update_option('bimbeau_ms_reminder_admin_body', wp_kses_post(wp_unslash($_POST['reminder_admin_body'])));
        }
        if (isset($_POST['reminder_days_before'])) {
            update_option('bimbeau_ms_reminder_days_before', intval(wp_unslash($_POST['reminder_days_before'])));
        }
        if (isset($_POST['reminder_time'])) {
            update_option('bimbeau_ms_reminder_time', sanitize_text_field(wp_unslash($_POST['reminder_time'])));
        }
        echo '<div class="updated"><p>' . esc_html(get_option(
            'bimbeau_ms_msg_saved',
            'Options enregistrées.'
        )) . '</p></div>';
    }

    $confirmClientSubject  = get_option('bimbeau_ms_confirm_client_subject');
    $confirmClientBody     = get_option('bimbeau_ms_confirm_client_body');
    $confirmAdminSubject   = get_option('bimbeau_ms_confirm_admin_subject');
    $confirmAdminBody      = get_option('bimbeau_ms_confirm_admin_body');
    $reminderAdminSubject  = get_option('bimbeau_ms_reminder_admin_subject');
    $reminderAdminBody     = get_option('bimbeau_ms_reminder_admin_body');
    $reminderDays          = get_option('bimbeau_ms_reminder_days_before', 1);
    $reminderTime          = get_option('bimbeau_ms_reminder_time', '10:00');
    $enable_delay          = get_option('bimbeau_ms_enable_delay_step', 1);

    $active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'confirmation';
    if (!in_array($active_tab, ['confirmation', 'rappel'], true)) {
        $active_tab = 'confirmation';
    }
    if (!$enable_delay && $active_tab === 'rappel') {
        $active_tab = 'confirmation';
    }

    ?>
        <div class="wrap">
            <h1>Emails</h1>
            <?php bimbeau_ms_admin_tabs('bimbeau-ms-emails'); ?>
            <p>Utilisez les raccourcis {prenom}, {nom}, {date} et {details} pour inserer les valeurs correspondantes.</p>

            <?php
            $tabs_data = [ [ 'slug' => 'confirmation', 'title' => 'Confirmation' ] ];
            if ( $enable_delay ) {
                $tabs_data[] = [ 'slug' => 'rappel', 'title' => 'Rappel' ];
            }
            ?>
            <div id="bimbeau-ms-email-tabs" data-current="<?php echo esc_attr( $active_tab ); ?>" data-tabs="<?php echo esc_attr( wp_json_encode( $tabs_data ) ); ?>">
                <div class="tab-panel-container"></div>
                <form method="post">
                    <div class="email-tab" data-slug="confirmation">
                        <h2>Confirmation Client</h2>
                        <table class="form-table" role="presentation">
                            <tr>
                                <th scope="row"><label for="confirm_client_subject">Sujet</label></th>
                                <td><input type="text" id="confirm_client_subject" name="confirm_client_subject" value="<?php echo esc_attr($confirmClientSubject); ?>" class="regular-text" style="width:40em;" /></td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="confirm_client_body">Corps</label></th>
                                <td><?php wp_editor($confirmClientBody, 'confirm_client_body_editor', ['textarea_name' => 'confirm_client_body']); ?></td>
                            </tr>
                        </table>

                        <h2>Confirmation Admin</h2>
                        <table class="form-table" role="presentation">
                            <tr>
                                <th scope="row"><label for="confirm_admin_subject">Sujet</label></th>
                                <td><input type="text" id="confirm_admin_subject" name="confirm_admin_subject" value="<?php echo esc_attr($confirmAdminSubject); ?>" class="regular-text" style="width:40em;" /></td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="confirm_admin_body">Corps</label></th>
                                <td><?php wp_editor($confirmAdminBody, 'confirm_admin_body_editor', ['textarea_name' => 'confirm_admin_body']); ?></td>
                            </tr>
                        </table>
                    </div>
                    <?php if ( $enable_delay ) : ?>
                    <div class="email-tab" data-slug="rappel">
                        <h2>Rappel Admin</h2>
                        <table class="form-table" role="presentation">
                            <tr>
                                <th scope="row"><label for="reminder_admin_subject">Sujet</label></th>
                                <td><input type="text" id="reminder_admin_subject" name="reminder_admin_subject" value="<?php echo esc_attr($reminderAdminSubject); ?>" class="regular-text" style="width:40em;" /></td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="reminder_admin_body">Corps</label></th>
                                <td><?php wp_editor($reminderAdminBody, 'reminder_admin_body_editor', ['textarea_name' => 'reminder_admin_body']); ?></td>
                            </tr>
                        </table>

                        <h2>Programmation du rappel</h2>
                        <table class="form-table" role="presentation">
                            <tr>
                                <th scope="row"><label for="reminder_days_before">Jours avant la date de réponse</label></th>
                                <td><input type="number" id="reminder_days_before" name="reminder_days_before" min="0" value="<?php echo esc_attr($reminderDays); ?>" class="small-text" /></td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="reminder_time">Heure d'envoi</label></th>
                                <td><input type="time" id="reminder_time" name="reminder_time" value="<?php echo esc_attr($reminderTime); ?>" /></td>
                            </tr>
                        </table>
                    </div>
                    <?php endif; ?>

                    <p class="submit"><input type="submit" name="bimbeau_ms_save_emails" class="button button-primary" value="Enregistrer" /></p>
                </form>
            </div>
        </div>
    <?php
}

function bimbeau_ms_labels_page() {
    if ( ! current_user_can( 'edit_others_posts' ) ) {
        return;
    }

    bimbeau_ms_enqueue_labels_app();

    echo '<div class="wrap">';
    echo '<h1>Messages personnalisés</h1>';
    bimbeau_ms_admin_tabs( 'bimbeau-ms-labels' );
    echo '<div id="bimbeau-ms-labels-app"></div>';
    echo '</div>';
}
