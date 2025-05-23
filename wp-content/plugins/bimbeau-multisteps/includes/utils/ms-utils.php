<?php
// Utility and configuration for BimBeau MultiSteps plugin

// Stripe and general options
$modeStripe = get_option('bimbeau_ms_mode', 'PROD');
if ($modeStripe === 'PROD') {
    $GLOBALS['stripeOptions'] = [
        'payment-link' => get_option('bimbeau_ms_payment_link', 'https://buy.stripe.com/14k5mzfDf86f7U4cO6'),
        'secret-key'   => get_option('bimbeau_ms_secret_key', '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')
    ];
} else {
    $GLOBALS['stripeOptions'] = [
        'payment-link' => get_option('bimbeau_ms_payment_link_test', 'https://buy.stripe.com/test_bIY2bbckteyjgbm4gg'),
        'secret-key'   => get_option('bimbeau_ms_secret_key_test', 'sk_test_51JUCdyHKX5FyumXs1WF9dsIgDPgJu2a05VtBgspxxA86CDwrkGy3cPadlSXx9LyZhP5iDitOcQ8m62dvEgsWESoT007cVCjJiA')
    ];
}

// General options
$GLOBALS['generalOptions'] = [
    'admin-email' => get_option('bimbeau_ms_admin_email', 'hello@secretdeco.fr'),
];

// Form choice lists
$GLOBALS['profilOptions'] = [
    'proprietaire'     => 'Propriétaire',
    'compromis'        => 'En train de signer mon compromis',
    'renseignements'   => 'Juste à la recherche de renseignements'
];

$GLOBALS['projetOptions'] = [
    'maison-villa'                => 'Une maison ou une villa',
    'appartement-loft'            => 'Un appartement ou un loft',
    'investissement-professionnel' => 'Un investissement locatif ou un local professionnel'
];

$GLOBALS['accompagnementOptions'] = [
    'renovation' => [
        'label'       => 'Rénovation',
        'description' => 'Cette prestation est pour moi si j\xE2\x80\x99ai besoin de recomposer les volumes, décloisonner, réagencer l\xE2\x80\x99espace intérieur de ma maison ou mon appartement, qu\xE2\x80\x99il soit neuf ou ancien.'
    ],
    'construction' => [
        'label'       => 'Construction',
        'description' => 'Cette prestation est pour moi si mon logement est en cours de construction, et que j\xE2\x80\x99ai besoin de conseils pour peaufiner mon projet et faire des choix durables.'
    ],
    'amenagement' => [
        'label'       => 'Aménagement',
        'description' => 'Cette prestation est pour moi si j\xE2\x80\x99ai besoin d\xE2\x80\x99aménager et décorer mes espaces existants sans toucher à la structure de ma maison ou de mon appartement.'
    ]
];

$GLOBALS['superficieOptions'] = [
    'moins-10'     => '- de 10 m\xC2\xB2',
    'entre-10-20'  => 'Entre 10 et 20 m\xC2\xB2',
    'entre-20-30'  => 'Entre 20 et 30 m\xC2\xB2',
    'entre-30-40'  => 'Entre 30 et 40 m\xC2\xB2',
    'entre-40-50'  => 'Entre 40 et 50 m\xC2\xB2',
    'entre-50-60'  => 'Entre 50 et 60 m\xC2\xB2',
    'entre-60-70'  => 'Entre 60 et 70 m\xC2\xB2',
    'entre-70-80'  => 'Entre 70 et 80 m\xC2\xB2',
    'entre-80-90'  => 'Entre 80 et 90 m\xC2\xB2',
    'entre-90-100' => 'Entre 90 et 100 m\xC2\xB2',
    'entre-100-110'=> 'Entre 100 et 110 m\xC2\xB2',
    'entre-110-120'=> 'Entre 110 et 120 m\xC2\xB2',
    'entre-120-130'=> 'Entre 120 et 130 m\xC2\xB2',
    'entre-130-140'=> 'Entre 130 et 140 m\xC2\xB2',
    'entre-140-150'=> 'Entre 140 et 150 m\xC2\xB2',
    'plus-150'     => '+ de 150 m\xC2\xB2'
];

