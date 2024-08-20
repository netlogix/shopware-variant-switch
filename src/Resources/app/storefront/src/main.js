import VariantHoverSwitchPlugin from './plugin/variant-hover-switch.plugin';
import OffCanvasCartSwitchOptionPlugin from "./plugin/offcanvas-cart-switch-option.plugin";
import VariantListingGallery from "./plugin/variant-listing-gallery";

const PluginManager = window.PluginManager;

if (window.sasShowOnProductCard) {
    PluginManager.register('VariantHoverSwitch', VariantHoverSwitchPlugin, '[data-variant-hover-switch]');
}

if (window.sasShowGalleryOnListingPage) {
    PluginManager.register('VariantListingGallery', VariantListingGallery, '.product-box.box-standard');
}

if (window.sasShowOnOffCanvasCart) {
    PluginManager.override('OffCanvasCart', OffCanvasCartSwitchOptionPlugin, '[data-offcanvas-cart]');
}
