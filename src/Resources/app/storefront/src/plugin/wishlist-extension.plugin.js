import Plugin from 'src/plugin-system/plugin.class';

export default class WishlistExtensionPlugin extends Plugin {
    init() {
        const plugin = window.PluginManager.getPluginInstanceFromElement(document.querySelector('[data-wishlist-storage]'), 'WishlistStorage');
        plugin.$emitter.subscribe(
            'Wishlist/onProductRemoved',
            () => {
                window.location.reload();
            }
        );
    }
}
