<?php
// unset($_SESSION['estimation']);
require_once dirname(__DIR__) . '/utils/ms-utils.php';

/**
 * Traitement des données des formulaires des pages "Estimation"
 */

unset($_SESSION['estimation']['errors']);

// Étape 1 - Mon profil

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['step']) && $_POST['step'] == '1') {
    if (empty($_POST['profil'])) {
        // Enregistrer le message d'erreur dans la session
        $_SESSION['estimation']['errors']['profil'] = 'Veuillez sélectionner une option pour votre profil.';
    } else {
        // Nettoyer et stocker la sélection dans la session
        $_SESSION['estimation']['profil'] = htmlspecialchars($_POST['profil']);
    }

    // Rediriger uniquement si aucune erreur n'a été trouvée
    if (empty($_SESSION['estimation']['errors']['profil'])) {
        wp_safe_redirect(home_url('/estimation/mon-projet/'));
        exit;
    }
}

// Étape 2 - Mon projet
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['step']) && $_POST['step'] == '2') {
    if (empty($_POST['projet'])) {
        // Enregistrer le message d'erreur dans la session
        $_SESSION['estimation']['errors']['projet'] = 'Veuillez sélectionner une option pour votre projet.';
    } else {
        // Nettoyer et stocker la sélection dans la session
        $_SESSION['estimation']['projet'] = htmlspecialchars($_POST['projet']);
    }

    // Rediriger uniquement si aucune erreur n'a été trouvée
    if (empty($_SESSION['estimation']['errors']['projet'])) {
        wp_safe_redirect(home_url('/estimation/mon-accompagnement/'));
        exit;
    }
}

// Étape 3 - Mon accompagnement
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['step']) && $_POST['step'] == '3') {
    if (empty($_POST['accompagnement'])) {
        // Enregistrer le message d'erreur dans la session
        $_SESSION['estimation']['errors']['accompagnement'] = 'Veuillez sélectionner une option pour l\'accompagnement.';
    } else {
        $nouvelAccompagnement = htmlspecialchars($_POST['accompagnement']);

        // Vérifier si l'accompagnement a changé
        if (isset($_SESSION['estimation']['accompagnement']) && $_SESSION['estimation']['accompagnement'] != $nouvelAccompagnement) {
            // Réinitialiser les choix de besoins si l'accompagnement a changé
            unset($_SESSION['estimation']['besoins']);
        }

        // Mettre à jour l'accompagnement dans la session
        $_SESSION['estimation']['accompagnement'] = $nouvelAccompagnement;
    }

    // Rediriger uniquement si aucune erreur n'a été trouvée
    if (empty($_SESSION['estimation']['errors']['accompagnement'])) {
        wp_safe_redirect(home_url('/estimation/mes-besoins/'));
        exit;
    }
}

// Étape 4 - Mes besoins
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['step']) && $_POST['step'] == '4') {
    if (empty($_POST['besoins']) || !is_array($_POST['besoins'])) {
        $_SESSION['estimation']['errors']['besoins'] = 'Veuillez sélectionner au moins une option pour vos besoins.';
    } else {
        // Sécuriser et assigner les besoins
        $_SESSION['estimation']['besoins'] = array_map('htmlspecialchars', $_POST['besoins']);
    }

    // Redirection conditionnelle
    if (empty($_SESSION['estimation']['errors']['besoins'])) {
        wp_safe_redirect(home_url('/estimation/informations-complementaires/'));
        exit;
    }
}


// Étape 5 - Informations complémentaires
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['step']) && $_POST['step'] == '5') {
    // Nettoyer la valeur du champ facultatif 'infos-complementaires'
    $_SESSION['estimation']['infos-complementaires'] = isset($_POST['infos-complementaires']) ? htmlspecialchars($_POST['infos-complementaires']) : '';

    // Redirection vers la prochaine étape
    wp_safe_redirect(home_url('/estimation/superficie/'));
    exit;
}


// Étape 6 - Superficie
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['step']) && $_POST['step'] == '6') {
    if (empty($_POST['superficie'])) {
        // Enregistrer le message d'erreur dans la session
        $_SESSION['estimation']['errors']['superficie'] = 'Veuillez sélectionner une option pour la superficie.';
    } else {
        // Nettoyer et stocker la sélection dans la session
        $_SESSION['estimation']['superficie'] = htmlspecialchars($_POST['superficie']);
    }

    // Rediriger uniquement si aucune erreur n'a été trouvée
    if (empty($_SESSION['estimation']['errors']['superficie'])) {
        wp_safe_redirect(home_url('/estimation/demarrage/'));
        exit;
    }
}

// Étape 7 - Démarrage du projet
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['step']) && $_POST['step'] == '7') {
    $dateDemarrage = null; // Initialisation de $dateDemarrage

    if (empty($_POST['delai'])) {
        $_SESSION['estimation']['errors']['demarrage'] = 'Veuillez sélectionner un délai pour le démarrage du projet.';
    } elseif ($_POST['delai'] === 'date-precise') {
        if (empty($_POST['date-demarrage'])) {
            $_SESSION['estimation']['errors']['date-demarrage'] = 'Veuillez indiquer la date de démarrage envisagée.';
        } else {
            $dateDemarrageTemp = htmlspecialchars($_POST['date-demarrage']);
            $dateDemarrage = DateTime::createFromFormat('Y-m-d', $dateDemarrageTemp); // Création de l'objet DateTime
            $dateMinimum = new DateTime();
            $dateMinimum->modify('+7 days');

            if ($dateDemarrage < $dateMinimum) {
                $_SESSION['estimation']['errors']['date-demarrage'] = 'La date de démarrage doit être au moins 7 jours après la date actuelle.';
                $dateDemarrage = null; // Réinitialisation si la date n'est pas valide
            }
        }
    }

    // Continuer le traitement si pas d'erreur pour 'date-demarrage'
    if (!isset($_SESSION['estimation']['errors']['date-demarrage'])) {
        $_SESSION['estimation']['demarrage'] = [
            'delai' => htmlspecialchars($_POST['delai']),
            'date-demarrage' => $dateDemarrage ? $dateDemarrage->format('Y-m-d') : null
        ];
    }

    // Rediriger uniquement si aucune erreur n'a été trouvée
    if (empty($_SESSION['estimation']['errors']['demarrage']) && empty($_SESSION['estimation']['errors']['date-demarrage'])) {
        wp_safe_redirect(home_url('/estimation/mon-budget/'));
        exit;
    }
}


