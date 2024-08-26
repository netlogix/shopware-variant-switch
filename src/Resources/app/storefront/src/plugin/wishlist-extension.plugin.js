import Plugin from 'src/plugin-system/plugin.class';

export default class WishlistExtensionPlugin extends Plugin {
    init() {
        this.$emitter.subscribe(
            'Wishlist/onProductRemoved',
            () => {
                window.location.reload();
            }
        );
    }
}
