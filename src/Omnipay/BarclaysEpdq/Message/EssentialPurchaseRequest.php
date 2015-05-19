<?php

namespace Omnipay\BarclaysEpdq\Message;

use Omnipay\Common\Currency;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Message\AbstractRequest;

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

    public function getTitle()
    {
        return $this->getParameter('title');
    }

    public function setTitle($value)
    {
        return $this->setParameter('title', $value);
    }

    public function getButtonTxtColor()
    {
        return $this->getParameter('buttonTxtColor');
    }

    public function setButtonTxtColor($value)
    {
        return $this->setParameter('buttonTxtColor', $value);
    }

    public function getButtonBgColor()
    {
        return $this->getParameter('buttonBgColor');
    }

    public function setButtonBgColor($value)
    {
        return $this->setParameter('buttonBgColor', $value);
    }

    public function getBgColor()
    {
        return $this->getParameter('bgColor');
    }

    public function setBgColor($value)
    {
        return $this->setParameter('bgColor', $value);
    }

    public function getTblTxtColor()
    {
        return $this->getParameter('tblTxtColor');
    }

    public function setTblTxtColor($value)
    {
        return $this->setParameter('tblTxtColor', $value);
    }

    public function getTxtColor()
    {
        return $this->getParameter('txtColor');
    }

    public function setTxtColor($value)
    {
        return $this->setParameter('txtColor', $value);
    }

    public function getTblBgColor()
    {
        return $this->getParameter('tblBgColor');
    }

    public function setTblBgColor($value)
    {
        return $this->setParameter('tblBgColor', $value);
    }

    public function getFontType()
    {
        return $this->getParameter('fontType');
    }

    public function setFontType($value)
    {
        return $this->setParameter('fontType', $value);
    }
    
    public function getCallbackMethod()
    {
        return $this->getParameter('callbackMethod');
    }

    public function setCallbackMethod($value)
    {
        return $this->setParameter('callbackMethod', $value);
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
            $data['OWNERTELNO']      = $card->getPhone();
            $data['OWNERADDRESS']    = $card->getAddress1();
        }

        $data['BUTTONTXTCOLOR'] = $this->getButtonTxtColor();
        $data['BUTTONBGCOLOR']  = $this->getButtonBgColor();
        $data['BGCOLOR']        = $this->getBgColor();
        $data['TBLTXTCOLOR']    = $this->getTblTxtColor();
        $data['TITLE']          = $this->getTitle(); 
        $data['TXTCOLOR']       = $this->getTxtColor();
        $data['TBLBGCOLOR']     = $this->getTblBgColor();
        $data['FONTTYPE']       = $this->getFontType();

        $items = $this->getItems();

        if ($items) {
            foreach ($items as $n => $item) {
                $data["ITEMNAME$n"] = $item->getName();
                $data["ITEMDESC$n"] = $item->getDescription();
                $data["ITEMQUANT$n"] = $item->getQuantity();
                $data["ITEMPRICE$n"] = $this->formatCurrency($item->getPrice());
            }
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
