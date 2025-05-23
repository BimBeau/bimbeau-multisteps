<?php
/*
Plugin Name: ACF Image Comparison
Description: Shortcode pour comparer deux images avec ACF.
Version: 1.0
Author: Slaaap
*/

function acf_image_comparison_enqueue_scripts() {
    wp_enqueue_script('img-comparison-slider', plugin_dir_url(__FILE__) . 'assets/img-comparison-slider.js', array(), '1.0', true);
    wp_enqueue_style('img-comparison-slider-css', plugin_dir_url(__FILE__) . 'assets/img-comparison-slider.css');
}
add_action('wp_enqueue_scripts', 'acf_image_comparison_enqueue_scripts');

function acf_image_comparison_shortcode($atts) {
    // Extraction des attributs du shortcode
    extract(shortcode_atts(array(
        'before_image' => 'imageavant_coulisse',
        'after_image' => 'imageapres_coulisse',
        'size' => 'medium', // Taille par défaut
    ), $atts));

    // Assurez-vous d'avoir l'ID du post actuel. Vous pouvez le passer en argument au shortcode si nécessaire.
    $post_id = get_the_ID();

    // Récupération des IDs des images en utilisant get_post_meta au lieu de get_field
    $before_image_id = get_post_meta($post_id, $before_image, true);
    $after_image_id = get_post_meta($post_id, $after_image, true);

    // Récupération des URLs des images avec la taille spécifiée
    $before_image_url = wp_get_attachment_image_src($before_image_id, $size)[0];
    $after_image_url = wp_get_attachment_image_src($after_image_id, $size)[0];

    // Vérification de la présence des images
    if (!$before_image_url || !$after_image_url) {
        return 'Images non trouvées.';
    }

    // Début de la capture de sortie
    ob_start();
?>
    <img-comparison-slider>
        <figure slot="first" class="before">
            <img slot="first" width="100%" src="<?php echo esc_url($before_image_url); ?>" data-no-lazy="1">
            <figcaption>Avant</figcaption>
        </figure>
        <figure slot="second" class="after">
            <img slot="second" width="100%" src="<?php echo esc_url($after_image_url); ?>" data-no-lazy="1">
            <figcaption>Après</figcaption>
        </figure>
        <svg slot="handle" xmlns="http://www.w3.org/2000/svg" width="100" viewBox="-8 -3 16 6">
            <path stroke="#fff" d="M -5 -2 L -7 0 L -5 2 M -5 -2 L -5 2 M 5 -2 L 7 0 L 5 2 M 5 -2 L 5 2" stroke-width="1" fill="#fff" vector-effect="non-scaling-stroke"></path>
        </svg>
    </img-comparison-slider>
<?php
    // Fin de la capture et récupération du contenu
    return ob_get_clean();
}
add_shortcode('acf_image_comparison', 'acf_image_comparison_shortcode');