$GLOBALS['besoinsOptions'] = [
    'renovation' => [
        'restructurer-espaces'     => 'Je souhaite restructurer mes espaces intérieurs',
        'renover-investissement'   => 'Je souhaite rénover mon logement destiné à de l\xE2\x80\x99investissement locatif',
        'renover-total'            => 'Je souhaite rénover l\xE2\x80\x99intégralité de mon logement',
        'renover-pieces'           => 'Je souhaite rénover une ou plusieurs pièces : <span class="efs_field_description">salon, salle à manger, chambre, dressing…</span>',
        'renover-pieces-techniques'=> 'Je souhaite rénover une ou plusieurs pièces techniques : <span class="efs_field_description">salle de bains, WC, cuisine, suite parentale, buanderie…</span>',
        'renover-sols'             => 'Je souhaite rénover mes sols',
        'renover-murs'             => 'Je souhaite rénover mes murs',
        'logement-non-adapte'      => 'Mon logement n\xE2\x80\x99est pas adapté à mes habitudes de vie',
        'nouveau-bureau-chambre'   => 'J\xE2\x80\x99ai besoin d\xE2\x80\x99un nouveau bureau, d\xE2\x80\x99une nouvelle chambre d\xE2\x80\x99amis…',
        'support-visuel'           => 'J\xE2\x80\x99ai du mal à me projeter, j\xE2\x80\x99ai besoin d\xE2\x80\x99un support visuel (rendu 3D)',
        'amenager-exterieur'       => 'Je souhaite aussi aménager mon extérieur: <span class="efs_field_description">balcon, terrasse, véranda, jardin, abords de piscine ou autre</span>',
        'liste-deco'               => 'Je souhaite recevoir ma liste personnalisée de références déco & matériaux proposés par Dounia'
    ],
    'construction' => [
        'configuration-optimale' => 'J\xE2\x80\x99ai besoin d\xE2\x80\x99aide pour trouver la meilleure configuration possible',
        'repartir-espaces'       => 'J\xE2\x80\x99ai besoin d\xE2\x80\x99aide pour répartir les espaces',
        'optimiser-investissement' => 'J\xE2\x80\x99ai besoin d\xE2\x80\x99aide pour optimiser un investissement locatif',
        'choix-materiaux'        => 'J\xE2\x80\x99ai besoin d\xE2\x80\x99aide pour choisir des matériaux de construction au meilleur rapport qualité/prix',
        'oeil-professionnel'     => 'J\xE2\x80\x99ai besoin d\xE2\x80\x99un œil professionnel pour confirmer mes choix',
        'trouver-style'          => 'J\xE2\x80\x99ai besoin d\xE2\x80\x99aide pour trouver mon style',
        'couleurs-interieur'     => 'J\xE2\x80\x99ai besoin d\xE2\x80\x99aide pour mettre des couleurs dans mon intérieur',
        'eclairage-pieces'       => 'J\xE2\x80\x99ai besoin d\xE2\x80\x99aide pour penser l\xE2\x80\x99éclairage de mes pièces',
        'maison-chaleureuse'     => 'J\xE2\x80\x99ai besoin d\xE2\x80\x99aide pour rendre ma nouvelle maison chaleureuse',
        'manque-inspiration'     => 'Je manque d\xE2\x80\x99inspiration pour ma cuisine, mon salon, ma chambre…',
        'support-visuel'         => 'J\xE2\x80\x99ai du mal à me projeter, j\xE2\x80\x99ai besoin d\xE2\x80\x99un support visuel (rendu 3D)',
        'amenager-exterieur'     => 'Je souhaite aussi aménager mon extérieur : <span class="efs_field_description">balcon, terrasse, véranda, jardin, abords de piscine ou autre</span>',
        'liste-deco'             => 'Je souhaite recevoir ma liste personnalisée de références déco & matériaux proposés par Dounia'
    ],
    'amenagement' => [
        'salon-chambre-cuisine' => 'Mon salon, ma chambre ou ma cuisine ne me plaisent plus',
        'deco-personnelle'      => 'Je veux une déco qui me ressemble',
        'changement-mobilier'    => 'Je change de mobilier, j\xE2\x80\x99ai besoin d\xE2\x80\x99aide pour faire mon choix',
        'optimiser-espaces'     => 'J\xE2\x80\x99ai besoin d\xE2\x80\x99optimiser mes espaces',
        'plus-rangements'       => 'J\xE2\x80\x99ai besoin de plus de rangements',
        'plus-lumiere'          => 'J\xE2\x80\x99ai besoin de plus de lumière',
        'evolution-famille'     => 'Ma vie de famille évolue : la famille s\xE2\x80\x99agrandit / les enfants deviennent grands / les enfants quittent le nid',
        'garder-meubles'        => 'Je veux garder certains de mes meubles ou tous mes meubles',
        'choix-durables'        => 'Je veux m\xE2\x80\x99assurer de faire des choix cohérents et durables',
        'amenagement-locatif'   => 'Je souhaite aménager mon bien pour un investissement locatif',
        'support-visuel'        => 'J\xE2\x80\x99ai du mal à me projeter, j\xE2\x80\x99ai besoin d\xE2\x80\x99un support visuel (rendu 3D)',
        'amenager-exterieur'    => 'Je souhaite aussi aménager mon extérieur : balcon, terrasse, véranda, jardin, abords de piscine ou autre',
        'liste-deco'            => 'Je souhaite recevoir ma liste personnalisée de références déco & matériaux proposés par Dounia'
    ]
];

