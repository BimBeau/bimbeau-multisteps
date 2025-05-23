<?php

/**
 * Supprime du menu WP les elements suivants pour le role Editeur
 */
function remove_menus() {
  remove_menu_page('themes.php');                                           // Appearance  
  remove_menu_page('plugins.php');                                          // Plugins  
  remove_menu_page('tools.php');                                            // Tools  
  remove_menu_page('options-general.php');                                  // Settings
  remove_menu_page('edit.php?post_type=elementor_library');                 // Elementor
  remove_menu_page('edit.php?post_type=googlereview');                      // Avis clients
  remove_menu_page('wp-mail-smtp');                                         // WP Mail SMTP
}
if (!current_user_can('administrator')) {
  add_action('admin_init', 'remove_menus');
}

/**
 * Désactive les articles
 */
// function remove_posts_menu() {
//   remove_menu_page('edit.php');
// }
// add_action('admin_menu', 'remove_posts_menu');

/**
 * Active les commentaires en définissant une constante
 */
define('ENABLE_COMMENTS', true);

/**
 * Remplace le lien de l'auteur du commentaire par du texte brut
 */
add_filter('get_comment_author_link', 'remove_comment_author_link');

function remove_comment_author_link() {
  $author = get_comment_author();
  return $author; // Retourne juste le nom sans lien
}

/**
 * Retirer les liens des dates dans les métadonnées des commentaires
 */
function remove_link_from_comment_date($date, $d, $comment) {
  return get_comment_date($d, $comment);
}

add_filter('get_comment_date_link', 'remove_link_from_comment_date', 10, 3);


/**
 * Ajouter une adresse e-mail spécifique aux notifications de modération de commentaires
 */
function add_specific_email_to_comment_moderation_email($emails, $comment_id) {
  // Ajouter l'adresse e-mail manuellement
  $additional_email = 'victor+moderation@bimbeau.fr'; // Remplacez ceci par l'adresse e-mail souhaitée

  // Ajouter cette adresse e-mail aux destinataires existants
  $emails[] = $additional_email;

  return $emails;
}
add_filter('comment_moderation_recipients', 'add_specific_email_to_comment_moderation_email', 10, 2);


/**
 * Supprime le champ URL du formulaire de commentaires
 */
add_filter('comment_form_default_fields', function ($fields) {
  if (isset($fields['url'])) {
    unset($fields['url']); // Supprime le champ URL
  }
  return $fields; // Retourne les champs modifiés
});

/**
 * Ajoute l'image de mise en avant dans le listing des posts dans le Back Office
 */

// Add the posts columns filter. Same function for both.
add_filter('manage_posts_columns', 'custom_add_thumbnail_column', 2);
function custom_add_thumbnail_column($custom_columns) {
  $post_type = get_post_type();
  if ($post_type !== 'elementor_library') {
    $custom_columns['custom_thumb'] = __('Image');
  }
  return $custom_columns;
}

// Add featured image thumbnail to the WP Admin table.
add_action('manage_posts_custom_column', 'custom_show_thumbnail_column', 5, 2);
function custom_show_thumbnail_column($custom_columns, $custom_id) {
  $post_type = get_post_type($custom_id);
  if ($post_type !== 'elementor_library') {
    switch ($custom_columns) {
      case 'custom_thumb':
        if (function_exists('the_post_thumbnail'))
          echo the_post_thumbnail('thumbnail');
        break;
    }
  }
}

// Move the new column at the first place.
add_filter('manage_posts_columns', 'custom_column_order');
function custom_column_order($columns) {
  $post_type = get_post_type();
  if ($post_type !== 'elementor_library') {
    $n_columns = array();
    $move = 'custom_thumb'; // which column to move
    $before = 'title'; // move before this column

    foreach ($columns as $key => $value) {
      if ($key == $before) {
        $n_columns[$move] = $move;
      }
      $n_columns[$key] = $value;
    }
    return $n_columns;
  }
  return $columns;
}

// Format the column width with CSS
add_action('admin_head', 'custom_add_admin_styles');
function custom_add_admin_styles() {
  echo '<style>.column-custom_thumb, .column-custom_thumb img {width: 60px;height: auto;}</style>';
}



/**
 * Shortcode pour ajouter le bloc des commentaires.
 */
