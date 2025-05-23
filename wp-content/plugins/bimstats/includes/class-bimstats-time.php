<?php

// Ce fichier s'occupe du suivi du temps passé sur un post par l'utilisateur et de l'envoi des données via AJAX.

if (!defined('ABSPATH')) {
    exit;
}

class BimStats_Time {

    public static function register_time_actions() {
        add_action('wp_footer', array(__CLASS__, 'track_time_with_js'));
        add_action('wp_ajax_bimstats_time_spent', array(__CLASS__, 'save_time_spent'));
        add_action('wp_ajax_nopriv_bimstats_time_spent', array(__CLASS__, 'save_time_spent'));
    }

    public static function track_time_with_js() {
        global $post;

        // Restreindre à seulement les posts, pages, et landing pages Elementor
        if (is_singular(array('post', 'page', 'e-landing-page'))) {
            ?>
            <script type="text/javascript">
                (function() {
                    // Vérifier si la variable 'bimstats_admin_user' existe dans le localStorage
                    if (localStorage.getItem('bimstats_admin_user') === null) {
                        var startTime = new Date().getTime();
                        var postID = '<?php echo $post->ID; ?>';
                        var cookieName = 'bimstats_time_' + postID;

                        // Vérifier si le cookie existe pour ne compter que la première visite sur la page pendant la durée du cookie
                        if (document.cookie.indexOf(cookieName) === -1) {
                            // Fonction pour envoyer le temps passé via AJAX
                            function sendTimeSpent() {
                                var endTime = new Date().getTime();
                                var timeSpent = Math.floor((endTime - startTime) / 1000); // Temps en secondes

                                jQuery.post(
                                    '<?php echo admin_url('admin-ajax.php'); ?>',
                                    {
                                        'action': 'bimstats_time_spent',
                                        'post_id': postID,
                                        'time_spent': timeSpent
                                    }
                                );

                                // Définir un cookie après avoir envoyé le temps, valide pour 20 minutes
                                document.cookie = cookieName + "=true; path=/; max-age=" + (60 * 20);
                            }

                            // Envoyer le temps passé lorsque l'utilisateur quitte la page
                            window.addEventListener('beforeunload', sendTimeSpent);
                        } 
                    }else{
                        console.log("L'utilisateur est un admin, temps passé non enregistré.");
                    }
                })();
            </script>
            <?php
        }
    }

    public static function save_time_spent() {
        // Vérifier que les données ont été envoyées correctement
        if (isset($_POST['post_id']) && isset($_POST['time_spent'])) {
            $postID = intval($_POST['post_id']);
            $time_spent = intval($_POST['time_spent']);
            
            // Récupérer l'IP du visiteur pour le débogage
            $visitor_ip = BimStats_Utils::get_visitor_ip();
    
            // Exclure les robots
            if (BimStats_Utils::is_bot()) {
                BimStats_Utils::bs_log("Exclusion d'un robot détecté pour le post ID: {$postID} depuis l'IP: {$visitor_ip}");
                wp_send_json_error("Visite par un robot.");
                wp_die(); // Terminer l'exécution ici
            }
    
            // Récupérer le contenu du post pour calculer la durée de lecture
            $post = get_post($postID);
            $content = $post->post_content;
    
            // Compter le nombre de mots et estimer la durée de lecture
            $word_count = str_word_count(strip_tags($content));
            $words_per_minute = 400; // Vous pouvez ajuster cette valeur
            $estimated_reading_time = ceil($word_count / $words_per_minute) * 60; // Durée de lecture en secondes
    
            // Limiter le temps passé à la durée de lecture estimée
            if ($time_spent > $estimated_reading_time) {
                $time_spent = $estimated_reading_time;
            }
    
            // Récupérer le temps moyen actuel et le nombre de vues (qui remplace total_visits)
            $average_time = get_post_meta($postID, 'bimstats_avg_time', true) ?: 0;
            $total_views = get_post_meta($postID, 'bimstats_post_views', true) ?: 0;
    
            // S'assurer que total_views n'est pas à zéro pour éviter la division par zéro
            if ($total_views > 0) {
                // Calculer le nouveau temps moyen en fonction du temps passé et des vues actuelles
                $new_avg_time = (($average_time * ($total_views - 1)) + $time_spent) / $total_views;
    
                // Mettre à jour le temps moyen dans la base de données
                update_post_meta($postID, 'bimstats_avg_time', $new_avg_time);
    
                // Enregistrer un log du temps passé et des mises à jour
                BimStats_Utils::bs_log("Temps passé ajouté pour le post ID: {$postID} depuis l'IP: {$visitor_ip}. Temps: {$time_spent} secondes. Nombre de vues: {$total_views}. Temps moyen mis à jour: {$new_avg_time} secondes.");
            } else {
                BimStats_Utils::bs_log("Erreur : Nombre de vues est à zéro pour le post ID: {$postID}. Impossible de calculer le temps moyen depuis l'IP: {$visitor_ip}.");
            }
        } else {
            // Log si les données envoyées sont manquantes
            BimStats_Utils::bs_log("Erreur : post_id ou time_spent non définis depuis l'IP: {$visitor_ip}");
        }
    
        wp_die(); // Terminer correctement la requête AJAX
    }
                
}
