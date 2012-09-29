<?php
class Order extends BaseItem
{
    const
        ORDER_STATUS_HOLD = "Hold",
        ORDER_STATUS_PENDING = "Pending",
        ORDER_STATUS_PROCESSING = "Processing",
        ORDER_STATUS_COMPLETED = "Completed",
        ORDER_STATUS_CANCELED = "Canceled";

    public
        $order_srl,
        $module_srl,
        $cart_srl,
        $member_srl,
        $client_name,
        $client_email,
        $client_company,
        $billing_address,
        $shipping_address,
        $payment_method,
        $shipping_method,
        $shipping_cost,
        $total,
        $vat,
        $order_status,
        $ip,
        $regdate,
        $invoice,
        $shipment,
        $transaction_id,
        $discount_min_order,
        $discount_type,
        $discount_amount,
        $discount_tax_phase,
        $currency;

    /** @var OrderRepository */
    public $repo;

    public function save()
    {
        return $this->order_srl ? $this->repo->update($this) : $this->repo->insert($this);
    }

    public function __construct($data=null)
    {
        if ($data) {
            if($data instanceof Cart)
            {
                $this->loadFromCart($data);
                parent::__construct();
                return;
            }
            foreach (array('billing_address', 'shipping_address', 'shipping_method', 'payment_method') as $val) {
                if (!isset($orderData[$val])) {
                    //throw new Exception("Missing $val, can't continue.");
                }
            }
        }
        parent::__construct($data);
    }

    public function loadFromCart(Cart $cart)
    {
        $shopInfo = new ShopInfo($cart->module_srl);
        $this->cart_srl = $cart->cart_srl;
        $this->module_srl = $cart->module_srl;
        $this->member_srl = $cart->member_srl;
        $this->client_name = $cart->getBillingAddress()->firstname . ' ' . $cart->getBillingAddress()->lastname;
        $this->client_email = $cart->getBillingAddress()->email;
        $this->client_company = $cart->getBillingAddress()->company;
        $this->billing_address = (string) $cart->getBillingAddress();
        $this->shipping_address = (string) $cart->getShippingAddress();
        $this->payment_method = $cart->getExtra('payment_method');
        $this->shipping_method = $cart->getExtra('shipping_method');
        $this->shipping_cost = $cart->getShippingCost();
        $this->total = $cart->getTotal(true, true);
        $this->vat = ($shopInfo->getVAT() ? $shopInfo->getVAT() : 0);
        $this->order_status = Order::ORDER_STATUS_PENDING;
        $this->ip = $_SERVER['REMOTE_ADDR'];
        $this->currency = $shopInfo->getCurrency();
        if ($discount = $cart->getDiscount()) {
            $this->discount_min_order = $discount->getMinValueForDiscount();
            $this->discount_type = $shopInfo->getShopDiscountType();
            $this->discount_amount = $discount->getReductionValue();
            $this->discount_tax_phase = $discount->calculateBeforeApplyingVAT() ? 'pre_taxes' : 'post_taxes';
        }
    }

    public function getBillToName()
    {
        return $this->client_name;
    }

    public function getShipToName()
    {
        return reset(explode(',',$this->shipping_address));
    }

    public function saveCartProducts(Cart $cart, $calculateTotal=true)
    {
        if (!$this->order_srl) throw new Exception('Order not persisted');
        if (!$cart->cart_srl) throw new Exception('Cart not persisted');
        //remove all already existing links
        $this->repo->deleteOrderProducts($this->order_srl);
        //set the new links
        $total = 0;
        /** @var $productWithQuantity SimpleProduct */
        $products = $cart->getProducts();
        foreach ($products as $productWithQuantity) {
            if ($productWithQuantity->available && $productWithQuantity->product_srl) {
                $this->repo->insertOrderProduct($this->order_srl, $productWithQuantity, $productWithQuantity->quantity);
                $total += $productWithQuantity->quantity * $productWithQuantity->price;
            }
        }
        $this->total = $total;
        if ($calculateTotal) $this->save();
    }

    public function getProducts()
    {
        return $this->repo->getOrderItems($this);
    }

}