<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Désactive ces pages
 */

add_action('template_redirect', 'my_custom_disable_page');
function my_custom_disable_page() {
    global $wp_query;

    if (is_author()) {
        wp_redirect(get_option('home'), 301);
        exit;
    }
    if (is_attachment()) {
        wp_redirect(wp_get_attachment_url(get_queried_object_id()), 301);
        exit;
    }
}


/**
 * Désactive la compression des images
 */
add_filter('jpeg_quality', function ($arg) {
    return 100;
});

/**
 * Désactive les commentaires à moins que le thème enfant ne spécifie le contraire
 */
add_action('admin_init', function () {
    if (defined('ENABLE_COMMENTS') && ENABLE_COMMENTS) {
        // Si la constante est définie et vraie, ne désactive pas les commentaires
        return;
    }

    global $pagenow;
    if ($pagenow === 'edit-comments.php') {
        wp_redirect(admin_url());
        exit;
    }
    remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');
    foreach (get_post_types() as $post_type) {
        if (post_type_supports($post_type, 'comments')) {
            remove_post_type_support($post_type, 'comments');
            remove_post_type_support($post_type, 'trackbacks');
        }
    }
});
add_action('admin_menu', function () {
    if (defined('ENABLE_COMMENTS') && ENABLE_COMMENTS) {
        // Si la constante est définie et vraie, ne retire pas le menu des commentaires
        return;
    }
    remove_menu_page('edit-comments.php');
});
add_action('admin_bar_menu', function ($wp_admin_bar) {
    if (defined('ENABLE_COMMENTS') && ENABLE_COMMENTS) {
        // Si la constante est définie et vraie, ne retire pas l'option des commentaires de la barre d'admin
        return;
    }
    if (is_admin_bar_showing()) {
        $wp_admin_bar->remove_menu('comments');
    }
}, 70);


/**
 * Masque la barre d'admin pour les utilisateurs connectés
 */

add_action('show_admin_bar', '__return_false', 999);


/**
 * Désactive l'éditeur Gutenberg
 */

// Désactive l'éditeur dans les posts
add_filter('use_block_editor_for_post', '__return_false', 10);

// Décharge les ressources de Gutenberg
function remove_gutenberg_styles_scripts() {
    wp_dequeue_style('wp-block-library');
    wp_dequeue_style('wp-block-library-theme');
    wp_dequeue_script('wp-block-library');
}
add_action('wp_enqueue_scripts', 'remove_gutenberg_styles_scripts', 100);


/**
 * Redirige les utilisateurs à la connexion selon leur rôle
 */

function custom_login_redirect() {

    global $user;
    $role = $user->roles[0] ?? null;

    if ($role == 'subscriber') {
        return home_url(); // Les abonnés sont redirigés vers la page d'accueil
    } else {
        return admin_url(); // Les autres rôles vers le tableau de bord
    }
}
add_filter('login_redirect', 'custom_login_redirect');

/**
 * Autorise les Editeurs et Administrateurs à modifier la page de la politique de confidentialité
 */

add_action('map_meta_cap', 'custom_manage_privacy_options', 1, 4);
function custom_manage_privacy_options($caps, $cap, $user_id, $args) {
    if (!is_user_logged_in()) return $caps;

    $user_meta = get_userdata($user_id);
    if (array_intersect(['editor', 'administrator'], $user_meta->roles)) {
        if ('manage_privacy_options' === $cap) {
            $manage_name = is_multisite() ? 'manage_network' : 'manage_options';
            $caps = array_diff($caps, [$manage_name]);
        }
    }
    return $caps;
}

/**
 * Ajoute reCaptcha sur la page de connexion si les clés API sont définies
 */

