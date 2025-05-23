<?php

/**
 * Plugin Name: ACF to Elementor Flexible Content
 * Description: Intègre les dispositions ACF dans les templates Elementor en fonction de l'article en cours.
 * Version: 1.0
 * Author: Slaaap
 */

// S'assurer que le plugin ne peut pas être exécuté en dehors de WordPress
defined('ABSPATH') or die('Direct script access disallowed.');


/**
 * Boucle sur les dispositions ACF et charge les templates Elementor correspondants.
 */

function acf_elementor_flexible_content_shortcode() {
    if (!function_exists('get_field') || !class_exists('\Elementor\Plugin')) {
        return 'Advanced Custom Fields et Elementor doivent être installés et actifs.';
    }

    $output = '';

    if (have_rows('constructeur_article')) {
        while (have_rows('constructeur_article')) {
            the_row();

            /** Texte mis en avant */
            if (get_row_layout() == 'excerpt_block_article') {

                // Récupération des valeurs
                $excerpt_article = get_sub_field('excerpt_article');

                // Chargement du modèle Elementor
                $elementor_template = \Elementor\Plugin::$instance->frontend->get_builder_content_for_display(8146);

                // Remplacement
                $output .= str_replace("{{excerpt_article}}", $excerpt_article, $elementor_template);
            }

            /** Citation */
            elseif (get_row_layout() == 'citation_block_article_copier') {

                // Récupération des valeurs
                $citation_article = get_sub_field('citation_article');

                // Chargement du modèle Elementor
                $elementor_template = \Elementor\Plugin::$instance->frontend->get_builder_content_for_display(8150);

                // Remplacement
                $output .= str_replace("{{citation_article}}", $citation_article, $elementor_template);
            }

            /** Titre + paragraphe */
            elseif (get_row_layout() == 'onecol_text_block_article') {
                // Récupérer les sous-champs flexibles
                if (have_rows('onecol_text_article')) {
                    while (have_rows('onecol_text_article')) {
                        the_row();

                        // Récupération des valeurs
                        $h_onecol_text_article = get_sub_field('h_onecol_text_article');
                        $p_onecol_text_article = get_sub_field('p_onecol_text_article');

                        // Chargement du modèle Elementor
                        $elementor_template = \Elementor\Plugin::$instance->frontend->get_builder_content_for_display(8153);

                        // Remplacement
                        $output .= str_replace("{{h_onecol_text_article}}", $h_onecol_text_article, $elementor_template);
                        $output = str_replace("{{p_onecol_text_article}}", $p_onecol_text_article, $output);
                    }
                }
            }

            /** Image */
            elseif (get_row_layout() == 'image_block_article') {

                // Récupération des valeurs
                $image_field_article = get_sub_field('image_field_article');
                $image_full_url = $image_field_article['url'];
                $image_thumb_url = $image_field_article['sizes']['og_image'];
                $image_caption = $image_field_article['caption'];

                // Chargement du modèle Elementor
                $elementor_template = \Elementor\Plugin::$instance->frontend->get_builder_content_for_display(8158);


                // Remplacement
                $output .= str_replace("{{image_full_url}}", $image_full_url, $elementor_template);
                $output = str_replace("{{image_thumb_url}}", $image_thumb_url, $output);
                $output = str_replace("{{image_caption}}", $image_caption, $output);
            }


            /** Galerie photos */
            elseif (get_row_layout() == 'gallery_block_article') {

                // Récupération des valeurs
                $gallery_field_article = get_sub_field('gallery_field_article');

                // Chargement du modèle Elementor
                $elementor_template = \Elementor\Plugin::$instance->frontend->get_builder_content_for_display(8162);

                // Remplacement des variables dans le modèle Elementor
                $gallery_html = ''; // Variable pour stocker le code HTML de la galerie
                $gallery_ids = []; // Tableau pour stocker les IDs des images

                // Boucle à travers les ID d'images pour créer le code HTML de la galerie
                foreach ($gallery_field_article as $gallery_image_id) {
                    $gallery_image_full_url = wp_get_attachment_url($gallery_image_id); // Récupère l'URL complète de l'image
                    $gallery_image_thumb_url = wp_get_attachment_image_src($gallery_image_id, 'large')[0]; // Récupère l'URL de l'image miniature
                    $gallery_image_caption = wp_get_attachment_caption($gallery_image_id); // Récupère la légende de l'image

                    // Ajoute l'ID de l'image au tableau des IDs
                    $gallery_ids[] = $gallery_image_id;

                    // Ajoute le code HTML pour chaque image à la variable $gallery_html
                    $gallery_html .= '<a class="e-gallery-item elementor-gallery-item elementor-animated-content" href="' . $gallery_image_full_url . '" data-elementor-open-lightbox="yes" data-elementor-lightbox-slideshow="e3c4747" data-elementor-lightbox-title="' . $gallery_image_caption . '" data-e-action-hash="#elementor-action%3Aaction%3Dlightbox%26settings%3DeyJpZCI6NjE3MSwidXJsIjoiaHR0cHM6XC9cL3NlY3JldGRlY28uZnJcL3dwLWNvbnRlbnRcL3VwbG9hZHNcLzIwMjRcLzAyXC9wbGFjZWhvbGRlci0zNC5wbmciLCJzbGlkZXNob3ciOiJlM2M0NzQ3In0%3D" style="--column: 0; --row: 0;">
                <div class="e-gallery-image elementor-gallery-item__image" data-thumbnail="' . $gallery_image_thumb_url . '" aria-label="icône" role="img" style="background-image: url(&quot;' . $gallery_image_thumb_url . '&quot;);"></div>
            </a>';
                }

                // Crée une chaîne avec tous les IDs d'images séparés par des virgules
                $gallery_ids_string = implode(',', $gallery_ids);

                // Remplace la chaîne {{gallery_images_id}} et {{gallery_html}} dans le modèle Elementor
                $elementor_template = str_replace("{{gallery_images_id}}", $gallery_ids_string, $elementor_template);
                $output .= str_replace("{{gallery_html}}", $gallery_html, $elementor_template);
            }


            /** Vidéo */
            elseif (get_row_layout() == 'video_block_article') {

                // Récupération des valeurs
                $h_video_article = get_sub_field('h_video_article');
                $video_article = get_sub_field('video_article');

                // Chargement du modèle Elementor
                $elementor_template = \Elementor\Plugin::$instance->frontend->get_builder_content_for_display(8165);

                // Remplacement
                $output .= str_replace("{{h_video_article}}", $h_video_article, $elementor_template);
                $output = str_replace("{{video_article}}", $video_article, $output);
            }

            /** Html */
            elseif (get_row_layout() == 'html_block_article') {

                // Récupération des valeurs
                $html_article = get_sub_field('html_article');

                // Chargement du modèle Elementor
                $elementor_template = \Elementor\Plugin::$instance->frontend->get_builder_content_for_display(8168);

                // Remplacement
                $output .= str_replace("{{html_article}}", $html_article, $elementor_template);
            }
        }
    } else {
        echo 'Cette publication n\'a pas encore de contenu.';
    }


    return $output;
}

