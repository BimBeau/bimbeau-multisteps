<?php
if (!defined('ABSPATH')) {
    exit;
}

function bimbeau_ms_data_form_shortcode($atts) {
    global $wpdb;
    $atts = shortcode_atts(['id' => 0], $atts);
    $id = intval($atts['id']);
    $table = $wpdb->prefix . 'bimbeau_ms_steps';
    $step = null;
    if ($id) {
        $step = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE id=%d", $id));
        if (!$step) {
            return '<p>' . __('Étape inconnue.', 'bimbeau-ms') . '</p>';
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bimbeau_ms_data_form_nonce']) && wp_verify_nonce(wp_unslash($_POST['bimbeau_ms_data_form_nonce']), 'bimbeau_ms_data_form')) {
        $label   = sanitize_text_field(wp_unslash($_POST['label']));
        $type    = sanitize_text_field(wp_unslash($_POST['type']));
        $choices = isset($_POST['choices']) ? sanitize_textarea_field(wp_unslash($_POST['choices'])) : '';
        if ($id) {
            $wpdb->update($table, [
                'label' => $label,
                'question_type' => $type,
                'choices' => $choices
            ], ['id' => $id]);
            return '<p>' . __('Étape mise à jour.', 'bimbeau-ms') . '</p>';
        } else {
            $order = (int)$wpdb->get_var("SELECT MAX(step_order) FROM {$table}") + 1;
            $wpdb->insert($table, [
                'step_order'    => $order,
                'step_key'      => sanitize_title($label),
                'label'         => $label,
                'question_type' => $type,
                'choices'       => $choices
            ]);
            return '<p>' . __('Étape ajoutée.', 'bimbeau-ms') . '</p>';
        }
    }

    ob_start();
    ?>
    <form method="post" class="bimbeau-data-form">
        <?php wp_nonce_field('bimbeau_ms_data_form', 'bimbeau_ms_data_form_nonce'); ?>
        <p>
            <label>
                <?php _e('Label', 'bimbeau-ms'); ?><br>
                <input type="text" name="label" value="<?php echo esc_attr($step->label ?? ''); ?>" required>
            </label>
        </p>
        <p>
            <label>
                <?php _e('Type', 'bimbeau-ms'); ?><br>
                <select name="type">
                    <option value="text" <?php selected($step->question_type ?? '', 'text'); ?>>Text</option>
                    <option value="radio" <?php selected($step->question_type ?? '', 'radio'); ?>>Radio</option>
                    <option value="checkbox" <?php selected($step->question_type ?? '', 'checkbox'); ?>>Checkbox</option>
                </select>
            </label>
        </p>
        <p>
            <label>
                <?php _e('Choices', 'bimbeau-ms'); ?><br>
                <textarea name="choices" rows="4" cols="40"><?php echo esc_textarea($step->choices ?? ''); ?></textarea>
            </label>
        </p>
        <p>
            <button type="submit" class="button button-primary"><?php echo $id ? __('Mettre à jour', 'bimbeau-ms') : __('Ajouter', 'bimbeau-ms'); ?></button>
        </p>
    </form>
    <?php
    return ob_get_clean();
}
add_shortcode('ms_data_form', 'bimbeau_ms_data_form_shortcode');

function bimbeau_ms_data_view_shortcode() {
    global $wpdb;
    $table = $wpdb->prefix . 'bimbeau_ms_steps';
    $steps = $wpdb->get_results("SELECT * FROM {$table} ORDER BY step_order ASC");
    ob_start();
    ?>
    <input type="text" id="ms-data-view-filter" placeholder="<?php esc_attr_e('Filtrer…', 'bimbeau-ms'); ?>">
    <table class="wp-list-table widefat fixed striped ms-data-view">
        <thead>
            <tr>
                <th><?php _e('Étape', 'bimbeau-ms'); ?></th>
                <th><?php _e('Type', 'bimbeau-ms'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($steps as $step) : ?>
                <tr>
                    <td><?php echo esc_html($step->label); ?></td>
                    <td><?php echo esc_html($step->question_type); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php
    return ob_get_clean();
}
add_shortcode('ms_data_view', 'bimbeau_ms_data_view_shortcode');
