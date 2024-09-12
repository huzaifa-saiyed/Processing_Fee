<?php
namespace Kitchen\CustomExtraFee\Model\Creditmemo\Total;

use Magento\Sales\Model\Order\Creditmemo\Total\AbstractTotal;

class CustomFee extends AbstractTotal
{
    /**
     * @param \Magento\Sales\Model\Order\Creditmemo $creditmemo
     * @return $this
     */
    public function collect(\Magento\Sales\Model\Order\Creditmemo $creditmemo)
    {
        $creditmemo->setCustomExtraFee(0);
        $creditmemo->setBaseCustomExtraFee(0);

        $amount = $creditmemo->getOrder()->getCustomExtraFee();
        $creditmemo->setCustomExtraFee($amount);

        $creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $creditmemo->getCustomExtraFee());
        $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $creditmemo->getCustomExtraFee());

        return $this;
    }
}
