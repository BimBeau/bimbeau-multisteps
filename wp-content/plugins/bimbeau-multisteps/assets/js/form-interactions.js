(function($) {
    "use strict";

    $(window).on('elementor/frontend/init', function() {
        function verifierEtActiverClasse() {
            $('.multi_step_form_step div[data-elementor-type="section"]').each(function() {
                var $this = $(this);
                var $field = $this.find('input[type="radio"]:checked, input[type="checkbox"]:checked');
                if ($field.length) {
                    $this.addClass('active');
                }
            });
        }
        verifierEtActiverClasse();

        $(document).on('click', '.multi_step_form_step div[data-elementor-type="section"]', function() {
            var $this = $(this);
            var $field = $this.find('input[type="text"], input[type="radio"], input[type="checkbox"], input[type="email"], input[type="date"], input[type="tel"], textarea');

            if ($field.is(':radio, :checkbox')) {
                $field.prop('checked', !$field.prop('checked'));
            }

            $this.toggleClass('active');

            if ($field.is(':radio')) {
                $this.siblings().removeClass('active');
            }
        });

        function animateAndRedirect(url, delay, isFormSubmission, isFinalSubmission) {
            $('#ef_step').addClass('efs-animate-out');
            setTimeout(function() {
                if (isFormSubmission) {
                    if (isFinalSubmission) {
                        grecaptcha.ready(function() {
                            grecaptcha.execute("6LdmaecoAAAAADI-XWX738fvkmXIN3Oq0lXqZutN", { action: "submit" }).then(function(token) {
                                $('#recaptchaResponse').val(token);
                                $('form').submit();
                            });
                        });
                    } else {
                        $('form').submit();
                    }
                } else if (url) {
                    window.location = url;
                }
            }, delay);
        }

        $('a').on('click', function(e) {
            var url = $(this).attr('href');
            if (url && url !== '#' && !$(this).hasClass('no-animation')) {
                e.preventDefault();
                animateAndRedirect(url, 600, false, false);
            }
        });

        $('#multi_step-next a').on('click', function(e) {
            e.preventDefault();
            animateAndRedirect(null, 600, true, false);
        });

        $('#multi_step-standard-submit a, #multi_step-express-submit a').on('click', function(e) {
            e.preventDefault();
            animateAndRedirect(null, 600, true, true);
        });
    });
})(jQuery);