// Étape 8 - Mon budget
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['step']) && $_POST['step'] == '8') {
    if (empty($_POST['budget'])) {
        $_SESSION['estimation']['errors']['budget'] = 'Veuillez sélectionner une option pour votre budget.';
    } else {
        $_SESSION['estimation']['budget'] = htmlspecialchars($_POST['budget']);
    }

    // Rediriger uniquement si aucune erreur n'a été trouvée
    if (empty($_SESSION['estimation']['errors']['budget'])) {
        wp_safe_redirect(home_url('/estimation/mes-coordonnees/'));
        exit;
    }
}


// Étape 9 - Mes coordonnées
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['step']) && $_POST['step'] == '9') {
    $erreurs = [];
    $prenom = trim($_POST['prenom']);
    $nom = trim($_POST['nom']);
    $email = trim($_POST['email']);
    $telephone = trim($_POST['telephone']);
    $adresse = trim($_POST['adresse']);

    // Sauvegarde des valeurs saisies dans la session
    $_SESSION['estimation']['coordonnees'] = [
        'prenom' => htmlspecialchars($prenom),
        'nom' => htmlspecialchars($nom),
        'email' => htmlspecialchars($email),
        'telephone' => htmlspecialchars($telephone),
        'adresse' => htmlspecialchars($adresse)
    ];

    // Validation des champs
    if (empty($prenom)) $erreurs['prenom'] = 'Le prénom est requis.';
    if (empty($nom)) $erreurs['nom'] = 'Le nom est requis.';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $erreurs['email'] = 'Une adresse email valide est requise.';
    if (empty($telephone)) $erreurs['telephone'] = 'Le numéro de téléphone est requis.';
    if (empty($adresse)) $erreurs['adresse'] = 'L\'adresse est requise.';

    if (count($erreurs) === 0) {
        wp_safe_redirect(home_url('/estimation/envoyer-ma-demande/'));
        exit;
    } else {
        $_SESSION['estimation']['errors']['coordonnees'] = $erreurs;
    }
}


// Étape 10 - Envoyer ma demande
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['step']) && $_POST['step'] == '10') {
    if (empty($_POST['delai'])) {
        $_SESSION['estimation']['errors']['delai'] = 'Veuillez sélectionner un délai pour recevoir votre estimation.';
    } else {
        $_SESSION['estimation']['delai'] = htmlspecialchars($_POST['delai']);

        // Validation de reCAPTCHA v3
        $recaptchaResponse = $_POST['recaptcha_response'];
        $secretKey = '6LdmaecoAAAAALw_WpUQRRekPLF4DXVUgdEl4KnO';
        $verifyResponse = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=$secretKey&response=$recaptchaResponse");
        $responseData = json_decode($verifyResponse);
        if (!$responseData->success) {
            // En cas d'échec de la validation reCAPTCHA, définir un message d'erreur
            $_SESSION['estimation']['errors']['recaptcha'] = 'La vérification reCAPTCHA a échoué. Veuillez réessayer.';
        }
    }

    // Rediriger uniquement si aucune erreur n'a été trouvée
    if (empty($_SESSION['estimation']['errors'])) {
        // Ici, vous pouvez implémenter une logique pour traiter les données de l'estimation
        // Par exemple, envoyer les informations par email ou les enregistrer dans une base de données

        // Redirection conditionnelle en fonction du délai
        if ($_SESSION['estimation']['delai'] === 'standard') {
            wp_safe_redirect(home_url('/estimation/bravo/'));
        } elseif ($_SESSION['estimation']['delai'] === 'express') {
            wp_safe_redirect($GLOBALS['stripeOptions']['payment-link']);
        }
        exit;
    }
}


