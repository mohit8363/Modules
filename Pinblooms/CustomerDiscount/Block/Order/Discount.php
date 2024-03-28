<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Pinblooms\CustomerDiscount\Block\Order;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderFactory;

class Discount extends Template
{
    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $order;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * Constructor
     *
     * @param Context $context
     * @param \Magento\Sales\Model\Order $order
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Order $order,
        OrderFactory $orderFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->order = $order;
        $this->orderFactory = $orderFactory;
    }

    /**
     * Return current order
     *
     * @param
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getCurrentOrder()
    {
        $orderId = $this->getRequest()->getParam("order_id");
        if ($orderId) {
            $order = $this->orderFactory->create()->load($orderId);
            return $order;
        }
        return null;
    }

    /**
     * @inheritdoc
     */
    public function getDiscountType()
    {
        return $this->getCurrentOrder()->getData("discount_type");
    }

    /**
     * @inheritdoc
     */
    public function getCustomerDiscount()
    {
        return $this->getCurrentOrder()->getData("customer_discount");
    }
}
