<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Shopware\SwagUserPrice\Bundle\StoreFrontBundle\Service\Core;

use Shopware\Bundle\StoreFrontBundle\Service\Core\CheapestPriceService;
use Shopware\Bundle\StoreFrontBundle\Struct;
use Shopware\Bundle\StoreFrontBundle\Service;
use Shopware\SwagUserPrice\Components;

/**
 * Plugin CheapestUserPriceService class.
 *
 * This class is an extension to the default CheapestPriceService.
 * We need this to inject the plugin-prices to the detail- and listing-page.
 *
 * @category Shopware
 * @package Shopware\Plugin\SwagUserPrice
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class CheapestUserPriceService implements Service\CheapestPriceServiceInterface
{
    /** @var CheapestPriceService */
    private $service;

    /** @var Components\AccessValidator */
    private $validator;

    /** @var \Shopware_Plugins_Backend_SwagUserPrice_Bootstrap */
    private $bootstrap;

    /** @var Components\ServiceHelper */
    private $helper;

    /**
     * Constructor to set the variables, that we will need here.
     *
     * @param \Shopware_Plugins_Backend_SwagUserPrice_Bootstrap $bootstrap
     * @param Service\CheapestPriceServiceInterface $service
     * @param Components\AccessValidator $validator
     * @param Components\ServiceHelper $helper
     */
    public function __construct(
        \Shopware_Plugins_Backend_SwagUserPrice_Bootstrap $bootstrap,
        Service\CheapestPriceServiceInterface $service,
        Components\AccessValidator $validator,
        Components\ServiceHelper $helper
    ) {
        $this->service = $service;
        $this->validator = $validator;
        $this->bootstrap = $bootstrap;
        $this->helper = $helper;
    }

    /**
     * Gets a single price for a product.
     *
     * @param Struct\ListProduct $product
     * @param Struct\ProductContextInterface $context
     * @return mixed
     */
    public function get(Struct\ListProduct $product, Struct\ProductContextInterface $context)
    {
        $cheapestPrices = $this->getList([$product], $context);

        return array_shift($cheapestPrices);
    }

    /**
     * Gets all prices for a product.
     *
     * @param Struct\ListProduct[] $products
     * @param Struct\ProductContextInterface $context
     * @return array|Struct\BaseProduct[]|Struct\Product\PriceRule[]
     */
    public function getList($products, Struct\ProductContextInterface $context)
    {
        $products = $this->service->getList($products, $context);

        foreach ($products as $number => &$rule) {
            if (!$this->validator->validateProduct($number)) {
                continue;
            }
            $rule = $this->getCustomRule($rule, $number);
        }

        return $products;
    }

    /**
     * Builds a custom rule-struct.
     *
     * @param $rule Struct\Product\PriceRule
     * @param $number
     * @return Struct\Product\PriceRule
     */
    private function getCustomRule($rule, $number)
    {
        $price = $this->helper->getPrice($number);

        $customRule = $this->helper->buildRule($price);
        $customRule->setCustomerGroup($rule->getCustomerGroup());
        $customRule->setUnit($rule->getUnit());

        return $customRule;
    }
}
