<?php namespace Omnipay\Asaas\Message;


abstract class AbstractRequest extends \Omnipay\Common\Message\AbstractRequest
{
    protected $liveEndpoint = 'https://www.asaas.com/api/v3';
    protected $testEndpoint = 'https://sandbox.asaas.com/api/v3';
    protected $version = 3;
    protected $requestMethod = 'POST';
    protected $resource = '';

    public function sendData($data)
    {
        $method = $this->requestMethod;
        $url = $this->getEndpoint();

        $headers = [
            'access_token' => $this->getApiKey(),
            'Content-Type' => 'application/json',
        ];

        //print_r([$method, $url, $headers, json_encode($data)]);exit();
        $response = $this->httpClient->request(
            $method,
            $url,
            $headers,
            $this->toJSON($data)
            //http_build_query($data, '', '&')
        );
        //print_r($response);
        //print_r($data);

        if ($response->getStatusCode() != 200 && $response->getStatusCode() != 201 && $response->getStatusCode() != 400) {
            $array = [
                'error' => [
                    'code' => $response->getStatusCode(),
                    'message' => $response->getReasonPhrase()
                ]
            ];

            return $this->response = $this->createResponse($array);
        }

        $json = $response->getBody()->getContents();
        $array = @json_decode($json, true);
        //print_r($array);

        return $this->response = $this->createResponse(@$array);
    }

    protected function setBaseEndpoint($value)
    {
        $this->baseEndpoint = $value;
    }

    public function __get($name)
    {
        return $this->getParameter($name);
    }

    protected function setRequestMethod($value)
    {
        return $this->requestMethod = $value;
    }

    protected function decode($data)
    {
        return json_decode($data, true);
    }

    public function getEmail()
    {
        return $this->getParameter('email');
    }

    public function setEmail($value)
    {
        return $this->setParameter('email', $value);
    }

    public function getCustomerId()
    {
        return $this->getParameter('customer_id');
    }

    public function setCustomerId($value)
    {
        return $this->setParameter('customer_id', $value);
    }

    public function getApiKey()
    {
        return $this->getParameter('apiKey');
    }

    public function setApiKey($value)
    {
        return $this->setParameter('apiKey', $value);
    }

    public function setOrderId($value)
    {
        return $this->setParameter('order_id', $value);
    }
    public function getOrderId()
    {
        return $this->getParameter('order_id');
    }

    public function setInstallments($value)
    {
        return $this->setParameter('installments', $value);
    }
    public function getInstallments()
    {
        return $this->getParameter('installments');
    }

    public function setSoftDescriptor($value)
    {
        return $this->setParameter('soft_descriptor', $value);
    }
    public function getSoftDescriptor()
    {
        return $this->getParameter('soft_descriptor');
    }

    public function getCustomerName()
    {
        return $this->getParameter('customer_name');
    }

    public function setCustomerName($value)
    {
        $this->setParameter('customer_name', $value);
    }

    public function getPaymentType()
    {
        return $this->getParameter('paymentType');
    }

    public function setPaymentType($value)
    {
        $this->setParameter('paymentType', $value);
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

    public function getTransactionID()
    {
        return $this->getParameter('transactionId');
    }

    public function setTransactionID($value)
    {
        return $this->setParameter('transactionId', $value);
    }

    public function getDataCreditCard()
    {
        $this->validate('card');
        $card = $this->getCard();
        $customer = $this->getCustomer();

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
                "email"=> $customer->getEmail(),
                "cpfCnpj"=> $card->getHolderDocumentNumber(),
                "postalCode"=> $customer->getShippingPostcode(),
                "addressNumber"=> $customer->getShippingNumber(),
                "addressComplement"=> $customer->getShippingAddress2(),
                "phone"=> $customer->getPhone(),
                "mobilePhone"=> $customer->getPhone()
            ]
        ];

        return $data;
    }

    public function getDataBoleto()
    {
        $this->validate('customer');
        $customer = $this->getCustomer();

        $data = [
            "customer"=> $this->getCustomerId(),
            "billingType"=> "BOLETO",
            "dueDate"=> $this->getDueDate(), // vencimento
            "value"=> $this->getAmount(),
            "description"=> "Compra em ".$this->getSoftDescriptor(),
            "externalReference"=> $this->getOrderId(),
        ];

        return $data;
    }

    public function getDataPix()
    {
        $this->validate('customer');
        $customer = $this->getCustomer();

        $data = [
            "customer"=> $this->getCustomerId(),
            "billingType"=> "PIX",
            "dueDate"=> $this->getDueDate(), // vencimento
            "value"=> $this->getAmount(),
            "description"=> "Compra em ".$this->getSoftDescriptor(),
            "externalReference"=> $this->getOrderId(),
        ];

        return $data;
    }

    public function getCustomer()
    {
        return $this->getParameter('customer');
    }

    public function setCustomer($value)
    {
        return $this->setParameter('customer', $value);
    }

    public function getResource()
    {
        return $this->resource;
    }

    public function getVersion()
    {
        return $this->version;
    }

    public function getMethod()
    {
        return $this->requestMethod;
    }

    protected function createResponse($data)
    {
        return $this->response = new Response($this, $data);
    }

    protected function getEndpoint()
    {
        $endPoint = ($this->getTestMode()?$this->testEndpoint:$this->liveEndpoint);
        return  "{$endPoint}/{$this->getResource()}";
    }

    public function getData()
    {
        $this->validate('apiKey');

        return [
            'apiKey' => $this->getApiKey(),
        ];
    }

    public function toJSON($data, $options = 0)
    {
        if (version_compare(phpversion(), '5.4.0', '>=') === true) {
            return json_encode($data, $options | 64);
        }
        return str_replace('\\/', '/', json_encode($data, $options));
    }
}
