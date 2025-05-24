(function(wp){
    const { useState, useEffect } = wp.element;
    const { TextControl, Button, Notice } = wp.components;
    const apiFetch = wp.apiFetch;

    const App = () => {
        const [fields, setFields] = useState(null);
        const [saving, setSaving] = useState(false);
        const [notice, setNotice] = useState('');

        useEffect(() => {
            apiFetch({ path: '/bimbeau-ms/v1/labels' }).then(setFields);
        }, []);

        if (!fields) {
            return wp.element.createElement('p', null, 'Chargement...');
        }

        const save = () => {
            setSaving(true);
            apiFetch({
                path: '/bimbeau-ms/v1/labels',
                method: 'POST',
                data: fields
            }).then(() => {
                setNotice('saved');
                setSaving(false);
            }).catch(() => {
                setNotice('error');
                setSaving(false);
            });
        };

        return wp.element.createElement('div', { className: 'bimbeau-labels-form' },
            notice === 'error' && wp.element.createElement(Notice, { status: 'error', onRemove: () => setNotice('') }, 'Erreur lors de la sauvegarde.'),
            notice === 'saved' && wp.element.createElement(Notice, { status: 'success', onRemove: () => setNotice('') }, 'Options enregistr\u00e9es.'),
            wp.element.createElement(TextControl, {
                label: 'Message champ requis',
                value: fields.label_required,
                onChange: (label_required) => setFields({ ...fields, label_required })
            }),
            wp.element.createElement(TextControl, {
                label: 'Message option manquante',
                value: fields.label_select_option,
                onChange: (label_select_option) => setFields({ ...fields, label_select_option })
            }),
            wp.element.createElement(TextControl, {
                label: 'Texte du bouton Continuer',
                value: fields.label_continue,
                onChange: (label_continue) => setFields({ ...fields, label_continue })
            }),
            wp.element.createElement(TextControl, {
                label: 'Message \u00e9tape inconnue',
                value: fields.label_unknown_step,
                onChange: (label_unknown_step) => setFields({ ...fields, label_unknown_step })
            }),
            wp.element.createElement(TextControl, {
                label: "Message de confirmation d'enregistrement",
                value: fields.msg_saved,
                onChange: (msg_saved) => setFields({ ...fields, msg_saved })
            }),
            wp.element.createElement(TextControl, {
                label: 'Message Elementor manquant',
                value: fields.msg_elementor_missing,
                onChange: (msg_elementor_missing) => setFields({ ...fields, msg_elementor_missing })
            }),
            wp.element.createElement(TextControl, {
                label: 'Message rappel d\u00e9sactiv\u00e9',
                value: fields.msg_reminder_disabled,
                onChange: (msg_reminder_disabled) => setFields({ ...fields, msg_reminder_disabled })
            }),
            wp.element.createElement(TextControl, {
                label: 'Message accueil du tableau de bord',
                value: fields.msg_dashboard_welcome,
                onChange: (msg_dashboard_welcome) => setFields({ ...fields, msg_dashboard_welcome })
            }),
            wp.element.createElement(Button, { isPrimary: true, isBusy: saving, onClick: save }, 'Enregistrer')
        );
    };

    document.addEventListener('DOMContentLoaded', function(){
        const container = document.getElementById('bimbeau-ms-labels-app');
        if(container){
            wp.element.render(wp.element.createElement(App), container);
        }
    });
})(window.wp);