// Ajouter le JavaScript de reCaptcha sur la page de connexion
function enqueue_recaptcha_script() {
    // Vérifier si les clés reCaptcha sont définies
    $site_key = get_option('elementor_pro_recaptcha_site_key');
    $secret_key = get_option('elementor_pro_recaptcha_secret_key');

    // Si l'une des clés est vide, ne pas enregistrer ni ajouter les scripts
    if (empty($site_key) || empty($secret_key)) {
        return;
    }

    // Enregistrer et ajouter le script reCaptcha
    wp_register_script('recaptcha-script', 'https://www.google.com/recaptcha/api.js', false, null, true);
    wp_enqueue_script('recaptcha-script');
}
add_action('login_enqueue_scripts', 'enqueue_recaptcha_script');

// Ajouter reCaptcha sur la page de connexion
function add_recaptcha_to_login_form() {
    $site_key = get_option('elementor_pro_recaptcha_site_key');

    // Vérifier si la clé du site est définie avant d'afficher le captcha
    if (!empty($site_key)) {
        echo '<div class="g-recaptcha" data-sitekey="' . esc_attr($site_key) . '"></div>';
    }
}
add_action('login_form', 'add_recaptcha_to_login_form');

// Vérifier le reCaptcha sur la page de connexion
function validate_recaptcha_on_login($user, $password) {
    // Vérifier si nous sommes sur une page de connexion standard WordPress ou sur une page de connexion WooCommerce
    if (!(is_wp_login_page() || is_page('mon-compte'))) {
        // Si nous ne sommes pas sur une page de connexion, retourner l'utilisateur sans vérifier le reCaptcha
        return $user;
    }

    $secret_key = get_option('elementor_pro_recaptcha_secret_key');

    // Vérifier si la clé secrète est définie avant de procéder à la validation
    if (empty($secret_key)) {
        return $user;
    }

    // Vérifier la réponse reCaptcha
    if (isset($_POST['g-recaptcha-response'])) {
        $captcha_response = sanitize_text_field($_POST['g-recaptcha-response']);
        $response = wp_remote_get("https://www.google.com/recaptcha/api/siteverify?secret={$secret_key}&response={$captcha_response}");

        // Vérifier si la requête a réussi
        if (is_wp_error($response)) {
            return new WP_Error('recaptcha_error', __('Une erreur est survenue lors de la vérification du captcha.', 'text-domain'));
        }

        $result = json_decode(wp_remote_retrieve_body($response), true);

        // Vérifier si la validation du captcha a réussi
        if ($result['success']) {
            return $user;
        } else {
            return new WP_Error('recaptcha_invalid', __('Captcha invalide.', 'text-domain'));
        }
    } else {
        return new WP_Error('recaptcha_empty', __('Captcha requis.', 'text-domain'));
    }
}

add_filter('wp_authenticate_user', 'validate_recaptcha_on_login', 10, 2);

// Vérifie si la page courante est la page de connexion WordPress
function is_wp_login_page() {
    return $GLOBALS['pagenow'] === 'wp-login.php';
}


/**
 * Retire les posts d'auteurs du sitemap
 */
add_filter('wp_sitemaps_add_provider', 'remove_from_sitemap', 10, 2);
function remove_from_sitemap($provider, $name) {
    if ('users' === $name) {
        return false;
    }
    return $provider;
}

/**
 * Personnalise le logo pour la page de login
 */
