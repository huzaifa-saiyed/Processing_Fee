<?php
namespace Kitchen\CustomExtraFee\Model\Creditmemo\Total;

use Magento\Sales\Model\Order\Creditmemo\Total\AbstractTotal;
use Magento\Framework\App\Config\ScopeConfigInterface;

class ProcessingFee extends AbstractTotal
{
    protected $scopeConfig;

    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param \Magento\Sales\Model\Order\Creditmemo $creditmemo
     * @return $this
     */
    public function collect(\Magento\Sales\Model\Order\Creditmemo $creditmemo)
    {
        $order = $creditmemo->getOrder();

        $processingFeeEnabled = $this->scopeConfig->getValue(
            'extraFeeSection/processingFeeGroup/enableProcessingFee', 
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $processingFeeAmount = (float)$this->scopeConfig->getValue(
            'extraFeeSection/processingFeeGroup/processingFee', 
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        if ($processingFeeEnabled && $processingFeeAmount > 0) {
            $selectedPaymentMethod = $order->getPayment()->getMethod();

            if ($selectedPaymentMethod == 'checkmo') {

                $subtotal = $creditmemo->getSubtotal();
                $discountAmount = $creditmemo->getDiscountAmount();
                $subtotalWithDiscount = $subtotal + $discountAmount;
                
                $shippingFee = $creditmemo->getShippingAmount();
                $customExtraFee = $order->getCustomExtraFee();
                $processingFee = $this->calculateProcessingFee($subtotalWithDiscount, $shippingFee, $customExtraFee);

                $creditmemo->setData('processing_fee', $processingFee);
                $creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $processingFee);
                $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $processingFee);
            }
        }

        return $this;
    }

    /**
     * Calculate processing fee based on subtotal, shipping fee, and custom extra fee
     *
     * @param float $subtotalWithDiscount
     * @param float $shippingFee
     * @param float $customExtraFee
     * @return float
     */
    private function calculateProcessingFee($subtotalWithDiscount, $shippingFee, $customExtraFee)
    {
        $processingFeePercentage = $this->getProcessingFeePercentage();

        $processingFeeSubtotal = $subtotalWithDiscount * $processingFeePercentage;
        $processingFeeShipping = $shippingFee * $processingFeePercentage;
        $processingFeeCustomExtra = $customExtraFee * $processingFeePercentage;

        return $processingFeeSubtotal + $processingFeeShipping + $processingFeeCustomExtra;
    }

    /**
     * Get processing fee percentage from configuration
     *
     * @return float
     */
    private function getProcessingFeePercentage()
    {
        $processingFeeValue = (float)$this->scopeConfig->getValue(
            'extraFeeSection/processingFeeGroup/processingFee',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        return $processingFeeValue / 100;
    }
}
