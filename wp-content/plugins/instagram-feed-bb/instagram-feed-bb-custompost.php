<?php
/**
 * Enregistre le type de contenu personnalisé pour les posts Instagram.
 */
function register_instagram_post_type() {
    // Récupère l'option permettant de masquer les posts Instagram dans le back office
    $hide_posts = get_option('hide_instagram_posts_in_backend', false);

    $labels = array(
        'name'               => 'Instagram Posts',
        'singular_name'      => 'Instagram Post',
        'menu_name'          => 'Instagram Posts',
        'name_admin_bar'     => 'Instagram Post',
        'add_new'            => 'Ajouter nouveau',
        'add_new_item'       => 'Ajouter un nouveau post Instagram',
        'new_item'           => 'Nouveau post Instagram',
        'edit_item'          => 'Éditer le post Instagram',
        'view_item'          => 'Voir le post Instagram',
        'all_items'          => 'Tous les posts Instagram',
        'search_items'       => 'Rechercher des posts Instagram',
        'not_found'          => 'Aucun post trouvé',
        'not_found_in_trash' => 'Aucun post trouvé dans la corbeille'
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'has_archive'        => false,
        'show_in_menu'       => !$hide_posts, // Masquer ou afficher dans le back office selon l'option
        'supports'           => array('title'),
    );

    register_post_type('instagram_post', $args);
}
add_action('init', 'register_instagram_post_type');

/**
 * Ajoute une métabox sur l'écran d'édition des Instagram Posts pour afficher les champs récupérés.
 */
function instagram_feed_bb_add_meta_box() {
    add_meta_box(
        'instagram_feed_bb_meta_box',       // ID unique pour la métabox
        'Détails du post Instagram',        // Titre de la métabox
        'instagram_feed_bb_meta_box_html',  // Fonction de rappel pour afficher le contenu de la métabox
        'instagram_post',                   // Post type concerné
        'normal',                           // Contexte ('normal', 'side', etc.)
        'default'                           // Priorité
    );
}
add_action('add_meta_boxes', 'instagram_feed_bb_add_meta_box');

function instagram_feed_bb_meta_box_html($post) {
    // Récupérer les valeurs des champs personnalisés
    $like_count = get_post_meta($post->ID, 'like_count', true);
    $comment_count = get_post_meta($post->ID, 'comment_count', true);
    $post_url = get_post_meta($post->ID, 'post_url', true);
    $image_attachment_id = get_post_meta($post->ID, 'image_attachment_id', true);

    echo '<p><strong>Likes :</strong> ' . esc_html($like_count) . '</p>';
    echo '<p><strong>Commentaires :</strong> ' . esc_html($comment_count) . '</p>';
    if ($post_url) {
        echo '<p><strong>URL du post :</strong> <a href="' . esc_url($post_url) . '" target="_blank">' . esc_html($post_url) . '</a></p>';
    }
    if ($image_attachment_id) {
        $img_url = wp_get_attachment_url($image_attachment_id);
        if ($img_url) {
            echo '<p><strong>Image :</strong><br><img src="' . esc_url($img_url) . '" style="max-width:100%; height:auto;" /></p>';
        }
    }
}


/**
 * Exclure le type 'instagram_post' du sitemap natif de WordPress.
 */
function exclude_instagram_post_from_wp_sitemap($post_types) {
    // Si le type 'instagram_post' est présent dans la liste, on le retire
    if (isset($post_types['instagram_post'])) {
        unset($post_types['instagram_post']);
    }
    return $post_types;
}
add_filter('wp_sitemaps_post_types', 'exclude_instagram_post_from_wp_sitemap');
  