<?php namespace Omnipay\Asaas\Message;

class PurchaseRequest extends AbstractRequest
{
    protected $resource = 'payments';
    /**
     * Get the raw data array for this message. The format of this varies from gateway to
     * gateway, but will usually be either an associative array, or a SimpleXMLElement.
     *
     * @return mixed
     */

    public function getData()
    {
        $this->validate('customer', 'paymentType');

        // faz o registro do cliente, se não houver especificado
        if(strlen($this->getCustomerId()) <= 0)
        {
            $cl = new CustomersRequest($this->httpClient, $this->httpRequest);
            $cl->initialize($this->parameters->all());
            $result = $cl->sendData($cl->getData());
            if ($result->isSuccessful())
                $this->setCustomerId($result->getTransactionID());
        }

        $this->validate('customer_id');

        $data = [];
        switch(strtolower($this->getPaymentType()))
        {
            case 'creditcard':
                $data = $this->getDataCreditCard();
                break;

            case 'boleto':
                $data = $this->getDataBoleto();
                break;

            case 'pix':
                $data = $this->getDataPix();
                break;

            default:
                $data = $this->getDataCreditCard();
        }

        //$this->getNotifyUrl()  // verificar se no painel é especificado uma url para notificação

        return $data;
    }
}
