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

        function toggleExtras($input) {
            var target = $input.data('extra-target');
            if (!target) return;
            if ($input.is(':radio')) {
                $('input[name="' + $input.attr('name') + '"][data-extra-target]').each(function(){
                    var t = $(this).data('extra-target');
                    if (t) $('#' + t).hide();
                });
            }
            if ($input.is(':checked')) {
                $('#' + target).show();
            } else {
                $('#' + target).hide();
            }
        }

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

            if ($field.is(':radio, :checkbox')) {
                toggleExtras($field);
            }
        });

        $('.multi_step_form_step input[type="radio"], .multi_step_form_step input[type="checkbox"]').each(function(){
            toggleExtras($(this));
        }).on('change', function(){
            toggleExtras($(this));
        });

        function animateAndRedirect(url, delay, isFormSubmission, isFinalSubmission) {
            $('#ef_step').addClass('efs-animate-out');
            setTimeout(function() {
                if (isFormSubmission) {
                    if (isFinalSubmission) {
                        var key = window.bimbeauMsData ? bimbeauMsData.recaptchaKey : '';
                        if (key) {
                            grecaptcha.ready(function() {
                                grecaptcha.execute(key, { action: "submit" }).then(function(token) {
                                    $('#recaptchaResponse').val(token);
                                    $('form').submit();
                                });
                            });
                        } else {
                            $('form').submit();
                        }
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
