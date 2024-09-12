<?php

namespace Kitchen\CustomExtraFee\Model\Quote;

use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class ProcessingFee extends AbstractTotal
{
    protected $scopeConfig;

    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->setCode('processing_fee');
        $this->scopeConfig = $scopeConfig;
    }

    public function collect(
        Quote $quote,
        ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        parent::collect($quote, $shippingAssignment, $total);

        $processingFeeEnabled = $this->scopeConfig->getValue('extraFeeSection/processingFeeGroup/enableProcessingFee', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $processingFeeAmount = (float)$this->scopeConfig->getValue('extraFeeSection/processingFeeGroup/processingFee', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        if ($processingFeeEnabled && $processingFeeAmount > 0) {
            $selectedPaymentMethod = $quote->getPayment()->getMethod();
            if ($selectedPaymentMethod == 'checkmo') {
                
                $subtotal = $quote->getSubtotalWithDiscount();
                $shippingData = $quote->getShippingAddress()->getData();
                $shippingFee = $shippingData['shipping_amount'];
                $customExtraFee = $quote->getCustomExtraFee();
                $processingFee = $this->calculateProcessingFee($subtotal, $shippingFee, $customExtraFee);


                $quote->setProcessingFee($processingFee);
                $total->setGrandTotal($total->getGrandTotal() + $processingFee);
                $total->setBaseGrandTotal($total->getBaseGrandTotal() + $processingFee);
            }
        }

        return $this;
    }

    public function fetch(
        Quote $quote,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        $result = null;
        $processingFee = $quote->getProcessingFee();

        if ($processingFee > 0) {
            $result = [
                'code' => 'processing_fee',
                'title' => __('Processing Fee'),
                'value' => $processingFee
            ];
        }
        return $result;
    }

    private function calculateProcessingFee($subtotal, $shippingFee, $customExtraFee)
    {
        $processingFeePercentage = $this->getProcessingFeePercentage();

        $processingFeeSubtotal = $subtotal * $processingFeePercentage;
        $processingFeeShipping = $shippingFee * $processingFeePercentage;
        $processingFeeCustomExtra = $customExtraFee * $processingFeePercentage;

        return $processingFeeSubtotal + $processingFeeShipping + $processingFeeCustomExtra;
    }

    private function getProcessingFeePercentage()
    {
        $processingFeeValue = (float)$this->scopeConfig->getValue('extraFeeSection/processingFeeGroup/processingFee',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        return $processingFeeValue / 100;
    }
}