$GLOBALS['demarrageOptions'] = [
    'des-que-possible' => 'Le plus tôt possible',
    'date-precise'     => 'À une date précise',
    'non-decide'       => 'Ce n\xE2\x80\x99est pas encore décidé'
];

$GLOBALS['budgetOptions'] = [
    'moins-5000'       => '- de 5 000 \xE2\x82\xAC',
    'entre-5000-10000' => 'Entre 5 000 \xE2\x82\xAC et 10 000 \xE2\x82\xAC',
    'entre-10000-20000'=> 'Entre 10 000 \xE2\x82\xAC et 20 000 \xE2\x82\xAC',
    'plus-20000'       => '+ de 20 000 \xE2\x82\xAC'
];

$GLOBALS['delaiOptions'] = [
    'standard' => 'Délai Standard sous 7 jours',
    'express'  => 'Délai Express sous 48 heures'
];

/**
 * Vérifie si un identifiant de session Stripe est valide.
 */
function bimbeau_ms_isSessionIdValid($session_id) {
    $ch = curl_init('https://api.stripe.com/v1/checkout/sessions/' . $session_id);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, $GLOBALS['stripeOptions']['secret-key'] . ':');
    $response = curl_exec($ch);
    curl_close($ch);
    $session = json_decode($response, true);
    if (isset($session['error'])) {
        return false;
    } elseif ($session && $session['payment_status'] == 'paid') {
        return true;
    }
    return false;
}

/**
 * Vérifie si l\'étape précédente a été complétée.
 */
function bimbeau_ms_isPreviousStepCompleted($current_step) {
    $step_keys = [
        '1'  => 'profil',
        '2'  => 'projet',
        '3'  => 'accompagnement',
        '4'  => 'besoins',
        '5'  => 'infos-complementaires',
        '6'  => 'superficie',
        '7'  => 'demarrage',
        '8'  => 'budget',
        '9'  => 'coordonnees',
        '10' => 'delai',
        '11' => 'remerciement'
    ];

    if ($current_step === '1') {
        return true;
    }

    $previous_step_index = array_search($current_step, array_keys($step_keys)) - 1;
    $previous_step_key   = $step_keys[array_keys($step_keys)[$previous_step_index]];

    return isset($_SESSION['estimation'][$previous_step_key]);
}

/**
 * Journalise les messages dans un fichier de log.
 */
function bimbeau_ms_custom_log($message) {
    date_default_timezone_set('Europe/Paris');
    $log_file       = dirname(__FILE__) . '/estimation-log.txt';
    $log_size_limit = 5 * 1024 * 1024; // 5 Mo
    if (file_exists($log_file) && filesize($log_file) > $log_size_limit) {
        file_put_contents($log_file, '');
    }
    $date_time = date('Y-m-d H:i:s');
    $log_entry = "[{$date_time}] {$message}\n";
    error_log($log_entry, 3, $log_file);
}

/**
 * Envoie un email HTML personnalisé et log l\'activité.
 */
