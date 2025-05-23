<?php if (!defined( 'ABSPATH' ) ) die( 'Forbidden' );
/*
Plugin Name: Elementor Form Submissions Access
Description: Changes the access level required for the form submissions page so that editors can view it too.
Version: 1.0
Author: Paul Tero
Author URI: http://www.tero.co.uk/
License: GPL2+
Released: 22/3/2022

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
*/

if (!class_exists('ElementorFormSubmissionsAccess'))
{

    class ElementorFormSubmissionsAccess
    {
        /** 
         * See if this user is just an editor (if they have edit_posts but not manage_options).
         * If they have manage_options, they can see the Form Submissions page anyway.
         * @return boolean
         */
        static function isJustEditor()
        {
            return current_user_can('edit_posts') && !current_user_can('manage_options');
        }

        /**
         * This is called around line 849 of wp-includes/rest-api/class-wp-rest-server.php by the ajax request which loads the data
         * into the form submissions view for Elementor (see the add_menu_page below). The ajax request checks the user has
         * the manage_options permission in modules/forms/submissions/data/controller.php within the handler's permission_callback.
         * This overrides that, and also for the call to modules/forms/submissions/data/forms-controller.php (which fills the
         * Forms dropdown on the submissions page). By changing the $route check below, you could open up more pages to editors.
         * @param array [endpoints=>hanlders]
         * @return array [endpoints=>hanlders]
         */
        static function filterRestEndpoints($endpoints)
        {
            if (self::isJustEditor()) 
            {
                error_reporting(0); // there are a couple of PHP notices which prevent the Ajax JSON data from loading
                foreach($endpoints as $route=>$handlers) //for each endpoint
                    if (strpos($route, '/elementor/v1/form') === 0) //it is one of the elementor endpoints forms, form-submissions or form-submissions/export
                        foreach($handlers as $num=>$handler) //loop through the handlers
                            if (is_array ($handler) && isset ($handler['permission_callback'])) //if this handler has a permission_callback
                                $endpoints[$route][$num]['permission_callback'] = function($request){return true;}; //handler always returns true to grant permission
            }
            return $endpoints;
        }

        /**
         * Add the submissions page to the admin menu on the left for editors only, as administrators
         * can already see it.
         */
        static function addOptionsPage()
        {
            if (!self::isJustEditor()) return;
            add_menu_page(
                'Formulaires', // Titre de la page (affiché en haut de l'écran lorsqu'on clique sur le menu)
                'Formulaires', // Intitulé du menu dans la barre latérale
                'edit_posts', // Capacité requise pour voir le menu
                'e-form-submissions', // Slug de la page (identifiant unique)
                function() {
                    echo '<div id="e-form-submissions"></div>';
                },
                'dashicons-email-alt2' // Dashicon pour l'icône du menu
            );
                    }

        /**
         * Hook up the filter and action. I can't check if they are an editor here as the wp_user_can function
         * is not available yet.
         */
        static function hookIntoWordpress()
        {
            add_filter ('rest_endpoints', array('ElementorFormSubmissionsAccess', 'filterRestEndpoints'), 1, 3);
            add_action ('admin_menu', array('ElementorFormSubmissionsAccess', 'addOptionsPage'));
        }
    }

    ElementorFormSubmissionsAccess::hookIntoWordpress();
} //a wrapper to see if the class already exists or not



