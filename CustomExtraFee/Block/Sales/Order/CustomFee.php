<?php
  
namespace Kitchen\CustomExtraFee\Block\Sales\Order;

use Magento\Framework\App\Config\ScopeConfigInterface;

class CustomFee extends \Magento\Framework\View\Element\Template
{
    /**
    * Tax configuration model
    *
    * @var \Magento\Tax\Model\Config
    */
    protected $_config;

    /**
    * @var Order
    */
    protected $_order;

    /**
    * @var \Magento\Framework\DataObject
    */
    protected $_source;
    protected $scopeConfig;

    /**
    * @param \Magento\Framework\View\Element\Template\Context $context
    * @param \Magento\Tax\Model\Config $taxConfig
    * @param array $data
    */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Tax\Model\Config $taxConfig,
        ScopeConfigInterface $scopeConfig,
        array $data = []
    ) {
        $this->_config = $taxConfig;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context, $data);
    }

    /**
    * Check if we nedd display full tax total info
    *
    * @return bool
    */
    public function displayFullSummary()
    {
        return true;
    }

    /**
    * Get data (totals) source model
    *
    * @return \Magento\Framework\DataObject
    */
    public function getSource()
    {
        return $this->_source;
    }

    public function getStore()
    {
        return $this->_order->getStore();
    }

    /**
    * @return Order
    */
    public function getOrder()
    {
        return $this->_order;
    }

    /**
    * @return array
    */
    public function getLabelProperties()
    {
        return $this->getParentBlock()->getLabelProperties();
    }

    /**
    * @return array
    */
    public function getValueProperties()
    {
        return $this->getParentBlock()->getValueProperties();
    }

    /**
    * Initialize all order totals relates with tax
    *
    * @return \Magento\Tax\Block\Sales\Order\Tax
    */
    public function initTotals()
    {
        $enabled = $this->scopeConfig->getValue('extraFeeSection/extraFeeGroup/enable', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        if ($enabled) {
            $parent = $this->getParentBlock();
            $this->_order = $parent->getOrder();
            $this->_source = $parent->getSource();
            if($this->_order->getData('custom_extra_fee') > 0){
                $custom_extra_fee = new \Magento\Framework\DataObject(
                    [
                        'code'=>'custom_extra_fee',
                        'strong'=>true,
                        'value'=>$this->_order->getData('custom_extra_fee'),
                        'label'=>__('Extra Amount Fee'),
                    ]
                );
                $parent->addTotal($custom_extra_fee, 'custom_extra_fee');
            }
            return $this;
        }
    }
}
