<?php

abstract class PaymentGatewayAbstract
{
    /**
     * Returns the payment gateway's name
     * Defaults: Splits folder name into words and makes them uppercase
     * @return string
     */
    public function getDisplayName()
    {
        $name = $this->getUniqueName();
        return ucwords(str_replace('_', ' ', $name));
    }

    /**
     * Returns unique identifier for Payment gateway
     * Represents the folder name where the gateway class is found
     */
    final public function getName()
    {
        $reflector = new ReflectionClass(get_class($this));
        $payment_class_directory_path = dirname($reflector->getFileName());
        $folders = explode(DIRECTORY_SEPARATOR, $payment_class_directory_path);
        return array_pop($folders);
    }
}