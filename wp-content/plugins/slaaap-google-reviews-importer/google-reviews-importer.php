<?php
/*
Plugin Name: Google Reviews Importer
Description: Importe les avis Google en tant que custom posts dans WordPress.
Version: 1.0
Author: Slaaap
*/

// Enregistrement du Custom Post Type 'googlereview'
if (!function_exists('register_googlereview_post_type')) {
    function register_googlereview_post_type() {
        $labels = array(
            'name'                  => _x('Avis clients', 'Post type general name', 'google-reviews-importer'),
            'singular_name'         => _x('Avis client', 'Post type singular name', 'google-reviews-importer'),
            // Nom personnalisé pour l'élément de menu dans l'administration
            'menu_name'             => _x('Avis Google', 'Admin Menu text', 'google-reviews-importer'),
            'name_admin_bar'        => _x('Avis client', 'Add New on Toolbar', 'google-reviews-importer'),
            'add_new'               => __('Ajouter un avis', 'google-reviews-importer'),
            'add_new_item'          => __('Ajouter un nouvel avis', 'google-reviews-importer'),
            'new_item'              => __('Nouvel avis', 'google-reviews-importer'),
            'edit_item'             => __('Éditer l\'avis', 'google-reviews-importer'),
            'view_item'             => __('Voir l\'avis', 'google-reviews-importer'),
            'all_items'             => __('Tous les avis', 'google-reviews-importer'),
            'search_items'          => __('Rechercher des avis', 'google-reviews-importer'),
            'parent_item_colon'     => __('Avis parent :', 'google-reviews-importer'),
            'not_found'             => __('Aucun avis trouvé.', 'google-reviews-importer'),
            'not_found_in_trash'    => __('Aucun avis trouvé dans la corbeille.', 'google-reviews-importer'),
            'featured_image'        => _x('Avatar de l\'auteur', 'Overrides the “Featured Image” phrase', 'google-reviews-importer'),
            'set_featured_image'    => _x('Définir l\'image de l\'avis', 'Overrides the “Set featured image” phrase', 'google-reviews-importer'),
            'remove_featured_image' => _x('Retirer l\'image de l\'avis', 'Overrides the “Remove featured image” phrase', 'google-reviews-importer'),
            'use_featured_image'    => _x('Utiliser comme image de l\'avis', 'Overrides the “Use as featured image” phrase', 'google-reviews-importer'),
            'archives'              => _x('Archives des avis', 'The post type archive label', 'google-reviews-importer'),
            'insert_into_item'      => _x('Insérer dans l\'avis', 'Overrides the “Insert into post”/“Insert into page” phrase', 'google-reviews-importer'),
            'uploaded_to_this_item' => _x('Téléversé sur cet avis', 'Overrides the “Uploaded to this post”/“Uploaded to this page” phrase', 'google-reviews-importer'),
            'filter_items_list'     => _x('Filtrer la liste des avis', 'Overrides the “Filter posts list”/“Filter pages list” phrase', 'google-reviews-importer'),
            'items_list_navigation' => _x('Navigation de la liste des avis', 'Overrides the “Posts list navigation”/“Pages list navigation” phrase', 'google-reviews-importer'),
            'items_list'            => _x('Liste des avis', 'Overrides the “Posts list”/“Pages list” phrase', 'google-reviews-importer'),
        );

        $args = array(
            'labels'             => $labels,
            'public'             => false, // Le type de post n'est pas public
            'publicly_queryable' => false, // Le type de post ne peut pas être interrogé sur le front-end
            'show_ui'            => true,  // Le type de post est visible dans l'UI de l'admin
            'show_in_menu'       => true,  // Le type de post est visible dans le menu de l'admin
            'query_var'          => true,
            'rewrite'            => array('slug' => 'googlereview'), // Réécriture d'URL personnalisée, ajustez selon vos besoins
            'capability_type'    => 'post',
            'has_archive'        => false, // Désactive l'archive pour ce type de post
            'hierarchical'       => false,
            'menu_position'      => null,
            'supports'           => array('title', 'editor', 'custom-fields', 'thumbnail'),
            // Icône personnalisée pour le menu du type de contenu
            'menu_icon'          => 'dashicons-star-half',
        );

        register_post_type('googlereview', $args);
    }
}

