<?php namespace Omnipay\Asaas\Message;

/**
 *
 * <code>
 *   // Do a refund transaction on the gateway
 *   $transaction = $gateway->void(array(
 *       'transactionId'     => $transactionCode,
 *   ));
 *
 *   $response = $transaction->send();
 *   if ($response->isSuccessful()) {
 *   }
 * </code>
 */

class VoidRequest extends AbstractRequest   // está dando  erro para vendas com cartao parcelado, não permitindo estornar individualmente o pagamento
{
    protected $resource = 'payments';
    protected $requestMethod = 'POST';

    /**
     * Get the raw data array for this message. The format of this varies from gateway to
     * gateway, but will usually be either an associative array, or a SimpleXMLElement.
     *
     * @return mixed
     */
    public function getData()
    {
        return parent::getData();
    }

    public function sendData($data)
    {
        $this->validate('transactionId');

        $headers = [
            'access_token' => $this->getApiKey(),
            'Content-Type' => 'application/json',
        ];

        $url = sprintf(
            '%s/%s/refund',
            $this->getEndpoint(),
            $this->getTransactionID()
        );

        $httpResponse = $this->httpClient->request($this->getMethod(), $url, $headers);
        $json = $httpResponse->getBody()->getContents();
        return $this->createResponse(@json_decode($json, true));
    }
}
