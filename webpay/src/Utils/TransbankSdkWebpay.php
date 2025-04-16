<?php

namespace PrestaShop\Module\WebpayPlus\Utils;

use GuzzleHttp\Exception\GuzzleException;
use PrestaShop\Module\WebpayPlus\Helpers\TbkFactory;
use Transbank\Plugin\Exceptions\EcommerceException;
use Transbank\Webpay\Options;
use Transbank\Webpay\WebpayPlus\Responses\TransactionCommitResponse;
use Transbank\Webpay\WebpayPlus\Transaction;
use Transbank\Webpay\WebpayPlus\Exceptions\TransactionCommitException;
use Transbank\Webpay\WebpayPlus\Exceptions\TransactionCreateException;
//mall
use Transbank\Webpay\WebpayPlus\MallTransaction;
use Transbank\Webpay\WebpayPlus\Responses\MallTransactionCommitResponse;
use Transbank\Webpay\WebpayPlus\Exceptions\MallTransactionCommitException;
use Transbank\Webpay\WebpayPlus\Exceptions\MallTransactionCreateException;
use Transbank\Webpay\WebpayPlus\Responses\MallTransactionRefundResponse;
use Transbank\Webpay\WebpayPlus\Exceptions\WebpayRequestException;
/**
 * Class TransbankSdkWebpayRest.
 */
class TransbankSdkWebpay
{
    /**
     * @var Options
     * @var MallOptions
     */
    public $options;
    public $malloptions;
    protected $log;

    protected $transaction = null;
    protected $mallTransaction = null;

    /**
     * TransbankSdkWebpayRest constructor.
     *
     * @param $config
     */
    public function __construct($config)
    {
        $this->log = TbkFactory::createLogger();
        $this->options = Transaction::getDefaultOptions();
        $this->malloptions = MallTransaction::getDefaultOptions();

        $environment = isset($config['ENVIRONMENT']) ? $config['ENVIRONMENT'] : null;

        if (isset($config) && $environment == Options::ENVIRONMENT_PRODUCTION) {
            $this->options = Options::forProduction($config['COMMERCE_CODE'], $config['API_KEY_SECRET']);
            $this->malloptions = Options::forProduction($config['COMMERCE_CODE'], $config['API_KEY_SECRET']);
        }
        
        $this->mallTransaction = new MallTransaction($this->malloptions);

        $this->transaction = new Transaction($this->options);
    }

    public function getCommerceCode()
    {
        return $this->options->getCommerceCode();
    }

    public function getEnviroment()
    {
        return $this->options->getIntegrationType();
    }

    /**
     * @param $amount
     * @param $sessionId
     * @param $buyOrder
     * @param $returnUrl
     *
     * @throws EcommerceException
     *
     * @return array
     */
    public function createTransaction($amount, $sessionId, $buyOrder, $returnUrl)
    {
        $result = [];

        try {
            $txDate = date('d-m-Y');
            $txTime = date('H:i:s');
            $this->log->logInfo('createTransaction : amount: ' . $amount . ', sessionId: ' .
                $sessionId . ', buyOrder: ' . $buyOrder . ', txDate: ' . $txDate . ', txTime: ' . $txTime);
                
            $initResult = $this->transaction->create($buyOrder, $sessionId, $amount, $returnUrl);


            $this->log->logInfo('createTransaction.result: ' . json_encode($initResult));
            
            if (isset($initResult) && isset($initResult->url) && isset($initResult->token)) {
                $result = [
                    'url' => $initResult->url,
                    'token_ws' => $initResult->token,
                ];
            } else {
                $errorMessage = "Error creando la transacción para => buyOrder: {$buyOrder}, amount: {$amount}";
                throw new EcommerceException($errorMessage);
            }
        } catch (TransactionCreateException $e) {
            $errorMessage = "Error creando la transacción para =>
                buyOrder: {$buyOrder}, amount: {$amount}, error: {$e->getMessage()}";
            throw new EcommerceException($errorMessage, $e);
        }

        return $result;
    }

    public function createMallTransaction($details, $sessionId, $buyOrder, $returnUrl)
    {
        $result = [];

        try {
            $txDate = date('d-m-Y');
            $txTime = date('H:i:s');

            $this->log->logInfo('createMallTransaction : details: ' . json_encode($details) . ', sessionId: ' .
                $sessionId . ', buyOrder: ' . $buyOrder . ', txDate: ' . $txDate . ', txTime: ' . $txTime);
                

            $initResult = $this->mallTransaction->create($buyOrder, $sessionId, $returnUrl, $details);

            $this->log->logInfo('createTransactionMall.result: ' . json_encode($initResult));
            
            if (isset($initResult) && isset($initResult->url) && isset($initResult->token)) {
                $result = [
                    'url' => $initResult->url,
                    'token_ws' => $initResult->token,
                ];
            } else {
                $errorMessage = "Error creando la transacción mall para => buyOrder: {$buyOrder}, details: {$details}";
                throw new EcommerceException($errorMessage);
            }
        } catch (MallTransactionCreateException $e) {
            $errorMessage = "Error creando la transacción Mall para =>
                buyOrder: {$buyOrder}, details: {$details}, error: {$e->getMessage()}";
            throw new EcommerceException($errorMessage, $e);
        }

        return $result;
    }



    /**
     * @param $token
     *
     * @throws \Transbank\Plugin\Exceptions\EcommerceException
     *
     * @return \Transbank\Webpay\WebpayPlus\Responses\TransactionCommitResponse
     */
    public function commitTransaction(string $token): TransactionCommitResponse
    {
        try {
            $this->log->logInfo("commitTransaction : token: {$token}");
            if (!isset($token)) {
                throw new EcommerceException('El token webpay es requerido');
            }

            return $this->transaction->commit($token);
        } catch (TransactionCommitException | \InvalidArgumentException | GuzzleException $e) {
            $errorMessage = "Error confirmando la transacción para el token: {$token}, error: {$e->getMessage()}";
            throw new EcommerceException($errorMessage, $e);
        }
    }


    public function commitMallTransaction(string $token): MallTransactionCommitResponse
    {
        try {
            $this->log->logInfo("commit MallTransaction : token: {$token}");
            if (!isset($token)) {
                throw new EcommerceException('El token webpay es requerido');
            }

            return $this->mallTransaction->commit($token);
        } catch (MallTransactionCommitException | \InvalidArgumentException | GuzzleException $e) {
            $errorMessage = "Error confirmando la transacción Mall para el token: {$token}, error: {$e->getMessage()}";
            throw new EcommerceException($errorMessage, $e);
        }
    }
    
    public function refundMallTransaction(string $token, string $buyOrder, string $commerceCode, int $amount): MallTransactionRefundResponse
    {
        try {
            $this->log->logInfo("Ejecutando refundMallTransaction: token: {$token}, buyOrder: {$buyOrder}, commerceCode: {$commerceCode}, amount: {$amount}");

            $response = $this->mallTransaction->refund($token, $buyOrder, $commerceCode, $amount);

            $this->log->logInfo("Resultado refundMallTransaction: " . json_encode($response));

            return $response;
        } catch (WebpayRequestException $e) {
            $this->log->logError("Error en refundMallTransaction: " . $e->getMessage());
            throw new EcommerceException("Error ejecutando refund para subtransacción: {$buyOrder}", $e);
        }
    }


}
