<?php
class Intraface_modules_currency_Controller_ExchangeRate_Index extends k_Component
{
    /**
     * Returns translations object
     *
     * @return object Translation
     */
    public function getTranslation()
    {
        return $this->context->getTranslation();
    }

    public function getCurrency()
    {
        return $this->context->getCurrency();
    }

    function renderHtml()
    {
        return 'Intentionally left blank';
    }

    function map($name)
    {
        if ($name == 'productprice') {
            return 'Intraface_modules_currency_Controller_ExchangeRate_ProductPrice';
        } elseif ($name == 'payment') {
            return 'Intraface_modules_currency_Controller_ExchangeRate_Payment';
        }
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }
}
