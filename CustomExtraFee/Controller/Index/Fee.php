<?php

namespace Kitchen\CustomExtraFee\Controller\Index;

class Fee extends \Magento\Framework\App\Action\Action implements \Magento\Framework\App\Action\HttpPostActionInterface
{
    protected $resultJsonFactory;
    protected $checkoutSession;
    protected $quoteRepository;
    protected $_productloader;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Quote\Model\QuoteRepository $quoteRepository,
        \Magento\Catalog\Model\ProductFactory $_productloader
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->checkoutSession = $checkoutSession;
        $this->quoteRepository = $quoteRepository;
        $this->_productloader = $_productloader;
    }
    public function execute()
    {
        $response = [
            'error' => 0,
            'message' => ''
        ];

        try {
            if ($this->checkoutSession->getQuote()) {
                $cartId = $this->checkoutSession->getQuote()->getId();
                if ($cartId) {
                    $quote = $this->quoteRepository->get($cartId);
                
                    if($this->getRequest()->getParam('is_assembly_option_selected') == 1) {
                        
                        $is_assembly_option_selected =  $this->getRequest()->getParam('is_assembly_option_selected');
                        
                        $quote->setIsAsembly($is_assembly_option_selected);
                        $quote->collectTotals()->save();
                    } else {
                        $quote->setIsAsembly(0);
                        $quote->collectTotals()->save();
                    }                    
                }
            }
        } catch (\Exception $e) {
            $response = [
                'error' => 1,
                'message' => $e->getMessage()
            ];
        }
        $dataJson = $this->resultJsonFactory->create();
        return $dataJson->setData($response);
    }
}
