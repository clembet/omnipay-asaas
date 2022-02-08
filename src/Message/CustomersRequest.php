<?php namespace Omnipay\Asaas\Message;


class CustomersRequest extends AbstractRequest   // /cancels é utilizado em pagamentos om cartão com o status em AUTHORIZED, ou seja para transações authorized (2 etapas)
{
    protected $resource = 'customers';
    protected $requestMethod = 'POST';

    public function getData()
    {
        $card = $this->getCard();
        //$this->validate('amount');
        //$data = parent::getData();
        $data = [
            "name"=> $card->getName(),
            "email"=> $card->getEmail(),
            "phone"=> $card->getPhone(),
            "mobilePhone"=> $card->getPhone(),
            "cpfCnpj"=> $card->getHolderDocumentNumber(),
            "postalCode"=> $card->getShippingPostcode(),
            "address"=> $card->getShippingAddress1(),
            "addressNumber"=> $card->getShippingNumber(),
            "complement"=> $card->getShippingAddress2(),
            "city"=> $card->getShippingCity(),
            "state"=> $card->getShippingState(),
            "province"=> $card->getShippingDistrict(),
            "notificationDisabled"=> true
        ];

        return $data;
    }

    public function sendData($data)
    {
        $method = $this->requestMethod;
        $url = $this->getEndpoint();

        $headers = [
            'access_token' => $this->getApiKey(),
            'Content-Type' => 'application/json',
        ];

        //print_r([$method, $url, $headers, json_encode($data)]);
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

}
