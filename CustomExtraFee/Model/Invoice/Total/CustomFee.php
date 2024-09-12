<?php

namespace Kitchen\CustomExtraFee\Model\Invoice\Total;

use Magento\Sales\Model\Order\Invoice\Total\AbstractTotal;

class CustomFee extends AbstractTotal
{
    /**
     * @param \Magento\Sales\Model\Order\Invoice $invoice
     * @return $this
     */
    public function collect(\Magento\Sales\Model\Order\Invoice $invoice)
    {
        $order = $invoice->getOrder();

        $invoice->setCustomExtraFee($order->getCustomExtraFee());

        if ($order->getCustomExtraFee()) {
            $invoice->setGrandTotal($invoice->getGrandTotal() + $order->getCustomExtraFee());
            $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $order->getCustomExtraFee());
        }
        return $this;
    }
}