add_shortcode('acf_elementor_flexible_content', 'acf_elementor_flexible_content_shortcode');


/**
 * Génère l'extrait de l'article à partir des dispositions ACF.
 *
 * @return string
 */
function generate_excerpt_from_acf() {
    $excerpt = '';

    if (have_rows('constructeur_article')) {
        while (have_rows('constructeur_article')) {
            the_row();

            // Texte mis en avant
            if (get_row_layout() == 'excerpt_block_article') {
                $excerpt_article = get_sub_field('excerpt_article');
                $excerpt .= $excerpt_article . ' ';
            }
            // Titre + paragraphe
            elseif (get_row_layout() == 'onecol_text_block_article') {
                if (have_rows('onecol_text_article')) {
                    while (have_rows('onecol_text_article')) {
                        the_row();
                        $p_onecol_text_article = get_sub_field('p_onecol_text_article');
                        $excerpt .= $p_onecol_text_article . ' ';
                    }
                }
            }
            // Citation
            elseif (get_row_layout() == 'citation_block_article_copier') {
                $citation_article = get_sub_field('citation_article');
                $excerpt .= $citation_article . ' ';
            }
            // Vidéo
            elseif (get_row_layout() == 'video_block_article') {
                $h_video_article = get_sub_field('h_video_article');
                $excerpt .= $h_video_article . ' ';
            }
        }
    }

    // Limite l'extrait à 55 mots (comme la fonction par défaut de WordPress)
    $excerpt = wp_trim_words($excerpt, 55, '...');

    // Supprime les balises HTML et caractères spéciaux
    $excerpt = wp_strip_all_tags($excerpt);

    // Décodage des entités HTML
    $excerpt = html_entity_decode($excerpt, ENT_QUOTES | ENT_XML1, 'UTF-8');

    // Assure la sécurité du texte
    $excerpt = sanitize_text_field($excerpt);

    return $excerpt;
}


/**
 * Met à jour l'extrait de l'article lors de sa sauvegarde.
 *
 * @param int $post_id L'ID de l'article en cours de sauvegarde.
 */
function update_post_excerpt($post_id) {
    // Évite les auto-sauvegardes.
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Vérifie les autorisations de l'utilisateur.
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // Vérifie si le post est un type de post auquel nous voulons appliquer l'extrait généré
    $post_type = get_post_type($post_id);
    if ($post_type !== 'post') {
        return;
    }

    // Empêche les boucles infinies en désactivant temporairement ce hook.
    remove_action('save_post', 'update_post_excerpt');


    // Génère et sauvegarde l'extrait.
    $generated_excerpt = generate_excerpt_from_acf();
    wp_update_post(array(
        'ID' => $post_id,
        'post_excerpt' => $generated_excerpt
    ));

    // Ré-attache le hook après la mise à jour.
    add_action('save_post', 'update_post_excerpt');
}

// Attache la fonction au hook de sauvegarde de post
add_action('save_post', 'update_post_excerpt');
