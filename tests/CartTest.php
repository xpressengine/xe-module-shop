<?php
require_once "lib/Shop_Generic_Tests.class.php";
require dirname(__FILE__) . '/lib/Bootstrap.php';
require_once dirname(__FILE__) . '/../libs/repositories/CartRepository.php';
require_once dirname(__FILE__) . '/../shop.info.php';

class CartTest extends Shop_Generic_Tests_DatabaseTestCase
{
    /**
     * @return PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    public function getDataSet()
    {
        return new Shop_DbUnit_ArrayDataSet(array(
            'xe_shop_cart' => array(
                array('cart_srl' => '774','module_srl' => '107','member_srl' => '4','session_id' => 'u1d0efs24bm05no5s2tgjspvo6','billing_address_srl' => '253','shipping_address_srl' => '253','items' => '2','extra' => '{"price":44.979999542236,"shipping_method":"flat_rate_shipping","payment_method":"cash_on_delivery"}','regdate' => '20120929183309','last_update' => '20120929183309')
            ),
            'xe_shop_cart_products' => array(
                array('cart_srl' => '774','product_srl' => '133','quantity' => '1','title' => 'Cutie depozitare diferite modele'),
                array('cart_srl' => '774','product_srl' => '130','quantity' => '1','title' => 'Cutie din lemn')
            ),
            'xe_shop_products' => array(
                array('product_srl' => '130','member_srl' => '4','module_srl' => '107','parent_product_srl' => NULL,'product_type' => 'simple','title' => 'Cutie din lemn','description' => 'Bam boo magazinul on-line de cadouri si decoratiuni va recomanda aceasta cutie din lemn cu un design clasic, avand 6 compartimente poate indeplinii mai multe roluri in casa si viata dvs.','short_description' => 'Bam boo magazinul on-line de cadouri si decoratiuni va recomanda aceasta cutie din lemn cu un design clasic, avand 6 compartimente poate indeplinii mai multe roluri in casa si viata dvs.','sku' => 'MOL9505','weight' => '0','status' => 'enabled','friendly_url' => 'MOL9505','price' => '29.99','qty' => '10','in_stock' => 'Y','primary_image_filename' => 'MOL9505_5784.jpg','related_products' => NULL,'regdate' => '20120904144739','last_update' => '20120923191329','discount_price' => '0','is_featured' => 'Y'),
                array('product_srl' => '133','member_srl' => '4','module_srl' => '107','parent_product_srl' => NULL,'product_type' => 'simple','title' => 'Cutie depozitare diferite modele','description' => 'Bam boo, magazinul on-line de decoratiuni si cadouri, va prezinta noua gama de cutii de depozitare din metal in 3 modele simpatice, foarte utile in casa dvs.','short_description' => 'Bam boo, magazinul on-line de decoratiuni si cadouri, va prezinta noua gama de cutii de depozitare din metal in 3 modele simpatice, foarte utile in casa dvs.','sku' => 'NRUOMY742C','weight' => '0','status' => 'enabled','friendly_url' => 'NRUOMY742C','price' => '14.99','qty' => '10','in_stock' => 'Y','primary_image_filename' => 'turta-dulce.jpg','related_products' => NULL,'regdate' => '20120904144841','last_update' => '20120926171804','discount_price' => '0','is_featured' => 'Y')
            ),
            'xe_shop_shipping_methods' => array(
                array('id' => '768','name' => 'flat_rate_shipping','display_name' => 'Flat Rate Shipping','status' => '1','props' => 'O:8:"stdClass":2:{s:4:"type";s:9:"per_order";s:5:"price";s:2:"10";}','module_srl' => '107')
            ),
            'xe_shop' => array(
                array('module_srl' => '107','member_srl' => '4','shop_title' => '','shop_content' => '','profile_content' => '','input_email' => 'R','input_website' => 'R','timezone' => '+0300','currency' => 'EUR','VAT' => NULL,'telephone' => NULL,'address' => NULL,'regdate' => '20120831171133','currency_symbol' => '€','discount_min_amount' => NULL,'discount_type' => NULL,'discount_amount' => NULL,'discount_tax_phase' => NULL,'out_of_stock_products' => 'Y','minimum_order' => NULL,'show_VAT' => NULL,'menus' => 'a:2:{s:11:"header_menu";s:3:"108";s:11:"footer_menu";s:3:"393";}')
            ),
            'xe_sites' => array(
                array('site_srl' => '106','index_module_srl' => '107','domain' => 'shop','default_language' => 'en','regdate' => '20120831171133')
            ),
            'xe_modules' => array(
                array('module_srl' => '107','module' => 'shop','module_category_srl' => '0','layout_srl' => '0','use_mobile' => 'N','mlayout_srl' => '0','menu_srl' => '108','site_srl' => '106','mid' => 'shop','is_skin_fix' => 'Y','skin' => 'default','mskin' => NULL,'browser_title' => 'admin\'s Shop','description' => '','is_default' => 'N','content' => NULL,'mcontent' => NULL,'open_rss' => 'Y','header_text' => '','footer_text' => '','regdate' => '20120831171133')
            )
        ));
    }

    public function testFirstCount()
    {
        $this->assertEquals(1, $this->getConnection()->getRowCount('xe_shop_cart'), "First count");
    }

    public function testAddCart()
    {
        $cart = new Cart(array(
            'module_srl'    => 307,
            'member_srl'    => null,
            'guest_srl'     => 14,
            'session_id'    => null,
            'items'         => null,
            'regdate'       => '20100424171420',
            'last_update'   => '20100424192420'
        ));

        $cart_repository = new CartRepository();
        $cart_repository->insertCart($cart);
        $this->assertEquals(2, $this->getConnection()->getRowCount('xe_shop_cart'), "Insert failed");
    }

    public function testCartTotal_WithShipping()
    {
        $module_srl = 107;
        $cart_srl = 774;

        $cart = new Cart($cart_srl);

        // 1. Check that cart has expected products
        $this->assertEquals(2, count($cart->getProducts()));

        // 2. Check that shipping method is set and shipping cost is correct
        $this->assertEquals('flat_rate_shipping', $cart->getShippingMethodName());
        $this->assertEquals(10, $cart->getShippingCost());

        // 3. Check that item total is correct
        $this->assertEquals(44.98, $cart->getItemTotal());

        // 4. Check global total is correct
        $this->assertEquals(54.98, $cart->getTotal());
    }

    public function testCartTotal_WithShippingAndDiscount()
    {
        $module_srl = 107;
        $cart_srl = 774;

        // Configure shop to use discounts
        $args = new stdClass();
        $args->module_srl = $module_srl;
        $args->discount_min_amount = 10;
        $args->discount_type = 'fixed_amount';
        $args->discount_amount = 5;
        $args->discount_tax_phase = 'post_taxes';
        $output = executeQuery('shop.updateDiscountInfo',$args);
        if(!$output->toBool())
        {
            throw new Exception($output->getMessage());
        }

        $cart = new Cart($cart_srl);

        // 1. Check that cart has expected products
        $this->assertEquals(2, count($cart->getProducts()));

        // 2. Check that shipping method is set and shipping cost is correct
        $this->assertEquals('flat_rate_shipping', $cart->getShippingMethodName());
        $this->assertEquals(10, $cart->getShippingCost());

        // 3. Check that item total is correct
        $this->assertEquals(44.98, $cart->getItemTotal());

        // 4. Check total before discount is correct
        $this->assertEquals(54.98, $cart->getTotalBeforeDiscount());

        // 5. Check global total is correct
        $this->assertEquals(49.98, $cart->getTotal());
    }

    public function testCartGetProducts_AllAvailable()
    {
        $cart_srl = 774;
        $cart = new Cart($cart_srl);

        $this->assertEquals(2, count($cart->getProducts()));
    }

    public function testCartGetProducts_AllAvailableWithLimit()
    {
        $cart_srl = 774;
        $cart = new Cart($cart_srl);

        $this->assertEquals(1, count($cart->getProducts(1)));
    }

    public function testCartGetProducts_Unavailable()
    {
        $cart_srl = 774;
        $deleted_product_srl = 133;
        $cart = new Cart($cart_srl);

        // Act: delete one product from xe_products but keep it in cart
        $product_repository = new ProductRepository();
        $product_repository->deleteProduct(array('product_srl'=>$deleted_product_srl));

        // If we activate onlyAvailable, only 1 product should be returned
        $this->assertEquals(1, count($cart->getProducts(null, true)));
        // Default, onlyAvailable is false, so all products should be returned => 2
        $this->assertEquals(2, count($cart->getProducts()));
    }

    /**
     * Test cart when a product becomes unavailable (deleted / out of stock)
     * after the user has already added it to the cart
     */
    public function testCartTotal_WithUnavailableProducts()
    {
        $module_srl = 107;
        $cart_srl = 774;
        $deleted_product_srl = 133;

        // Act: delete one product from xe_products but keep it in cart
        $product_repository = new ProductRepository();
        $args = new stdClass();
        $args->product_srl = $deleted_product_srl;
        $product_repository->deleteProduct($args);

        $cart = new Cart($cart_srl);

        // Assert
        // 1. Check that cart has expected products
        $this->assertEquals(1, count($cart->getProducts(null, true))); // When $onlyAvailable is true, count just availablel products
        $this->assertEquals(2, count($cart->getProducts())); // Default, , show all products

        // 3. Check that item total is correct
        // 29.99+14.99
        //TODO: continue adding price to deleted products, then make the correct calculus
        $this->assertEquals(44.98, $cart->getItemTotal()); // Default, count all products
        $this->assertEquals(29.99, $cart->getItemTotal(true)); // Count just available products

        // 4. Check global total is correct (includes shipping +10)
        $this->assertEquals(54.98, $cart->getTotal()); // Default, count all products
        $this->assertEquals(39.99, $cart->getTotal(true)); // Count just available products
    }

}