function my_login_logo() {
    // Obtenez l'ID de l'attachment du logo
    $custom_logo_id = get_theme_mod('custom_logo');

    // Dimensions maximales
    $max_width = 250;
    $max_height = 120;

    // Obtenir l'url du logo
    $logo_url = wp_get_attachment_image_url($custom_logo_id, 'full');

    // Si un logo personnalisé est défini
    if ($logo_url) {

        // Vérifiez si l'image est un SVG
        if (strpos($logo_url, '.svg') !== false) {
            // Récupérez les dimensions du SVG
            $file_path = get_attached_file($custom_logo_id);
            $svg_contents = file_get_contents($file_path);
            preg_match('/viewBox="0 0 ([\d.]+) ([\d.]+)"/', $svg_contents, $matches);
            $original_width = $matches[1];
            $original_height = $matches[2];
        } else {
            // Récupérez les dimensions des autres types d'images
            $metadata = wp_get_attachment_metadata($custom_logo_id);
            $original_width = $metadata['width'];
            $original_height = $metadata['height'];
        }

        // Rapport hauteur / largeur
        $logo_ratio = $original_width / $original_height;

        // Calculez la nouvelle largeur et la nouvelle hauteur en conservant le ratio original
        // tout en veillant à ce qu'elles ne dépassent pas les limites spécifiées
        if ($logo_ratio > ($max_width / $max_height)) {
            $new_width = $max_width;
            $new_height = $max_width / $logo_ratio;
        } else {
            $new_height = $max_height;
            $new_width = $max_height * $logo_ratio;
        }
    } else {
        $logo_url = get_template_directory_uri() . '/assets/images/default-image.jpg';
        $new_height = $max_height;
        $new_width = $max_width;
    }
?>
    <style type="text/css">
        #login h1 a,
        .login h1 a {
            background-image: url(<?= $logo_url; ?>);
            width: <?= $new_width; ?>px;
            height: <?= $new_height; ?>px;
            background-size: contain;
            pointer-events: none;
        }
    </style>
<?php
}
add_action('login_enqueue_scripts', 'my_login_logo');


/**
 * Désactive les notices indésirables
 */

define('DISABLE_NAG_NOTICES', true);

/**
 * Ajoute un encart personnalisé du site au début de la barre latérale.
 */
function bimbeau_add_menu_items() {
    global $menu;
    $menu_items = array();

    // Favicon et nom du site
    $site_url = get_home_url();
    $favicon_url = get_site_icon_url();
    $site_name = get_bloginfo('name');
    if ($favicon_url == '') {
        $favicon_url = get_template_directory_uri() . '/assets/images/default-favicon.jpg';
    }
    $site_info_menu_item = array(
        $site_name,
        'read',
        '#',  // L'URL n'est pas nécessaire ici car cet élément servira de titre et de favicon
        '',
        'menu-top menu-icon-generic site-info-wrapper',
        'menu-posts-custom_site_info',
        $favicon_url
    );

    array_push($menu_items, $site_info_menu_item);

    // Lien vers le site
    $site_link_menu_item = array(
        __('Visiter le site', 'hello-bimbeau'),
        'read',
        $site_url,
        '',
        'menu-top menu-icon-generic target_blank',
        'menu-posts-custom_site_link',
        'dashicons-external'  // Utiliser dashicons-external comme icône pour Visiter le site
    );

    array_push($menu_items, $site_link_menu_item);

    // Lien pour traduire le site
    if (class_exists('TRP_Translate_Press')) {
        $translate_menu_item = array(
            __('Traduire le site', 'hello-bimbeau'),
            'read',
            $site_url . '/?trp-edit-translation=true',
            '',
            'menu-top menu-icon-generic target_blank',
            'menu-posts-custom_translate',
            'dashicons-translation'
        );
        array_push($menu_items, $translate_menu_item);
    }

    // Lien pour supprimer le cache
    if (class_exists('\LiteSpeed\Purge')) {
        $purge_cache_menu_item = array(
            __('Vider le cache', 'hello-bimbeau'),
            'read',
            $site_url . '/wp-admin/?purge_cache=1',
            '',
            'menu-top menu-icon-generic',
            'menu-posts-custom_purge_cache',
            'dashicons-trash'
        );
        array_push($menu_items, $purge_cache_menu_item);
    }

    // Lien de déconnexion
    $logout_menu_item = array(
        __('Se déconnecter', 'hello-bimbeau'),
        'read',
        wp_logout_url(),
        '',
        'menu-top menu-icon-generic',
        'menu-posts-custom_logout',
        'dashicons-exit'
    );

    array_push($menu_items, $logout_menu_item);

    // Lien vers le mode de maintenance (mis à jour)
    $maintenance_mode = get_option('elementor_maintenance_mode_mode');
    if (($maintenance_mode == 'maintenance' || $maintenance_mode == 'coming_soon') && current_user_can('administrator')) {
        $maintenance_url = admin_url('admin.php?page=elementor-tools#tab-maintenance_mode');  // URL mis à jour
        $maintenance_menu_item = array(
            __('Mode Maintenance', 'hello-bimbeau'),
            'read',
            $maintenance_url,
            '',
            'menu-top menu-icon-generic',
            'menu-posts-custom_maintenance',
            'dashicons-hammer'  // Utiliser dashicons-hammer comme icône pour le mode maintenance
        );

        array_push($menu_items, $maintenance_menu_item);
    }

    // Ajout des liens pour modifier les sites du réseau
    if (is_multisite() && current_user_can('manage_network')) {
        $sites = get_sites(array('number' => 100)); // Vous pouvez ajuster le nombre de sites récupérés si nécessaire
        foreach ($sites as $site) {
            $blog_details = get_blog_details($site->blog_id);
            $edit_url = get_admin_url($site->blog_id);
            $edit_site_menu_item = array(
                $blog_details->blogname,
                'manage_sites',
                $edit_url,
                '',
                'menu-top menu-icon-generic',
                'menu-posts-custom_edit_site',
                'dashicons-admin-site'
            );
            array_push($menu_items, $edit_site_menu_item);
        }

        // Lien pour gérer le réseau
        $network_admin_url = network_admin_url();
        $manage_network_menu_item = array(
            __('Gérer le Réseau', 'hello-bimbeau'),
            'manage_network',
            $network_admin_url,
            '',
            'menu-top menu-icon-generic',
            'menu-posts-custom_manage_network',
            'dashicons-networking'
        );
        array_push($menu_items, $manage_network_menu_item);
    }

    // Séparateur
    $separator_menu_item = array(
        0   =>  '',
        1   =>  'read',
        2   =>  wp_logout_url(),
        3   =>  '',
        4   =>  'site-info-separator'
    );

    array_push($menu_items, $separator_menu_item);

    // Inversez l'ordre des éléments dans le tableau $menu_items
    $reversed_menu_items = array_reverse($menu_items);

    // Insérez les éléments de menu dans le menu admin
    foreach ($reversed_menu_items as $menu_item) {
        array_splice($menu, 0, 0, array($menu_item));
    }
}
add_action('admin_menu', 'bimbeau_add_menu_items', 100);


