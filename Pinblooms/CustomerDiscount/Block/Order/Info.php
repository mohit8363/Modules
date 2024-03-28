<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Pinblooms\CustomerDiscount\Block\Order;

use Magento\Sales\Block\Order\Info as SalesInfo;

class Info extends SalesInfo
{
    /**
     * @var string
     */
    protected $_template = 'Pinblooms_CustomerDiscount::order/info.phtml';
}
