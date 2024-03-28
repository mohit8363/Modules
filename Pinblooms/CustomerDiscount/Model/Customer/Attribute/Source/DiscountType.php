<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Pinblooms\CustomerDiscount\Model\Customer\Attribute\Source;

class DiscountType extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{

    /**
     * GetAllOptions
     *
     * @return array
     */
    public function getAllOptions()
    {
        if ($this->_options === null) {
            $this->_options = [
                ['value' => '1', 'label' => __('Fixed')],
                ['value' => '2', 'label' => __('Percentage')]
            ];
        }
        return $this->_options;
    }
}
