(function(wp){
    const { createElement, render } = wp.element;
    const { TabPanel } = wp.components;

    document.addEventListener('DOMContentLoaded', function(){
        const container = document.getElementById('bimbeau-ms-email-tabs');
        if (!container) {
            return;
        }
        const tabs = JSON.parse(container.dataset.tabs);
        const current = container.dataset.current;
        const panelContainer = container.querySelector('.tab-panel-container');
        const tabContents = container.querySelectorAll('.email-tab');

        function showTab(name) {
            tabContents.forEach(div => {
                div.style.display = div.dataset.slug === name ? 'block' : 'none';
            });
        }

        render(
            createElement(TabPanel, {
                tabs: tabs.map(t => ({ name: t.slug, title: t.title })),
                onSelect: showTab,
                initialTabName: current,
            }),
            panelContainer
        );

        showTab(current);
    });
})(window.wp);
