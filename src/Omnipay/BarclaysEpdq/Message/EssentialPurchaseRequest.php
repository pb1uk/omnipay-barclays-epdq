<?php

namespace Omnipay\BarclaysEpdq\Message;

use Omnipay\BarclaysEpdq\PageLayout;
use Omnipay\BarclaysEpdq\Delivery;
use Omnipay\Common\Message\AbstractRequest;
use Omnipay\Omnipay;

/**
 * BarclaysEpdq Essential Purchase Request
 */
class EssentialPurchaseRequest extends AbstractRequest
{

    protected $liveEndpoint = 'https://payments.epdq.co.uk/ncol/prod/orderstandard_utf8.asp';
    protected $testEndpoint = 'https://mdepayments.epdq.co.uk/ncol/test/orderstandard_utf8.asp';

    public function getClientId()
    {
        return $this->getParameter('clientId');
    }

    public function setClientId($value)
    {
        return $this->setParameter('clientId', $value);
    }

    public function getLanguage()
    {
        return $this->getParameter('language');
    }

    public function setLanguage($value)
    {
        return $this->setParameter('language', $value);
    }

    public function getReturnUrl()
    {
        return $this->getParameter('returnUrl');
    }

    public function getDeclineUrl()
    {
        return $this->getParameter('declineUrl');
    }

    public function getExceptionUrl()
    {
        return $this->getParameter('exceptionUrl');
    }

    public function setReturnUrl($value)
    {
        $this->setParameter('returnUrl', $value);
        $this->setParameter('declineUrl', $value);
        $this->setParameter('exceptionUrl', $value);

        return $this;
    }

    public function getShaIn()
    {
        return $this->getParameter('shaIn');
    }

    public function setShaIn($value)
    {
        return $this->setParameter('shaIn', $value);
    }

    public function getShaOut()
    {
        return $this->getParameter('shaOut');
    }

    public function setShaOut($value)
    {
        return $this->setParameter('shaOut', $value);
    }

    public function getCallbackMethod()
    {
        return $this->getParameter('callbackMethod');
    }

    public function setCallbackMethod($value)
    {
        return $this->setParameter('callbackMethod', $value);
    }

    /**
     * Get the page layout configuration
     *
     * @return PageLayout
     */
    public function getPageLayout()
    {
        return $this->getParameter('pageLayout');
    }

    public function setPageLayout($value)
    {
        return $this->setParameter('pageLayout', $value);
    }

    /**
     * Get the delivery and invoicing data parameters
     *
     * @return Delivery
     */
    public function getDelivery()
    {
        return $this->getParameter('delivery');
    }

    public function setDelivery($value)
    {
        return $this->setParameter('delivery', $value);
    }

