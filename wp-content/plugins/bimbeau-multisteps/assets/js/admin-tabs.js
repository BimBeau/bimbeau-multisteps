(function (wp) {
    const { createElement, render } = wp.element;
    const { TabPanel } = wp.components;

    function init() {
        const container = document.getElementById('bimbeau-ms-admin-tabs');
        if (!container || !wp || !wp.element || !wp.components) {
            return;
        }

        const fallback = document.getElementById('bimbeau-ms-admin-tabs-fallback');
        const hideFallback = () => {
            if (fallback) {
                fallback.style.display = 'none';
            }
        };

        const tabs = JSON.parse(container.dataset.tabs);
        const current = container.dataset.current;
        const panelTabs = tabs.map((tab) => ({ name: tab.slug, title: tab.label }));

        const onSelect = (name) => {
            const tab = tabs.find((t) => t.slug === name);
            if (tab) {
                window.location = tab.url;
            }
        };

        render(
            createElement(TabPanel, {
                tabs: panelTabs,
                onSelect,
                initialTabName: current,
            }),
            container
        );

        hideFallback();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})(window.wp);