function estimation_etape_shortcode($atts) {

    // Récupération des attributs du shortcode
    $atts = shortcode_atts(array(
        'etape' => '1', // Valeur par défaut
    ), $atts);
    $etape = $atts['etape'];

    // Vérifier si nous sommes dans l'éditeur ou en prévisualisation Elementor
    $elementor_preview_active = \Elementor\Plugin::$instance->preview->is_preview_mode();
    $elementor_edit_mode = \Elementor\Plugin::$instance->editor->is_edit_mode();
    $elementor_active = $elementor_preview_active || $elementor_edit_mode;

    // Vérifier si l'étape précédente a été complétée
    if (!$elementor_active) {
        if (!bimbeau_ms_isPreviousStepCompleted($etape)) {
            // Script pour vérifier si on est dans l'éditeur Elementor
            echo '<script>window.location.href = "/estimation/mon-profil/";</script>';
            exit;
        }
    }

    // Mise en mémoire tampon du contenu HTML
    ob_start();

    // Afficher le formulaire en fonction de l'étape spécifiée
    switch ($etape) {
        case '1':

            // Récupération du modèle d'affichage des champs du formulaire
            $radio_column_template = \Elementor\Plugin::$instance->frontend->get_builder_content('4240'); // Estimation Field - Radio Column

            $profilChoisi = isset($_SESSION['estimation']['profil']) ? $_SESSION['estimation']['profil'] : '';

            echo '<form id="mon-profil-form" action="" method="POST" class="estimation_form_step efs_columns efs_gap_M">
                    <input type="hidden" name="step" value="1">';

            $animation_delay = 0;
            foreach ($GLOBALS['profilOptions'] as $value => $label) {
                $checked = ($profilChoisi == $value) ? 'checked' : '';

                // Remplacez les balises de remplacement par les données réelles
                $radio_column_html = str_replace('animation_delay&quot;:200', 'animation_delay&quot;:' . $animation_delay, $radio_column_template);
                $radio_column_html = str_replace('{LABEL}', $label . '<input type="radio" id="' . $value . '" name="profil" value="' . $value . '" ' . $checked . ' required>', $radio_column_html);
                $radio_column_html = str_replace('<div class="elementor-icon-box-wrapper">', '<div class="elementor-icon-box-wrapper"><div class="elementor-icon-box-icon"><span class="elementor-icon elementor-animation-">' . file_get_contents(BIMBEAU_MS_DIR . 'assets/img/' . $value . '.svg') . '</span></div>', $radio_column_html);
                echo $radio_column_html;
                $animation_delay += 100;
            }

            echo '</form>';
            break;

        case '2':

            // Récupération du modèle d'affichage des champs du formulaire
            $radio_column_template = \Elementor\Plugin::$instance->frontend->get_builder_content('4240'); // Estimation Field - Radio Column

            $projetChoisi = isset($_SESSION['estimation']['projet']) ? $_SESSION['estimation']['projet'] : '';

            echo '<form id="mon-projet-form" action="" method="POST" class="estimation_form_step efs_columns efs_gap_M">
                        <input type="hidden" name="step" value="2">';


            $animation_delay = 0;
            foreach ($GLOBALS['projetOptions'] as $value => $label) {
                $checked = ($projetChoisi == $value) ? 'checked' : '';

                // Remplacez les balises de remplacement par les données réelles
                $radio_column_html = str_replace('animation_delay&quot;:200', 'animation_delay&quot;:' . $animation_delay, $radio_column_template);
                $radio_column_html = str_replace('{LABEL}', $label . '<input type="radio" id="' . $value . '" name="projet" value="' . $value . '" ' . $checked . ' required>', $radio_column_html);
                $radio_column_html = str_replace('<div class="elementor-icon-box-wrapper">', '<div class="elementor-icon-box-wrapper"><div class="elementor-icon-box-icon"><span class="elementor-icon elementor-animation-">' . file_get_contents(BIMBEAU_MS_DIR . 'assets/img/' . $value . '.svg') . '</span></div>', $radio_column_html);
                echo $radio_column_html;
                $animation_delay += 100;
            }


            echo '</form>';
            break;

        case '3':

            // Récupération du modèle d'affichage des champs du formulaire
            $radio_column_template = \Elementor\Plugin::$instance->frontend->get_builder_content('4336'); // Estimation Field – Radio Row

            $accompagnementChoisi = isset($_SESSION['estimation']['accompagnement']) ? $_SESSION['estimation']['accompagnement'] : '';


            echo '<form id="mon-accompagnement-form" action="" method="POST" class="estimation_form_step efs_rows efs_gap_S">
                        <input type="hidden" name="step" value="3">';

            $animation_delay = 0;
            foreach ($GLOBALS['accompagnementOptions'] as $value => $option) {
                $checked = ($accompagnementChoisi == $value) ? 'checked' : '';

                // Remplacez les balises de remplacement par les données réelles
                $radio_column_html = str_replace('&quot;animation&quot;:&quot;fadeInUp&quot;,', '&quot;animation&quot;:&quot;none&quot;,', $radio_column_template);
                $radio_column_html = str_replace('{LABEL}', $option['label'] . '<input type="radio" id="' . $value . '" name="accompagnement" value="' . $value . '" ' . $checked . ' required><div class="efs_field_description">' . $option['description'] . '</div>', $radio_column_html);
                $radio_column_html = str_replace('<div class="elementor-icon-box-wrapper">', '<div class="elementor-icon-box-wrapper"><div class="elementor-icon-box-icon"><span class="elementor-icon elementor-animation-">' . file_get_contents(BIMBEAU_MS_DIR . 'assets/img/' . $value . '.svg') . '</span></div>', $radio_column_html);

                echo $radio_column_html;
                $animation_delay += 100;
            }

            echo '</form>';
            break;



        case '4':

            // Récupération du modèle d'affichage des champs du formulaire
            $radio_column_template = \Elementor\Plugin::$instance->frontend->get_builder_content('4240'); // Estimation Field - Radio Column

            $besoinsChoisis = isset($_SESSION['estimation']['besoins']) ? $_SESSION['estimation']['besoins'] : [];
            $accompagnementChoisi = isset($_SESSION['estimation']['accompagnement']) ? $_SESSION['estimation']['accompagnement'] : '';

            echo '<form id="besoins-form" action="" method="POST" class="estimation_form_step efs_columns efs_gap_S no-icon efs_minheight_S">
                        <input type="hidden" name="step" value="4">';

            if (array_key_exists($accompagnementChoisi, $GLOBALS['besoinsOptions'])) {
                $animation_delay = 0;
                foreach ($GLOBALS['besoinsOptions'][$accompagnementChoisi] as $value => $label) {
                    $checked = in_array($value, $besoinsChoisis) ? 'checked' : '';

                    // Remplacez les balises de remplacement par les données réelles
                    $radio_column_html = str_replace('&quot;animation&quot;:&quot;fadeInUp&quot;,', '&quot;animation&quot;:&quot;none&quot;,', $radio_column_template);
                    $radio_column_html = str_replace('{LABEL}', '<span class="custom-input custom-input-checkbox"><input type="checkbox" id="' . $value . '" name="besoins[]" value="' . $value . '" ' . $checked . ' required><span>' . $label . '</span></span>', $radio_column_html);
                    echo $radio_column_html;
                    $animation_delay += 100;
                }
            }


            echo '</form>';

            break;

        case '5':
            $infosComplementaires = isset($_SESSION['estimation']['infos-complementaires']) ? $_SESSION['estimation']['infos-complementaires'] : '';
?>
            <!-- Formulaire pour "Informations complémentaires" (post_id = 3370) -->
            <form id="informations-complementaires-form" action="" method="POST" class="estimation_form_step efs_flex_column">
                <input type="hidden" name="step" value="5">

                <!-- Champ de texte pour les informations complémentaires -->
                <div>
                    <textarea id="infos-complementaires" name="infos-complementaires" rows="9" placeholder="N'hésitez pas à décrire les aspects particuliers de votre projet non couverts dans les questions précédentes. Par exemple, si vous avez des préférences en matière de style ou de couleur ou de toute autre information qui pourrait nous aider à mieux comprendre vos attentes."><?php echo htmlspecialchars($infosComplementaires); ?></textarea>
                </div>
            </form>
        <?php
            break;


        case '6':

            // Récupération du modèle d'affichage des champs du formulaire
            $radio_column_template = \Elementor\Plugin::$instance->frontend->get_builder_content('4336'); // Estimation Field – Radio Row

            $superficieChoisie = isset($_SESSION['estimation']['superficie']) ? $_SESSION['estimation']['superficie'] : '';

            echo '<form id="superficie-form" action="" method="POST" class="estimation_form_step efs_columns efs_gap_S no-icon">
                            <input type="hidden" name="step" value="6">';

            $animation_delay = 0;
            foreach ($GLOBALS['superficieOptions'] as $value => $option) {
                $checked = ($superficieChoisie == $value) ? 'checked' : '';

                // Remplacez les balises de remplacement par les données réelles
                $radio_column_html = str_replace('&quot;animation&quot;:&quot;fadeInUp&quot;,', '&quot;animation&quot;:&quot;none&quot;,', $radio_column_template);
                // $radio_column_html = str_replace('animation_delay&quot;:200', 'animation_delay&quot;:' . $animation_delay, $radio_column_template);
                $radio_column_html = str_replace('{LABEL}', '<span class="custom-input custom-input-radio"><input type="radio" id="' . $value . '" name="superficie" value="' . $value . '" ' . $checked . ' required>' . $option . '</span>', $radio_column_html);

                echo $radio_column_html;
                $animation_delay += 100;
            }

            echo '</form>';
            break;


        case '7':

            // Récupération du modèle d'affichage des champs du formulaire
            $radio_column_template = \Elementor\Plugin::$instance->frontend->get_builder_content('4240'); // Estimation Field - Radio Column

            $demarrageChoisi = isset($_SESSION['estimation']['demarrage']['delai']) ? $_SESSION['estimation']['demarrage']['delai'] : '';
            $dateEnvisagee = isset($_SESSION['estimation']['demarrage']['date-demarrage']) ? $_SESSION['estimation']['demarrage']['date-demarrage'] : '';

            // Calculer la date minimale (aujourd'hui + 7 jours)
            $dateMin = date('Y-m-d', strtotime('+7 days'));

            echo '<form id="demarrage-projet-form" action="" method="POST" class="estimation_form_step efs_columns efs_gap_M">
                          <input type="hidden" name="step" value="7">';


            $animation_delay = 0;
            foreach ($GLOBALS['demarrageOptions'] as $value => $label) {
                $checked = ($demarrageChoisi == $value) ? 'checked' : '';

                // Remplacez les balises de remplacement par les données réelles
                $radio_column_html = str_replace('animation_delay&quot;:200', 'animation_delay&quot;:' . $animation_delay, $radio_column_template);
                $radio_column_html = str_replace('{LABEL}', $label . '<input type="radio" id="' . $value . '" name="delai" value="' . $value . '" ' . $checked . ' required>', $radio_column_html);
                $radio_column_html = str_replace('<div class="elementor-icon-box-wrapper">', '<div class="elementor-icon-box-wrapper"><div class="elementor-icon-box-icon"><span class="elementor-icon elementor-animation-">' . file_get_contents(BIMBEAU_MS_DIR . 'assets/img/' . $value . '.svg') . '</span></div>', $radio_column_html);
                echo $radio_column_html;
                $animation_delay += 100;
            }

            $datepickerStyle = ($demarrageChoisi == 'date-precise') ? '' : 'display: none;';
            echo '<div id="datepicker-container" style="' . $datepickerStyle . '">
                          <label for="date-demarrage">Date de démarrage envisagée</label>
                          <input type="date" id="date-demarrage" name="date-demarrage" value="' . $dateEnvisagee . '" min="' . $dateMin . '" required>
                      </div>';

            echo '</form>';
        ?>
            <script>
                (function($) {
                    "use strict";

                    $(window).on('elementor/frontend/init', function() {

                        // Sélectionner l'élément parent et gérer le clic
                        $(document).on('click', '.estimation_form_step div[data-elementor-type="section"]', function() {
                            var $this = $(this);

                            // Trouver le champ enfant
                            var $field = $this.find('input[type="text"], input[type="radio"], input[type="checkbox"], input[type="email"], input[type="date"], input[type="tel"], textarea');

                            // Vérifier si le champ sélectionné a l'ID 'date-precise'
                            if ($field.attr('id') === 'date-precise') {
                                $('#datepicker-container').show();
                            } else {
                                $('#datepicker-container').hide();

                            }
                        });
                    });

                })(jQuery);
            </script>
        <?php
            break;


        case '8':
            // Récupération du modèle d'affichage des champs du formulaire
            $radio_column_template = \Elementor\Plugin::$instance->frontend->get_builder_content('4336'); // Estimation Field – Radio Row

            $budgetChoisi = isset($_SESSION['estimation']['budget']) ? $_SESSION['estimation']['budget'] : '';

            echo '<form id="budget-projet-form" action="" method="POST" class="estimation_form_step efs_rows efs_gap_S no-icon">
                        <input type="hidden" name="step" value="8">';

            $animation_delay = 0;
            foreach ($GLOBALS['budgetOptions'] as $value => $option) {
                $checked = ($budgetChoisi == $value) ? 'checked' : '';

                // Remplacez les balises de remplacement par les données réelles
                $radio_column_html = str_replace('animation_delay&quot;:200', 'animation_delay&quot;:' . $animation_delay, $radio_column_template);
                $radio_column_html = str_replace('{LABEL}', '<span class="custom-input custom-input-radio"><input type="radio" id="' . $value . '" name="budget" value="' . $value . '" ' . $checked . ' required>' . $option . '</span>', $radio_column_html);

                echo $radio_column_html;
                $animation_delay += 200;
            }

            echo '</form>';
            break;


        case '9':
            $coordonnees = isset($_SESSION['estimation']['coordonnees']) ? $_SESSION['estimation']['coordonnees'] : [];
            $prenom = isset($coordonnees['prenom']) ? $coordonnees['prenom'] : '';
            $nom = isset($coordonnees['nom']) ? $coordonnees['nom'] : '';
            $email = isset($coordonnees['email']) ? $coordonnees['email'] : '';
            $telephone = isset($coordonnees['telephone']) ? $coordonnees['telephone'] : '';
            $adresse = isset($coordonnees['adresse']) ? $coordonnees['adresse'] : '';
        ?>
            <!-- Formulaire pour "Mes coordonnées" (post_id = 3367) -->
            <form id="mes-coordonnees-form" action="" method="POST" class="estimation_form_step efs_gap_M">
                <input type="hidden" name="step" value="9">
                <div class="efs_col_50">
                    <label for="prenom">Prénom</label>
                    <input type="text" id="prenom" name="prenom" value="<?php echo htmlspecialchars($prenom); ?>" placeholder="Saisir votre Prénom" required>
                </div>
                <div class="efs_col_50">
                    <label for="nom">Nom</label>
                    <input type="text" id="nom" name="nom" value="<?php echo htmlspecialchars($nom); ?>" placeholder="Saisir votre Nom" required>
                </div>
                <div class="efs_col_50">
                    <label for="email">Adresse e-mail</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" placeholder="Saisir votre e-mail" required>
                </div>
                <div class="efs_col_50">
                    <label for="telephone">Téléphone</label>
                    <input type="tel" id="telephone" name="telephone" value="<?php echo htmlspecialchars($telephone); ?>" placeholder="Saisir votre Téléphone" required>
                </div>
                <div class="efs_col_100">
                    <label for="adresse">Adresse du logement concerné</label>
                    <input type="text" id="adresse" name="adresse" value="<?php echo htmlspecialchars($adresse); ?>" required>
                </div>
            </form>

            <!-- Script pour Google Places API -->
            <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBl12eXEAh6VqqVZF0m4kZth34_ZOmtMro&libraries=places&callback=initAutocomplete" async defer></script>
            <script>
                function initAutocomplete() {
                    // Initialisation de l'autocomplétion d'adresse
                    var autocomplete = new google.maps.places.Autocomplete(
                        document.getElementById('adresse'), {
                            types: ['geocode']
                        }
                    );
                }
            </script>

            <?php
            break;

        case '10':
            // Récupération du modèle d'affichage des champs du formulaire
            $radio_column_template = \Elementor\Plugin::$instance->frontend->get_builder_content('4826'); // Estimation Field - Radio Column

            $delaiChoisi = isset($_SESSION['estimation']['delai']) ? $_SESSION['estimation']['delai'] : '';

            // Ajout du script reCAPTCHA
            echo '<script src="https://www.google.com/recaptcha/api.js?render=6LdmaecoAAAAADI-XWX738fvkmXIN3Oq0lXqZutN"></script>';

            echo '<form id="envoyer-ma-demande-form" action="" method="POST" class="estimation_form_step efs_columns efs_gap_M">
                          <input type="hidden" name="step" value="10">';

            $animation_delay = 0;
            foreach ($GLOBALS['delaiOptions'] as $value => $label) {
                $checked = ($delaiChoisi == $value) ? 'checked' : '';

                // Déterminer le prix en fonction du choix de l'utilisateur
                $price = ($value == 'express') ? '29€' : 'Gratuit';

                $radio_column_html = str_replace('animation_delay&quot;:200', 'animation_delay&quot;:' . $animation_delay, $radio_column_template);
                $radio_column_html = str_replace('{LABEL}', $label . '<input type="radio" id="' . $value . '" name="delai" value="' . $value . '" ' . $checked . ' required>', $radio_column_html);
                $radio_column_html = str_replace('{PRICE}', $price, $radio_column_html);
                $radio_column_html = str_replace('<div class="elementor-icon-box-wrapper">', '<div class="elementor-icon-box-wrapper"><div class="elementor-icon-box-icon"><span class="elementor-icon elementor-animation-">' . file_get_contents(BIMBEAU_MS_DIR . 'assets/img/' . $value . '.svg') . '</span></div>', $radio_column_html);
                echo $radio_column_html;
                $animation_delay += 100;
            }

            // Définir l'affichage du boton et du badge Stripe en fonction de si le délai choisi est 'express' ou non
            if ($delaiChoisi !== 'express') {
            ?>
                <style>
                    .stripe-badge,
                    #estimation-express-submit {
                        display: none;
                    }
                </style>

            <?php
            } else {
            ?>
                <style>
                    #estimation-standard-submit {
                        display: none;
                    }
                </style>

            <?php
            }

            $isExpress = $delaiChoisi == 'express';
            $isExpressStyle = $isExpress ? '' : 'display: none;';
            // Affichage du style Stripe et du bouton
            echo '<style> .stripe-badge,{' . $isExpressStyle . '}</style>';

            // Ajout d'un champ caché pour la réponse reCAPTCHA
            echo '<input type="hidden" name="recaptcha_response" id="recaptchaResponse">';
            echo '</form>';

            ?>
            <script>
                (function($) {
                    "use strict";
                    $(window).on('elementor/frontend/init', function() {

                        // Sélectionner l'élément parent et gérer le clic
                        $(document).on('click', '.estimation_form_step div[data-elementor-type="section"]', function() {
                            var $this = $(this);

                            // Trouver le champ enfant
                            var $field = $this.find('input[type="text"], input[type="radio"], input[type="checkbox"], input[type="email"], input[type="date"], input[type="tel"], textarea');

                            // Mise à jour du texte du bouton en fonction du délai sélectionné
                            if ($field.attr('id') === 'express') {
                                $('.stripe-badge').show();
                                $('#estimation-express-submit').show();
                                $('#estimation-standard-submit').hide();
                            } else {
                                $('.stripe-badge').hide();
                                $('#estimation-express-submit').hide();
                                $('#estimation-standard-submit').show();
                            }
                        });
                    });
                })(jQuery);
            </script>
