<?php

namespace Kitchen\CustomExtraFee\Plugin;

use Magento\Sales\Controller\Adminhtml\Order\Creditmemo\UpdateQty;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Psr\Log\LoggerInterface;
use Magento\Sales\Model\Order\CreditmemoFactory;
use Magento\Sales\Api\CreditmemoRepositoryInterface;

class UpdateQtyPlugin
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var CreditmemoFactory
     */
    protected $creditmemoFactory;

    /**
     * @var CreditmemoRepositoryInterface
     */
    protected $creditmemoRepository;

    /**
     * @param LoggerInterface $logger
     * @param ScopeConfigInterface $scopeConfig
     * @param CreditmemoFactory $creditmemoFactory
     * @param CreditmemoRepositoryInterface $creditmemoRepository
     */
    public function __construct(
        LoggerInterface $logger,
        ScopeConfigInterface $scopeConfig,
        CreditmemoFactory $creditmemoFactory,
        CreditmemoRepositoryInterface $creditmemoRepository
    ) {
        $this->logger = $logger;
        $this->scopeConfig = $scopeConfig;
        $this->creditmemoFactory = $creditmemoFactory;
        $this->creditmemoRepository = $creditmemoRepository;
    }

    /**
     * After execute plugin to get updated creditmemo data and update grand total
     *
     * @param UpdateQty $subject
     * @param $result
     * @return mixed
     */
    public function afterExecute(UpdateQty $subject, $result)
    {
        // Get updated credit memo data from the request
        $creditmemoData = $subject->getRequest()->getParam('creditmemo');

        if (isset($creditmemoData['shipping_amount'])) {
            $shippingAmount = $creditmemoData['shipping_amount'];
            $this->logger->info('Updated Shipping Amount: ' . $shippingAmount);

            // Calculate processing fee based on the updated shipping amount
            $processingFee = $this->calculateProcessingFee($shippingAmount);

            // Load the credit memo entity using request data
            $this->logger->info('Calculated Processing Fee: ' . $processingFee);
            $creditmemoId = $subject->getRequest()->getParam('creditmemo_id');
            $creditmemo = $this->creditmemoRepository->get($creditmemoId);

            // Update processing fee in the credit memo and adjust grand total
            $creditmemo->setData('processing_fee', $processingFee);
            $creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $processingFee);
            $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $processingFee);

            // Save the updated credit memo
            $this->creditmemoRepository->save($creditmemo);

            $this->logger->info('Updated Grand Total: ' . $creditmemo->getGrandTotal());
        }

        return $result;
    }

    /**
     * Example function to calculate processing fee
     *
     * @param float $shippingAmount
     * @return float
     */
    protected function calculateProcessingFee($shippingAmount)
    {
        $processingFeeValue = (float)$this->scopeConfig->getValue(
            'extraFeeSection/processingFeeGroup/processingFee',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        // Calculate the processing fee as a percentage of the shipping amount
        $processingFee = ($shippingAmount * $processingFeeValue) / 100;
        return $processingFee;
    }
}
