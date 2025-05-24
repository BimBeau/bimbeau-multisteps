(function(wp){
    const { useState, useEffect } = wp.element;
    const { TextControl, SelectControl, CheckboxControl, Button, Notice } = wp.components;
    const apiFetch = wp.apiFetch;

    const App = () => {
        const [ options, setOptions ] = useState(null);
        const [ saving, setSaving ] = useState(false);
        const [ notice, setNotice ] = useState('');

        useEffect(() => {
            apiFetch({ path: '/bimbeau-ms/v1/options' }).then(setOptions);
        }, []);

        if (!options) {
            return wp.element.createElement('p', null, 'Chargement...');
        }

        const save = () => {
            setSaving(true);
            apiFetch({
                path: '/bimbeau-ms/v1/options',
                method: 'POST',
                data: options
            }).then(() => {
                setNotice('saved');
                setSaving(false);
            }).catch(() => {
                setNotice('error');
                setSaving(false);
            });
        };

        return wp.element.createElement('div', { className: 'bimbeau-settings-form' },
            notice === 'error' && wp.element.createElement(Notice, { status: 'error', onRemove: () => setNotice('') }, 'Erreur lors de la sauvegarde.'),
            notice === 'saved' && wp.element.createElement(Notice, { status: 'success', onRemove: () => setNotice('') }, 'Options enregistr\u00e9es.'),
            wp.element.createElement(SelectControl, {
                label: 'Mode Stripe',
                value: options.mode,
                options: [
                    { label: 'PROD', value: 'PROD' },
                    { label: 'TEST', value: 'TEST' }
                ],
                onChange: (mode) => setOptions({ ...options, mode })
            }),
            wp.element.createElement(TextControl, {
                label: 'Payment Link PROD',
                value: options.payment_link_prod,
                onChange: (payment_link_prod) => setOptions({ ...options, payment_link_prod })
            }),
            wp.element.createElement(TextControl, {
                label: 'Payment Link TEST',
                value: options.payment_link_test,
                onChange: (payment_link_test) => setOptions({ ...options, payment_link_test })
            }),
            wp.element.createElement(TextControl, {
                label: 'Email admin',
                type: 'email',
                value: options.admin_email,
                onChange: (admin_email) => setOptions({ ...options, admin_email })
            }),
            wp.element.createElement(TextControl, {
                label: 'Nom du menu',
                value: options.menu_label,
                onChange: (menu_label) => setOptions({ ...options, menu_label })
            }),
            wp.element.createElement(TextControl, {
                label: 'Dashicon du menu',
                value: options.menu_icon,
                onChange: (menu_icon) => setOptions({ ...options, menu_icon })
            }),
            wp.element.createElement(CheckboxControl, {
                label: 'Activer le choix du d\u00e9lai de r\u00e9ponse',
                checked: !!options.enable_delay_step,
                onChange: (enable_delay_step) => setOptions({ ...options, enable_delay_step })
            }),
            wp.element.createElement(Button, { isPrimary: true, isBusy: saving, onClick: save }, 'Enregistrer')
        );
    };

    document.addEventListener('DOMContentLoaded', function(){
        const container = document.getElementById('bimbeau-ms-settings-app');
        if(container){
            wp.element.render(wp.element.createElement(App), container);
        }
    });
})(window.wp);
