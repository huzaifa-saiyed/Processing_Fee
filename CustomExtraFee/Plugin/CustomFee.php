<?php


namespace Kitchen\CustomExtraFee\Plugin;

use Magento\Framework\App\Config\ScopeConfigInterface;

class CustomFee

{

    protected $checkoutSession;

    protected $scopeConfig;


    public function __construct(

        \Magento\Checkout\Model\Session $checkoutSession,
        ScopeConfigInterface $scopeConfig

    ) {

        $this->checkoutSession   = $checkoutSession;
        $this->scopeConfig = $scopeConfig;
    }


    public function afterGetConfig(

        \Magento\Checkout\Model\DefaultConfigProvider $subject,

        $output

    ) {

        $enabled = $this->scopeConfig->getValue('extraFeeSection/extraFeeGroup/enable', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        $processingFeeAmount = (float)$this->scopeConfig->getValue('extraFeeSection/processingFeeGroup/processingFee', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        $processingFeeEnabled = $this->scopeConfig->getValue('extraFeeSection/processingFeeGroup/enableProcessingFee', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $quote = $this->checkoutSession->getQuote();


        $output['custom_extra_fee'] = (int)$quote->getCustomExtraFee();
        $output['is_asembly'] = (int)$quote->getIsAsembly();

        $output['is_enable_fee'] = $enabled;
        $output['fee_percentage'] = $processingFeeAmount;
        $output['fee_percentage_enable'] = $processingFeeEnabled;
        


        return $output;
    }
}
