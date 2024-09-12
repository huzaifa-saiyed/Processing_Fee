<?php
namespace Kitchen\CustomExtraFee\Model\Product\Type;

class Price extends \Magento\Catalog\Model\Product\Type\Price
{
    /**
     * Retrieve product final price
     *
     * @param float|null $qty
     * @param Product $product
     * @return float
     */
    public function getFinalPrice($qty, $product)
    {
        if ($qty === null && $product->getCalculatedFinalPrice() !== null) {
            return $product->getCalculatedFinalPrice();
        }

        $finalPrice = $this->getBasePrice($product, $qty);
        $product->setFinalPrice($finalPrice);

        $this->_eventManager->dispatch('catalog_product_get_final_price', ['product' => $product, 'qty' => $qty]);

        $finalPrice = $product->getData('final_price');

        $finalPrice = max(0, $finalPrice);
        $product->setFinalPrice($finalPrice);

        return $finalPrice;
    }

    /**
     * Retrieve product Custom Options price
     *
     * @param float|null $qty
     * @param Product $product
     * @return float
     */
    public function getFinalPriceWithCuOptions($qty, $product)
    {
        $oldFinalPrice = $product->getData('final_price');
        $finalPrice = $this->_applyOptionsPrice($product, $qty, $oldFinalPrice);
        $finalPrice = max(0, $finalPrice);
        return $finalPrice - $oldFinalPrice;
    }
}
