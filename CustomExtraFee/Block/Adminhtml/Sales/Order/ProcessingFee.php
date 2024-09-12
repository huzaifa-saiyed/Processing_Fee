<?php
  
namespace Kitchen\CustomExtraFee\Block\Adminhtml\Sales\Order;

class ProcessingFee extends \Magento\Sales\Block\Adminhtml\Order\Totals\Item
{
    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    private $priceCurrency;

    public function __construct(
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Helper\Admin $adminHelper,
        array $data = []
    ) {
        parent::__construct($context, $registry, $adminHelper, $data);
        $this->priceCurrency = $priceCurrency;
        $this->setInitialFields();
    }

    protected function _initTotals()
    {
        parent::_initTotals();

        $this->addTotal(
            new \Magento\Framework\DataObject(
                [
                    'code' => 'processing_fee',
                    'strong' => $this->getStrong(),
                    'value' => $this->getSource()->getData($this->getAmountField()),
                    'base_value' => $this->getSource()->getData($this->getBaseAmountField()),
                    'label' => __('Processing Fee'),
                ]
            ),
            $this->getAfter()
        );

        return $this;
    }

    public function getExtraFee()
    {
        $source = $this->getSource();
        if($source->getData('processing_fee') > 0)
        {
            return true;
        }
        return false;
    }

    public function getProcessingFee()
    {
        $source = $this->getSource();
        $priceCurrencyFormat = $this->priceCurrency->format($source->getData('processing_fee'),
                true,
                2,
                null,
                $source->getOrderCurrencyCode()
            );

        return $priceCurrencyFormat;
    }
}