    public function getData()
    {
        $this->validate('amount', 'clientId', 'currency', 'language');

        $data = array();

        $data['PSPID']          = $this->getClientId();

        $data['ORDERID']        = $this->getTransactionId();
        $data['CURRENCY']       = $this->getCurrency();
        $data['LANGUAGE']       = $this->getLanguage();
        $data['AMOUNT']         = $this->getAmountInteger();

        $data['ACCEPTURL']      = $this->getReturnUrl();
        $data['CANCELURL']      = $this->getCancelUrl();
        $data['DECLINEURL']     = $this->getDeclineUrl();
        $data['EXCEPTIONURL']   = $this->getExceptionUrl();

        $card = $this->getCard();
        if ($card) {
            $data['CN']              = $card->getName();
            $data['COM']             = $card->getCompany();
            $data['EMAIL']           = $card->getEmail();
            $data['OWNERZIP']        = $card->getPostcode();
            $data['OWNERTOWN']       = $card->getCity();
            $data['OWNERCTY']        = $card->getCountry();
            $data['OWNERTELNO']      = $card->getPhone();
            $data['OWNERADDRESS']    = $card->getAddress1();
            $data['OWNERADDRESS2']   = $card->getAddress2();
        }

        $items = $this->getItems();
        if ($items) {
            foreach ($items as $n => $item) {
                $data["ITEMNAME$n"]            = $item->getName();
                $data["ITEMDESC$n"]            = $item->getDescription();
                $data["ITEMQUANT$n"]           = $item->getQuantity();
                $data["ITEMPRICE$n"]           = $this->formatCurrency($item->getPrice());
                if (is_a($item, 'Omnipay\BarclaysEpdq\Item')) {
                    $data["ITEMID$n"]              = $item->getId();
                    $data["ITEMCOMMENTS$n"]        = $item->getComments();
                    $data["ITEMCATEGORY$n"]        = $item->getCategory();
                    $data["ITEMATTRIBUTES$n"]      = $item->getAttributes();
                    $data["ITEMDISCOUNT$n"]        = $this->formatCurrency($item->getDiscount());
                    $data["ITEMUNITOFMEASURE$n"]   = $item->getUnitOfMeasure();
                    $data["ITEMWEIGHT$n"]          = $item->getWeight();
                    $data["ITEMVAT$n"]             = $this->formatCurrency($item->getVat());
                    $data["ITEMVATCODE$n"]         = $item->getVatCode();
                    $data["ITEMFDMPRODUCTCATEG$n"] = $item->getFraudModuleCategory();
                    $data["ITEMQUANTORIG$n"]       = $item->getMaximumQuantity();
                }
            }
        }

        $pageLayout = $this->getPageLayout();
        if ($pageLayout) {
            $data['TITLE']          = $pageLayout->getTitle();
            $data['BGCOLOR']        = $pageLayout->getBackgroundColor();
            $data['TXTCOLOR']       = $pageLayout->getTextColor();
            $data['TBLBGCOLOR']     = $pageLayout->getTableBackgroundColor();
            $data['TBLTXTCOLOR']    = $pageLayout->getTableTextColor();
            $data['HDTBLBGCOLOR']   = $pageLayout->getHdTableBackgroundColor();
            $data['HDTBLTXTCOLOR']  = $pageLayout->getHdTableTextColor();
            $data['HDFONTTYPE']     = $pageLayout->getHdFontType();
            $data['BUTTONBGCOLOR']  = $pageLayout->getButtonBackgroundColor();
            $data['BUTTONTXTCOLOR'] = $pageLayout->getButtonTextColor();
            $data['FONTTYPE']       = $pageLayout->getFontType();
            $data['LOGO']           = $pageLayout->getLogo();
        }

        $delivery = $this->getDelivery();
        if ($delivery) {
            $data['ORDERSHIPMETH'] = $delivery->getDeliveryMethod();
            $data['ORDERSHIPCOST'] = $delivery->getDeliveryCost();
            $data['ORDERSHIPTAXCODE'] = $delivery->getDeliveryTaxCode();
            $data['CUID'] = $delivery->getCuid();
            $data['CIVILITY'] = $delivery->getCivility();
            $data['ECOM_CONSUMER_GENDER'] = $delivery->getGender();
            $data['ECOM_BILLTO_POSTAL_NAME_FIRST'] = $delivery->getInvoicingFirstName();
            $data['ECOM_BILLTO_POSTAL_NAME_LAST'] = $delivery->getInvoicingLastName();
            $data['ECOM_BILLTO_POSTAL_STREET_LINE1'] = $delivery->getInvoicingAddress1();
            $data['ECOM_BILLTO_POSTAL_STREET_LINE2'] = $delivery->getInvoicingAddress2();
            $data['ECOM_BILLTO_POSTAL_STREET_NUMBER'] = $delivery->getInvoicingStreetNumber();
            $data['ECOM_BILLTO_POSTAL_POSTALCODE'] = $delivery->getInvoicingPostalCode();
            $data['ECOM_BILLTO_POSTAL_CITY'] = $delivery->getInvoicingCity();
            $data['ECOM_BILLTO_POSTAL_COUNTRYCODE'] = $delivery->getInvoicingCountryCode();
            $data['ECOM_SHIPTO_POSTAL_NAME_PREFIX'] = $delivery->getDeliveryNamePrefix();
            $data['ECOM_SHIPTO_POSTAL_NAME_FIRST'] = $delivery->getDeliveryFirstName();
            $data['ECOM_SHIPTO_POSTAL_LAST_FIRST'] = $delivery->getDeliveryLastName();
            $data['ECOM_SHIPTO_POSTAL_STREET_LINE1'] = $delivery->getDeliveryAddress1();
            $data['ECOM_SHIPTO_POSTAL_STREET_LINE2'] = $delivery->getDeliveryAddress2();
            $data['ECOM_SHIPTO_POSTAL_STREET_NUMBER'] = $delivery->getDeliveryStreetNumber();
            $data['ECOM_SHIPTO_POSTAL_POSTALCODE'] = $delivery->getDeliveryPostalCode();
            $data['ECOM_SHIPTO_POSTAL_CITY'] = $delivery->getDeliveryCity();
            $data['ECOM_SHIPTO_POSTAL_COUNTRYCODE'] = $delivery->getDeliveryCountryCode();
            $data['ECOM_SHIPTO_ONLINE_EMAIL'] = $delivery->getDeliveryEmail();
            $data['ECOM_SHIPTO_TELECOM_FAX_NUMBER'] = $delivery->getDeliveryFax();
            $data['ECOM_SHIPTO_TELECOM_PHONE_NUMBER'] = $delivery->getDeliveryPhone();
            $data['ECOM_SHIPTO_DOB'] = $delivery->getDeliveryBirthDate();
        }

        $data = $this->cleanParameters($data);

        if ($this->getShaIn()) {
            $data['SHASIGN'] = $this->calculateSha($data, $this->getShaIn());
        }

        return $data;
    }

    protected function cleanParameters($data)
    {
        $clean = array();
        foreach ($data as $key => $value) {
            if (!is_null($value) && $value !== false && $value !== '') {
                $clean[strtoupper($key)] = $value;
            }
        }

        return $clean;
    }

    public function calculateSha($data, $shaKey)
    {
        ksort($data);

        $shaString = '';
        foreach ($data as $key => $value) {
            $shaString .= sprintf('%s=%s%s', strtoupper($key), $value, $shaKey);
        }

        return strtoupper(sha1($shaString));
    }

    public function sendData($data)
    {
        return $this->response = new EssentialPurchaseResponse($this, $data);
    }

    public function getEndpoint()
    {
        return $this->getTestMode() ? $this->testEndpoint : $this->liveEndpoint;
    }
}
