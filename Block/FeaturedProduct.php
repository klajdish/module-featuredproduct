<?php

namespace SheroCommerce\FeaturedProduct\Block;

use Magento\Framework\View\Element\Template;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Catalog\Helper\Image;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Magento\Catalog\Helper\Output as CatalogOutputHelper;
use Psr\Log\LoggerInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use SheroCommerce\FeaturedProduct\Model\Config\Source\ImageDesktopPosition;

class FeaturedProduct extends Template
{
    protected const XML_PATH_FEATURED_PRODUCT_SKU = 'featured_product/settings/sku';
    protected const XML_PATH_IMAGE_DESKTOP_POSITION = 'featured_product/settings/image_desktop_position';

    /**
     * Constructor
     *
     * @param Template\Context $context
     * @param ProductRepository $productRepository
     * @param ScopeConfigInterface $scopeConfig
     * @param Image $imageHelper
     * @param PriceHelper $priceHelper
     * @param CatalogOutputHelper $catalogOutputHelper
     * @param LoggerInterface $logger
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        ProductRepository $productRepository,
        ScopeConfigInterface $scopeConfig,
        Image $imageHelper,
        PriceHelper $priceHelper,
        CatalogOutputHelper $catalogOutputHelper,
        LoggerInterface $logger,
        array $data = []
    ) {
        $this->productRepository = $productRepository;
        $this->scopeConfig = $scopeConfig;
        $this->imageHelper = $imageHelper;
        $this->priceHelper = $priceHelper;
        $this->catalogOutputHelper = $catalogOutputHelper;
        $this->logger = $logger;
        parent::__construct($context, $data);
    }

    /**
     * Get the featured product.
     *
     * @return ProductInterface|null
     */
    public function getFeaturedProduct(): ?ProductInterface
    {
        $productSku = $this->scopeConfig->getValue(
            self::XML_PATH_FEATURED_PRODUCT_SKU,
            ScopeInterface::SCOPE_STORE
        );

        if ($productSku) {
            try {
                return $this->productRepository->get($productSku);
            } catch (NoSuchEntityException $e) {
                $this->logger->error(
                    __(
                        'The featured product with SKU %1 does not exist.',
                        $productSku
                    )
                );
            } catch (\Exception $e) {
                $this->logger->error(
                    __(
                        'An error occurred while loading the featured product with SKU %1.',
                        $productSku
                    )
                );
            }
        }

        return null;
    }

    /**
     * Get the card image desktop position.
     *
     * @return int
     */
    public function getImageDesktopPosition(): int
    {
        return (int) $this->scopeConfig->getValue(
            self::XML_PATH_IMAGE_DESKTOP_POSITION,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Prepare the HTML classes for the order position of card elements.
     *
     * @param string $baseClass
     * @return string[]
     */

    public function prepareOrderPostionHtmlClasses($baseClass = ''): array
    {
        $imageDesktopPosition = $this->getImageDesktopPosition();
        $otherPosition = $imageDesktopPosition === ImageDesktopPosition::IMAGE_DESKTOP_POSITION_LEFT
            ? ImageDesktopPosition::IMAGE_DESKTOP_POSITION_RIGHT
            : ImageDesktopPosition::IMAGE_DESKTOP_POSITION_LEFT;


        return [
            'firstElementOrderPositionClass' => $baseClass . $imageDesktopPosition,
            'secondElementOrderPositionClass' => $baseClass . $otherPosition
        ];
    }

    /**
     * Get the image URL for the product.
     *
     * @param ProductInterface $product
     * @param string $type
     * @return string
     */
    public function getImageUrl(ProductInterface $product, string $type = 'product_page_main_image'): string
    {
        if (!$this->isValid($product)) {
            return '';
        }

        try {
            return $this->imageHelper->init($product, $type)->getUrl();
        } catch (\Exception $e) {
            $this->logger->error(
                __(
                    'An error occurred while loading the image for product with ID %1.',
                    $product->getId()
                )
            );
            return '';
        }
    }

    /**
     * Get the formatted price for the product with currency symbol.
     *
     * @param ProductInterface $product
     * @return string
     */
    public function getPrice(ProductInterface $product): string
    {
        if (!$this->isValid($product)) {
            return '';
        }

        return $this->priceHelper->currency(
            $product->getFinalPrice(),
            true,
            false
        );
    }

    /**
     * Get the product description or short description.
     *
     * @param ProductInterface $product
     * @return string
     */
    public function getDescription(ProductInterface $product): string
    {
        if (!$this->isValid($product)) {
            return '';
        }

        $description = $this->catalogOutputHelper->productAttribute(
            $product,
            $product->getDescription(),
            'description'
        );
        $shortDescription = $this->catalogOutputHelper->productAttribute(
            $product,
            $product->getShortDescription(),
            'short_description'
        );

        return $description ?: $shortDescription ?: '';
    }

    /**
     * Validate the product object.
     *
     * @param ProductInterface|null $product
     * @return bool
     */
    private function isValid(?ProductInterface $product): bool
    {
        if ($product === null || $product->getId() === null) {
            $this->logger->error(__('Invalid product object: Product is null or does not have an ID.'));
            return false;
        }
        return true;
    }
}
