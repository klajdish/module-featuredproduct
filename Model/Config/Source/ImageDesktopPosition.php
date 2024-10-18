<?php

namespace SheroCommerce\FeaturedProduct\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class ImageDesktopPosition implements OptionSourceInterface
{
    /**
     * Retrieve image desktop position options
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => '1', 'label' => __('Left')],
            ['value' => '2', 'label' => __('Right')],
        ];
    }
}
