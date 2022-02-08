<?php namespace Omnipay\Asaas\Message;

/*
https://docs.google.com/document/d/1XUJRHY_0nd45CzFK5EmjDK92qgaQJGMxT0rjZriTk-g/edit#

POST de notificação (Webhook)

Opcionalmente, você pode configurar o Asaas para que seja enviado um POST para a sua aplicação sempre que ocorrerem alterações em uma cobrança. Os eventos que geram notificações deste tipo são: criação, confirmação de pagamento, vencimento, exclusão e alteração de dados da cobrança.
Para habilitar estas notificações, acesse a área de Configurações do Asaas, Aba Integração, e informe a URL da sua aplicação que deve receber o POST.

Para que o Asaas considere o POST como enviado, o status HTTP da resposta deve ser 200, além de conter o texto “SUCCESS”.


O Asaas fará um POST para a sua aplicação, contendo um único atributo: “data”. Este atributo contém a String que é a representação de um objeto JSON, contendo o evento e todos os dados atualizados da cobrança que o originou. Verifique a tabela de eventos para saber o que cada um deles representa.

Segue exemplo do objeto enviado:
{
    "event": "PAYMENT_RECEIVED",
    "payment": {
        "object": "payment",
        "id": "pay_614896582179",
        "customer": "cus_k9c5dkgf82j9",
        "value": 500.00,
        "netValue": 495.00,
        "originalValue": null,
        "nossoNumero": "80516081",
        "description": "Pedido nr. 10598",
        "billingType": "BOLETO",
        "status": "RECEIVED",
        "dueDate": "07/05/2016",
        "paymentDate": "07/05/2016",
        "invoiceUrl": "https://www.asaas.com/i/614896582179",
        "boletoUrl": "https://www.asaas.com/b/pdf/614896582179",
        "invoiceNumber": "00932305",
        "externalReference": null,
        "deleted": false
    }
}


Eventos disponíveis
Nome
Evento
PAYMENT_CREATED  Geração de nova cobrança
PAYMENT_UPDATED Alteração no vencimento ou valor de cobrança existente.
PAYMENT_CONFIRMED Cobrança autorizada pela adquirente (somente cartão de crédito)
PAYMENT_RECEIVED Cobrança recebida.
PAYMENT_OVERDUE Cobrança vencida
PAYMENT_DELETED Cobrança removida
PAYMENT_REFUNDED Cobrança estornada (somente cartão de crédito)



 */

class NotificationRequest extends AbstractRequest
{
    protected $resource = 'notifications';
    protected $requestMethod = 'GET';

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

    public function getNotificationType()
    {
        return $this->getParameter('notificationType');
    }

    public function setNotificationType($value)
    {
        return $this->setParameter('notificationType', $value);
    }

    public function setNotificationCode($value)
    {
        return $this->setParameter('notificationCode', $value);
    }

    public function getNotificationCode()
    {
        return $this->getParameter('notificationCode');
    }

    public function sendData($data)
    {
        $this->validate('notificationCode');

        $url = sprintf(
            '%s/%s?%s',
            $this->getEndpoint(),
            $this->getNotificationCode(),
            http_build_query($data, '', '&')
        );

        print $url."\n\n";
        $httpResponse = $this->httpClient->request($this->getMethod(), $url, ['Content-Type' => 'application/x-www-form-urlencoded']);
        $xml          = @simplexml_load_string($httpResponse->getBody()->getContents(), 'SimpleXMLElement', LIBXML_NOCDATA);

        return $this->createResponse(@$this->xml2array($xml));
    }
}
