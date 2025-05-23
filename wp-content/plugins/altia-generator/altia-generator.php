<?php
/*
Plugin Name: Altia Generator
Description: Plugin pour générer des balises ALT pour les images à l'aide de l'API Imagga.
Version: 1.2.9
Author: BimBeau
*/

// Ajouter une page de réglages pour le plugin
function altia_add_admin_menu() {
    add_options_page(
        'Réglages Altia Generator', // Titre de la page
        'Altia Generator',          // Titre du menu
        'manage_options',           // Capacité requise
        'altia-generator-settings', // Slug du menu
        'altia_settings_page'       // Fonction de rappel qui affiche la page
    );
}
add_action('admin_menu', 'altia_add_admin_menu');

/**
 * Afficher la page de réglages
 */
function altia_settings_page() {
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Réglages Altia Generator', 'altia-generator'); ?></h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('altia-generator-settings-group');
            do_settings_sections('altia-generator-settings-group');

            // Champs pour les clés API
            $api_key = get_option('altia_imagga_api_key', '');
            $api_secret = get_option('altia_imagga_api_secret', '');
            ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php esc_html_e('Clé API Imagga', 'altia-generator'); ?></th>
                    <td><input type="text" name="altia_imagga_api_key" value="<?php echo esc_attr($api_key); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php esc_html_e('Secret API Imagga', 'altia-generator'); ?></th>
                    <td><input type="text" name="altia_imagga_api_secret" value="<?php echo esc_attr($api_secret); ?>" /></td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>

        <form method="post" action="">
            <?php
            // Protection contre les attaques CSRF
            wp_nonce_field('altia_clear_meta_nonce', 'altia_clear_meta_nonce_field');

            // Bouton pour vider la méta-donnée _altia_processed
            ?>
            <h2><?php esc_html_e('Supprimer les métadonnées traitées', 'altia-generator'); ?></h2>
            <p><?php esc_html_e('Cliquez sur le bouton ci-dessous pour supprimer les métadonnées "_altia_processed" de toutes les images de la bibliothèque de médias.', 'altia-generator'); ?></p>
            <input type="submit" name="altia_clear_meta" class="button button-primary" value="<?php esc_attr_e('Supprimer les métadonnées', 'altia-generator'); ?>" />
        </form>
    </div>
    <?php
}

/**
 * Enregistrer les réglages
 */
function altia_register_settings() {
    register_setting('altia-generator-settings-group', 'altia_imagga_api_key');
    register_setting('altia-generator-settings-group', 'altia_imagga_api_secret');
}
add_action('admin_init', 'altia_register_settings');

/**
 * Gérer la soumission du formulaire pour vider la méta-donnée _altia_processed
 */
function altia_handle_clear_meta() {
    // Vérifiez si le formulaire a été soumis et si le nonce est valide
    if (isset($_POST['altia_clear_meta']) && check_admin_referer('altia_clear_meta_nonce', 'altia_clear_meta_nonce_field')) {
        // Appeler la fonction pour vider les métadonnées
        altia_clear_all_processed_meta();

        // Afficher un message de confirmation
        add_action('admin_notices', 'altia_meta_cleared_notice');
    }
}
add_action('admin_init', 'altia_handle_clear_meta');

// Fonction pour afficher un message de confirmation
function altia_meta_cleared_notice() {
    ?>
    <div class="notice notice-success is-dismissible">
        <p><?php esc_html_e('Toutes les métadonnées "_altia_processed" ont été supprimées de toutes les images.', 'altia-generator'); ?></p>
    </div>
    <?php
}

/**
 * Fonction pour vider la méta-donnée '_altia_processed' de toutes les images 
 */
function altia_clear_all_processed_meta() {
    // Obtenir toutes les images de la bibliothèque de médias
    $args = array(
        'post_type'      => 'attachment',
        'post_mime_type' => 'image',
        'post_status'    => 'inherit',
        'numberposts'    => -1,
    );
    $images = get_posts($args);

    // Parcourir toutes les images
    foreach ($images as $image) {
        // Supprimer la méta-donnée '_altia_processed' associée à l'image
        delete_post_meta($image->ID, '_altia_processed');
    }
}

/**
 * Active les mises à jour automatiques pour ce plugin.
 */