/**
 * Gère le clic sur l'élément de menu "Vider le cache"
 */
function bimbeau_handle_purge_cache() {
    // Vérifiez si l'utilisateur a cliqué sur l'élément de menu "Vider le cache"
    if (isset($_GET['purge_cache'])) {
        // Exécutez l'action pour purger le cache
        do_action('litespeed_purge_all');

        // Redirigez l'utilisateur vers la page d'accueil de l'admin avec un message de succès
        wp_redirect(admin_url('?cache_purged=1'));
        exit;
    }
}
add_action('admin_init', 'bimbeau_handle_purge_cache');


/**
 * Modifie le comportement de l'éditeur de post Admin
 */
function bimbeau_add_custom_admin_scripts() {
    // Vérifie si nous sommes sur une page d'édition de post
    $screen = get_current_screen();
    if ($screen->base !== 'post') {
        return;
    }
    ?>
    <style>
        /* Bouton flottant */
        #bimbeau-floating-button {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: var(--hover); /* Couleur du bouton */
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            border-radius: 5px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            cursor: pointer;
            display: none; /* Caché par défaut */
            z-index: 9999;
        }

        /* Animation d'apparition */
        .bimbeau-fade-in {
            animation: fadeInBottom 0.5s forwards;
        }

        /* Animation de disparition */
        .bimbeau-fade-out {
            animation: fadeOutBottom 0.5s forwards;
        }

        /* Keyframes */
        @keyframes fadeInBottom {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeOutBottom {
            from {
                opacity: 1;
                transform: translateY(0);
            }
            to {
                opacity: 0;
                transform: translateY(20px);
            }
        }
    </style>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            // Création du bouton flottant
            const floatingButton = $('<button>', {
                id: 'bimbeau-floating-button',
                click: function() {
                    $('#publishing-action .button').click();
                    // Simule un clic sur le bouton natif
                }
            }).appendTo('body');

            /**
            * Update the floating button label based on the native button label.
            */
            function updateFloatingButtonLabel() {
                const nativeButtonLabel = $('#publishing-action .button').val(); // Récupère le texte du bouton natif
                floatingButton.text(nativeButtonLabel);
            }

            // Synchronise immédiatement au chargement de la page
            updateFloatingButtonLabel();

            // Détection du scroll
            $(window).on('scroll', function() {
                let scrollPosition = $(window).scrollTop();

                if (scrollPosition > 100) {
                    // Affiche le bouton après 100px de scroll
                    if (!floatingButton.is(':visible')) {
                        updateFloatingButtonLabel(); // Met à jour le texte si nécessaire
                        floatingButton.removeClass('bimbeau-fade-out').addClass('bimbeau-fade-in').show();
                    }
                } else {
                    // Cache le bouton si le scroll revient en haut
                    if (floatingButton.is(':visible')) {
                        floatingButton.removeClass('bimbeau-fade-in').addClass('bimbeau-fade-out');
                        setTimeout(() => floatingButton.hide(), 500); // Cache après l'animation
                    }
                }
            });

            // Utilisation de MutationObserver pour surveiller les modifications du bouton natif
            const nativeButton = document.querySelector('#publishing-action .button');
            if (nativeButton) {
                // Création d'un observer pour détecter les mutations dans le DOM du bouton natif
                const observer = new MutationObserver(function(mutations) {
                    mutations.forEach(function(mutation) {
                        updateFloatingButtonLabel();
                    });
                });

                // Options de l'observer : surveille les modifications des enfants et des contenus textuels
                const config = { childList: true, subtree: true, characterData: true };
                observer.observe(nativeButton, config);
            }
        });
    </script>
    <?php
}
add_action('admin_footer', 'bimbeau_add_custom_admin_scripts');


