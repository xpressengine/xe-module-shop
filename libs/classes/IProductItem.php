<?php
/**
 * Defines common structure of products in an Order, Cart
 * or anything else that contains product items
 */
interface IProductItem extends IThumbnailable
{
    /**
     * Product title
     */
    public function getTitle();

    /**
     * Ordered quantity
     *
     * @return int
     */
    public function getQuantity();

    /**
     * Price
     */
    public function getPrice();


}