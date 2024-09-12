<?php

namespace Kitchen\CustomExtraFee\Plugin\Api;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\Data\OrderSearchResultInterface;
use Magento\Sales\Api\Data\OrderItemExtensionFactory;
use Psr\Log\LoggerInterface;

class OrderRepositoryPlugin
{
    protected $orderItemExtensionFactory;
    protected $logger;

    public function __construct(
        OrderItemExtensionFactory $orderItemExtensionFactory,
        LoggerInterface $logger
    ) {
        $this->orderItemExtensionFactory = $orderItemExtensionFactory;
        $this->logger = $logger;
    }

    public function afterGet(OrderRepositoryInterface $subject, OrderInterface $order)
    {

        foreach ($order->getItems() as $item) {
            $customData = $this->getCustomOptionPricesForItem($item);
            if ($this->hasCustomizableOptions($customData)) {
                $productName = $item->getName();

                $extensionAttributes = $item->getExtensionAttributes();
                if (!$extensionAttributes) {
                    $extensionAttributes = $this->orderItemExtensionFactory->create();
                }

                $extensionAttributes->setData('product_name', $productName);
                $extensionAttributes->setData('assembly', $customData['Assembly'] ?? 'NA');
                $extensionAttributes->setData('assembly_charges', $customData['Assembly_charges'] ?? 0);
                $extensionAttributes->setData('hinge', $customData['Hinge'] ?? 'NA');
                
                $item->setExtensionAttributes($extensionAttributes);

            }
        }

        return $order;
    }

    protected function getCustomOptionPricesForItem($item)
    {
        $customOptions = $item->getProductOptions();
        $filteredOptions = [
            'Assembly' => 'NA',
            'Hinge' => 'NA'
        ];

        if (isset($customOptions['options'])) {
            foreach ($customOptions['options'] as $option) {
                if (strpos($option['label'], 'Select Assembly') !== false) {
                    $filteredOptions['Assembly'] = $option['value'];
                } elseif (strpos($option['label'], 'Select Hinge') !== false) {
                    $filteredOptions['Hinge'] = $option['value'];
                }
            }
        }

        $customOptionPrice = $this->getCustomOptionPrices($item);

        return [
            'Assembly' => $filteredOptions['Assembly'],
            'Assembly_charges' => $customOptionPrice,
            'Hinge' => $filteredOptions['Hinge'],
        ];
    }

    protected function getCustomOptionPrices($item)
    {
        $customOptions = $item->getProductOptions();
        $optionsPrice = 0;

        if (isset($customOptions['options'])) {
            foreach ($customOptions['options'] as $option) {
                $optionId = $option['option_id'];
                $optionValue = $option['option_value'];

                $productOption = $item->getProduct()->getOptionById($optionId);
                if ($productOption) {
                    $values = $productOption->getValues();
                    if (isset($values[$optionValue])) {
                        $optionPrice = $values[$optionValue]->getPrice();
                        $optionsPrice += $optionPrice;
                    }
                }
            }
        }

        return $optionsPrice;
    }

    protected function hasCustomizableOptions($customData)
    {
        return !empty($customData['Assembly']) && $customData['Assembly'] !== 'NA' ||
               !empty($customData['Hinge']) && $customData['Hinge'] !== 'NA';
    }

    public function afterGetList(OrderRepositoryInterface $subject, OrderSearchResultInterface $searchResult)
    {
        foreach ($searchResult->getItems() as $order) {
            $this->afterGet($subject, $order);
        }
        
        return $searchResult;
    }
}