/**
 * Vérifie si ACF est installé et activé. Sinon, affiche une notice dans l'admin.
 */
function check_acf_installed_notice() {
    // Vérifier si la fonction get_field (fournie par ACF) existe
    if (!function_exists('get_field')) {
        // Ajouter une notice d'erreur
        function acf_required_admin_notice() {
    ?>
            <div class="notice notice-error">
                <p><?php _e('Le plugin Advanced Custom Fields est requis pour que ce site fonctionne correctement. Veuillez l\'installer et l\'activer.', 'text-domain'); ?></p>
            </div>
<?php
        }
        add_action('admin_notices', 'acf_required_admin_notice');
    }
}
// Hook pour vérifier ACF au démarrage de l'admin
add_action('admin_init', 'check_acf_installed_notice');


/**
 * Désactive l'indexation de ces posts
 */
function add_noindex_to_posts() {
    // Vérifier si la fonction get_field d'ACF est disponible
    if (function_exists('get_field')) {
        $noindex = get_field("noindex_seo");
        if ($noindex == 1) {
            echo '<meta name="robots" content="noindex">';
            return;
        }
    }
    // Si ACF n'est pas disponible, ne rien faire
}
add_action('wp_head', 'add_noindex_to_posts');


/**
 * Personnalise le titre SEO des pages.
 */
function custom_title($title_parts) {
    $titre_seo = ''; // Initialise la variable pour éviter les erreurs si ACF n'est pas installé.

    // Vérifie si ACF est installé et que get_field est disponible.
    if (function_exists('get_field')) {
        if (is_singular()) {
            // Récupère le titre SEO de ACF s'il existe pour les posts ou custom post types.
            $titre_seo = get_field("titre_seo");
        } elseif (is_home()) {
            // Récupère le titre SEO pour la page des articles s'il est défini.
            $titre_seo = get_field('titre_seo', get_option('page_for_posts'));
        }
    }

    // Applique le titre SEO si défini.
    if ($titre_seo) {
        if (is_front_page()) {
            $title_parts['title'] = $titre_seo;
            $title_parts['tagline'] = get_bloginfo('name'); // Utilise le nom du site comme tagline pour la page d'accueil.
        } else {
            $title_parts['title'] = $titre_seo;
        }
    }

    return $title_parts;
}
add_filter('document_title_parts', 'custom_title');


