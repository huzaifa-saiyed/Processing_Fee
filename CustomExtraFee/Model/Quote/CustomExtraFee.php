<?php

namespace Kitchen\CustomExtraFee\Model\Quote;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Tax\Model\Config as TaxConfig;
use Magento\Framework\App\Config\ScopeConfigInterface; 

class CustomExtraFee extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
    private $priceCurrency;
    protected $productFactory;
    protected $taxConfig;
    protected $customerFactory;
    protected $priceModel;
    protected $scopeConfig;

    public function __construct(
        PriceCurrencyInterface $priceCurrency,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        TaxConfig $taxConfig,
        \Kitchen\CustomExtraFee\Model\Product\Type\Price $priceModel,
        ScopeConfigInterface $scopeConfig 
    ) {
        $this->setCode('custom_extra_fee');
        $this->priceCurrency = $priceCurrency;
        $this->productFactory = $productFactory;
        $this->taxConfig = $taxConfig;
        $this->customerFactory = $customerFactory;
        $this->priceModel = $priceModel;
        $this->scopeConfig = $scopeConfig;
    }

    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        parent::collect($quote, $shippingAssignment, $total);

        $enabled = $this->scopeConfig->getValue('extraFeeSection/extraFeeGroup/enable', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        if($enabled) {
            $this->getAssemblyCharges($quote);
            $customExtraFee = $quote->getCustomExtraFee();
            $isAsembly = $quote->getIsAsembly();
            
            $total->setCustomExtraFee($customExtraFee);
            $total->setTotalAmount($this->getCode(), $customExtraFee);
            $total->setBaseTotalAmount($this->getCode(), $customExtraFee);

            if ($isAsembly == 1 && $customExtraFee > 0) {
                $this->addTaxIntoAdditionalCharges($total, $customExtraFee, $isAsembly, $quote);
            } else {

            
                $total->setGrandTotal((float)$total->getGrandTotal() - $customExtraFee);
                $total->setBaseGrandTotal((float)$total->getBaseGrandTotal()  - $customExtraFee);

            }

            return $this;
        }   
    }

    /**
     * Calculate Assembly Charges
     */
    private function getAssemblyCharges($quote)
    {
        $charges = 0;
        $cartAllItems = $quote->getAllItems();

        foreach ($cartAllItems as $_item) {
            $_product = $_item->getProduct();
            $finalPrice = $_item->getPrice();

            if ($_item->getDiscountPercent()) {
                $finalPrice -= (($finalPrice * $_item->getDiscountPercent()) / 100);
            } elseif ($_item->getDiscountAmount()) {
                $finalPrice -= ($_item->getDiscountAmount() / $_item->getQty());
            }

            $_product->setData("final_price", $finalPrice);
            $itemCustomPrice = $this->priceModel->getFinalPriceWithCuOptions($_item->getQty(), $_product);
            $_item->setCustomExtraFee($itemCustomPrice);
            $itemCustomPrice *= $_item->getQty();
            $charges += $itemCustomPrice;
        }

        $quote->setCustomExtraFee($charges);
        return $charges;
    }

    public function addTaxIntoAdditionalCharges($total, $balance, $isAsembly, $quote)
    {
        
            $additionalCharges = $balance;

            $address = $quote->getShippingAddress();
            $appliedTaxes = $total->getAppliedTaxes() ? $total->getAppliedTaxes() : $address->getAppliedTaxes();

            if (!empty($appliedTaxes) && $additionalCharges > 0) {
                $additionalTaxAmt = 0;
                $percent = 0;
                foreach ($appliedTaxes as $value) {
                    $additionalTaxAmt += $value['amount'];
                    $percent = $value['percent'];
                }
                $totalCharges = $additionalCharges * $percent / 100;
                $additionalTaxAmt += $totalCharges;
                $additionalTaxAmt = $this->priceCurrency->round($additionalTaxAmt);
                $total->setTaxAmount($additionalTaxAmt);
                $total->setBaseTaxAmount($additionalTaxAmt);
                $total->setGrandTotal((float)$total->getGrandTotal() + $totalCharges);
                $total->setBaseGrandTotal((float)$total->getBaseGrandTotal() + $totalCharges);
            }
    }

    public function fetch(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        $result = null;
        $customExtraFee = $quote->getCustomExtraFee();

        if ($customExtraFee > 0) {
            $result = [
                'code' => $this->getCode(),
                'title' => __('Custom Extra Fee'),
                'value' => $customExtraFee
            ];
        }
        return $result;
    }
}

