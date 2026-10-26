<?php declare(strict_types=1);

namespace SasVariantSwitch\Tests\Storefront\Page;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use SasVariantSwitch\Storefront\Page\ProductListingConfigurationLoader;
use Shopware\Core\Content\Product\Aggregate\ProductConfiguratorSetting\ProductConfiguratorSettingCollection;
use Shopware\Core\Content\Product\Aggregate\ProductConfiguratorSetting\ProductConfiguratorSettingEntity;
use Shopware\Core\Content\Product\ProductCollection;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Content\Product\SalesChannel\Detail\AvailableCombinationResult;
use Shopware\Core\Content\Property\Aggregate\PropertyGroupOption\PropertyGroupOptionEntity;
use Shopware\Core\Content\Property\PropertyGroupCollection;
use Shopware\Core\Content\Property\PropertyGroupEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class ProductListingConfigurationLoaderTest extends TestCase
{
    public function testEachProductOnlyGetsItsOwnGroupsAndSettingsAreNotIteratedPerProduct(): void
    {
        $settings = new CountingSettingCollection();
        $products = new ProductCollection();
        $combinations = [];

        foreach (['a', 'b', 'c'] as $key) {
            $parentId = str_repeat($key, 32);
            $optionId = str_repeat($key, 31) . '1';

            $settings->add($this->createSetting($parentId, $optionId));
            $products->add($this->createVariant($key, $parentId, $optionId));

            $combination = new AvailableCombinationResult();
            $combination->addCombination([$optionId], true);
            $combinations[$parentId] = $combination;
        }

        CountingSettingCollection::$iterations = 0;

        $this->createLoader($settings, $combinations)->loadListing($products, $this->createMock(SalesChannelContext::class));

        foreach ($products as $product) {
            /** @var PropertyGroupCollection $groups */
            $groups = $product->getExtension('groups');
            static::assertCount(1, $groups);

            $options = $groups->first()->getOptions();
            static::assertCount(1, $options);
            static::assertSame(str_repeat($product->getId()[0], 31) . '1', $options->first()->getId());
        }

        // constant, not once more per product (3 products here; the old code needed 4)
        static::assertLessThanOrEqual(2, CountingSettingCollection::$iterations);
    }

    public function testProductWithoutSettingsGetsEmptyGroups(): void
    {
        $parentId = str_repeat('a', 32);
        $optionId = str_repeat('a', 31) . '1';
        $combination = new AvailableCombinationResult();
        $combination->addCombination([$optionId], true);

        $product = $this->createVariant('a', $parentId, $optionId);

        $this->createLoader(new CountingSettingCollection(), [$parentId => $combination])
            ->loadListing(new ProductCollection([$product]), $this->createMock(SalesChannelContext::class));

        static::assertCount(0, $product->getExtension('groups'));
    }

    private function createLoader(ProductConfiguratorSettingCollection $settings, array $combinations): ProductListingConfigurationLoader
    {
        $repository = $this->createMock(EntityRepository::class);
        $repository->method('search')->willReturn(
            new EntitySearchResult('product_configurator_setting', $settings->count(), $settings, null, new Criteria(), Context::createDefaultContext())
        );

        $loader = $this->getMockBuilder(ProductListingConfigurationLoader::class)
            ->setConstructorArgs([$repository, $this->createMock(Connection::class)])
            ->onlyMethods(['loadCombinations'])
            ->getMock();
        $loader->method('loadCombinations')->willReturn($combinations);

        return $loader;
    }

    private function createSetting(string $parentId, string $optionId): ProductConfiguratorSettingEntity
    {
        $group = new PropertyGroupEntity();
        $group->setId(md5('group-' . $parentId));
        $group->setSortingType('alphanumeric');
        $group->setPosition(1);

        $option = new PropertyGroupOptionEntity();
        $option->setId($optionId);
        $option->setGroupId($group->getId());
        $option->setGroup($group);
        $option->setPosition(1);
        $option->setTranslated(['name' => 'option', 'position' => 1]);

        $setting = new ProductConfiguratorSettingEntity();
        $setting->setId(md5('setting-' . $parentId));
        $setting->setProductId($parentId);
        $setting->setOption($option);
        $setting->setPosition(1);

        return $setting;
    }

    private function createVariant(string $key, string $parentId, string $optionId): ProductEntity
    {
        $product = new ProductEntity();
        $product->setId(str_repeat($key, 31) . '2');
        $product->setParentId($parentId);
        $product->setOptionIds([$optionId]);

        return $product;
    }
}

class CountingSettingCollection extends ProductConfiguratorSettingCollection
{
    public static int $iterations = 0;

    public function getIterator(): \Traversable
    {
        ++self::$iterations;

        return parent::getIterator();
    }
}
