<?php

namespace Kitchen\CustomExtraFee\Model\Sales\Pdf;

use Magento\Sales\Model\Order\Pdf\Total\DefaultTotal;

class ProcessingFee extends DefaultTotal
{
    /**
     * Get array of arrays with totals information for display in PDF
     * array(
     *  $index => array(
     *      'amount'   => $amount,
     *      'label'    => $label,
     *      'font_size'=> $font_size
     *  )
     * )
     * @return array
     */
    public function getTotalsForDisplay(): array
    {
        $processingFee = $this->getOrder()->getProcessingFee();
        if ($processingFee == 0) {
            return [];
        }
        $amountInclTax = $this->getOrder()->formatPriceTxt($processingFee);
        $fontSize = $this->getFontSize() ? $this->getFontSize() : 7;
        return [
            [
                'amount' => $this->getAmountPrefix() . $amountInclTax,
                'label' => __('Processing Fee') . ':',
                'font_size' => $fontSize,
            ]
        ];
    }
}