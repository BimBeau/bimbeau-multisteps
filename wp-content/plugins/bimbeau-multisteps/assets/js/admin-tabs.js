(function(wp){
    const { createElement, render } = wp.element;
    const { TabPanel } = wp.components;

    document.addEventListener('DOMContentLoaded', function(){
        const container = document.getElementById('bimbeau-ms-admin-tabs');
        if(!container){
            return;
        }
        const tabs = JSON.parse(container.dataset.tabs);
        const current = container.dataset.current;
        const panelTabs = tabs.map(tab => ({ name: tab.slug, title: tab.label }));

        const onSelect = (name) => {
            const tab = tabs.find(t => t.slug === name);
            if(tab){
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
    });
})(window.wp);
