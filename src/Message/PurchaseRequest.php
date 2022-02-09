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
        // faz o registro do cliente, se não houver especificado
        if(strlen($this->getCustomerId()) <= 0)
        {
            $cl = new CustomersRequest($this->httpClient, $this->httpRequest);
            $cl->initialize($this->parameters->all());
            $result = $cl->sendData($cl->getData());
            if ($result->isSuccessful())
                $this->setCustomerId($result->getTransactionReference());
        }

        $this->validate('customer_id');

        $card = $this->getCard();

        $data = [
            "customer"=> $this->getCustomerId(),
            "billingType"=> "CREDIT_CARD",
            "dueDate"=> $this->getDueDate(), // vencimento
            "value"=> $this->getAmount(),
            "description"=> "Compra em ".$this->getSoftDescriptor(),
            "externalReference"=> $this->getOrderId(),
            "installmentCount"=> $this->getInstallments(),
            "installmentValue"=> (float)($this->getAmount()/$this->getInstallments()),
            "creditCard"=> [
                "holderName"=> $card->getName(),
                "number"=> $card->getNumber(),
                "expiryMonth"=> sprintf("%02d", $card->getExpiryMonth()*1),
                "expiryYear"=> $card->getExpiryYear(),
                "ccv"=> $card->getCvv()
            ],
            "creditCardHolderInfo" => [
                "name"=> $card->getName(),
                "email"=> $card->getEmail(),
                "cpfCnpj"=> $card->getHolderDocumentNumber(),
                "postalCode"=> $card->getShippingPostcode(),
                "addressNumber"=> $card->getHolderDocumentNumber(),
                "addressComplement"=> $card->getShippingAddress2(),
                "phone"=> $card->getPhone(),
                "mobilePhone"=> $card->getPhone()
            ]
        ];
        //$this->getNotifyUrl()  // verificar se no painel é especificado uma url para notificação

        return $data;
    }

    public function getShippingType()
    {
        return $this->getParameter('shippingType');
    }

    public function setShippingType($value)
    {
        return $this->setParameter('shippingType', $value);
    }

    public function getShippingCost()
    {
        return $this->getParameter('shippingCost');
    }

    public function setShippingCost($value)
    {
        return $this->setParameter('shippingCost', $value);
    }

    public function getCustomer()
    {
        return $this->getParameter('customer');
    }

    public function setCustomer($value)
    {
        return $this->setParameter('customer', $value);
    }

    public function getDueDate()
    {
        $dueDate = $this->getParameter('dueDate');
        if($dueDate)
            return $dueDate;

        $time = localtime(time());
        $ano = $time[5]+1900;
        $mes = $time[4]+1+1;
        $dia = 1;// $time[3];
        if($mes>12)
        {
            $mes=1;
            ++$ano;
        }

        $dueDate = sprintf("%04d-%02d-%02d", $ano, $mes, $dia);
        $this->setDueDate($dueDate);

        return $dueDate;
    }

    public function setDueDate($value)
    {
        return $this->setParameter('dueDate', $value);
    }
}
