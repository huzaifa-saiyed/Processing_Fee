<?php

namespace Kitchen\CustomExtraFee\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;

class InvoiceSaveAfter implements ObserverInterface
{
    public function execute(Observer $observer)
    {
        $invoice = $observer->getEvent()->getInvoice();
        $order = $invoice->getOrder();
        $invoice->setCustomExtraFee($order->getCustomExtraFee());

        return $this;
    }
}
