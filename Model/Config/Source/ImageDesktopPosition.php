<?php

namespace SheroCommerce\FeaturedProduct\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class ImageDesktopPosition implements OptionSourceInterface
{
    public const IMAGE_DESKTOP_POSITION_LEFT = 1;
    public const IMAGE_DESKTOP_POSITION_RIGHT = 2;

    /**
     * Retrieve image desktop position options
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => self::IMAGE_DESKTOP_POSITION_LEFT, 'label' => __('Left')],
            ['value' => self::IMAGE_DESKTOP_POSITION_RIGHT, 'label' => __('Right')],
        ];
    }
}