function bimbeau_ms_sendCustomEmail($to, $subject, $content, $customHeader = '', $returnHtml = false) {
    global $phpmailer;
    if (isset($phpmailer)) {
        $phpmailer->SMTPDebug = 2;
    }
    $logoId                  = 7301;
    $pageBackgroundColor     = '#f7f3f2';
    $containerBackgroundColor = '#ffffff';
    $fontFamily              = '"Helvetica Neue",Helvetica,Roboto,Arial,sans-serif';
    $headerSettings          = [
        'backgroundColor' => '#000000',
        'textColor'       => '#ffffff',
        'fontSize'        => '30px',
    ];
    $contentSettings         = [
        'backgroundColor' => '#ffffff',
        'textColor'       => '#000000',
        'fontSize'        => '14px',
    ];
    $footerSettings          = [
        'textColor' => '#a19f9c',
        'fontSize'  => '12px',
    ];
    $logoUrl = wp_get_attachment_url($logoId);
    if (!$logoUrl) {
        error_log("❌ ERREUR : L'URL du logo n'a pas pu être récupérée pour l'ID $logoId.");
    } else {
        error_log("✅ Succès : URL du logo récupérée - $logoUrl");
    }
    $emailHtml = "<!DOCTYPE html><html><head><meta http-equiv='Content-Type' content='text/html; charset=UTF-8'><meta content='width=device-width, initial-scale=1.0' name='viewport'><title>Secret Déco</title><style>@media screen and (max-width: 600px){#header_wrapper{padding: 27px 36px !important; font-size: 24px;}#body_content_inner{font-size: 10px !important;}}</style></head><body style='background-color: {$pageBackgroundColor}; font-family: {$fontFamily};'><center><a href='" . get_site_url() . "'><img src='{$logoUrl}' alt='Logo' style='width: 400px; display: block; margin: 20px auto; max-width: 100%;'/></a><div style='border: 1px solid #dedbda; box-shadow: 0 1px 4px rgba(0,0,0,.1); width: 600px; max-width: 100%; background-color: {$containerBackgroundColor}; margin: 0 auto; box-sizing: border-box;'><div id='header_wrapper' style='background-color: {$headerSettings['backgroundColor']}; color: {$headerSettings['textColor']}; font-size: {$headerSettings['fontSize']}; font-weight: 300; padding: 40px; text-align: center;'>{$customHeader}</div><div style='line-height: 150%; text-align: left; background-color: {$contentSettings['backgroundColor']}; color: {$contentSettings['textColor']}; font-size: {$contentSettings['fontSize']}; padding: 40px;'>{$content}</div></div><div style='color: {$footerSettings['textColor']}; font-size: {$footerSettings['fontSize']}; padding: 25px; text-align: center;'>Secret Déco – Révélons le potentiel déco de votre intérieur</div></center></body></html>";

    if ($returnHtml) {
        return $emailHtml;
    }
    $headers = [
        'Content-Type: text/html; charset=UTF-8',
        'From: Secret Déco <hello@secretdeco.fr>',
    ];
    error_log("📤 Tentative d'envoi de l'email à $to avec le sujet : $subject");
    $emailSentToRecipient = wp_mail($to, $subject, $emailHtml, $headers);
    if ($emailSentToRecipient) {
        error_log("✅ Succès : L'email a été envoyé à $to.");
    } else {
        error_log("❌ ERREUR : L'email n'a pas pu être envoyé à $to.");
        if (isset($phpmailer) && $phpmailer->ErrorInfo) {
            error_log("📌 Détail de l'erreur PHPMailer : " . $phpmailer->ErrorInfo);
        }
    }
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

/**
 * Envoie un email de rappel pour une demande d'estimation.
 */
function bimbeau_ms_send_estimation_reminder($uniqueId) {
    $estimationDetails = get_option('estimation_reminder_' . $uniqueId);
    if (!$estimationDetails) {
        error_log("Les détails de l'estimation pour l'ID $uniqueId n'ont pas été récupérés");
        return;
    }
    $prenom            = htmlspecialchars($estimationDetails['prenom']);
    $nom               = htmlspecialchars($estimationDetails['nom']);
    $dateEstimation    = $estimationDetails['dateEstimation'];
    $emailAdmin        = $estimationDetails['emailAdmin'];
    $detailsEstimation = $estimationDetails['detailsEstimation'];
    $subjectAdmin = "[Secret Déco] L'estimation travaux de " . $prenom . ' ' . $nom . " est attendue pour le " . $dateEstimation;
    $headerAdmin  = 'Rappel demande d\'estimation';
    $startAdmin   = '<h2>Bonjour !</h2><p>Voici les détails de la demande d\'estimation :</p>';
    $endAdmin     = '<p>Cette personne attend une estimation pour le ' . $dateEstimation . '.</p>';
    $contentAdmin = $startAdmin . $detailsEstimation . $endAdmin;
    bimbeau_ms_sendCustomEmail($emailAdmin, $subjectAdmin, $contentAdmin, $headerAdmin, false);
    delete_option('estimation_reminder_' . $uniqueId);
}
add_action('send_estimation_reminder', 'bimbeau_ms_send_estimation_reminder', 10, 1);
