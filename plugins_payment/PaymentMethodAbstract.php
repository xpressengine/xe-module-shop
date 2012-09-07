<?php

abstract class PaymentMethodAbstract
{
    static protected $frontend_form = 'frontend_form.html';
    static protected $backend_form = 'backend_form.html';

    public $id = null;
    public $display_name;  /// Display name
    public $name; /// Unique name = folder name
    public $status = 0;
    public $properties;

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
        $payment_class_directory_path = $this->getPaymentMethodDir();
        $folders = explode(DIRECTORY_SEPARATOR, $payment_class_directory_path);
        return array_pop($folders);
    }

    public function setProperties($data)
    {
        foreach($data as $property_name => $property_value)
        {
            $this->{$property_name} = $property_value;
        }
    }

    public function isActive()
    {
        return $this->status ? true : false;
    }

    public function __set($name, $value)
    {
        $this->properties->$name = $value;
    }

    public function __get($name)
    {
        return $this->properties->$name;
    }


    private function getPaymentMethodDir()
    {
        $reflector = new ReflectionClass(get_class($this));
        return dirname($reflector->getFileName());
    }

    private function getFormHtml($filename)
    {
        if(!file_exists($this->getPaymentMethodDir() . DIRECTORY_SEPARATOR . $filename))
        {
            return '';
        }

        $oTemplate = &TemplateHandler::getInstance();
        return $oTemplate->compile($this->getPaymentMethodDir(), $filename);
    }

    public function getFrontendFormHTML()
    {
        return $this->getFormHtml(self::$frontend_form);
    }

    public function getBackendFormHTML()
    {
        return $this->getFormHtml(self::$backend_form);
    }
}