/**
 * Gère le contenu SEO personnalisé pour l'en-tête, incluant la description et l'image de partage.
 */
function custom_seo_header_content() {
    if (function_exists('get_field')) {
        $meta_description = '';
        $og_image = '';

        // Récupérer la méta-description et l'image de partage en fonction du type de page
        if (is_archive()) {
            $meta_description = strip_tags(term_description());
        } elseif (is_home()) {
            // Page des articles
            $meta_description = get_field('meta_description_seo', get_option('page_for_posts'));
            $og_image = get_field('image_de_partage_seo', get_option('page_for_posts'));
        } else {
            $meta_description = get_field('meta_description_seo');
            $og_image = get_field('image_de_partage_seo');
        }

        // Afficher la méta-description si disponible
        if ($meta_description) {
            echo '<meta name="description" content="' . esc_attr($meta_description) . '">';
        }

        // Afficher l'image de partage Open Graph si disponible
        if ($og_image && isset($og_image['sizes']['og_image'])) {
            echo '<meta property="og:image" content="' . esc_url($og_image['sizes']['og_image']) . '" />' . "\n";
        }
    }
}
add_action('wp_head', 'custom_seo_header_content');

/**
 * Donne la possibilité aux Editeurs de traduire via Translatepress
 */
function bimbeau_change_translation_capability($capability) {
    return 'edit_posts';  // Choisit une capacité que les éditeurs ont déjà
}
add_filter('trp_translating_capability', 'bimbeau_change_translation_capability');

/**
 * Personnalisation de l'éditeur TinyMCE dans l'Admin de Wordpress et dans l'éditeur d'Elementor
 */

// Affiche que les boutons principaux
function custom_remove_tinymce_buttons_first_row($buttons) {
    // Vérifie si Elementor est actif et si nous sommes dans l'éditeur d'Elementor
    if (defined('ELEMENTOR_VERSION') && \Elementor\Plugin::$instance->editor->is_edit_mode()) {
        $remove = array('wp_adv', 'wp_more');
        return array_diff($buttons, $remove);
    }

    // Applique les modifications uniquement si nous ne sommes pas dans l'éditeur d'Elementor
    $current_screen = get_current_screen();
    if ($current_screen && $current_screen->base == 'post') {
        $remove = array('wp_adv', 'wp_more', 'alignleft', 'aligncenter', 'alignright', 'alignjustify');
        return array_diff($buttons, $remove);
    }

    return $buttons;
}
add_filter('mce_buttons', 'custom_remove_tinymce_buttons_first_row', 2000);

/**
 * Personnalisation de la barre d'outils de l'éditeur WYSIWYG ACF
 */
function custom_acf_wysiwyg_toolbars($toolbars) {
    // Liste des boutons à supprimer
    // Ajout des boutons 'blockquote', 'bullist', 'numlist', et 'fullscreen'
    $remove_buttons = array('wp_adv', 'wp_more', 'alignleft', 'aligncenter', 'alignright', 'alignjustify', 'blockquote', 'bullist', 'numlist', 'fullscreen');

    // Parcourt toutes les barres d'outils
    foreach ($toolbars as $toolbar_key => &$toolbar) {
        foreach ($toolbar as $row_key => &$row) {
            // Supprime les boutons non souhaités
            foreach ($remove_buttons as $remove_button) {
                if (($key = array_search($remove_button, $row)) !== false) {
                    unset($row[$key]);
                }
            }
        }
    }

    return $toolbars;
}

