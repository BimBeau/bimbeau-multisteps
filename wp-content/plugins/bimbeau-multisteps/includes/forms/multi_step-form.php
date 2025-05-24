<?php
require_once dirname(__DIR__) . '/utils/ms-utils.php';

// Reset errors for each load
if (!isset($_SESSION['multi_step'])) {
    $_SESSION['multi_step'] = [];
}
$_SESSION['multi_step']['errors'] = [];

function bimbeau_ms_handle_generic_post($step) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['step'])) {
        return;
    }
    if (intval($_POST['step']) !== (int)$step->id) {
        return;
    }
    $key  = $step->step_key;
    $type = $step->question_type;
    if ($type === 'checkbox') {
        $value = isset($_POST[$key]) ? array_map('sanitize_text_field', (array)$_POST[$key]) : [];
        if (empty($value)) {
            $_SESSION['multi_step']['errors'][$key] = 'Veuillez sélectionner au moins une option.';
        } else {
            $_SESSION['multi_step'][$key] = $value;
        }
    } else {
        $value = isset($_POST[$key]) ? sanitize_text_field(wp_unslash($_POST[$key])) : '';
        if ($value === '') {
            $_SESSION['multi_step']['errors'][$key] = 'Ce champ est requis.';
        } else {
            $_SESSION['multi_step'][$key] = $value;
        }
    }
    if (empty($_SESSION['multi_step']['errors'])) {
        $nextUrl = bimbeau_ms_get_next_step_url($step->step_order);
        wp_safe_redirect($nextUrl);
        exit;
    }
}

function bimbeau_ms_get_next_step_url($currentOrder) {
    $steps = bimbeau_ms_get_step_definitions();
    foreach ($steps as $step) {
        if ($step->step_order == $currentOrder + 1) {
            return home_url('/multi_step/step-' . $step->step_order . '/');
        }
    }
    return home_url('/multi_step/merci/');
}

function bimbeau_ms_render_step($step) {
    bimbeau_ms_handle_generic_post($step);
    $key     = $step->step_key;
    $type    = $step->question_type;
    $choices = json_decode($step->choices, true) ?: [];
    $value   = isset($_SESSION['multi_step'][$key]) ? $_SESSION['multi_step'][$key] : '';
    ob_start();
    ?>
    <form method="POST" class="multi_step_form_step">
        <input type="hidden" name="step" value="<?php echo esc_attr($step->id); ?>">
        <h3><?php echo esc_html($step->label); ?></h3>
        <?php if ($type === 'radio'): ?>
            <?php foreach ($choices as $val => $label): ?>
                <label>
                    <input type="radio" name="<?php echo esc_attr($key); ?>" value="<?php echo esc_attr($val); ?>" <?php checked($value, $val); ?> required>
                    <?php echo esc_html($label); ?>
                </label><br>
            <?php endforeach; ?>
        <?php elseif ($type === 'checkbox'): ?>
            <?php foreach ($choices as $val => $label): ?>
                <label>
                    <input type="checkbox" name="<?php echo esc_attr($key); ?>[]" value="<?php echo esc_attr($val); ?>" <?php echo is_array($value) && in_array($val, $value, true) ? 'checked' : ''; ?>>
                    <?php echo esc_html($label); ?>
                </label><br>
            <?php endforeach; ?>
        <?php else: ?>
            <input type="text" name="<?php echo esc_attr($key); ?>" value="<?php echo esc_attr($value); ?>" required>
        <?php endif; ?>
        <p class="submit"><button type="submit" class="button button-primary">Continuer</button></p>
    </form>
    <?php
    return ob_get_clean();
}

function multi_step_form_shortcode($atts) {
    $atts = shortcode_atts(['etape' => '1'], $atts);
    $order = intval($atts['etape']);
    $steps = bimbeau_ms_get_step_definitions();
    foreach ($steps as $step) {
        if ($step->step_order == $order) {
            return bimbeau_ms_render_step($step);
        }
    }
    return 'Étape inconnue.';
}
add_shortcode('multi_step_form', 'multi_step_form_shortcode');
