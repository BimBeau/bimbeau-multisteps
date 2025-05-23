<?php

/**
 * Shortcode pour afficher un accordéon basé sur ACF repeater field 'details_renovation'
 */
function realisation_details_accordeon_shortcode() {
    // Vérifie si ACF est actif et si le champ 'details_renovation' existe
    if (function_exists('have_rows') && have_rows('details_renovation')) {
        ob_start();
        echo '<div class="realisation_details_accordeon">';

        // Chemin de base pour les images du thème enfant
        $base_theme_img_url = get_stylesheet_directory_uri() . '/assets/img/acf/';
        // Chemin de base pour les images du plugin
        $base_plugin_img_url = plugin_dir_url(__FILE__) . 'img/';

        // Boucle sur chaque ligne du repeater
        while (have_rows('details_renovation')) {
            the_row();
            $icone_url = get_sub_field('icone_details_realisation'); // Assurez-vous que ce champ contient juste le nom de l'icône, par exemple "Sofa.svg"
            $titre = get_sub_field('titre_details_renovation');
            $description = get_sub_field('description_details_renovation');

            // Affiche chaque élément de l'accordéon
            echo '<div class="accordeon-item">';
            echo '<div class="accordeon-title"><img class="accordeon-icon" src="' . $base_theme_img_url . esc_attr($icone_url) . '.svg" alt="Icone"><h3>' . esc_html($titre) . '</h3><img class="accordeon-state-icon" src="' . $base_plugin_img_url . 'minimal_arrow_right.svg" alt="Collapse Icon"></div>';
            echo '<div class="accordeon-content">' . $description . '</div>';
            echo '</div>';
        }

        echo '</div>';
        return ob_get_clean();
    } else {
        return 'Les détails de la rénovation ne sont pas disponibles.';
    }
}

// Ajoute le shortcode 'realisation_details_accordeon' à WordPress
add_shortcode('realisation_details_accordeon', 'realisation_details_accordeon_shortcode');
