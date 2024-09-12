<?php

namespace Kitchen\CustomExtraFee\Block\Index;

class Fee extends \Magento\Framework\View\Element\Template
{
    protected $checkoutSession;
    protected $quoteRepository;
    
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Quote\Model\QuoteRepository $quoteRepository
    ) {
        parent::__construct($context);
        $this->checkoutSession = $checkoutSession;
        $this->quoteRepository = $quoteRepository;
    }
    
    public function getQuote() {
        try {
            if ($this->checkoutSession->getQuote()) {
                $cartId = $this->checkoutSession->getQuote()->getId();
                if ($cartId) {
                    $quote = $this->quoteRepository->get($cartId);
                    return $quote;
                }
            }
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Unable to retrieve quote.'));
        }
    }
}
