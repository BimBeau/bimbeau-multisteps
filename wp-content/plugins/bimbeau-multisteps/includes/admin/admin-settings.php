<?php
if (!defined('ABSPATH')) {
    exit;
}

function bimbeau_ms_register_admin_menu() {
    $label = get_option('bimbeau_ms_menu_label', 'BimBeau MultiSteps');
    $icon  = get_option('bimbeau_ms_menu_icon', 'dashicons-admin-generic');
    add_menu_page(
        'Emails',
        $label,
        'bimbeau_ms_manage_emails',
        'bimbeau-ms-emails',
        'bimbeau_ms_email_page',
        $icon
    );

    add_submenu_page(
        'bimbeau-ms-emails',
        'Emails',
        'Emails',
        'bimbeau_ms_manage_emails',
        'bimbeau-ms-emails',
        'bimbeau_ms_email_page'
    );

    add_submenu_page(
        'bimbeau-ms-emails',
        'Réglages',
        'Réglages',
        'manage_options',
        'bimbeau-ms-settings',
        'bimbeau_ms_options_page'
    );

    add_submenu_page(
        'bimbeau-ms-emails',
        'Gérer les étapes',
        'Gérer les étapes',
        'manage_options',
        'bimbeau-ms-steps',
        'bimbeau_ms_steps_page'
    );
}
add_action('admin_menu', 'bimbeau_ms_register_admin_menu');

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
        update_option('bimbeau_ms_secret_key_test', sanitize_text_field($_POST['secret_key_test']));
        update_option('bimbeau_ms_admin_email', sanitize_email($_POST['admin_email']));
        update_option('bimbeau_ms_menu_label', sanitize_text_field($_POST['menu_label']));
        update_option('bimbeau_ms_menu_icon', sanitize_text_field($_POST['menu_icon']));
        echo '<div class="updated"><p>Options enregistrées.</p></div>';
    }
    $mode = get_option('bimbeau_ms_mode', 'PROD');
    $payment_prod = get_option('bimbeau_ms_payment_link', '');
    $payment_test = get_option('bimbeau_ms_payment_link_test', '');
    $secret       = get_option('bimbeau_ms_secret_key', '');
    $secret_test  = get_option('bimbeau_ms_secret_key_test', '');
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
                    <th scope="row"><label for="secret_key">Secret Key PROD</label></th>
                    <td><input type="text" id="secret_key" name="secret_key" value="<?php echo esc_attr($secret); ?>" class="regular-text" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="secret_key_test">Secret Key TEST</label></th>
                    <td><input type="text" id="secret_key_test" name="secret_key_test" value="<?php echo esc_attr($secret_test); ?>" class="regular-text" /></td>
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

function bimbeau_ms_email_page() {
    if (!current_user_can('bimbeau_ms_manage_emails')) {
        return;
    }

    if (isset($_POST['bimbeau_ms_save_emails'])) {
        update_option('bimbeau_ms_confirm_client_subject', wp_kses_post($_POST['confirm_client_subject']));
        update_option('bimbeau_ms_confirm_client_body', wp_kses_post($_POST['confirm_client_body']));
        update_option('bimbeau_ms_confirm_admin_subject', wp_kses_post($_POST['confirm_admin_subject']));
        update_option('bimbeau_ms_confirm_admin_body', wp_kses_post($_POST['confirm_admin_body']));
        update_option('bimbeau_ms_reminder_client_subject', wp_kses_post($_POST['reminder_client_subject']));
        update_option('bimbeau_ms_reminder_client_body', wp_kses_post($_POST['reminder_client_body']));
        update_option('bimbeau_ms_reminder_admin_subject', wp_kses_post($_POST['reminder_admin_subject']));
        update_option('bimbeau_ms_reminder_admin_body', wp_kses_post($_POST['reminder_admin_body']));
        echo '<div class="updated"><p>Options enregistrées.</p></div>';
    }

    $confirmClientSubject  = get_option('bimbeau_ms_confirm_client_subject');
    $confirmClientBody     = get_option('bimbeau_ms_confirm_client_body');
    $confirmAdminSubject   = get_option('bimbeau_ms_confirm_admin_subject');
    $confirmAdminBody      = get_option('bimbeau_ms_confirm_admin_body');
    $reminderClientSubject = get_option('bimbeau_ms_reminder_client_subject');
    $reminderClientBody    = get_option('bimbeau_ms_reminder_client_body');
    $reminderAdminSubject  = get_option('bimbeau_ms_reminder_admin_subject');
    $reminderAdminBody     = get_option('bimbeau_ms_reminder_admin_body');

    ?>
    <div class="wrap">
        <h1>Emails</h1>
        <p>Utilisez les raccourcis {prenom}, {nom}, {date} et {details} pour insérer les valeurs correspondantes.</p>
        <form method="post">
            <h2>Confirmation Client</h2>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="confirm_client_subject">Sujet</label></th>
                    <td><input type="text" id="confirm_client_subject" name="confirm_client_subject" value="<?php echo esc_attr($confirmClientSubject); ?>" class="regular-text" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="confirm_client_body">Corps</label></th>
                    <td><textarea id="confirm_client_body" name="confirm_client_body" class="large-text" rows="5"><?php echo esc_textarea($confirmClientBody); ?></textarea></td>
                </tr>
            </table>

            <h2>Confirmation Admin</h2>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="confirm_admin_subject">Sujet</label></th>
                    <td><input type="text" id="confirm_admin_subject" name="confirm_admin_subject" value="<?php echo esc_attr($confirmAdminSubject); ?>" class="regular-text" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="confirm_admin_body">Corps</label></th>
                    <td><textarea id="confirm_admin_body" name="confirm_admin_body" class="large-text" rows="5"><?php echo esc_textarea($confirmAdminBody); ?></textarea></td>
                </tr>
            </table>

            <h2>Rappel Client</h2>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="reminder_client_subject">Sujet</label></th>
                    <td><input type="text" id="reminder_client_subject" name="reminder_client_subject" value="<?php echo esc_attr($reminderClientSubject); ?>" class="regular-text" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="reminder_client_body">Corps</label></th>
                    <td><textarea id="reminder_client_body" name="reminder_client_body" class="large-text" rows="5"><?php echo esc_textarea($reminderClientBody); ?></textarea></td>
                </tr>
            </table>

            <h2>Rappel Admin</h2>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="reminder_admin_subject">Sujet</label></th>
                    <td><input type="text" id="reminder_admin_subject" name="reminder_admin_subject" value="<?php echo esc_attr($reminderAdminSubject); ?>" class="regular-text" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="reminder_admin_body">Corps</label></th>
                    <td><textarea id="reminder_admin_body" name="reminder_admin_body" class="large-text" rows="5"><?php echo esc_textarea($reminderAdminBody); ?></textarea></td>
                </tr>
            </table>

            <p class="submit"><input type="submit" name="bimbeau_ms_save_emails" class="button button-primary" value="Enregistrer" /></p>
        </form>
    </div>
    <?php
}
