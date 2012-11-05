<?php

class CartPreview implements IProductItemsContainer
{
	private $cart;
	private $products_to_show;

	public function __construct(Cart $cart, $products_to_show = 3)
	{
		$this->cart = $cart;
		$this->products_to_show = $products_to_show;
	}

	public function getProducts()
	{
		return $this->cart->getProducts($this->products_to_show);
	}

	public function getCartProductsCount()
	{
		return $this->cart->count(TRUE);
	}

	public function hasProducts()
	{
		return count($this->getProducts()) > 0;
	}

	private function getCartPreviewProductsCount()
	{
		$products = $this->getProducts();
		$count = 0;
		foreach($products as $product)
		{
			$count += $product->getQuantity();
		}
		return $count;
	}

	public function getNumberOfProductsNotDisplayed()
	{
		return $this->getCartProductsCount() - $this->getCartPreviewProductsCount();
	}

	public function hasMoreProducts()
	{
		return $this->getNumberOfProductsNotDisplayed() > 0;
	}

	/**
	 * Shipping cost
	 */
	public function getShippingCost()
	{
		return $this->cart->getShippingCost();
	}

	/**
	 * Total before applying discount
	 *
	 * @return float
	 */
	public function getTotalBeforeDiscount()
	{
		return $this->cart->getTotalBeforeDiscount();
	}

	/**
	 * Discount name
	 */
	public function getDiscountName()
	{
		return $this->cart->getDiscountName();
	}

	/**
	 * Discount description
	 */
	public function getDiscountDescription()
	{
		return $this->cart->getDiscountDescription();
	}

	/**
	 * Discount amount
	 */
	public function getDiscountAmount()
	{
		return $this->cart->getDiscountAmount();
	}

	/**
	 * Returns global total
	 */
	public function getTotal()
	{
		return $this->cart->getTotal();
	}

	/**
	 * Returns amount of total that represents taxes
	 */
	public function getVAT()
	{
		return $this->cart->getVAT();
	}

    public function getShippingMethodName(){
        return $this->cart->getShippingMethodName();
    }
}