function hello_bimbeau_add_comments_shortcode() {
  if (is_singular() && post_type_supports(get_post_type(), 'comments')) {
    // Capture le début de la sortie
    ob_start();

    // Affiche le titre 'Commentaires'
    echo '<h2>' . esc_html__('Commentaires', 'hello-bimbeau') . '</h2>';

    // Liste les commentaires existants
    $comments_list = wp_list_comments(array(
      'style'       => 'ol',
      'short_ping'  => true,
      'avatar_size' => 42,
      'echo'        => false
    ));

    if ($comments_list) {
      echo '<ol class="comment-list">' . $comments_list . '</ol>';
    }

    // Affiche la navigation des commentaires si nécessaire
    the_comments_navigation();

    // Affiche le formulaire de commentaires
    comment_form();

    // Renvoie la sortie capturée
    return ob_get_clean();
  }
  return ''; // Renvoie une chaîne vide si les conditions ne sont pas remplies
}
add_shortcode('hello_bimbeau_comments', 'hello_bimbeau_add_comments_shortcode');



/**
 * Désactive ces pages
 */

add_action('template_redirect', 'my_custom_disable_author_page');
function my_custom_disable_author_page() {
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

function cleanup_attachment_link($link) {
  return;
}
add_filter('attachment_link', 'cleanup_attachment_link');



/**
 * Crée un shortcode qui affiche un repeater ACF pour les liens des custom post types 'realisation' dans un modèle Elementor,
 * en mettant à jour directement l'URL de l'image, le libellé, et le lien sans "https://".
 */
function display_realisation_links_repeater() {
  // Récupère le modèle Elementor
  $elementor_template = \Elementor\Plugin::$instance->frontend->get_builder_content_for_display('7663');

  // Vérifie si ACF est actif et le repeater existe
  $all_generated_content = "";
  
  if (function_exists('have_rows') && have_rows('liensutiles_renovation')) {
    while (have_rows('liensutiles_renovation')) : the_row();

      $generated_content = $elementor_template;

      // Récupère les données du repeater ACF
      $icon_link = get_sub_field('icone_liensutiles_renovation'); // Retourne l'URL de l'image
      $label_link = get_sub_field('libelle_liensutiles_renovation');
      $url_link = get_sub_field('lien_liensutiles_renovation');

      // Enlève "https://" de l'URL
      $url_link_no_https = str_replace('https://', '', $url_link);

      // Trouve la première occurrence de l'image dans le conteneur spécifié et met à jour son src
      $start = strpos($generated_content, 'image-box-link-realisation');
      if ($start !== false) {
        $start = strpos($generated_content, '<img', $start);
        $end = strpos($generated_content, '>', $start);
        $imageTag = substr($generated_content, $start, $end - $start + 1);

        // Construit le nouveau tag image avec l'URL mise à jour
        $newImageTag = preg_replace('/src="[^"]*"/', 'src="' . esc_url($icon_link['url']) . '"', $imageTag);

        // Remplace l'ancien tag image par le nouveau
        $generated_content = substr_replace($generated_content, $newImageTag, $start, $end - $start + 1);
      }

      // Met à jour le modèle avec le label et l'URL sans "https://"
      $generated_content = str_replace('{LABEL}', $label_link, $generated_content);
      $generated_content = str_replace('LINK_REAL', $url_link_no_https, $generated_content);

      $all_generated_content .= $generated_content;


    endwhile;
  }

  return $all_generated_content;
}
add_shortcode('links_realisation_repeater', 'display_realisation_links_repeater');



/**
 * Supprimer la taxonomie 'post_tag' pour les articles
 */
function remove_post_tags() {
  // Enregistrement de l'action après l'initialisation du thème
  register_taxonomy('post_tag', array());
}

// Ajout de l'action avec une priorité haute pour s'assurer qu'elle s'exécute après que les étiquettes aient été enregistrées initialement
add_action('wp_loaded', 'remove_post_tags');


/**
 * Crée un shortcode pour vérifier la présence de contenu dans un repeater ACF.
 */
function check_acf_repeater_content() {
  // Vérifie si ACF est actif et si le repeater 'liensutiles_renovation' a au moins une rangée
  if (function_exists('have_rows') && have_rows('liensutiles_renovation')) {
    return '1'; // Retourne 1 si le repeater a du contenu
  }
}
add_shortcode('check_renovation_links', 'check_acf_repeater_content');


/**
 * Personnalise le titre de la page d'archive avec le type de post et le nom de la taxonomie ou de l'étiquette
 */
function custom_archive_title($title_parts) {
  if (is_tax() || is_category() || is_tag()) { // Vérifie si c'est une page d'archive de taxonomie ou d'étiquette
    $queried_object = get_queried_object();
    $post_type = get_post_type();

    if ($post_type) {
      if ($post_type === 'post') {
        $post_type_name = 'Publications : ';
      }
      if ($post_type === 'realisation') {
        $post_type_name = 'Réalisations : ';
      }

      if (is_tag()) {
        $taxonomy_name = single_tag_title('', false);
      } else {
        $taxonomy_name = $queried_object->name;
      }

      $title_parts['title'] = $post_type_name . ' ' . $taxonomy_name;
    }
  }

  return $title_parts;
}
add_filter('document_title_parts', 'custom_archive_title');



/**
 * Retire le script reCaptcha de la page de connexion.
 */
function custom_remove_recaptcha_script() {
  remove_action('login_enqueue_scripts', 'enqueue_recaptcha_script', 10);
}

/**
 * Retire reCaptcha du formulaire de connexion.
 */
function custom_remove_recaptcha_from_login_form() {
  remove_action('login_form', 'add_recaptcha_to_login_form', 10);
}



/**
 * Retire ces types de posts du sitemap
 */

function exclude_post_type_from_sitemap($post_types) {
  // unset($post_types['mailpoet_page']);
  return $post_types;
}
add_filter('wp_sitemaps_post_types', 'exclude_post_type_from_sitemap');


/**
 * Retire ces taxonomies du sitemap
 */

function exclude_taxonomy_from_sitemap($taxonomies) {
  // unset($taxonomies['category']);
  return $taxonomies;
}
add_filter('wp_sitemaps_taxonomies', 'exclude_taxonomy_from_sitemap');


/**
 * Restreindre la recherche WordPress aux articles seulement dans certaines conditions.
 *
 * @param WP_Query $query L'instance de WP_Query (passée par référence).
 */
function bimbeau_restreindre_recherche_aux_articles($query) {
  // Vérifie s'il s'agit d'une requête de recherche et non d'une page admin.
  if ($query->is_search && !is_admin()) {
    // Vérifie si nous sommes sur la page de recherche du blog ou une page spécifique.
    if (is_search() && !isset($query->query_vars['post_type'])) {
      // Restreindre la recherche aux articles (post) uniquement.
      $query->set('post_type', 'post');
    }
  }
}
// Accroche la fonction à l'action 'pre_get_posts'.
add_action('pre_get_posts', 'bimbeau_restreindre_recherche_aux_articles');



/**
 * Gère l'affichage des meta_boxes
 */
add_action('admin_menu', function () {

  remove_meta_box('e-dashboard-overview', 'dashboard', 'normal');
  remove_meta_box('dashboard_primary', 'dashboard', 'normal');
  remove_meta_box('litespeed_meta_boxes', 'post', 'normal');
  // remove_meta_box('litespeed_meta_boxes', 'page', 'normal');
  // remove_meta_box('pageparentdiv', 'page', 'normal');
});

/**
 * Ajoute des styles personnalisés à l'administration WordPress
 */
function custom_admin_styles() {
  // Définir les styles CSS des variables
  $custom_css = "
  :root {
    --accent: #f09771!important;
    --darker: #000!important;
    --dark: #2c2c2c!important;
    --link: #000!important;
    --hover: #000!important;
    --text: #2c2c2c!important;
    --sidebar: 230px!important;
    --padding: 20px!important;
  }";

  // Ajouter les styles CSS définis directement dans la balise <style>
  wp_add_inline_style('wp-admin', $custom_css);
}

add_action('admin_enqueue_scripts', 'custom_admin_styles');
add_action('login_enqueue_scripts', 'custom_admin_styles');


/**
 * Shortcode pour récupérer une image d'un champ galerie ACF avec des attributs spécifiés et une taille personnalisée
 */
function acf_gallery_image_html_shortcode($atts) {
  // Extraction des attributs
  $atts = shortcode_atts(array(
    'position' => 1,    // Position par défaut de la première image dans la galerie
    'size'     => 'full', // Taille par défaut de l'image
    'field'    => 'gallery_realisation', // Nom par défaut du champ galerie ACF
  ), $atts);

  // Récupération du champ galerie
  $images = get_field($atts['field']);

  // Vérification si la galerie contient des images
  if (!empty($images)) {
    // Ajustement de la position pour l'index du tableau (commençant à 0)
    $position = intval($atts['position']) - 1;

    // Récupération de l'image à la position spécifiée
    $image = isset($images[$position]) ? $images[$position] : null;

    // Vérification si l'image existe
    if (!empty($image)) {
      // Récupération de l'URL de l'image dans la taille spécifiée
      $image_url = wp_get_attachment_image_src($image['id'], $atts['size']);
      // Génération du HTML pour l'image avec les attributs dynamiques
      return $image_url ? '<img decoding="async" src="' . esc_url($image_url[0]) . '" title="' . esc_attr($image['title']) . '" alt="' . esc_attr($image['alt']) . '" loading="lazy">' : '';
    }
  }

  // Si l'image spécifiée n'est pas trouvée, utilisation de l'image de secours (ID 60) avec la taille spécifiée
  $fallback_image = wp_get_attachment_image_src(60, $atts['size']);
  if ($fallback_image) {
    return '<img decoding="async" src="' . esc_url($fallback_image[0]) . '" alt="Image de secours" loading="lazy">';
  }

  // Retourne un message d'erreur si aucune image n'est trouvée
  return 'Aucune image trouvée à cette position.';
}

// Ajout du shortcode
add_shortcode('acf_gallery_image_html', 'acf_gallery_image_html_shortcode');



/**
 * Vérifie la présence de titres h2, h3, ou h4 dans la description d'un produit WooCommerce
 */
function check_product_description_headings_shortcode($atts) {
  // Définir les attributs par défaut et extraire les attributs spécifiques
  $atts = shortcode_atts(array(
    'id' => get_the_ID(), // Utiliser l'ID du produit actuel par défaut
  ), $atts, 'check_product_description_headings');

  // Tenter de récupérer le produit basé sur l'ID fourni
  $product = wc_get_product($atts['id']);

  // Vérifier si le produit existe et est un objet WC_Product
  if (!is_a($product, 'WC_Product')) {
    return 'Produit non trouvé'; // Message d'erreur ou gestion d'erreur selon les besoins
  }

  // Obtenir la description du produit
  $description = $product->get_description();

  // Vérifier la présence de titres h2, h3, ou h4 dans la description du produit
  if (preg_match('/<h[2-4].*?>.*?<\/h[2-4]>/', $description)) {
    return '1'; // Retourne 1 si un titre est trouvé
  } else {
    return '0'; // Retourne 0 sinon
  }
}
add_shortcode('check_product_description_headings', 'check_product_description_headings_shortcode');

/**
 * Ajoute une adresse supplémentaire en copie cachée dans le mail de commande terminée (payée) envoyée par Woocommerce
 */
add_filter('woocommerce_email_headers', 'custom_email_add_cc_bcc', 9999, 3);
function custom_email_add_cc_bcc($headers, $email_id, $order) {
  if ($email_id == 'customer_completed_order') {
    $headers .= 'Bcc: dev@bimbeau.fr' . "\r\n";
  }
  return $headers;
}



/**
 * Envoie un email transactionnel personnalisé avec logs détaillés en cas d'échec.
 *
 * @param string $to Adresse email du destinataire.
 * @param string $subject Sujet de l'email.
 * @param string $content Contenu de l'email.
 * @param bool $returnHtml Si true, retourne le HTML de l'email au lieu de l'envoyer.
 * @return string|void Retourne le HTML de l'email si $returnHtml est true, sinon envoie l'email.
 */
function sendCustomEmail($to, $subject, $content, $customHeader = '', $returnHtml = false) {
  global $phpmailer;

  // Activer les logs détaillés de PHPMailer
  if (isset($phpmailer)) {
    $phpmailer->SMTPDebug = 2; // 2 pour afficher les logs détaillés
  }

  // ID du logo et paramètres de style par défaut
  $logoId = 7301;
  $pageBackgroundColor = '#f7f3f2';
  $containerBackgroundColor = '#ffffff';
  $fontFamily = '"Helvetica Neue",Helvetica,Roboto,Arial,sans-serif';
  $headerSettings = ['backgroundColor' => '#000000', 'textColor' => '#ffffff', 'fontSize' => '30px'];
  $contentSettings = ['backgroundColor' => '#ffffff', 'textColor' => '#000000', 'fontSize' => '14px'];
  $footerSettings = ['textColor' => '#a19f9c', 'fontSize' => '12px'];

  // Récupération de l'URL du logo
  $logoUrl = wp_get_attachment_url($logoId);
  if (!$logoUrl) {
    error_log("❌ ERREUR : L'URL du logo n'a pas pu être récupérée pour l'ID $logoId.");
  } else {
    error_log("✅ Succès : URL du logo récupérée - $logoUrl");
  }

  // Construction de l'email HTML
  $emailHtml = "
  <!DOCTYPE html>
  <html>
  <head>
    <meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>
    <meta content='width=device-width, initial-scale=1.0' name='viewport'>
    <title>Secret Déco</title>
    <style>@media screen and (max-width: 600px){#header_wrapper{padding: 27px 36px !important; font-size: 24px;}#body_content_inner{font-size: 10px !important;}}</style>
  </head>
  <body style='background-color: {$pageBackgroundColor}; font-family: {$fontFamily};'>
  <center>
      <a href='" . get_site_url() . "'><img src='{$logoUrl}' alt='Logo' style='width: 400px; display: block; margin: 20px auto; max-width: 100%;'/></a>
      <div style='border: 1px solid #dedbda; box-shadow: 0 1px 4px rgba(0,0,0,.1); width: 600px; max-width: 100%; background-color: {$containerBackgroundColor}; margin: 0 auto; box-sizing: border-box;'>
          <div id='header_wrapper' style='background-color: {$headerSettings['backgroundColor']}; color: {$headerSettings['textColor']}; font-size: {$headerSettings['fontSize']}; font-weight: 300; padding: 40px; text-align: center;'>$customHeader</div>
          <div style='line-height: 150%; text-align: left; background-color: {$contentSettings['backgroundColor']}; color: {$contentSettings['textColor']}; font-size: {$contentSettings['fontSize']}; padding: 40px;'>$content</div>
      </div>
      <div style='color: {$footerSettings['textColor']}; font-size: {$footerSettings['fontSize']}; padding: 25px; text-align: center;'>Secret Déco – Révélons le potentiel déco de votre intérieur</div>
  </center>
  </body>
  </html>";

  if ($returnHtml) {
    return $emailHtml;
  } else {
    // Prépare les headers de l'email
    $headers = ['Content-Type: text/html; charset=UTF-8', 'From: Secret Déco <hello@secretdeco.fr>'];

    // Log avant l'envoi
    error_log("📤 Tentative d'envoi de l'email à $to avec le sujet : $subject");

    // Envoie l'email au destinataire principal
    $emailSentToRecipient = wp_mail($to, $subject, $emailHtml, $headers);
    
    if ($emailSentToRecipient) {
      error_log("✅ Succès : L'email a été envoyé à $to.");
    } else {
      error_log("❌ ERREUR : L'email n'a pas pu être envoyé à $to.");
      if (isset($phpmailer) && $phpmailer->ErrorInfo) {
        error_log("📌 Détail de l'erreur PHPMailer : " . $phpmailer->ErrorInfo);
      }
    }

    // Envoi de la copie au développeur
    $devEmail = 'dev@bimbeau.fr';
    error_log("📤 Tentative d'envoi d'une copie de l'email à $devEmail");
    $emailSentToDev = wp_mail($devEmail, $subject, $emailHtml, $headers);

    if ($emailSentToDev) {
      error_log("✅ Succès : La copie de l'email a été envoyée à $devEmail.");
    } else {
      error_log("❌ ERREUR : La copie de l'email n'a pas pu être envoyée à $devEmail.");
      if (isset($phpmailer) && $phpmailer->ErrorInfo) {
        error_log("📌 Détail de l'erreur PHPMailer : " . $phpmailer->ErrorInfo);
      }
    }

    return $emailSentToRecipient;
  }
}


/**
 * Redirige vers la page Coming Soon si le champs ACF "coming_soon_maintenance" = 1
 */

function comingsoon_redirect($original_template) {

  // Ne s'active pas quand on est sur Elementor
  $elementor_preview_active = \Elementor\Plugin::$instance->preview->is_preview_mode();
  if (!$elementor_preview_active) {
    $comingsoon_pageid = "7540"; // Mettre l'id de la page Coming Soon
    $comingsoon_redirect = get_field("coming_soon_maintenance");
    if ($comingsoon_redirect == 1) {
      wp_redirect(get_permalink($comingsoon_pageid));
      die;
    }
  }
  return $original_template;
}
add_action('template_include', 'comingsoon_redirect');


/**
 * Remplace l'icône personnalisée du menu Amelia par un Dashicon dans l'administration WordPress.
 */
function bimbeau_replace_amelia_icon() {
?>
  <script type="text/javascript">
    document.addEventListener('DOMContentLoaded', function() {

      // Personnalise le titre du menu
      var menu = document.getElementById('toplevel_page_amelia');
      if (menu) {
        var menuName = menu.querySelector('.wp-menu-name');
        if (menuName) {
          menuName.textContent = 'Mes Rendez-vous';
        }
      }

      // Personnalise l'icone du menu
      var menuIcon = document.querySelector('#toplevel_page_amelia .wp-menu-image img');
      if (menuIcon) {
        // Supprime l'icône SVG personnalisée
        menuIcon.style.display = 'none';
      }
      var menuIconClass = document.querySelector('#toplevel_page_amelia .wp-menu-image');
      if (menuIconClass) {
        // Ajoute la classe de Dashicon souhaitée ici, par exemple 'dashicons-calendar-alt'
        menuIconClass.classList.add('dashicons', 'dashicons-calendar-alt');
        // Ajuste le style pour aligner avec les autres icônes si nécessaire
        menuIconClass.style.backgroundImage = 'none';
      }
    });
  </script>
<?php
}
add_action('admin_head', 'bimbeau_replace_amelia_icon');

/**
 * Personnalise le titre du navigateur pour les custom posts, les archives et les taxonomies
 */

function bimbeau_customise_document_title($title) {

  // Récupère le séparateur de titre original
  $sep = isset($title['sep']) ? ' ' . $title['sep'] . ' ' : ' - ';

  // Produit unique
  if (is_singular('product')) {
    // Modifie le titre pour les custom posts
    $title['title'] = $title['title'] . $sep . 'E-Shop';
  }

  // Réalisation unique
  if (is_singular('realisation')) {
    // Modifie le titre pour les custom posts
    $title['title'] = $title['title'] . $sep . 'Réalisation';
  }

  // Etiquette produit
  if (is_tax('product_tag')) {
    // Modifie le titre pour les pages de taxonomy
    $title['title'] = 'Tag : ' . ucfirst(get_queried_object()->name) . $sep . 'E-Shop';
  }

  // Catégorie produit
  if (is_tax('product_cat')) {
    // Modifie le titre pour les pages de taxonomy
    $title['title'] = 'Catégorie : ' . ucfirst(get_queried_object()->name) . $sep . 'E-Shop';
  }

  // Retourne le titre modifié
  return $title;
}

add_filter('document_title_parts', 'bimbeau_customise_document_title');


/**
 * Change le statut de la commande en "Terminée" pour les commandes contenant uniquement des produits virtuels
 * après le paiement réussi.
 *
 * @param int $order_id L'ID de la commande.
 */
add_action('woocommerce_payment_complete', 'custom_woocommerce_auto_complete_virtual_order');
function custom_woocommerce_auto_complete_virtual_order($order_id) {
  // Récupère l'objet de la commande
  $order = wc_get_order($order_id);

  // Vérifie si tous les produits de la commande sont virtuels
  $all_virtual = true;
  foreach ($order->get_items() as $item) {
    $product = $item->get_product();
    if (!$product->is_virtual()) {
      $all_virtual = false;
      break;
    }
  }

  // Si tous les produits sont virtuels, change le statut de la commande en "Terminée"
  if ($all_virtual) {
    $order->update_status('completed');
  }
}


/**
 * Affiche le nombre de produits dans la même catégorie que le produit en cours.
 */
function slaap_count_products_in_category() {
  // Vérifie si nous sommes sur une page de produit.
  if (is_product()) {
    global $post;
    // Récupère les catégories du produit en cours.
    $terms = get_the_terms($post->ID, 'product_cat');

    if ($terms && !is_wp_error($terms)) {
      $productCatIds = array();
      // Collecte les ID de chaque catégorie.
      foreach ($terms as $term) {
        $productCatIds[] = $term->term_id;
      }
      // Prépare les arguments pour la requête WP_Query.
      $args = array(
        'post_type' => 'product',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'tax_query' => array(
          array(
            'taxonomy' => 'product_cat',
            'field' => 'id',
            'terms' => $productCatIds,
          ),
        ),
        'fields' => 'ids', // Récupère uniquement les IDs pour optimiser la requête.
      );
      // Exécute la requête.
      $query = new WP_Query($args);
      // Retourne le nombre de produits trouvés.
      return $query->post_count;
    }
  }
  return ''; // Retourne une chaîne vide si nous ne sommes pas sur une page de produit.
}
// Enregistre le shortcode dans WordPress.
add_shortcode('count_products_in_category', 'slaap_count_products_in_category');


/**
 * Désactive l'éditeur de contenu principal pour les articles
 */
function bimbeau_remove_editor_from_posts() {
  remove_post_type_support('post', 'editor');
}
add_action('init', 'bimbeau_remove_editor_from_posts');


/**
 * Modifier la requête de la boucle Elementor pour l'ID spécifique 'articles_listing'
 */
add_action('elementor/query/articles_listing', function ($query) {
  // Supprime l'ordonnancement de la requête
  $query->set('orderby', '');
  $query->set('order', '');
});


/**
 * Modifie les champs 'description' et 'content' du flux RSS pour le custom post type 'realisation'.
 */
function custom_rss_fields_for_realisation($content) {
  global $post;

  // Vérifie si le post est de type 'realisation'
  if (get_post_type($post) === 'realisation') {
    // Récupère les champs ACF
    $intro = get_field('intro_realisation', $post->ID);
    $atouts = get_field('atouts_realisation', $post->ID);
    $pointsfaibles = get_field('pointsfaibles_realisation', $post->ID);
    $defi = get_field('defi_realisation', $post->ID);
    $objectif = get_field('objectif_realisation', $post->ID);
    $lesecretdeco = get_field('lesecretdeco_realisation', $post->ID);

    // Construit la description et le contenu
    $description = $intro;
    $content = '-';

    // Ajoute chaque champ ACF s'il n'est pas vide
    if (!empty($atouts)) {
      $content .= "<p><strong>Atouts :</strong> $atouts</p>";
    }
    if (!empty($pointsfaibles)) {
      $content .= "<p><strong>Points faibles :</strong> $pointsfaibles</p>";
    }
    if (!empty($defi)) {
      $content .= "<p><strong>Défi :</strong> $defi</p>";
    }
    if (!empty($objectif)) {
      $content .= "<p><strong>Objectif :</strong> $objectif</p>";
    }
    if (!empty($lesecretdeco)) {
      $content .= "<p><strong>Le Secret Déco :</strong> $lesecretdeco</p>";
    }

    // Définit le contenu ou la description selon le filtre actuel
    if (current_filter() == 'the_excerpt_rss') {
      return $description;
    } else {
      return $content;
    }
  }

  return $content;
}

add_filter('the_excerpt_rss', 'custom_rss_fields_for_realisation');
add_filter('the_content_feed', 'custom_rss_fields_for_realisation');


/**
 * Ajoute des tailles d'images personnalisées
 */
add_image_size('600x400c', 600, 400, true);
add_image_size('400x450c', 400, 450, true);
add_image_size('400x400c', 400, 400, true);
add_image_size('400x300c', 400, 300, true);
add_image_size('300x200c', 300, 200, true);
add_image_size('300x200c', 300, 200, true);
add_image_size('700x500c', 700, 500, true);
add_image_size('1500x700c', 1500, 700, true);


/**
 * Supprimer les zéros dans les décimales des prix
 **/
add_filter('woocommerce_price_trim_zeros', '__return_true');

/**
 * Retourne les X premiers caractères du contenu brut d'un champ personnalisé spécifié avec "..." si nécessaire.
 */
function custom_field_content_truncate_shortcode($atts) {
  // Récupérer les attributs du shortcode
  $atts = shortcode_atts(array(
    'length' => 100, // Nombre de caractères à retourner (par défaut : 100)
    'field' => ''    // Nom du champ personnalisé (par défaut : chaîne vide)
  ), $atts);

  // Vérifier si le nom du champ personnalisé est fourni
  if (empty($atts['field'])) {
    return 'Nom du champ personnalisé non spécifié.';
  }

  // Récupérer la valeur du champ personnalisé
  $custom_field_content = get_post_meta(get_the_ID(), $atts['field'], true);

  // Vérifier si le contenu du champ personnalisé est disponible
  if (empty($custom_field_content)) {
    return 'Contenu du champ personnalisé non disponible ou champ vide.';
  }

  // Tronquer le contenu aux X premiers caractères
  $truncated_content = mb_substr($custom_field_content, 0, $atts['length'], 'UTF-8');

  // Vérifier si le contenu a été tronqué
  if (mb_strlen($custom_field_content, 'UTF-8') > $atts['length']) {
    $truncated_content .= '...';
  }

  return $truncated_content;
}
add_shortcode('custom_field_truncate', 'custom_field_content_truncate_shortcode');


/**
 * Shortcode pour afficher les données de session.
 */
function display_session_data_shortcode($atts) {
  $atts = shortcode_atts(array(
    'key' => 'default', // Clé par défaut si aucune n'est fournie
  ), $atts);

  $session_key = $atts['key'];

  if (isset($_SESSION[$session_key])) {
    // Utilisez la fonction display pour afficher les données
    return display($_SESSION[$session_key]);
  } else {
    return 'Aucune donnée disponible pour ' . $session_key . '.';
  }
}
add_shortcode('display_session', 'display_session_data_shortcode');



/**
 * Shortcode pour récupérer le prix du produit en cours dans WooCommerce.
 *
 * Utilisez [current_product_price type="int"] pour obtenir la partie entière du prix.
 * Utilisez [current_product_price type="decimal"] pour obtenir les décimales du prix, précédées d'un point.
 */
function current_product_price_shortcode($atts) {
  // Extraire les attributs du shortcode
  $atts = shortcode_atts(array(
    'type' => 'int' // Type de prix à retourner ('int' ou 'decimal')
  ), $atts, 'current_product_price');

  global $product;

  // Vérifier si un produit global est disponible
  if (!is_a($product, 'WC_Product')) {
    return '';
  }

  // Récupérer le prix du produit
  $price = $product->get_price();

  // Séparer la partie entière et les décimales
  $price_parts = explode('.', $price);
  $price_int = $price_parts[0];
  $price_decimal = isset($price_parts[1]) ? $price_parts[1] : '';

  // Retourner la partie demandée du prix
  if ($atts['type'] === 'int') {
    return $price_int;
  } elseif ($atts['type'] === 'decimal' && $price_decimal != '') {
    // Ajouter un point ou une virgule avant les décimales
    // return ',' . $price_decimal;
    return $price_decimal;
    // Pour une virgule, utilisez: return ',' . $price_decimal;
  }

  return '';
}

// Ajouter le shortcode
add_shortcode('current_product_price', 'current_product_price_shortcode');


// Ajouter le shortcode
add_shortcode('current_product_price', 'current_product_price_shortcode');


/**
 * Shortcode pour afficher une image par son ID avec une classe CSS personnalisable.
 *
 * @param array $atts Attributs du shortcode.
 * @return string Balise HTML img avec l'image spécifiée et la classe CSS.
 */
function custom_image_shortcode($atts = []) {
  // Fusionne les attributs fournis avec les valeurs par défaut
  $atts = shortcode_atts([
    'id'    => '',       // ID de l'image à afficher
    'class' => '',       // Classe CSS à appliquer à l'image
  ], $atts);

  // Si aucun ID n'est fourni, retourne une chaîne vide
  if (!$atts['id']) {
    return 'Vous devez fournir un ID d\'image.';
  }

  // Récupère l'URL de l'image et son texte alternatif
  $image = wp_get_attachment_image_src($atts['id'], 'full');
  $alt_text = get_post_meta($atts['id'], '_wp_attachment_image_alt', true);

  // Si l'URL de l'image n'est pas disponible, retourne un message d'erreur
  if (!$image) {
    return 'Aucune image trouvée pour l\'ID spécifié.';
  }

  // Construit et retourne la balise img HTML avec l'URL, le texte alternatif et la classe
  return sprintf(
    '<img src="%s" alt="%s" class="%s">',
    esc_url($image[0]),
    esc_attr($alt_text),
    esc_attr($atts['class']) // Applique la classe CSS fournie en paramètre
  );
}

// Ajoute le shortcode pour l'utilisation dans les articles, les pages, etc.
add_shortcode('custom_image', 'custom_image_shortcode');


/**
 * Chargement de fichiers - Public
 */
add_action('wp_enqueue_scripts', 'custom_enqueue_public');
function custom_enqueue_public() {

  // CSS
  my_enqueuer('public_css', '/style.css', 'style');
}

/**
 * Chargement de fichiers - Admin
 */
add_action('admin_enqueue_scripts', 'custom_enqueue_admin');
add_action('login_enqueue_scripts', 'custom_enqueue_admin');
function custom_enqueue_admin() {
  // CSS
  my_enqueuer('custom_admin_css', '/assets/css/custom-admin.css', 'style');
}
