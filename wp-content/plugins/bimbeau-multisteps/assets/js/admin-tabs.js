(function ( wp ) {
    const { createElement, render } = wp.element;
    const { TabPanel } = wp.components;

    function init() {
        const wrapper = document.getElementById( 'bimbeau-ms-admin-tabs' );
        if ( ! wrapper ) {
            return;
        }

        const tabs = JSON.parse( wrapper.dataset.tabs );
        const current = wrapper.dataset.current;
        const panelTabs = tabs.map( ( tab ) => ( { name: tab.slug, title: tab.label } ) );

        const onSelect = ( name ) => {
            const tab = tabs.find( ( t ) => t.slug === name );
            if ( tab ) {
                window.location = tab.url;
            }
        };

        const mountPoint = document.createElement( 'div' );
        wrapper.parentNode.insertBefore( mountPoint, wrapper );

        render( createElement( TabPanel, { tabs: panelTabs, onSelect, initialTabName: current } ), mountPoint );

        wrapper.style.display = 'none';
    }

    if ( document.readyState === 'loading' ) {
        document.addEventListener( 'DOMContentLoaded', init );
    } else {
        init();
    }
})( window.wp );
