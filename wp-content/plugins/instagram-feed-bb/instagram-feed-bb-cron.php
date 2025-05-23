<?php

/**
 * Synchronise les posts Instagram en interrogeant l'API et en mettant à jour les Custom Posts.
 */
function instagram_feed_bb_sync_posts() {
    write_insta_log('Début de la synchronisation des posts Instagram', null, 'info');

    $username    = get_option('instagram_username');
    $rapidapiKey = get_option('rapidapi_key');

    if (!$username || !$rapidapiKey) {
        write_insta_log('Erreur : Nom d\'utilisateur Instagram ou clé RapidAPI manquants', null, 'error');
        return;
    }

    $url = 'https://instagram-scraper-api2.p.rapidapi.com/v1.2/posts?username_or_id_or_url=' 
           . urlencode($username) . '&url_embed_safe=true';

    $args = array(
        'headers' => array(
            'X-Rapidapi-Key'  => $rapidapiKey,
            'X-Rapidapi-Host' => 'instagram-scraper-api2.p.rapidapi.com',
        ),
        'timeout' => 20,
    );

    $response = wp_remote_get($url, $args);

    if (is_wp_error($response)) {
        write_insta_log('Erreur lors de la récupération des posts Instagram', $response->get_error_message(), 'error');
        return;
    }

    $data = json_decode(wp_remote_retrieve_body($response));
    if (!$data || isset($data->error)) {
        $error_message = isset($data->error->message) ? $data->error->message : 'Aucune information d\'erreur disponible.';
        write_insta_log('Erreur lors de la récupération des posts Instagram', $error_message, 'error');
        return;
    }

    $posts = $data->data->items;
    if (!$posts) {
        write_insta_log('Aucun post trouvé', null, 'error');
        return;
    }

    write_insta_log('Posts récupérés avec succès', null, 'info');

    // Garder seulement les 12 premiers posts récupérés et inverser leur ordre
    $posts = array_slice($posts, 0, 12);
    $posts = array_reverse($posts); // Du plus ancien au plus récent

    foreach ($posts as $insta_post) {
        // Afficher dans le log le JSON complet du post pour débogage
        // write_insta_log('DEBUG: Post JSON: ' . json_encode($insta_post), null, 'info');
    
        $instagramCode = $insta_post->code;
        // Nouvel identifiant unique pour l'image (issu du JSON Instagram)
        $instagramImageId = isset($insta_post->id) ? $insta_post->id : '';
    
        // Rechercher le custom post associé à ce post Instagram
        $existingPosts = get_posts(array(
            'post_type'   => 'instagram_post',
            'meta_key'    => 'instagram_code',
            'meta_value'  => $instagramCode,
            'numberposts' => 1
        ));
    
        if (!empty($existingPosts)) {
            $wp_post = $existingPosts[0];
        } else {
            $wp_post_id = wp_insert_post(array(
                'post_type'   => 'instagram_post',
                'post_title'  => 'Instagram Post ' . $instagramCode,
                'post_status' => 'publish'
            ));
            $wp_post = get_post($wp_post_id);
            update_post_meta($wp_post->ID, 'instagram_code', $instagramCode);
        }
    
        $likeCount    = isset($insta_post->like_count) ? $insta_post->like_count : 0;
        $commentCount = isset($insta_post->comment_count) ? $insta_post->comment_count : 0;
        update_post_meta($wp_post->ID, 'like_count', $likeCount);
        update_post_meta($wp_post->ID, 'comment_count', $commentCount);
    
        $mediaType = $insta_post->media_type;
        $image_url = '';
        if ($mediaType == 1 && isset($insta_post->image_versions->items[0]->url)) {
            $image_url = $insta_post->image_versions->items[0]->url;
        } elseif ($mediaType == 2 && isset($insta_post->image_versions->items[0]->url)) {
            $image_url = $insta_post->image_versions->items[0]->url;
        } elseif ($mediaType == 8 && isset($insta_post->carousel_media[0]->image_versions->items[0]->url)) {
            $image_url = $insta_post->carousel_media[0]->image_versions->items[0]->url;
        }
    
        if ($image_url) {
            // Vérifier que l'URL est bien formée
            if (!filter_var($image_url, FILTER_VALIDATE_URL)) {
                write_insta_log('L\'URL n\'est pas valide : ' . $image_url, null, 'error');
            } else {
                // Vérifier l'accessibilité de l'image avec une requête HEAD
                $head_response = wp_remote_head($image_url);
                if (is_wp_error($head_response)) {
                    write_insta_log('L\'image n\'est pas accessible : ' . $image_url, $head_response->get_error_message(), 'error');
                } else {
                    $contentType = wp_remote_retrieve_header($head_response, 'content-type');
                    if (strpos($contentType, 'image') === false) {
                        write_insta_log('Le type MIME n\'indique pas une image pour l\'URL : ' . $image_url, null, 'error');
                    } else {
                        // Vérifier si l'image a déjà été importée en se basant sur l'identifiant unique (instagram_image_id)
                        $existingAttachments = get_posts(array(
                            'post_type'      => 'attachment',
                            'meta_key'       => 'instagram_image_id',
                            'meta_value'     => $instagramImageId,
                            'posts_per_page' => 1,
                            'fields'         => 'ids'
                        ));
                        if (!empty($existingAttachments)) {
                            $attachment_id = $existingAttachments[0];
                            write_insta_log('Image déjà importée pour l\'ID ' . $instagramImageId, null, 'info');
                        } else {
                            // Importation manuelle de l'image
                            require_once(ABSPATH . 'wp-admin/includes/file.php');
                            require_once(ABSPATH . 'wp-admin/includes/media.php');
                            require_once(ABSPATH . 'wp-admin/includes/image.php');
    
                            $desc = 'Instagram image ' . $instagramCode;
    
                            // Récupérer l'extension du fichier depuis l'URL ou via le type MIME
                            $file_ext = pathinfo($image_url, PATHINFO_EXTENSION);
                            if (empty($file_ext)) {
                                switch ($contentType) {
                                    case 'image/jpeg':
                                    case 'image/jpg':
                                        $file_ext = 'jpg';
                                        break;
                                    case 'image/png':
                                        $file_ext = 'png';
                                        break;
                                    case 'image/gif':
                                        $file_ext = 'gif';
                                        break;
                                    default:
                                        $file_ext = 'jpg';
                                        break;
                                }
                            }
    
                            // Télécharger l'image dans un fichier temporaire
                            $temp_file = download_url($image_url);
                            if (is_wp_error($temp_file)) {
                                write_insta_log('Erreur téléchargement de l\'image pour l\'URL ' . $image_url, $temp_file->get_error_message(), 'error');
                                $attachment_id = 0;
                            } else {
                                $file_array = array();
                                $file_array['name']     = 'instagram_' . $instagramCode . '.' . $file_ext;
                                $file_array['tmp_name'] = $temp_file;
    
                                $attachment_id = media_handle_sideload($file_array, $wp_post->ID, $desc);
                                if (is_wp_error($attachment_id)) {
                                    write_insta_log('Erreur traitement de l\'image pour l\'URL ' . $image_url, $attachment_id->get_error_message(), 'error');
                                    @unlink($temp_file);
                                    $attachment_id = 0;
                                } else {
                                    // Stocker l'identifiant unique dans un custom field pour la vérification future
                                    update_post_meta($attachment_id, 'instagram_image_id', $instagramImageId);
                                    write_insta_log('Image importée avec succès pour l\'ID ' . $instagramImageId, null, 'info');
                                }
                            }
                        }
                        if (isset($attachment_id) && $attachment_id) {
                            update_post_meta($wp_post->ID, 'image_attachment_id', $attachment_id);
                        }
                    }
                }
            }
        }
    
        $permalink = 'https://www.instagram.com/p/' . $instagramCode . '/';
        update_post_meta($wp_post->ID, 'post_url', $permalink);
    }
        
    // Récupérer tous les posts Instagram triés par date décroissante
    $all_posts = get_posts(array(
        'post_type'      => 'instagram_post',
        'posts_per_page' => -1,  // Tous les posts pour évaluation
        'orderby'        => 'date',
        'order'          => 'DESC'
    ));

    // Supprimer tous les posts à partir du 13ème (index 12 et suivants)
    if (count($all_posts) > 12) {
        $old_posts = array_slice($all_posts, 12);  // Conserver les 12 plus récents

        // Debug : journaliser les posts identifiés pour suppression
        $debug_info = array();
        foreach ($old_posts as $old_post) {
            $debug_info[] = array(
                'ID'    => $old_post->ID,
                'Title' => get_the_title($old_post->ID),
                'Date'  => get_the_date('Y-m-d H:i:s', $old_post->ID)
            );
        }
        write_insta_log('DEBUG : Posts identifiés pour suppression : ' . print_r($debug_info, true), null, 'info');

        // Supprimer les posts
        foreach ($old_posts as $old_post) {
            wp_delete_post($old_post->ID, true);
        }
        write_insta_log(count($old_posts) . ' anciens posts supprimés', null, 'info');
    } else {
        write_insta_log('Aucun post à supprimer. Moins de 13 posts existants.', null, 'info');
    }

    write_insta_log('Synchronisation terminée avec succès', null, 'info');
}

