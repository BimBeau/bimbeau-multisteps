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
        'Réglages avancés',
        'Réglages avancés',
        'bimbeau_ms_manage_advanced',
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

    add_submenu_page(
        'bimbeau-ms-emails',
        'Messages personnalisés',
        'Messages personnalisés',
        'bimbeau_ms_manage_emails',
        'bimbeau-ms-labels',
        'bimbeau_ms_labels_page'
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
    if (!current_user_can('bimbeau_ms_manage_advanced')) {
        return;
    }
    if (isset($_POST['bimbeau_ms_save'])) {
        update_option('bimbeau_ms_mode', sanitize_text_field(wp_unslash($_POST['mode'])));
        update_option('bimbeau_ms_payment_link', sanitize_text_field(wp_unslash($_POST['payment_link_prod'])));
        update_option('bimbeau_ms_payment_link_test', sanitize_text_field(wp_unslash($_POST['payment_link_test'])));
        update_option('bimbeau_ms_secret_key', sanitize_text_field(wp_unslash($_POST['secret_key'])));
        update_option('bimbeau_ms_secret_key_test', sanitize_text_field(wp_unslash($_POST['secret_key_test'])));
        update_option('bimbeau_ms_admin_email', sanitize_email(wp_unslash($_POST['admin_email'])));
        update_option('bimbeau_ms_menu_label', sanitize_text_field(wp_unslash($_POST['menu_label'])));
        update_option('bimbeau_ms_menu_icon', sanitize_text_field(wp_unslash($_POST['menu_icon'])));
        if (isset($_POST['recaptcha_key'])) {
            update_option('bimbeau_ms_recaptcha_key', sanitize_text_field(wp_unslash($_POST['recaptcha_key'])));
        }
        update_option('bimbeau_ms_enable_delay_step', isset($_POST['enable_delay_step']) ? 1 : 0);
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
    $recaptcha   = get_option('bimbeau_ms_recaptcha_key', '');
    $enable_delay = get_option('bimbeau_ms_enable_delay_step', 1);
    ?>
    <div class="wrap">
        <h1>Réglages avancés</h1>
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
                    <th scope="row"><label for="recaptcha_key">reCAPTCHA Site Key</label></th>
                    <td><input type="text" id="recaptcha_key" name="recaptcha_key" value="<?php echo esc_attr($recaptcha); ?>" class="regular-text" /></td>
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
                <tr>
                    <th scope="row">Activer le choix du délai de réponse</th>
                    <td><input type="checkbox" id="enable_delay_step" name="enable_delay_step" value="1" <?php checked($enable_delay, 1); ?> /></td>
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

    global $wpdb;
    $table = $wpdb->prefix . 'bimbeau_ms_steps';

    // Handle add step
    if (isset($_POST['add_step'])) {
        $label = sanitize_text_field(wp_unslash($_POST['step_label']));
        $type  = sanitize_text_field(wp_unslash($_POST['question_type']));
        $choices = isset($_POST['choices']) ? sanitize_textarea_field(wp_unslash($_POST['choices'])) : '';
        $order = (int)$wpdb->get_var("SELECT MAX(step_order) FROM {$table}") + 1;
        $wpdb->insert($table, [
            'step_order' => $order,
            'step_key' => sanitize_title($label),
            'label' => $label,
            'question_type' => $type,
            'choices' => $choices
        ]);
    }

    // Handle delete
    if (isset($_POST['delete_step'])) {
        $wpdb->delete($table, ['id' => intval($_POST['delete_step'])]);
    }

    // Handle order save
    if (isset($_POST['save_order']) && isset($_POST['order'])) {
        $order_raw = sanitize_text_field(wp_unslash($_POST['order']));
        $ids = array_filter(array_map('intval', explode(',', $order_raw)));
        $pos = 1;
        foreach ($ids as $id) {
            $wpdb->update($table, ['step_order' => $pos++], ['id' => $id]);
        }
    }

    $steps = $wpdb->get_results("SELECT * FROM {$table} ORDER BY step_order ASC");

    wp_enqueue_script('jquery-ui-sortable');

    echo '<div class="wrap">';
    echo '<h1>Gestion des étapes</h1>';
    echo '<form method="post" id="order-form">';
    echo '<input type="hidden" name="order" id="step-order" value="">';
    echo '<table class="wp-list-table widefat fixed striped"><thead><tr><th></th><th>Étape</th><th>Type</th><th>Actions</th></tr></thead><tbody id="steps-sortable">';
    foreach ($steps as $step) {
        echo '<tr data-id="' . esc_attr($step->id) . '">';
        echo '<td class="handle">&#9776;</td>';
        echo '<td>' . esc_html($step->label) . '</td>';
        echo '<td>' . esc_html($step->question_type) . '</td>';
        echo '<td><button type="submit" name="delete_step" value="' . esc_attr($step->id) . '" class="button-link-delete">Supprimer</button></td>';
        echo '</tr>';
    }
    echo '</tbody></table>';
    echo '<p><button type="submit" name="save_order" class="button button-primary">Enregistrer l\'ordre</button></p>';
    echo '</form>';

    echo '<h2>Ajouter une étape</h2>';
    echo '<form method="post">';
    echo '<p><input type="text" name="step_label" class="regular-text" placeholder="Label" required></p>';
    echo '<p><select name="question_type"><option value="text">Texte</option><option value="radio">Radio</option><option value="checkbox">Checkbox</option></select></p>';
    echo '<p><textarea name="choices" class="large-text" placeholder="option:value, ..."></textarea></p>';
    echo '<p class="submit"><input type="submit" name="add_step" class="button button-primary" value="Ajouter"></p>';
    echo '</form>';
    echo '</div>';

    ?>
    <script>
    jQuery(function($){
        $('#steps-sortable').sortable({
            handle: '.handle',
            update: function(){
                var order = $('#steps-sortable').sortable('toArray',{attribute:'data-id'});
                $('#step-order').val(order.join(','));
            }
        });
    });
    </script>
    <?php
}

function bimbeau_ms_email_page() {
    if (!current_user_can('bimbeau_ms_manage_emails')) {
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
        echo '<div class="updated"><p>Options enregistrées.</p></div>';
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
        <p>Utilisez les raccourcis {prenom}, {nom}, {date} et {details} pour insérer les valeurs correspondantes.</p>
        <h2 class="nav-tab-wrapper">
            <a href="?page=bimbeau-ms-emails&tab=confirmation" class="nav-tab <?php echo $active_tab == 'confirmation' ? 'nav-tab-active' : ''; ?>">Confirmation</a>
            <?php if ($enable_delay) : ?>
                <a href="?page=bimbeau-ms-emails&tab=rappel" class="nav-tab <?php echo $active_tab == 'rappel' ? 'nav-tab-active' : ''; ?>">Rappel</a>
            <?php endif; ?>
        </h2>
        <form method="post">
            <?php if ($active_tab === 'confirmation') : ?>
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
            <?php elseif ($active_tab === 'rappel') : ?>
                <?php if ($enable_delay) : ?>
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
                <?php else : ?>
                    <p>La fonctionnalité de rappel est désactivée.</p>
                <?php endif; ?>
            <?php endif; ?>

            <p class="submit"><input type="submit" name="bimbeau_ms_save_emails" class="button button-primary" value="Enregistrer" /></p>
        </form>
    </div>
    <?php
}

function bimbeau_ms_labels_page() {
    if (!current_user_can('bimbeau_ms_manage_emails')) {
        return;
    }

    if (isset($_POST['bimbeau_ms_save_labels'])) {
        if (isset($_POST['label_required'])) {
            update_option('bimbeau_ms_label_required', sanitize_text_field(wp_unslash($_POST['label_required'])));
        }
        if (isset($_POST['label_select_option'])) {
            update_option('bimbeau_ms_label_select_option', sanitize_text_field(wp_unslash($_POST['label_select_option'])));
        }
        if (isset($_POST['label_continue'])) {
            update_option('bimbeau_ms_label_continue', sanitize_text_field(wp_unslash($_POST['label_continue'])));
        }
        if (isset($_POST['label_unknown_step'])) {
            update_option('bimbeau_ms_label_unknown_step', sanitize_text_field(wp_unslash($_POST['label_unknown_step'])));
        }
        echo '<div class="updated"><p>Options enregistrées.</p></div>';
    }

    $labelRequired     = get_option('bimbeau_ms_label_required', 'Ce champ est requis.');
    $labelSelectOption = get_option('bimbeau_ms_label_select_option', 'Veuillez sélectionner au moins une option.');
    $labelContinue     = get_option('bimbeau_ms_label_continue', 'Continuer');
    $labelUnknownStep  = get_option('bimbeau_ms_label_unknown_step', 'Étape inconnue.');

    ?>
    <div class="wrap">
        <h1>Messages personnalisés</h1>
        <form method="post">
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="label_required">Message champ requis</label></th>
                    <td><input type="text" id="label_required" name="label_required" value="<?php echo esc_attr($labelRequired); ?>" class="regular-text" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="label_select_option">Message option manquante</label></th>
                    <td><input type="text" id="label_select_option" name="label_select_option" value="<?php echo esc_attr($labelSelectOption); ?>" class="regular-text" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="label_continue">Texte du bouton Continuer</label></th>
                    <td><input type="text" id="label_continue" name="label_continue" value="<?php echo esc_attr($labelContinue); ?>" class="regular-text" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="label_unknown_step">Message étape inconnue</label></th>
                    <td><input type="text" id="label_unknown_step" name="label_unknown_step" value="<?php echo esc_attr($labelUnknownStep); ?>" class="regular-text" /></td>
                </tr>
            </table>
            <p class="submit"><input type="submit" name="bimbeau_ms_save_labels" class="button button-primary" value="Enregistrer" /></p>
        </form>
    </div>
    <?php
}