function altia_auto_update_plugin($update, $item) {
    // Spécifiez les slugs des plugins que vous voulez mettre à jour automatiquement
    $auto_update_plugins = array(
        'altia-generator',
    );

    // Si le slug de ce plugin est dans le tableau des mises à jour automatiques,
    // alors retournez true pour autoriser la mise à jour automatique.
    if (in_array($item->slug, $auto_update_plugins)) {
        return true;
    }

    // Sinon, retournez la valeur d'origine.
    return $update;
}
add_filter('auto_update_plugin', 'altia_auto_update_plugin', 10, 2);

/**
 * Vérifie si une mise à jour du plugin est disponible.
 */
function altia_check_for_plugin_update($checked_data) {
    global $wp_version;

    // URL de l'API sur votre serveur privé
    $api_url = 'https://wordpress.bimbeau.fr/plugins/altia-generator/update-check.php';

    // Les données à envoyer à l'API
    $request_args = array(
        'slug' => 'altia-generator',
        'version' => isset($checked_data->checked['altia-generator/altia-generator.php']) ? $checked_data->checked['altia-generator/altia-generator.php'] : '',
    );

    // Réponse de l'API
    $response = wp_remote_post($api_url, array(
        'body' => json_encode($request_args),
        'user-agent' => 'WordPress/' . $wp_version,
    ));

    // Vérifier la réponse et gérer les erreurs ou les réponses mal formées
    if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) == 200) {
        $body = wp_remote_retrieve_body($response);
        $update_data = json_decode($body);
        // Vérifiez si $update_data contient les propriétés nécessaires et que la mise à jour est nécessaire
        if (is_object($update_data) && property_exists($update_data, 'new_version') && property_exists($update_data, 'update')) {
            $current_version = isset($checked_data->checked['altia-generator/altia-generator.php']) ? $checked_data->checked['altia-generator/altia-generator.php'] : '0.0.0';
            if ($update_data->update && version_compare($update_data->new_version, $current_version, '>')) {
                // Assurez-vous que l'objet $checked_data->response est initialisé correctement
                if (!isset($checked_data->response) || !is_array($checked_data->response)) {
                    $checked_data->response = array();
                }
                $checked_data->response['altia-generator/altia-generator.php'] = $update_data;
            }
        } else {
            // Log d'une réponse API incomplète ou incorrecte
            altia_log("La réponse de l'API de mise à jour est incomplète ou mal formée.");
        }
    } else {
        // Log des erreurs de communication avec l'API
        altia_log("Erreur lors de la vérification des mises à jour : " . wp_remote_retrieve_response_message($response));
    }

    return $checked_data;
}
add_filter('pre_set_site_transient_update_plugins', 'altia_check_for_plugin_update');

/**
 * Gère les appels API pour récupérer les informations de mise à jour du plugin.
 */
function altia_plugin_api_call($def, $action, $args) {
    global $wp_version;
    // URL de l'API sur votre serveur privé
    $api_url = 'https://wordpress.bimbeau.fr/plugins/altia-generator/update-info.php';

    if ($action == 'plugin_information' && isset($args->slug) && $args->slug == 'altia-generator') {
        $response = wp_remote_post($api_url, array(
            'body' => json_encode(array('slug' => 'altia-generator')),
            'user-agent' => 'WordPress/' . $wp_version,
        ));

        if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) == 200) {
            $plugin_info = json_decode(wp_remote_retrieve_body($response));
            return $plugin_info;
        }
    }

    return $def;
}
add_filter('plugins_api', 'altia_plugin_api_call', 10, 3);

/**
 * Génère des balises ALT pour les images non traitées dans la bibliothèque multimédia.
 */
