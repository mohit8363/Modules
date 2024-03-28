<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Pinblooms\CustomerDiscount\Observer\Checkout;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Quote\Model\QuoteRepository;
use Psr\Log\LoggerInterface;

/**
 * Store Customer Discount and type
 *
 */
class StoreDiscountInformation implements ObserverInterface
{
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var \Magento\Quote\Model\QuoteRepository
     */
    protected $quoteRepository;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * Constructor
     *
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Quote\Model\QuoteRepository $quoteRepository
     * @param \Psr\Log\LoggerInterface $logger
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        QuoteRepository $quoteRepository,
        LoggerInterface $logger
    ) {
        $this->orderRepository = $orderRepository;
        $this->quoteRepository = $quoteRepository;
        $this->logger = $logger;
    }

    /**
     * Execute after place order
     *
     * @param Observer $observer
     * @return array
     * @throws \Psr\Log\LoggerInterface;
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        // Get quote ID from the order
        $quoteId = $order->getQuoteId();

        try {
            // Load quote by quote ID
            $quote = $this->quoteRepository->get($quoteId);

            // Retrieve custom attribute value from the quote
            $customerDiscount = $quote->getData("customer_discount");
            $discountType = $quote->getData("discount_type");
            if ($customerDiscount) {
                // Set your custom attribute value here
                $order->setData("customer_discount", $customerDiscount);
                $order->setData("discount_type", $discountType);
                // Save the order to persist the custom attribute value

                $this->orderRepository->save($order);
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
