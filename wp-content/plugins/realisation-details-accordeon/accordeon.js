(function ($) {
    "use strict";

    $(window).on('elementor/frontend/init', function () {
        // Fonction pour gérer l'ouverture/fermeture de l'accordeon
        function toggleAccordeon(item) {
            var content = item.find('.accordeon-content');
            content.slideToggle(300, function () {
                // Toggle la classe 'open' sur l'élément '.accordeon-item' lors de l'ouverture/fermeture
                item.toggleClass('open', content.is(':visible'));
            });
            item.find('.accordeon-state-icon').toggleClass('open');
        }

        // Événement clic sur l'élément accordeon-title
        $(document).on('click', '.realisation_details_accordeon .accordeon-item .accordeon-title', function (e) {
            e.stopPropagation(); // Empêche l'événement de se propager
            var item = $(this).closest('.accordeon-item');
            toggleAccordeon(item);
        });

        // Événement clic sur l'élément accordeon-item
        $(document).on('click', '.realisation_details_accordeon .accordeon-item', function () {
            toggleAccordeon($(this));
        });
    });
})(jQuery);