function altia_generate_alt_tags_task() {

    // Récupérez toutes les images sans balise alt de la bibliothèque multimédia
    $args = array(
        'post_type' => 'attachment',
        'post_status' => 'inherit',
        'post_mime_type' => array('image/jpeg', 'image/png', 'image/webp'), // Ajout du support pour les images WebP
        'meta_query' => array(
            'relation' => 'AND',  // Les deux conditions principales doivent être remplies
            array(
                'relation' => 'OR',  // L'une des deux sous-conditions doit être remplie
                array(
                    'key' => '_wp_attachment_image_alt',
                    'compare' => 'NOT EXISTS'  // _wp_attachment_image_alt n'existe pas
                ),
                array(
                    'key' => '_wp_attachment_image_alt',
                    'value' => '',
                    'compare' => '='  // _wp_attachment_image_alt est égal à une chaîne vide
                )
            ),
            array(
                'key' => '_altia_processed',
                'compare' => 'NOT EXISTS'  // _altia_processed n'existe pas
            ),
            array(
                'key' => '_wp_attached_file',
                'value' => 'elementor/screenshots/',
                'compare' => 'NOT LIKE', // Exclure les images dont le chemin contient 'elementor/screenshots/'
            ),
        ),
        'posts_per_page' => 100
    );

    $images = get_posts($args);

    // Journalise le nombre total d'images à traiter
    $total_images = count($images);
    altia_log("Nombre total d'images à traiter : " . $total_images);

    $images_remaining = $total_images;

    foreach ($images as $image) {

        // Décrémentez le nombre d'images restantes
        $images_remaining--;

        // Vérifiez si l'élément en cours de traitement est une image
        if (!wp_attachment_is_image($image->ID)) {
            altia_log("L'élément {$image->ID} n'est pas une image, il est ignoré.");
            altia_log("Images restantes : " . $images_remaining);
            continue;
        }

        // Récupérez la balise ALT actuelle
        $current_alt_text = get_post_meta($image->ID, '_wp_attachment_image_alt', true);

        $image_url = wp_get_attachment_url($image->ID);

        // Vérifiez si la balise ALT actuelle est vide
        if (empty($current_alt_text)) {

            // Journalise l'URL de l'image en cours de traitement
            altia_log("Traitement de l'image : {$image_url}");

            // Effectuez une requête vers l'API pour obtenir la balise ALT
            $alt_text = altia_get_alt_text_from_api($image_url);

            // Vérifiez si la limite API est atteinte
            if ($alt_text === false) {
                altia_log("La limite mensuelle de l'API Imagga a été atteinte. Arrêt du traitement.");
                break; // Sortir de la boucle
            }

            // Vérifiez si la balise ALT a été générée avec succès
            if (!empty($alt_text)) {
                // Journalise la balise ALT générée
                altia_log("balise ALT générée : {$alt_text}");

                // Mettez à jour la balise ALT de l'image dans la langue actuelle
                update_post_meta($image->ID, '_wp_attachment_image_alt', $alt_text);

                // Marquez l'image comme traitée
                update_post_meta($image->ID, '_altia_processed', 'true');
            } else {
                // Ne pas marquer l'image comme traitée pour pouvoir réessayer plus tard
                altia_log("La balise ALT n'a pas pu être générée pour l'image : {$image_url}");
            }

            // Journalise la fin du traitement de l'image
            altia_log("Images restantes : " . $images_remaining);
        } else {
            // Si la balise ALT actuelle n'est pas vide, passez à l'image suivante sans rien faire
            altia_log("L'image {$image_url} possède déjà une balise ALT. Elle est ignorée.");
            altia_log("Images restantes : " . $images_remaining);
        }
    }
}

/**
 * Récupère la balise ALT d'une image à partir de l'API.
 */
