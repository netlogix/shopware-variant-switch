import Plugin from 'src/plugin-system/plugin.class';

export default class WishlistExtensionPlugin extends Plugin {
    init() {
        // only reload on whishlist
        if (!document.querySelector('.is-ctl-wishlist')) {
            return;
        }

        const plugin = window.PluginManager.getPluginInstanceFromElement(document.querySelector('[data-wishlist-storage]'), 'WishlistStorage');
        plugin.$emitter.subscribe(
            'Wishlist/onProductRemoved',
            () => {
                window.location.reload();
            }
        );
    }
}
