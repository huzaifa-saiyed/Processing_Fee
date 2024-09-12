<?php

namespace Kitchen\CustomExtraFee\Block\Adminhtml\Sales\Order\Invoice;

class ProcessingFee extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Sales\Model\Order\Invoice
     */
    protected $_invoice = null;
    /**
     * @var \Magento\Framework\DataObject
     */
    protected $_source;
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Prince\Extrafee\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }
    public function getSource()
    {
        return $this->getParentBlock()->getSource();
    }
    public function getInvoice()
    {
        return $this->getParentBlock()->getInvoice();
    }
    public function initTotals()
    {
        $this->getParentBlock();
        $this->getInvoice();
        $this->getSource();

        if($this->getSource()->getProcessingFee() <= 0) {
            return $this;
        }
        $total = new \Magento\Framework\DataObject(
            [
                'code' => 'processing_fee',
                'value' => $this->getSource()->getProcessingFee(),
                'label' => __('Processing Fee'),
            ]
        );
        $this->getParentBlock()->addTotalBefore($total, 'grand_total');
        return $this;
    }
}
