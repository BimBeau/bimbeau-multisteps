<?php

if (!defined('ABSPATH')) {
    exit;
}

class BimStats_Utils {

/**
     * Fonction pour ajouter une variable dans le localStorage si l'utilisateur est sur le back-office
     */
    public static function set_admin_localstorage() {
        if (is_admin()) {
            ?>
            <script type="text/javascript">
                // Ajouter une variable dans le localStorage si elle n'existe pas
                if (localStorage.getItem('bimstats_admin_user') === null) {
                    localStorage.setItem('bimstats_admin_user', 'true');
                }
            </script>
            <?php
        }
    }

    // Initialiser les hooks dans le back-office
    public static function init_hooks() {
        add_action('admin_head', array(__CLASS__, 'set_admin_localstorage'));
    }

    
    /**
     * Fonction pour récupérer l'adresse IP du visiteur
     */
    public static function get_visitor_ip() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            // IP depuis le partage Internet
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            // IP derrière un proxy
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            // IP directe
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }

    /**
     * Journalise les messages dans un fichier de log spécifique au plugin.
     * Ne journalise que si la constante BIMSTATS_LOG_ENABLED est définie à true.
     */
    public static function bs_log($message) {
        // Vérifier si les logs sont activés
        if (defined('BIMSTATS_LOG_ENABLED') && !BIMSTATS_LOG_ENABLED) {
            return; // Ne pas enregistrer le log si désactivé
        }

        // Définir le chemin vers le fichier de journal
        $log_file = plugin_dir_path(__FILE__) . '../bimstats.log';

        // Limite de taille du fichier de log (en octets)
        $log_size_limit = 5 * 1024 * 1024; // 5 Mo

        // Vérifier si le fichier de log existe et s'il dépasse la limite de taille
        if (file_exists($log_file) && filesize($log_file) > $log_size_limit) {
            // Si le fichier dépasse la limite, le réinitialiser
            file_put_contents($log_file, '');
        }

        // Format de date et heure pour chaque entrée de log
        $date_time = date('Y-m-d H:i:s');

        // Récupérer le fichier et la ligne d'où le log est appelé
        $backtrace = debug_backtrace();
        $file = isset($backtrace[0]['file']) ? $backtrace[0]['file'] : 'N/A';
        $line = isset($backtrace[0]['line']) ? $backtrace[0]['line'] : 'N/A';

        // Construction du message complet à enregistrer, incluant le fichier et la ligne
        $log_entry = "[{$date_time}] [File: {$file}] [Line: {$line}] {$message}\n";

        // Enregistrement du message dans le fichier de journal
        error_log($log_entry, 3, $log_file);
    }

    /**
     * Vérifie si le visiteur actuel est un robot.
     * 
     * @return bool True si c'est un robot, False sinon.
     */
    public static function is_bot() {
        // Liste des user-agents connus de robots
        $bots = array(
            'googlebot',
            'bingbot',
            'yandexbot',
            'slurp', // Yahoo
            'duckduckbot',
            'baiduspider',
            'sogou',
            'exabot',
            'facebot',
            'ia_archiver', // Wayback machine
            'mj12bot', // Majestic-12
            'semrushbot', // SEMrush
            'ahrefsbot', // Ahrefs
            'rogerbot', // Moz
            'seznambot', // Seznam
            'dotbot', // Moz/DotBot
            'pingdom', // Pingdom
            'uptimerobot', // Uptime monitoring
            'prerender', // Prerender.io services
            'coccocbot', // Coccoc search engine
            'applebot', // Apple search
            '360spider', // 360.cn Chinese search engine
            'linkpadbot', // Linkpad backlink crawler
            'petalbot', // Huawei Petal Search
            'serpstatbot', // Serpstat
            'bytespider', // ByteDance (TikTok)
            'bingpreview', // Bing preview bot
            'jobboersebot', // Jobbörse bot for job scraping
            'python-requests', // Bots using python requests
            'pythonurllib', // Bots using python urllib
            'datadog', // DataDog monitoring
            'monitis', // Monitis monitoring
        );
        
        // Vérifier l'en-tête 'User-Agent'
        $user_agent = strtolower($_SERVER['HTTP_USER_AGENT']);

        // Chercher si le user-agent correspond à un robot
        foreach ($bots as $bot) {
            if (strpos($user_agent, $bot) !== false) {
                return true;
            }
        }

        return false;
    }
}
