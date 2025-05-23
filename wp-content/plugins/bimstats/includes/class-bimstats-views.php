<?php

// Ce fichier gère les vues des posts pour le plugin BimStats. 
// Il inclut l'incrémentation des vues via une requête AJAX afin d'éviter les interférences avec la mise en cache des pages. 
// Le compteur de vues est mis à jour de manière dynamique en fonction de l'ID du post en cours, même si la page est servie depuis le cache.

if (!defined('ABSPATH')) {
    exit;
}

class BimStats_Views {

    public static function register_view_actions() {
        // Ajouter un script AJAX à la fin de chaque page
        add_action('wp_footer', array(__CLASS__, 'enqueue_ajax_script'));
        // Gestion des requêtes AJAX pour les utilisateurs non connectés et connectés
        add_action('wp_ajax_nopriv_increment_views', array(__CLASS__, 'handle_ajax_request'));
        add_action('wp_ajax_increment_views', array(__CLASS__, 'handle_ajax_request'));
    }

    public static function enqueue_ajax_script() {
        global $post;
        if (is_singular(array('post', 'page', 'e-landing-page', 'news', 'projet', 'realisation'))) {
            ?>
            <script type="text/javascript">
                jQuery(document).ready(function($) {
                    // Vérifier si la variable 'bimstats_admin_user' existe dans le localStorage
                    if (localStorage.getItem('bimstats_admin_user') === null) {
                        var postID = '<?php echo $post->ID; ?>';
                        var postType = '<?php echo get_post_type($post->ID); ?>';
                        var cookieName = 'bimstats_viewed_' + postID;

                        // Vérifier si le cookie existe déjà pour cette page
                        if (document.cookie.indexOf(cookieName) === -1) {
                            // Envoyer la requête AJAX pour enregistrer la vue
                            $.post(
                                '<?php echo admin_url('admin-ajax.php'); ?>',
                                {
                                    'action': 'increment_views',
                                    'post_id': postID,
                                    'post_type': postType
                                },
                                function(response) {
                                    console.log("Vue enregistrée pour le post ID: " + postID);
                                    // Définir un cookie après avoir enregistré la vue
                                    // La durée du cookie est réglée sur 20 minutes (1200 secondes)
                                    document.cookie = cookieName + "=true; path=/; max-age=" + (60 * 20); // Cookie valable 20 minutes
                                }
                            );
                        } else {
                            console.log("Vue déjà enregistrée pour le post ID: " + postID);
                        }
                    }else{
                        console.log("L'utilisateur est un admin, vue non enregistrée.");
                    }
                });
            </script>
            <?php
        }
    }

    public static function handle_ajax_request() {

        // Vérifier que les informations nécessaires sont disponibles dans la requête
        if (isset($_POST['post_id']) && isset($_POST['post_type'])) {
            $postID = intval($_POST['post_id']);
            $post_type = sanitize_text_field($_POST['post_type']);
                
            // Exclure les robots
            if (BimStats_Utils::is_bot()) {
                BimStats_Utils::bs_log("Exclusion d'un robot détecté pour le post ID: {$postID} depuis l'IP: {$visitor_ip}");
                wp_send_json_error("Visite par un robot.");
                wp_die(); // Terminer l'exécution ici
            }
    
            // Vérifier que nous sommes sur un post, une page ou une landing page Elementor et que l'utilisateur n'est pas connecté
            if (in_array($post_type, ['post', 'page', 'e-landing-page', 'news', 'projet', 'realisation']) && !is_user_logged_in() && !empty($postID)) {
                self::increment_post_views($postID);
                wp_send_json_success("Vue ajoutée pour le post ID: {$postID}");
            } else {
                BimStats_Utils::bs_log("Conditions non remplies pour l'enregistrement de la vue pour le post ID: {$postID} depuis l'IP: {$visitor_ip}");
                wp_send_json_error("Conditions non remplies pour l'enregistrement de la vue.");
            }
        } else {
            BimStats_Utils::bs_log("Erreur : post_id ou post_type non défini depuis l'IP: {$visitor_ip}");
            wp_send_json_error("Erreur : post_id ou post_type non défini.");
        }
    
        wp_die(); // Terminer correctement la requête AJAX
    }
        
    private static function increment_post_views($postID) {
        $visitor_ip = BimStats_Utils::get_visitor_ip();
        $views = get_post_meta($postID, 'bimstats_post_views', true) ?: 0;
        update_post_meta($postID, 'bimstats_post_views', ++$views);
        BimStats_Utils::bs_log("Vue ajoutée pour le post ID: {$postID} depuis l'IP: {$visitor_ip}. Nombre de vues actuel: {$views}");
    }
}
