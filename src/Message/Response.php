<?php namespace Omnipay\Asaas\Message;

use Omnipay\Common\Message\AbstractResponse;
use Omnipay\Common\Message\RedirectResponseInterface;

/**
 * Pagarme Response
 *
 * This is the response class for all Pagarme requests.
 *
 * @see \Omnipay\Pagarme\Gateway
 */
class Response extends AbstractResponse
{
    /**
     * Is the transaction successful?
     *
     * @return bool
     */
    public function isSuccessful()
    {
        //$result = $this->data;
        if(isset($this->data['error']) || isset($this->data['error_messages']))
            return false;

        //if(isset($this->data['result']) && $this->data['result'] === 'OK')
        //    return true;

        if ((isset($this->data['dateCreated']) && isset($this->data['id'])) ||  // criação de customer
            (isset($this->data['code']) && isset($this->data['date'])) || (@reset($this->data) === 'OK')) {
            return true;
        }

        return false;
    }

    /**
     * Get the transaction reference.
     *
     * @return string|null
     */
    public function getTransactionID()
    {
        if(isset($this->data['id']))
            return @$this->data['id'];

        return @$this->data['code'];
    }

    public function getTransactionAuthorizationCode()
    {
        if(isset($this->data['id']))
            return @$this->data['id'];

        return @$this->data['code'];
    }

    public function getStatus()
    {
        $status = null;
        if(isset($this->data['status']))
            $status = @$this->data['status'];
        else
        {
            if(isset($this->data['charges']))
                $status = @$this->data['charges'][0]['status'];
        }

        return $status;
    }

    public function isPaid()
    {
        $status = $this->getStatus();
        return ((strcmp($status, "RECEIVED")==0)||(strcmp($status, "CONFIRMED")==0));
    }

    public function isAuthorized()
    {
        $status = $this->getStatus();
        return strcmp($status, "AUTHORIZED")==0;
    }

    public function isPending()
    {
        $status = $this->getStatus();
        return strcmp($status, "PENDING")==0;
    }

    public function isVoided()
    {
        $status = $this->getStatus();
        return ((strcmp($status, "REFUNDED")==0)||(strcmp($status, "REFUND_REQUESTED")==0));
    }

    /**
     * Get the error message from the response.
     *
     * Returns null if the request was successful.
     *
     * @return string|null
     */
    public function getMessage()
    {
        //print_r($this->data);
        if(isset($this->data['error']))
            return "{$this->data['error']['code']} - {$this->data['error']['message']}";

        if(isset($this->data['error_messages'])) {
            $message = "";
            if(isset($this->data['error_messages'][0]['message']))
                $message = @$this->data['error_messages'][0]['message'];
            if(isset($this->data['error_messages'][0]['description']))
                $message = @$this->data['error_messages'][0]['description']." => ".@$this->data['error_messages'][0]['parameter_name'];

            return "{$this->data['error_messages'][0]['code']} - $message";
        }

        return null;
    }

    public function getBoleto()
    {
        $data = $this->getData();
        $boleto = array();
        $boleto['boleto_url'] = @$data['invoiceUrl'];
        $boleto['boleto_url_pdf'] = @$data['bankSlipUrl'];
        $boleto['boleto_barcode'] = NULL;
        $boleto['boleto_expiration_date'] = @$data['dueDate'];
        $boleto['boleto_valor'] = @$data['value'];
        $boleto['boleto_transaction_id'] = @$data['id'];
        //@$this->setTransactionReference(@$data['transaction_id']);

        return $boleto;
    }

    public function getPix()
    {
        $data = $this->getData();
        $boleto = array();
        $boleto['pix_qrcodebase64image'] = NULL;
        $boleto['pix_qrcodestring'] = NULL;
        $boleto['pix_valor'] = @$data['value'];
        $boleto['pix_externalurl'] = @$data['invoiceUrl'];
        $boleto['pix_transaction_id'] = @$data['id'];
        //@$this->setTransactionReference(@$data['transaction_id']);

        return $boleto;
    }
}