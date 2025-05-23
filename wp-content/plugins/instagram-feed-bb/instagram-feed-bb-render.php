<?php

/**
 * Affiche le mur des derniers posts Instagram en utilisant les Custom Posts synchronisés.
 */
function get_instagram_feed_bb_shortcode() {

    $overlay_background_color = get_option('overlay_background_color', '#000000');
    $overlay_text_color = get_option('overlay_text_color', '#FFFFFF');

    // Récupération des posts Instagram depuis la base de données
    $posts_query = new WP_Query(array(
        'post_type'      => 'instagram_post',
        'posts_per_page' => 12,
        'orderby'        => 'date',
        'order'          => 'DESC'
    ));
    
    $posts_data = $posts_query->posts;

    if (!$posts_data) {
        write_insta_log('Aucun post trouvé', null, 'error');
        return 'Erreur : Aucun post trouvé pour cet utilisateur.';
    }

    // Générer le CSS personnalisé
    $custom_css = "
    <style>
    .social_wall_item .social_wall_item_overlay {
        background-color: {$overlay_background_color} !important;
        color: {$overlay_text_color} !important;
    }
    .social_wall_item .social_wall_item_overlay svg {
        fill: currentColor;
    }
    </style>
    ";

    $output = $custom_css;
    $output .= '<div class="social_wall_container">';

    // Boucle sur les posts Custom récupérés
    foreach ($posts_data as $post) {
        // Récupérer les méta-données
        $like_count = get_post_meta($post->ID, 'like_count', true);
        $comment_count = get_post_meta($post->ID, 'comment_count', true);
        $image_url = get_post_meta($post->ID, 'image_url', true);
        $permalink = get_post_meta($post->ID, 'post_url', true);

        $formatted_like_count = format_number_french($like_count);
        $formatted_comment_count = format_number_french($comment_count);

        $output .= '<a class="social_wall_item" href="' . esc_url($permalink) . '" target="_blank">';
        $output .= '<span class="social_wall_item_overlay">';
        $output .= '<span class="social_wall_item_likes">' . get_svg_icon('like-icon.svg') . ' ' . esc_html($formatted_like_count) . '</span>';
        $output .= '<span class="social_wall_item_comments">' . get_svg_icon('comment-icon.svg') . ' ' . esc_html($formatted_comment_count) . '</span>';
        $output .= '</span>';

        // Récupère l'URL de l'image depuis la médiathèque si disponible, sinon depuis l'URL stockée
        $image_attachment_id = get_post_meta($post->ID, 'image_attachment_id', true);
        if ($image_attachment_id) {
            // Récupère l'image avec la taille croppée
            $img_src_array = wp_get_attachment_image_src($image_attachment_id, '600x600c');
            $img_src = $img_src_array ? $img_src_array[0] : '';
        } else {
            $img_src = get_post_meta($post->ID, 'image_url', true);
        }

        if ($img_src) {
            $output .= '<img src="' . esc_url($img_src) . '" alt="Image Instagram" loading="auto" />';
        }

        $output .= '</a>';
    }

    $output .= '</div>';

    return $output;
}

add_shortcode('instagram_feed', 'get_instagram_feed_bb_shortcode');

/**
 * Formate les nombres selon les règles françaises.
 */
function format_number_french($number) {
    if ($number < 1000) {
        return $number;
    } elseif ($number >= 1000 && $number < 10000) {
        $formatted = number_format($number / 1000, 1, ',', '') . 'K';
    } elseif ($number >= 10000 && $number < 1000000) {
        $formatted = number_format($number / 1000, 0, ',', '') . 'K';
    } elseif ($number >= 1000000 && $number < 10000000) {
        $formatted = number_format($number / 1000000, 1, ',', '') . 'M';
    } else {
        $formatted = number_format($number / 1000000, 0, ',', '') . 'M';
    }
    return $formatted;
}

/**
 * Obtient le contenu des icônes SVG.
 */
function get_svg_icon($filename) {
    $filepath = plugin_dir_path(__FILE__) . 'assets/imgs/' . $filename;
    if (file_exists($filepath)) {
        return file_get_contents($filepath);
    }
    return '';
}

?>
