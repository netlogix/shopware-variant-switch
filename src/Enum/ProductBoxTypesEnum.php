<?php

namespace SasVariantSwitch\Enum;

enum ProductBoxTypesEnum: string
{
    case PRODUCT_BOX_IMAGE = 'image';
    case PRODUCT_BOX_MINIMAL = 'minimal';
    case PRODUCT_BOX_STANDARD = 'standard';
    case PRODUCT_BOX_WISHLIST = 'wishlist';
}
