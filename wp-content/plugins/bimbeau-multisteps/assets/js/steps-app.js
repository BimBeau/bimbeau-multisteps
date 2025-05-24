(function(wp){
    const { useState, useEffect } = wp.element;
    const { TextControl, TextareaControl, SelectControl, Button, Notice } = wp.components;
    const apiFetch = wp.apiFetch;

    const StepRow = ({ step, index, total, moveUp, moveDown, onDelete }) => {
        return wp.element.createElement('tr', null,
            wp.element.createElement('td', null, step.label),
            wp.element.createElement('td', null, step.question_type),
            wp.element.createElement('td', null,
                wp.element.createElement(Button, { disabled: index === 0, onClick: () => moveUp(index), isSmall: true }, '↑'),
                ' ',
                wp.element.createElement(Button, { disabled: index === total - 1, onClick: () => moveDown(index), isSmall: true }, '↓'),
                ' ',
                wp.element.createElement(Button, { isDestructive: true, onClick: () => onDelete(step.id), isSmall: true }, 'Supprimer')
            )
        );
    };

    const AddStepForm = ({ onAdd }) => {
        const [label, setLabel] = useState('');
        const [type, setType] = useState('text');
        const [choices, setChoices] = useState('');

        const submit = () => {
            onAdd({ label, question_type: type, choices });
            setLabel('');
            setType('text');
            setChoices('');
        };

        return wp.element.createElement('div', { className: 'add-step-form' },
            wp.element.createElement(TextControl, {
                label: 'Label',
                value: label,
                onChange: setLabel
            }),
            wp.element.createElement(SelectControl, {
                label: 'Type',
                value: type,
                options: [
                    { label: 'Texte', value: 'text' },
                    { label: 'Radio', value: 'radio' },
                    { label: 'Checkbox', value: 'checkbox' }
                ],
                onChange: setType
            }),
            wp.element.createElement(TextareaControl, {
                label: 'Choix (option:value, ...)',
                value: choices,
                onChange: setChoices
            }),
            wp.element.createElement(Button, { isPrimary: true, onClick: submit }, 'Ajouter')
        );
    };

    const App = () => {
        const [steps, setSteps] = useState(null);
        const [notice, setNotice] = useState('');

        const load = () => {
            apiFetch({ path: '/bimbeau-ms/v1/steps' }).then(setSteps);
        };

        useEffect(load, []);

        const saveOrder = () => {
            apiFetch({
                path: '/bimbeau-ms/v1/steps',
                method: 'POST',
                data: { action: 'update_order', order: steps.map(s => s.id) }
            }).then(() => setNotice('saved')).catch(() => setNotice('error'));
        };

        const moveUp = index => {
            if(index === 0) return;
            const newSteps = [...steps];
            [newSteps[index - 1], newSteps[index]] = [newSteps[index], newSteps[index - 1]];
            setSteps(newSteps);
        };

        const moveDown = index => {
            if(index === steps.length - 1) return;
            const newSteps = [...steps];
            [newSteps[index + 1], newSteps[index]] = [newSteps[index], newSteps[index + 1]];
            setSteps(newSteps);
        };

        const deleteStep = id => {
            apiFetch({
                path: '/bimbeau-ms/v1/steps',
                method: 'POST',
                data: { action: 'delete', id }
            }).then(load).catch(() => setNotice('error'));
        };

        const addStep = data => {
            apiFetch({
                path: '/bimbeau-ms/v1/steps',
                method: 'POST',
                data: { action: 'create', ...data }
            }).then(load).catch(() => setNotice('error'));
        };

        if (!steps) {
            return wp.element.createElement('p', null, 'Chargement...');
        }

        return wp.element.createElement('div', { className: 'bimbeau-steps-admin' },
            notice === 'saved' && wp.element.createElement(Notice, { status: 'success', onRemove: () => setNotice('') }, 'Ordre enregistr\u00e9'),
            notice === 'error' && wp.element.createElement(Notice, { status: 'error', onRemove: () => setNotice('') }, 'Erreur lors de la mise \u00e0 jour'),
            wp.element.createElement('table', { className: 'wp-list-table widefat striped' },
                wp.element.createElement('thead', null,
                    wp.element.createElement('tr', null,
                        wp.element.createElement('th', null, 'Étape'),
                        wp.element.createElement('th', null, 'Type'),
                        wp.element.createElement('th', null, 'Actions')
                    )
                ),
                wp.element.createElement('tbody', null,
                    steps.map((step, i) =>
                        wp.element.createElement(StepRow, {
                            key: step.id,
                            step,
                            index: i,
                            total: steps.length,
                            moveUp,
                            moveDown,
                            onDelete: deleteStep
                        })
                    )
                )
            ),
            wp.element.createElement(Button, { isPrimary: true, onClick: saveOrder, style: { marginTop: '1em' } }, 'Enregistrer l\'ordre'),
            wp.element.createElement('h2', null, 'Ajouter une \u00e9tape'),
            wp.element.createElement(AddStepForm, { onAdd: addStep })
        );
    };

    document.addEventListener('DOMContentLoaded', function(){
        const container = document.getElementById('bimbeau-ms-steps-app');
        if(container){
            wp.element.render(wp.element.createElement(App), container);
        }
    });
})(window.wp);
