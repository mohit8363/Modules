<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Pinblooms\CustomerDiscount\Observer\Checkout;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Psr\Log\LoggerInterface;

/**
 * Customer Discount observer
 *
 */
class ApplyDiscountToProduct implements ObserverInterface
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * Constructor
     *
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Psr\Log\LoggerInterface $logger
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        CustomerSession $customerSession,
        CustomerRepositoryInterface $customerRepository,
        LoggerInterface $logger
    ) {
        $this->customerSession = $customerSession;
        $this->customerRepository = $customerRepository;
        $this->logger = $logger;
    }

    /**
     * Execute after add to cart and set custom discount
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return array
     * @throws \Psr\Log\LoggerInterface;
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $item = $observer->getEvent()->getData("quote_item");
            $product = $item->getProduct();
            $customerDiscountAttribute = $this->getCustomerData();
            if (!empty($customerDiscountAttribute) &&
                isset($customerDiscountAttribute["customer_discount"]) &&
                $customerDiscountAttribute["customer_discount"] > 0
            ) {
                $discountType =
                    $customerDiscountAttribute["discount_type"] ?? "fixed";
                $customerDiscount =
                    $customerDiscountAttribute["customer_discount"] ?? 0;
                // Fetch product price
                $productPrice = $product->getFinalPrice();

                // Calculate discount amount
                $discountAmount =
                    $discountType == "2"
                        ? ($productPrice * $customerDiscount) / 100
                        : $customerDiscount;

                // Apply discount to product price
                $finalPrice = $productPrice - $discountAmount;

                // Update product price in the cart
                $item->setCustomPrice($finalPrice);
                $item->setOriginalCustomPrice($finalPrice);
                $item->getProduct()->setIsSuperMode(true);

                // Set your custom attribute in quote
                if (isset($customerDiscountAttribute["discount_type"])) {
                    $discountType = $customerDiscountAttribute["discount_type"];
                } else {
                    $discountType = "null";
                }
                $setCustomDiscount = $this->setCustomDiscount(
                    $item,
                    $customerDiscount,
                    $discountType
                );
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * Fetch customer data
     *
     * @param array $value
     * @return array
     */
    public function getCustomerData()
    {
        try {
            $customAttributeValue = [];
            // Check if customer is logged in
            if ($this->customerSession->isLoggedIn()) {
                $customerId = $this->customerSession->getCustomerId();
                // Retrieve customer by ID
                $customer = $this->customerRepository->getById($customerId);
                $customerDiscountAttribute = $customer->getCustomAttribute(
                    "customer_discount"
                );
                if ($customerDiscountAttribute) {
                    $customAttributeValue["discount_type"] = $customer
                        ->getCustomAttribute("discount_type")
                        ->getValue();
                    $customAttributeValue["customer_discount"] = $customer
                        ->getCustomAttribute("customer_discount")
                        ->getValue();
                } else {
                    $customAttributeValue = []; // Return blank array
                }
            } else {
                // Handle case where customer is not logged in
                $customAttributeValue = []; // Return blank array
            }
            return $customAttributeValue;
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            // Handle exception gracefully, maybe return a default value or rethrow the exception
            // For now, let's return an empty array
            return [];
        }
    }

    /**
     * @inheritdoc
     */
    public function setCustomDiscount($item, $customerDiscount, $discountType)
    {
        try {
            $quote = $item->getQuote();
            if ($discountType == 1) {
                $discountType = "Fixed";
            } elseif ($discountType == 2) {
                $discountType = "Percentage";
            } else {
                $discountType = null;
            }
            $quote->setData("customer_discount", $customerDiscount);
            $quote->setData("discount_type", $discountType);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