<?php

            break;


        case '11':

            /**
             * Vérifie si le jour donné est un jour ouvré
             */
            function isBusinessDay($date) {
                // Liste des jours fériés
                $holidays = ['31/12', '01/01', '25/12', '08/05'];
                // Convertit la date en format "d/m" pour vérifier si elle est un jour férié
                $dateFormatted = $date->format('d/m');

                // Vérifie si le jour est un dimanche ou un jour férié
                if ($date->format('N') > 6 || in_array($dateFormatted, $holidays)) {
                    return false; // Ce n'est pas un jour ouvré
                }
                return true; // C'est un jour ouvré
            }

            /**
             * Ajoute des jours ouvrés à la date donnée
             */
            function addBusinessDays($date, $daysToAdd) {
                $daysAdded = 0;
                while ($daysAdded < $daysToAdd) {
                    $date->modify('+1 day'); // Ajoute un jour
                    // Vérifie si le jour ajouté est un jour ouvré
                    if (isBusinessDay($date)) {
                        $daysAdded++;
                    }
                }
            }

            // Calcul de la date de réponse
            $dateDeReponse = new DateTime(); // Date actuelle
            $heureActuelle = (int)$dateDeReponse->format('H');

            if (!$elementor_active) {
                $delai = isset($_SESSION['estimation']['delai']) ? $_SESSION['estimation']['delai'] : '';
                // Vérification supplémentaire pour le délai express
                if ($delai === 'express') {
                    // Vérifier la présence du session_id et son validité
                    if (!isset($_GET['session_id']) || !bimbeau_ms_isSessionIdValid($_GET['session_id'])) {
                        // Redirection vers l'étape de paiement avec un message d'erreur
                        echo '<script>window.location.href = "/estimation/envoyer-ma-demande/?payment-error";</script>';
                        exit;
                    }

                    // Ajoute 2 jours ouvrés pour le délai express
                    addBusinessDays($dateDeReponse, 2);

                    // Message de succès pour le délai express
                    echo '<div class="efs-success">';
                    echo '<svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 -960 960 960" width="24"><path d="m424-296 282-282-56-56-226 226-114-114-56 56 170 170Zm56 216q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-83 31.5-156T197-763q54-54 127-85.5T480-880q83 0 156 31.5T763-763q54 54 85.5 127T880-480q0 83-31.5 156T763-197q-54 54-127 85.5T480-80Z"/></svg>';
                    echo '<div>';
                    echo '<p>Merci ! Nous avons bien reçu le paiement pour l’option : Délai Express 48h</p>';
                    echo '</div>';
                    echo '</div>';
                } elseif ($delai === 'standard') {
                    // Ajoute 7 jours ouvrés pour le délai standard
                    addBusinessDays($dateDeReponse, 7);
                } else {
                    // Redirection vers l'étape de paiement
                    wp_safe_redirect(home_url('/estimation/envoyer-ma-demande/'));
                    exit;
                }
            } else {
                // Ajoute 7 jours ouvrés par défaut
                addBusinessDays($dateDeReponse, 7);
            }

            // Ajouter un jour supplémentaire si l'heure actuelle est après 16h
            if ($heureActuelle >= 16) {
                addBusinessDays($dateDeReponse, 1); // S'assure que le jour ajouté est ouvré
            }

            // Formatage de la date pour l'affichage
            $dateEstimation = $dateDeReponse->format('d/m/Y');

            // Affichage de la date d'envoi
            echo '<div class="efs-info">';
            echo '<svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 -960 960 960" width="24"><path d="M440-280h80v-240h-80v240Zm40-320q17 0 28.5-11.5T520-640q0-17-11.5-28.5T480-680q-17 0-28.5 11.5T440-640q0 17 11.5 28.5T480-600Zm0 520q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-83 31.5-156T197-763q54-54 127-85.5T480-880q83 0 156 31.5T763-763q54 54 85.5 127T880-480q0 83-31.5 156T763-197q-54 54-127 85.5T480-80Z"/></svg>';
            echo '<div>';
            echo '<p>Vous recevrez votre estimation personnalisée le ' . $dateEstimation . '</p>';
            echo '</div>';
            echo '</div>';

            /**
             * Emails transactionnels
             */

            // Détails de la demande d'estimation
            $estimation = $_SESSION['estimation'];
            $detailsEstimation = '<h2>Détails de la demande d\'estimation</h2><ul style="padding: 0">';
            $detailsEstimation .= '<ul style="padding: 0">';
            $detailsEstimation .= '<li><b>Profil : </b>' . $GLOBALS['profilOptions'][$estimation['profil']] . '</li>';
            $detailsEstimation .= '<li><b>Projet : </b>' . $GLOBALS['projetOptions'][$estimation['projet']] . '</li>';
            $detailsEstimation .= '<li><b>Accompagnement : </b>' . $GLOBALS['accompagnementOptions'][$estimation['accompagnement']]['label'] . '</li>';
            $detailsEstimation .= '<li><b>Superficie de l\'espace concerné : </b>' . $GLOBALS['superficieOptions'][$estimation['superficie']] . '</li>';
            $detailsEstimation .= '<li><b>Besoins : </b><ul style="padding: 0">';
            foreach ($estimation['besoins'] as $besoin) {
                $detailsEstimation .= '<li>' . $GLOBALS['besoinsOptions'][$estimation['accompagnement']][$besoin] . '</li>';
            }
            $detailsEstimation .= '</ul></li>';
            $detailsEstimation .= '<li><b>Informations complémentaires : </b>' . $estimation['infos-complementaires'] . '</li>';
            if ($estimation['demarrage']['delai'] === 'date-precise') {
                $detailsEstimation .= '<li><b>Démarrage : </b>À une date précise, le ' . date('d/m/Y', strtotime($estimation['demarrage']['date-demarrage'])) . '</li>';
            } else {
                $detailsEstimation .= '<li><b>Démarrage : </b>' . $GLOBALS['demarrageOptions'][$estimation['demarrage']['delai']] . '</li>';
            }
            $detailsEstimation .= '<li><b>Budget : </b>' . $GLOBALS['budgetOptions'][$estimation['budget']] . '</li>';
            $detailsEstimation .= '<li><b>Coordonnées : </b><ul style="padding: 0">';
            $detailsEstimation .= '<li><b>Prénom : </b>' . $estimation['coordonnees']['prenom'] . '</li>';
            $detailsEstimation .= '<li><b>Nom : </b>' . $estimation['coordonnees']['nom'] . '</li>';
            $detailsEstimation .= '<li><b>Email : </b>' . $estimation['coordonnees']['email'] . '</li>';
            $detailsEstimation .= '<li><b>Téléphone : </b>' . $estimation['coordonnees']['telephone'] . '</li>';
            $detailsEstimation .= '<li><b>Adresse : </b>' . $estimation['coordonnees']['adresse'] . '</li>';
            $detailsEstimation .= '</ul></li>';
            $detailsEstimation .= '<li><b>Délai de réception de l\'estimation : </b>' . $GLOBALS['delaiOptions'][$estimation['delai']] . '</li>';
            $detailsEstimation .= '</ul>';

            // Nouvelle demande d'estimation pour l'Administrateur
            $subjectAdmin = "[Secret Déco] Nouvelle demande d'estimation de travaux attendue le " . $dateEstimation;
            $headerAdmin = "Nouvelle demande d'estimation";
            $startAdmin = "<h2>Bonjour !</h2><p>Voici les détails de la demande d'estimation :</p>";
            $endAdmin = "<p>Cette personne attend une estimation pour le " . $dateEstimation . ". Vous recevrez un rappel 24h avant cette date</p>";
            $contentAdmin = $startAdmin . $detailsEstimation . $endAdmin;
            $emailAdmin = $GLOBALS['generalOptions']['admin-email'];
            $emailSentAdmin = bimbeau_ms_sendCustomEmail($emailAdmin, $subjectAdmin, $contentAdmin, $headerAdmin, false);


            // Confirmation de votre demande d'estimation pour le Client
            $subjectClient = "[Secret Déco] Confirmation de votre demande d'estimation de travaux";
            $headerClient = "Merci pour votre demande !";
            $startClient = "<h2>Bonjour " . htmlspecialchars($_SESSION['estimation']['coordonnees']['prenom']) . ",</h2><p>Nous avons bien reçu votre demande d'estimation pour votre projet. Voici un résumé de votre demande :</p>";
            $endClient = "<p>Nous reviendrons vers vous avec une estimation détaillée le " . $dateEstimation . ".</p><p>Merci pour votre confiance,</p><p>L'équipe Secret Déco</p>";
            $contentClient = $startClient . $detailsEstimation . $endClient;
            $emailClient = $_SESSION['estimation']['coordonnees']['email'];
            $emailSentClient = bimbeau_ms_sendCustomEmail($emailClient, $subjectClient, $contentClient, $headerClient, false);


            // Vérifier si les mails ont bien été envoyés
            if (!$emailSentAdmin || !$emailSentClient) {
                // Création d'un message détaillé pour le log
                $logData = "Une erreur est survenue lors de l'envoi des emails.\n";
                $logData .= "Données de la demande :\n" . print_r($estimation, true) . "\n";
                $logData .= "Date d'estimation : " . $dateEstimation . "\n";
                $logData .= "Email Admin : " . $emailAdmin . "\n";
                $logData .= "Email Client : " . $emailClient . "\n";

                // Utilisation de la fonction de log personnalisée
                bimbeau_ms_custom_log($logData);

                // Redirection vers l'étape de paiement avec un message d'erreur
                echo '<script>window.location.href = "/estimation/envoyer-ma-demande/?email-error";</script>';
                exit;
            }


            // Les mails ont bien été envoyés, envoyer l'événement à GA4
            echo "<script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            
            gtag('event', 'demande_estimation', {
                'event_category': 'Estimation',
                'event_label': 'Demande d\'estimation envoyée',
                'value': 1
            });
            </script>";

            // Email de rappel
            $uniqueId = time();
            $estimationDetails = [
                'prenom' => $_SESSION['estimation']['coordonnees']['prenom'],
                'nom' => $_SESSION['estimation']['coordonnees']['nom'],
                'dateEstimation' => $dateEstimation,
                'emailAdmin' => $GLOBALS['generalOptions']['admin-email'],
                'detailsEstimation' => $detailsEstimation
            ];
            update_option('estimation_reminder_' . $uniqueId, $estimationDetails);
            $dateRappel = clone $dateDeReponse;
            $dateRappel->modify('-1 day')->setTime(10, 0);
            $reminderTimestamp = $dateRappel->getTimestamp();
            wp_schedule_single_event($reminderTimestamp, 'send_estimation_reminder', [$uniqueId]);

            // Réinitialise les données de l'estimation
            unset($_SESSION['estimation']);

            break;


        default:
            echo 'Étape non spécifiée ou non reconnue.';
            break;
    }

    // Afficher les erreurs d'envoi du mail, le cas échéant
    if (isset($_GET['payment-error'])) {
        echo '<div class="efs-error">';
        echo '<svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 -960 960 960" width="24" style="vertical-align: middle; margin-right: 8px;"><path d="M480-280q17 0 28.5-11.5T520-320q0-17-11.5-28.5T480-360q-17 0-28.5 11.5T440-320q0 17 11.5 28.5T480-280Zm-40-160h80v-240h-80v240Zm40 360q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-83 31.5-156T197-763q54-54 127-85.5T480-880q83 0 156 31.5T763-763q54 54 85.5 127T880-480q0 83-31.5 156T763-197q-54 54-127 85.5T480-80Z"/></svg>';
        echo '<div><p>Une erreur est survenue lors du traitement de votre paiement. Merci d’essayer à nouveau ou de choisir le délai Standard.</p></div>';
        echo '</div>';
    }

    // Afficher les erreurs de paiement, le cas échéant
    if (isset($_GET['email-error'])) {
        echo '<div class="efs-error">';
        echo '<svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 -960 960 960" width="24" style="vertical-align: middle; margin-right: 8px;"><path d="M480-280q17 0 28.5-11.5T520-320q0-17-11.5-28.5T480-360q-17 0-28.5 11.5T440-320q0 17 11.5 28.5T480-280Zm-40-160h80v-240h-80v240Zm40 360q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-83 31.5-156T197-763q54-54 127-85.5T480-880q83 0 156 31.5T763-763q54 54 85.5 127T880-480q0 83-31.5 156T763-197q-54 54-127 85.5T480-80Z"/></svg>';
        echo '<div><p>Une erreur est survenue lors de l\'envoi du mail de confirmation. Merci d’essayer à nouveau ou de contacter notre notre équipe.</p></div>';
        echo '</div>';
    }

    // Afficher les erreurs, le cas échéant
    if (!empty($_SESSION['estimation']['errors'])) {
        echo '<div class="efs-error">';
        echo '<svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 -960 960 960" width="24" style="vertical-align: middle; margin-right: 8px;"><path d="M480-280q17 0 28.5-11.5T520-320q0-17-11.5-28.5T480-360q-17 0-28.5 11.5T440-320q0 17 11.5 28.5T480-280Zm-40-160h80v-240h-80v240Zm40 360q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-83 31.5-156T197-763q54-54 127-85.5T480-880q83 0 156 31.5T763-763q54 54 85.5 127T880-480q0 83-31.5 156T763-197q-54 54-127 85.5T480-80Z"/></svg>';
        echo '<div>';
        foreach ($_SESSION['estimation']['errors'] as $error) {
            if (is_array($error)) {
                foreach ($error as $errorMessage) {
                    echo '<p>' . $errorMessage . '</p>';
                }
            } else {
                echo '<p>' . $error . '</p>';
            }
        }
        echo '</div>';
        echo '</div>';
    }

    return ob_get_clean(); // Renvoyer et nettoyer la mémoire tampon
}
add_shortcode('estimation_etape', 'estimation_etape_shortcode');


