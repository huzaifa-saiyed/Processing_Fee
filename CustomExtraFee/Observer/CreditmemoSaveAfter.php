<?php

namespace Kitchen\CustomExtraFee\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;

class CreditmemoSaveAfter implements ObserverInterface
{
    public function execute(Observer $observer)
    {
        $creditMemo = $observer->getEvent()->getCreditmemo();
        $order = $creditMemo->getOrder();
        $creditMemo->setCustomExtraFee($order->getCustomExtraFee());

        return $this;
    }
}