add_action('init', 'register_googlereview_post_type');

// Désactive le masquage des champs personnalisés wordpress si ACF est activé
add_filter('acf/settings/remove_wp_meta_box', '__return_false');

// Enregistre l'Intervalle Personnalisé
function googlereview_custom_cron_schedule($schedules) {
    $sync_frequency = get_option('sync_frequency', 1); // 1 jour par défaut
    $interval = DAY_IN_SECONDS * $sync_frequency; // Convertir en secondes

    // Ajouter l'intervalle personnalisé
    $schedules['googlereview_interval'] = array(
        'interval' => $interval,
        'display'  => sprintf(__('Chaque %d jours', 'google-reviews-importer'), $sync_frequency)
    );

    return $schedules;
}
add_filter('cron_schedules', 'googlereview_custom_cron_schedule');

// Planifie l'Événement avec l'Intervalle Personnalisé
function schedule_review_import() {
    if (!wp_next_scheduled('import_google_reviews_event')) {
        wp_schedule_event(time(), 'googlereview_interval', 'import_google_reviews_event');
    }
}
add_action('wp_loaded', 'schedule_review_import');
add_action('import_google_reviews_event', 'import_google_reviews');



// Fonction pour importer les avis
function import_google_reviews() {
    $api_key = get_option('api_key');
    $place_id = get_option('place_id');
    $min_score = get_option('min_score', 1);

    // Construire l'URL de l'API Google Places
    $api_url = "https://maps.googleapis.com/maps/api/place/details/json?placeid=$place_id&key=$api_key&language=fr&reviews_sort=newest";

    // Envoyer la requête à l'API Google Places
    $response = wp_remote_get($api_url);
    if (is_wp_error($response)) {
        write_googlereview_log('Erreur lors de la récupération des avis Google: ' . $response->get_error_message(), "", "error");
        return;
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    // Vérifier si l'API a retourné une erreur
    if (isset($data['error_message'])) {
        write_googlereview_log('Erreur de l\'API Google Places : ' . $data['error_message'], "", "error");
        return;
    }

    // Vérifier et importer les avis
    if (isset($data['result']['reviews'])) {

        $reviews = $data['result']['reviews'];

        // write_googlereview_log('$reviews : ', $reviews, "notice");

        foreach ($reviews as $review) {
            // Vérifier la longueur du texte et la note
            if (strlen($review['text']) > 100 && $review['rating'] >= $min_score) {

                $existing_post_id = get_post_by_unique_time($review['time']);

                if ($existing_post_id) {
                    // Mettre à jour l'avis existant
                    update_existing_review($existing_post_id, $review);
                } else {
                    // Créer un nouvel avis
                    create_new_review($review);
                }
            }
        }
    } else {
        write_googlereview_log('Aucun avis trouvé pour le lieu spécifié.', "", "error");
    }
}

// Obtenir un Post par l'Identifiant Unique
function get_post_by_unique_time($unique_time) {
    $args = array(
        'post_type' => 'googlereview',
        'meta_query' => array(
            array(
                'key' => 'review_time',
                'value' => $unique_time,
                'compare' => '='
            )
        )
    );
    $query = new WP_Query($args);

    if ($query->have_posts()) {
        return $query->posts[0]->ID;
    }

    return false;
}

// Fonction pour Créer un Nouvel Avis
function create_new_review($review_data) {

    // Convertir le timestamp Unix en format de date WordPress
    $post_date = date('Y-m-d H:i:s', $review_data['time']);

    // Créer le post
    $post_data = array(
        'post_title'    => wp_strip_all_tags($review_data['author_name']),
        'post_content'  => $review_data['text'],
        'post_status'   => 'publish',
        'post_type'     => 'googlereview',
        'post_date'     => $post_date, // Définir la date de publication
    );
    $post_id = wp_insert_post($post_data);

    // Définir l'image à la une
    if (isset($review_data['profile_photo_url'])) {
        set_review_thumbnail($post_id, $review_data['profile_photo_url']);
    }

    // Récupérer l'ID du lieu Google Places et construire le lien vers l'avis sur Google
    $place_id = get_option('place_id');
    $author_url_parts = explode('/reviews', $review_data['author_url']);
    $review_link = $author_url_parts[0] . '/place/' . $place_id;

    // Ajouter des métadonnées personnalisées
    update_post_meta($post_id, 'review_score', $review_data['rating']);
    update_post_meta($post_id, 'review_link', $review_link);
    update_post_meta($post_id, 'review_time', $review_data['time']);
}

// Fonction pour Mettre à Jour un Avis Existant
function update_existing_review($post_id, $review_data) {

    // Convertir le timestamp Unix en format de date WordPress
    $post_date = date('Y-m-d H:i:s', $review_data['time']);

    // Mettre à jour le post (titre, contenu, etc.)
    $post_update = array(
        'ID'           => $post_id,
        'post_title'   => wp_strip_all_tags($review_data['author_name']),
        'post_content' => $review_data['text'],
        'post_date'    => $post_date, // Mettre à jour la date de publication
    );
    wp_update_post($post_update);

    // Mettre à jour l'image à la une
    if (isset($review_data['profile_photo_url'])) {
        set_review_thumbnail($post_id, $review_data['profile_photo_url']);
    }

    // Récupérer l'ID du lieu Google Places et construire le lien vers l'avis sur Google
    $place_id = get_option('place_id');
    $author_url_parts = explode('/reviews', $review_data['author_url']);
    $review_link = $author_url_parts[0] . '/place/' . $place_id;

    // Mettre à jour les métadonnées personnalisées
    update_post_meta($post_id, 'review_score', $review_data['rating']);
    update_post_meta($post_id, 'review_link', $review_link);
    update_post_meta($post_id, 'review_time', $review_data['time']);
}

// Télécharge une image et la définit comme image à la une
function set_review_thumbnail($post_id, $image_url) {
    // Générer un identifiant unique pour l'image
    $unique_identifier = get_unique_identifier_from_url($image_url);

    // Vérifier si une image avec cet identifiant existe déjà
    $existing_image_id = image_exists_with_unique_identifier($unique_identifier);

    if ($existing_image_id) {
        // Si l'image existe déjà, définir comme image à la une
        set_post_thumbnail($post_id, $existing_image_id);
    } else {
        // Si l'image n'existe pas, la télécharger et l'attacher
        download_and_attach_image($post_id, $image_url);
    }
}

// Télécharge et attache l'image au post
function download_and_attach_image($post_id, $image_url) {
    // Inclure les fichiers nécessaires pour la gestion des médias
    require_once(ABSPATH . 'wp-admin/includes/media.php');
    require_once(ABSPATH . 'wp-admin/includes/file.php');
    require_once(ABSPATH . 'wp-admin/includes/image.php');

    // Générer un identifiant unique pour l'image
    $unique_identifier = get_unique_identifier_from_url($image_url);

    // Ajouter l'identifiant unique dans le paramètre 'ext'
    $image_url_with_extension = $image_url . "?ext=" . $unique_identifier . ".jpeg";

    // Télécharger et attacher l'image
    $image_id = media_sideload_image($image_url_with_extension, $post_id, null, 'id');

    if (!is_wp_error($image_id)) {
        // Définir l'image téléchargée comme image à la une du post
        set_post_thumbnail($post_id, $image_id);

        // Ajouter l'identifiant unique comme métadonnée personnalisée
        update_post_meta($image_id, 'unique_image_identifier', $unique_identifier);

        return $image_id;
    } else {
        // Log de l'erreur
        write_googlereview_log('Erreur lors du téléchargement de l\'image : ' . $image_id->get_error_message(), "", "error");
        return false;
    }
}


// Créé un nom d'image unique à partir de l'url
function get_unique_identifier_from_url($url) {
    $parsed_url = parse_url($url);
    $path = $parsed_url['path'];

    // Extraire une partie unique de l'URL, par exemple après '/a/'
    $parts = explode('/a/', $path);
    if (count($parts) > 1) {
        return 'google_' . preg_replace("/[^A-Za-z0-9]/", '_', $parts[1]);
    }

    return 'google_image';  // Identifiant par défaut si l'URL n'est pas dans le format attendu
}

// Teste si l'image existe déjà en fonction de l'identifiant unique
function image_exists_with_unique_identifier($unique_identifier) {
    $query_args = array(
        'post_type'      => 'attachment',
        'post_mime_type' => 'image',
        'post_status'    => 'inherit',
        'fields'         => 'ids',
        'meta_query'     => array(
            array(
                'key'     => 'unique_image_identifier',
                'value'   => $unique_identifier,
                'compare' => '=',
            ),
        ),
    );
    $query = new WP_Query($query_args);

    // Retourner l'ID de l'image si elle existe
    if (count($query->posts) > 0) {
        return $query->posts[0];
    }

    return false;
}


// Rechercher dans la bibliothèque de médias une image avec la même URL
function get_existing_image_id($image_url) {

    $query_args = array(
        'post_type'      => 'attachment',
        'post_mime_type' => 'image',
        'post_status'    => 'inherit',
        'fields'         => 'ids',
        'meta_query'     => array(
            array(
                'key'     => '_wp_attached_file',
                'value'   => $image_url,
                'compare' => 'LIKE',
            ),
        ),
    );
    $query = new WP_Query($query_args);

    // Retourner l'ID de l'image si elle existe
    if (count($query->posts) > 0) {
        return $query->posts[0];
    }

    return false;
}

// Change le placeholder du titre du custom post googlereview
function wpb_change_title_text($title) {
    $screen = get_current_screen();

    if ('googlereview' == $screen->post_type) {
        $title = __('Saisissez le Prénom et Nom de l\'auteur de l\'avis', 'google-reviews-importer');
    }

    return $title;
}

add_filter('enter_title_here', 'wpb_change_title_text');


// Fonctions pour afficher les champs de configuration
function render_api_key_field() {
    $api_key = get_option('api_key');
    echo '<input type="text" name="api_key" value="' . esc_attr($api_key) . '" />';
}

function render_place_id_field() {
    $place_id = get_option('place_id');
    echo '<input type="text" name="place_id" value="' . esc_attr($place_id) . '" />';
}

function render_min_score_field() {
    $min_score = get_option('min_score', 5); // Valeur par défaut de 5
    echo '<input type="number" name="min_score" value="' . esc_attr($min_score) . '" min="1" max="5" />';
    echo '<p>La note minimale doit être comprise entre 1 et 5</p>';
}

function render_sync_frequency_field() {
    $sync_frequency = get_option('sync_frequency', 7); // Valeur par défaut de 7 jour
    echo '<input type="number" name="sync_frequency" value="' . esc_attr($sync_frequency) . '" min="1" />';
    echo '<p>La fréquence doit être d\'au moins 1 jour</p>';
}


// Validations

function validate_sync_frequency($input) { // Fréquence de synchronisation
    if (is_numeric($input) && $input >= 1) {
        return $input;
    } else {
        add_settings_error(
            'sync_frequency',
            'sync_frequency_error',
            'La fréquence de récupération doit être d\'au moins 1 jour.',
            'error'
        );
        return get_option('sync_frequency'); // Restaurer la valeur précédente en cas d'erreur
    }
}
add_filter('pre_update_option_sync_frequency', 'validate_sync_frequency');

function validate_min_score($input) {
    if (is_numeric($input) && $input >= 1 && $input <= 5) { // Note minimale
        return intval($input); // Convertit l'entrée en un entier
    } else {
        add_settings_error(
            'min_score',
            'min_score_error',
            'La note minimale doit être un nombre entier entre 1 et 5.',
            'error'
        );
        return get_option('min_score'); // Restaurer la valeur précédente en cas d'erreur
    }
}
add_filter('pre_update_option_min_score', 'validate_min_score');


// Enregistrement des champs de configuration
function google_reviews_settings_fields() {
    // Enregistrement des options de configuration
    register_setting('google-reviews-settings-group', 'api_key');
    register_setting('google-reviews-settings-group', 'place_id');
    register_setting('google-reviews-settings-group', 'min_score');
    register_setting('google-reviews-settings-group', 'sync_frequency');

    // Ajout des sections et des champs de réglages
    add_settings_section(
        'google-reviews-settings-section',
        null,
        null, // Pas de fonction de rappel nécessaire ici
        'google-reviews-settings'
    );

    add_settings_field(
        'api_key',
        'Clé API Google Places',
        'render_api_key_field',
        'google-reviews-settings',
        'google-reviews-settings-section'
    );

    add_settings_field(
        'place_id',
        'ID du lieu Google Places',
        'render_place_id_field',
        'google-reviews-settings',
        'google-reviews-settings-section'
    );

    add_settings_field(
        'min_score',
        'Note minimale',
        'render_min_score_field',
        'google-reviews-settings',
        'google-reviews-settings-section'
    );

    add_settings_field(
        'sync_frequency',
        'Fréquence de la récupération (en jours)',
        'render_sync_frequency_field',
        'google-reviews-settings',
        'google-reviews-settings-section'
    );
}
add_action('admin_init', 'google_reviews_settings_fields');

// Page de réglage du Plugin
function google_reviews_add_settings_page() {
    add_options_page(
        'Réglages Google Reviews Importer', // Titre de la page
        'Google Reviews Importer', // Titre du menu
        'manage_options', // Capacité requise pour accéder à la page
        'google-reviews-importer', // Slug de la page
        'google_reviews_render_settings_page' // Fonction pour afficher le contenu de la page
    );
}

function google_reviews_render_settings_page() {
?>
    <div class="wrap">
        <h1>Réglages de Google Reviews Importer</h1>
        <form method="post" action="options.php">
            <?php settings_fields('google-reviews-settings-group'); ?>
            <?php do_settings_sections('google-reviews-settings'); ?>
            <?php submit_button(); ?>
        </form>
    </div>
<?php
}

add_action('admin_menu', 'google_reviews_add_settings_page');


// Ecrit un message dans le fichier log
function write_googlereview_log($label, $data = null, $level = 'notice') {
    $log_file = plugin_dir_path(__FILE__) . 'debug.log';

    // Vérifie si le fichier existe, sinon le créer
    if (!file_exists($log_file)) {
        touch($log_file);
    }

    $max_size = 10 * 1024 * 1024; // 10 Mo

    // Vérifier la taille du fichier
    if (filesize($log_file) >= $max_size) {
        // Tronquer le fichier si la taille dépasse $max_size
        file_put_contents($log_file, '');
    }

    $current_date = date('Y-m-d H:i:s');
    $log_message = $current_date . ' [' . strtoupper($level) . '] - ' . $label;
    if ($data !== null) {
        $log_message .= "\n" . print_r($data, true);
    }
    $log_message .= "\n";
    file_put_contents($log_file, $log_message, FILE_APPEND);
}

function calculate_time_elapsed($timestamp) {
    $now = new DateTime();
    $review_date = new DateTime();
    $review_date->setTimestamp($timestamp);
    $interval = $now->diff($review_date);

    if ($interval->y > 0) {
        return $interval->y . ' ' . _n('an', 'ans', $interval->y, 'slaaap-google-reviews-importer');
    } elseif ($interval->m > 0) {
        return $interval->m . ' ' . _n('mois', 'mois', $interval->m, 'slaaap-google-reviews-importer');
    } elseif ($interval->d >= 7) {
        $weeks = floor($interval->d / 7);
        return $weeks . ' ' . _n('semaine', 'semaines', $weeks, 'slaaap-google-reviews-importer');
    } elseif ($interval->d > 0) {
        return $interval->d . ' ' . _n('jour', 'jours', $interval->d, 'slaaap-google-reviews-importer');
    } else {
        return $interval->h . ' ' . _n('heure', 'heures', $interval->h, 'slaaap-google-reviews-importer');
    }
}

function review_time_shortcode($atts) {
    $atts = shortcode_atts(array(
        'review_time' => '',
    ), $atts);

    $timestamp = intval($atts['review_time']);
    if ($timestamp <= 0) {
        return 'Temps inconnu';
    }

    return 'Il y a ' . calculate_time_elapsed($timestamp);
}

add_shortcode('review_time_elapsed', 'review_time_shortcode');
