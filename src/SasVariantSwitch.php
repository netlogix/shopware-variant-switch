<?php declare(strict_types=1);

namespace SasVariantSwitch;

use Shopware\Core\Content\Property\PropertyGroupDefinition;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Plugin\Context\UpdateContext;
use Shopware\Core\System\CustomField\CustomFieldTypes;

class SasVariantSwitch extends Plugin
{
    public const SHOW_ON_PRODUCT_CARD = 'SasVariantSwitch.config.showOnProductCard';
    public const PREVIEW_ON_HOVER = 'SasVariantSwitch.config.previewVariantOnHover';
    public const SHOW_ON_OFFCANVAS_CART = 'SasVariantSwitch.config.showOnOffCanvasCart';
    public const SHOW_ON_CART_PAGE = 'SasVariantSwitch.config.showOnCartPage';
    public const SHOW_ON_CHECKOUT_CONFIRM_PAGE = 'SasVariantSwitch.config.showOnCheckoutConfirmPage';

    public function install(InstallContext $installContext): void
    {
        $this->addCustomFieldSet($installContext);
    }

    public function update(UpdateContext $updateContext): void
    {
        $this->addCustomFieldSet($updateContext);
    }

    private function addCustomFieldSet(InstallContext $context): void
    {
        $repository = $this->container->get('custom_field_set.repository');

        $customFieldSet = [
            'id' => md5('variant_switch_settings'),
            'name' => 'variant_switch_settings',
            'active' => true,
            'config' => [
                'label' => [
                    'en-GB' => 'variant switch settings',
                    'de-DE' => 'Variant Switch Einstellungen',
                ],
            ],
            'customFields' => [
                [
                    'id' => md5('variant_switch_use_for_listing_color_preview'),
                    'name' => 'variant_switch_use_for_listing_color_preview',
                    'type' => CustomFieldTypes::BOOL,
                    'config' => [
                        'label' => [
                            'en-GB' => 'use property in listing page as color preview',
                            'de-DE' => 'Eigentschaftswerte als Farbvorschau auf der Listenseite nutzen',
                        ],
                    ],
                ],
                [
                    'id' => md5('variant_switch_use_for_listing_size_preview'),
                    'name' => 'variant_switch_use_for_listing_size_preview',
                    'type' => CustomFieldTypes::BOOL,
                    'config' => [
                        'label' => [
                            'en-GB' => 'use property in listing page as size preview',
                            'de-DE' => 'Eigentschaftswerte als Größenvorschau auf der Listenseite nutzen',
                        ],
                    ],
                ],
            ],
            'relations' => [
                [
                    'id' => md5('nlx_configurator_options' . PropertyGroupDefinition::ENTITY_NAME),
                    'entityName' => PropertyGroupDefinition::ENTITY_NAME,
                ],
            ],
        ];

        $repository->upsert([$customFieldSet], $context->getContext());
    }
}
