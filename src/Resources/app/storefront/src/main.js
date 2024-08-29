import VariantHoverSwitchPlugin from './plugin/variant-hover-switch.plugin';
import OffCanvasCartSwitchOptionPlugin from "./plugin/offcanvas-cart-switch-option.plugin";
import VariantListingGalleryPlugin from "./plugin/variant-listing-gallery.plugin";
import WishlistExtensionPlugin from "./plugin/wishlist-extension.plugin";

const PluginManager = window.PluginManager;

if (window.sasShowOnProductCard) {
    PluginManager.register('VariantHoverSwitch', VariantHoverSwitchPlugin, '[data-variant-hover-switch]');
}

if (window.sasShowGalleryOnListingPage) {
    PluginManager.register('VariantListingGallery', VariantListingGalleryPlugin, '.product-box');
}

if (window.sasShowOnOffCanvasCart) {
    PluginManager.override('OffCanvasCart', OffCanvasCartSwitchOptionPlugin, '[data-offcanvas-cart]');
}

if (window.sasShowOnWishlistPage) {
    PluginManager.register('SasWishlistExtension', WishlistExtensionPlugin, '[data-wishlist-storage]');
}
