(function ($) {
    "use strict";

    $(window).on("elementor/frontend/init", function () {
        $('.sc-progress-circle').each(function () {
            var $this = $(this),
                progressCurrent = $this.data('progress-current');

            // Animation de la progression de l'étape précédente à l'étape actuelle
            $this.find('.sc-circle').animate({ 'stroke-dashoffset': progressCurrent }, 600);
        });
    });
})(jQuery);
