<?php declare(strict_types=1);

namespace SasVariantSwitch;

use Shopware\Core\Framework\Plugin;

class SasVariantSwitch extends Plugin
{
    public const SHOW_ON_PRODUCT_CARD = 'SasVariantSwitch.config.showOnProductCard';
    public const PREVIEW_ON_HOVER = 'SasVariantSwitch.config.previewVariantOnHover';
    public const SHOW_ON_OFFCANVAS_CART = 'SasVariantSwitch.config.showOnOffCanvasCart';
    public const SHOW_ON_CART_PAGE = 'SasVariantSwitch.config.showOnCartPage';
    public const SHOW_ON_CHECKOUT_CONFIRM_PAGE = 'SasVariantSwitch.config.showOnCheckoutConfirmPage';
    public const SHOW_GALLERY_ON_PRODUCT_CART = 'SasVariantSwitch.config.showGalleryOnProductCard';
    public const SHOW_VARIANT_CONFIGURATION_TITLE_ON_LISTING = 'SasVariantSwitch.config.showVariantConfigurationTitleOnListing';
    public const SHOW_VARIANT_CONFIGURATION_PROPERTY_GROUP_TITLE_ON_LISTING = 'SasVariantSwitch.config.showVariantConfigurationPropertyGroupTitleOnListing';
    public const SHOW_ON_WISHLIST_PAGE = 'SasVariantSwitch.config.showOnWishlistPage';
}