function altia_get_alt_text_from_api($image_url) {

    // Récupérer les clés API depuis les options
    $api_key = get_option('altia_imagga_api_key', '');
    $api_secret = get_option('altia_imagga_api_secret', '');

    if (empty($api_key) || empty($api_secret)) {
        altia_log("Les clés API Imagga ne sont pas définies.");
        return '';
    }

    $auth_string = base64_encode("$api_key:$api_secret");

    // Récupérer le contenu de l'image
    $response = wp_remote_get($image_url);
    if (is_wp_error($response)) {
        $error_message = $response->get_error_message();
        altia_log("Erreur lors de la récupération de l'image : {$image_url} : {$error_message}");
        return '';
    }

    $image_content = wp_remote_retrieve_body($response);
    if (empty($image_content)) {
        altia_log("Le contenu de l'image est vide : {$image_url}");
        return '';
    }

    // Encoder les données de l'image en base64
    $image_data = base64_encode($image_content);
    if (empty($image_data)) {
        altia_log("Erreur lors de l'encodage en base64 de l'image : {$image_url}");
        return '';
    }

    // Préparer les paramètres pour l'appel à l'API
    $language = substr(get_bloginfo('language'), 0, 2);
    $api_url = 'https://api.imagga.com/v2/tags';
    $headers = array(
        'Accept' => 'application/json',
        'Authorization' => 'Basic ' . $auth_string,
    );
    $args = array(
        'body' => array(
            'image_base64' => $image_data,
            'language' => $language,
        ),
        'headers' => $headers,
        'timeout' => 90,
    );

    // Faire la requête POST à l'API
    $response = wp_remote_post($api_url, $args);

    // Vérifier les erreurs de la requête
    if (is_wp_error($response)) {
        $error_message = $response->get_error_message();
        altia_log("Erreur lors de l'appel à l'API Imagga pour l'image {$image_url} : {$error_message}");
        return '';
    }

    // Vérifier le code de réponse HTTP
    $response_code = wp_remote_retrieve_response_code($response);
    if ($response_code != 200) {
        $response_message = wp_remote_retrieve_response_message($response);
        $response_body = wp_remote_retrieve_body($response); // Capturer le corps de la réponse
        altia_log("Erreur HTTP {$response_code} lors de l'appel à l'API Imagga pour l'image {$image_url} : {$response_message}");
        altia_log("Réponse de l'API : {$response_body}");

        // Vérifier si la limite est atteinte
        $data = json_decode($response_body, true);
        if (isset($data['status']['type']) && $data['status']['type'] == 'error' && strpos($data['status']['text'], 'reached your monthly limits') !== false) {
            // Retourner une valeur spéciale pour indiquer que la limite est atteinte
            return false;
        }

        return '';
    }

    // Analyser la réponse JSON
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    // Vérifier les erreurs dans la réponse de l'API
    if (isset($data['status']['type']) && $data['status']['type'] == 'error') {
        $error_message = isset($data['status']['text']) ? $data['status']['text'] : 'Erreur inconnue';
        altia_log("Erreur de l'API Imagga pour l'image {$image_url} : {$error_message}");
        return '';
    }

    // Vérifiez si des tags ont été retournés
    if (!empty($data['result']['tags'])) {
        // Récupérer le premier tag (le plus pertinent) et le retourner comme texte ALT
        $tag = $data['result']['tags'][0]['tag'];
        $alt_text = isset($tag[$language]) ? $tag[$language] : reset($tag);

        // Si le texte ALT est vide, journaliser l'information
        if (empty($alt_text)) {
            altia_log("Le tag retourné par l'API est vide pour l'image : {$image_url}");
        }

        return $alt_text;
    } else {
        altia_log("Aucun tag retourné par l'API pour l'image : {$image_url}");
    }

    return '';
}

// Planifie la tâche cron lors de l'activation du plugin
function altia_schedule_cron() {
    if (!wp_next_scheduled('altia_generate_alt_tags_event')) {
        wp_schedule_event(time(), 'hourly', 'altia_generate_alt_tags_event');
    }
}
register_activation_hook(__FILE__, 'altia_schedule_cron');

// Ajoutez un gestionnaire pour l'événement planifié
add_action('altia_generate_alt_tags_event', 'altia_generate_alt_tags_task');

// Désactivez la tâche planifiée lors de la désactivation du plugin
register_deactivation_hook(__FILE__, 'altia_unschedule_cron');

function altia_unschedule_cron() {
    $timestamp = wp_next_scheduled('altia_generate_alt_tags_event');
    wp_unschedule_event($timestamp, 'altia_generate_alt_tags_event');
}

/**
 * Journalise les messages dans un fichier de log.
 */
function altia_log($message) {
    // Définissez le chemin vers le fichier de journal, vous pouvez personnaliser ce chemin
    $log_file = plugin_dir_path(__FILE__) . 'altia-log.txt';

    // Limite de taille du fichier de log (en octets)
    $log_size_limit = 5 * 1024 * 1024; // 5 Mo

    // Vérifiez si le fichier de log existe et dépasse la limite de taille
    if (file_exists($log_file) && filesize($log_file) > $log_size_limit) {
        // Si le fichier dépasse la limite, videz-le
        file_put_contents($log_file, '');
    }

    // Format de date et heure
    $date_time = date('Y-m-d H:i:s');

    // Message complet à enregistrer
    $log_entry = "[{$date_time}] {$message}\n";

    // Enregistrez le message dans le fichier de journal
    error_log($log_entry, 3, $log_file);
}