/**
 * Ajoute un intervalle de cron personnalisé basé sur la fréquence définie dans les options.
 */
add_filter('cron_schedules', 'instagram_feed_bb_custom_cron_schedule');
function instagram_feed_bb_custom_cron_schedule($schedules) {
    // Récupère la fréquence définie dans les options (en secondes)
    $interval = (int) get_option('cache_duration', 43200); // Par défaut 43200 sec (12 heures)

    // Définir un nouvel intervalle de cron personnalisé
    $schedules['custom_interval'] = array(
        'interval' => $interval,
        'display'  => 'Interval personnalisé basé sur la fréquence définie'
    );

    return $schedules;
}

/**
 * Planifie la tâche récurrente à l'activation du plugin.
 */
function instagram_feed_bb_activate() {
    write_insta_log('Fonction d\'activation appelée', null, 'info');

    if (!wp_next_scheduled('instagram_feed_bb_cron_hook')) {
        write_insta_log('Aucun événement cron existant trouvé, planification en cours...', null, 'info');
        $result = wp_schedule_event(time(), 'custom_interval', 'instagram_feed_bb_cron_hook');
        if ($result === false) {
            write_insta_log('Échec de la planification de l\'événement cron.', null, 'error');
        } else {
            write_insta_log('Événement cron planifié avec succès.', null, 'info');
        }
    } else {
        write_insta_log('Un événement cron est déjà programmé.', null, 'info');
    }
}
register_activation_hook(__FILE__, 'instagram_feed_bb_activate');

/**
 * Annule la tâche récurrente à la désactivation du plugin.
 */
function instagram_feed_bb_deactivate() {
    write_insta_log('Fonction de désactivation appelée', null, 'info');

    $timestamp = wp_next_scheduled('instagram_feed_bb_cron_hook');
    if ($timestamp) {
        wp_unschedule_event($timestamp, 'instagram_feed_bb_cron_hook');
        write_insta_log('Événement cron annulé.', null, 'info');
    } else {
        write_insta_log('Aucun événement cron trouvé lors de la désactivation.', null, 'info');
    }
}
register_deactivation_hook(__FILE__, 'instagram_feed_bb_deactivate');

// Lier le hook cron à la fonction de synchronisation
add_action('instagram_feed_bb_cron_hook', 'instagram_feed_bb_sync_posts');