add_filter('acf/fields/wysiwyg/toolbars', 'custom_acf_wysiwyg_toolbars');



/**
 * Ajoute l'image à la une dans le flux RSS pour tous les types de posts.
 */
function add_featured_image_to_rss_feed($content) {
    global $post;

    // Vérifie si le post actuel est un type de post valide (post classique ou custom post)
    if (get_post_type($post) && has_post_thumbnail($post->ID)) {
        // Récupère l'URL de l'image à la une.
        $featured_image = get_the_post_thumbnail_url($post->ID, 'full');

        // Crée la balise image à ajouter au début du contenu.
        $image_tag = '<img src="' . $featured_image . '" alt="" style="max-width:100%; height:auto; margin-bottom: 15px;" />';

        // Ajoute l'image au début du contenu du flux RSS.
        $content = $image_tag . $content;
    }

    return $content;
}

add_filter('the_excerpt_rss', 'add_featured_image_to_rss_feed');


// Affiche que les styles de textes principaux sans le "H1"
function custom_tinymce_heading($args) {

    $args['block_formats'] = 'Normal=p;Titre 2=h2;Titre 3=h3;Titre 4=h4;';

    return $args;
}
add_filter('tiny_mce_before_init', 'custom_tinymce_heading');

// Supprime le bouton "Ajouter un média"
function RemoveAddMediaButtonsForNonAdmins() {
    remove_action('media_buttons', 'media_buttons');
}
add_action('admin_head', 'RemoveAddMediaButtonsForNonAdmins');

/**
 * Autorise l'upload de fichiers SVG
 */
function wpc_mime_types($mimes) {
    $mimes['svg'] = 'image/svg+xml';
    return $mimes;
}
add_filter('upload_mimes', 'wpc_mime_types');


/**
 * Ajoute des tailles d'images personnalisées
 */
add_image_size('og_image', 1200, 627, true);


/**
 * Chargement optimisé du CSS et JS
 */
function my_enqueuer($my_handle, $relpath, $type = 'script', $my_deps = array()) {
    $uri = get_theme_file_uri($relpath);
    $vsn = filemtime(get_theme_file_path($relpath));
    if ($type == 'script') wp_enqueue_script($my_handle, $uri, $my_deps, $vsn);
    else if ($type == 'style') wp_enqueue_style($my_handle, $uri, $my_deps, $vsn);
}


/**
 * Chargement de fichiers - Admin
 */
add_action('login_enqueue_scripts', 'hellobimbeau_enqueue_admin');
function hellobimbeau_enqueue_admin() {
    my_enqueuer('admin_css', '/assets/css/admin.css', 'style');
}


/**
 * Ajoute le contenu complet des fichiers admin.css et variables.css dans le style en ligne
 */
function custom_admin_default_styles() {
    // Obtenez le contenu complet du fichier admin.css
    $admin_css = file_get_contents(get_template_directory() . '/assets/css/admin.css');
    // Obtenez le contenu complet du fichier variables.css
    $variables_css = file_get_contents(get_template_directory() . '/assets/css/variables.css');

    // Vérifiez si le contenu des fichiers a été correctement lu
    if ($admin_css !== false && $variables_css !== false) {
        // Concaténez le contenu des deux fichiers CSS
        $custom_css = $variables_css . "\n" . $admin_css;

        // Ajouter le contenu combiné dans la balise <style>
        wp_add_inline_style('wp-admin', $custom_css);
    }
}

add_action('admin_enqueue_scripts', 'custom_admin_default_styles');

/**
 * Ajoue un fichier de style pour le frontend
 */
function custom_default_styles() {
    my_enqueuer('custom_front_css', '/assets/css/custom.css', 'style');
}
add_action('wp_enqueue_scripts', 'custom_default_styles');



/**
 * Tools
 */

function display($var) {
    echo "<pre>";
    if (is_array($var) || is_object($var)) {
        print_r($var);
    } else {
        echo $var;
    }
    echo "</pre>";
}
