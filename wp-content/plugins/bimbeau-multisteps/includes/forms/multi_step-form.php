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
    if (
        !isset($_POST['bimbeau_ms_nonce']) ||
        !wp_verify_nonce(wp_unslash($_POST['bimbeau_ms_nonce']), 'bimbeau_ms_form')
    ) {
        return;
    }
    $key     = $step->step_key;
    $type    = $step->question_type;
    $choices = json_decode($step->choices, true) ?: [];

    if ($type === 'checkbox') {
        $value = isset($_POST[$key]) ? array_map('sanitize_text_field', (array)$_POST[$key]) : [];
        if (empty($value)) {
            $_SESSION['multi_step']['errors'][$key] = get_option('bimbeau_ms_label_select_option', 'Veuillez sélectionner au moins une option.');
        } else {
            $_SESSION['multi_step'][$key] = $value;
        }
    } else {
        $value = isset($_POST[$key]) ? sanitize_text_field(wp_unslash($_POST[$key])) : '';
        if ($value === '') {
            $_SESSION['multi_step']['errors'][$key] = get_option('bimbeau_ms_label_required', 'Ce champ est requis.');
        } else {
            $_SESSION['multi_step'][$key] = $value;
        }
    }

    $selected = ($type === 'checkbox') ? (array)$value : [$value];
    foreach ($selected as $val) {
        if (!isset($choices[$val]) || !is_array($choices[$val]) || empty($choices[$val]['extras'])) {
            continue;
        }
        foreach ($choices[$val]['extras'] as $extra) {
            $fname = $key . '_' . $val . '_' . sanitize_key($extra['name']);
            $fval  = isset($_POST[$fname]) ? sanitize_text_field(wp_unslash($_POST[$fname])) : '';
            if (!empty($extra['required']) && $fval === '') {
                $_SESSION['multi_step']['errors'][$fname] = get_option('bimbeau_ms_label_required', 'Ce champ est requis.');
            } else {
                $_SESSION['multi_step'][$fname] = $fval;
            }
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
        if ($step->step_order > $currentOrder) {
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
        <?php wp_nonce_field('bimbeau_ms_form', 'bimbeau_ms_nonce'); ?>
        <input type="hidden" name="step" value="<?php echo esc_attr($step->id); ?>">
        <h3><?php echo esc_html($step->label); ?></h3>
        <?php if ($type === 'radio'): ?>
            <?php foreach ($choices as $val => $choice): ?>
                <?php
                $label  = is_array($choice) ? $choice['label'] : $choice;
                $extras = is_array($choice) && !empty($choice['extras']) ? $choice['extras'] : [];
                $extra_id = $key . '-' . $val . '-extras';
                ?>
                <div class="ms-option">
                    <label>
                        <input type="radio" name="<?php echo esc_attr($key); ?>" value="<?php echo esc_attr($val); ?>" data-extra-target="<?php echo $extras ? esc_attr($extra_id) : ''; ?>" <?php checked($value, $val); ?> required>
                        <?php echo esc_html($label); ?>
                    </label>
                    <?php if ($extras): ?>
                        <div class="ms-extras" id="<?php echo esc_attr($extra_id); ?>" style="display:none;">
                            <?php foreach ($extras as $ex):
                                $fname = $key . '_' . $val . '_' . sanitize_key($ex['name']);
                                $fval  = isset($_SESSION['multi_step'][$fname]) ? $_SESSION['multi_step'][$fname] : '';
                                $etype = isset($ex['type']) ? $ex['type'] : 'text';
                                $elabel= isset($ex['label']) ? $ex['label'] : $ex['name'];
                            ?>
                                <label>
                                    <?php echo esc_html($elabel); ?>
                                    <input type="<?php echo esc_attr($etype); ?>" name="<?php echo esc_attr($fname); ?>" value="<?php echo esc_attr($fval); ?>" <?php echo !empty($ex['required']) ? 'required' : ''; ?>>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php elseif ($type === 'checkbox'): ?>
            <?php foreach ($choices as $val => $choice): ?>
                <?php
                $label  = is_array($choice) ? $choice['label'] : $choice;
                $extras = is_array($choice) && !empty($choice['extras']) ? $choice['extras'] : [];
                $extra_id = $key . '-' . $val . '-extras';
                ?>
                <div class="ms-option">
                    <label>
                        <input type="checkbox" name="<?php echo esc_attr($key); ?>[]" value="<?php echo esc_attr($val); ?>" data-extra-target="<?php echo $extras ? esc_attr($extra_id) : ''; ?>" <?php echo is_array($value) && in_array($val, (array)$value, true) ? 'checked' : ''; ?>>
                        <?php echo esc_html($label); ?>
                    </label>
                    <?php if ($extras): ?>
                        <div class="ms-extras" id="<?php echo esc_attr($extra_id); ?>" style="display:none;">
                            <?php foreach ($extras as $ex):
                                $fname = $key . '_' . $val . '_' . sanitize_key($ex['name']);
                                $fval  = isset($_SESSION['multi_step'][$fname]) ? $_SESSION['multi_step'][$fname] : '';
                                $etype = isset($ex['type']) ? $ex['type'] : 'text';
                                $elabel= isset($ex['label']) ? $ex['label'] : $ex['name'];
                            ?>
                                <label>
                                    <?php echo esc_html($elabel); ?>
                                    <input type="<?php echo esc_attr($etype); ?>" name="<?php echo esc_attr($fname); ?>" value="<?php echo esc_attr($fval); ?>" <?php echo !empty($ex['required']) ? 'required' : ''; ?>>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <input type="text" name="<?php echo esc_attr($key); ?>" value="<?php echo esc_attr($value); ?>" required>
        <?php endif; ?>
        <p class="submit"><button type="submit" class="button button-primary"><?php echo esc_html(get_option('bimbeau_ms_label_continue', 'Continuer')); ?></button></p>
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
    return get_option('bimbeau_ms_label_unknown_step', 'Étape inconnue.');
}
add_shortcode('multi_step_form', 'multi_step_form_shortcode');
