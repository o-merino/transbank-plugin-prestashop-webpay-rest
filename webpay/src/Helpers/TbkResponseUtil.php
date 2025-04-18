<?php

namespace PrestaShop\Module\WebpayPlus\Helpers;

use Configuration;
use DateTime;
use DateTimeZone;
use Transbank\Plugin\Helpers\TbkConstants;

/**
 * Utility class for handling Transbank responses.
 */
class TbkResponseUtil
{
    /**
     * Get the payment type from its code.
     *
     * @param string $paymentType The code of the payment type.
     * @return string The corresponding payment type.
     */
    public static function getPaymentType(string $paymentType): string
    {
        return TbkConstants::PAYMENT_TYPE[$paymentType] ?? $paymentType;
    }

    /**
     * Get the installment type from the payment type response.
     *
     * @param string $paymentType The code of the installment type.
     * @return string The corresponding installment type.
     */
    public static function getInstallmentType(string $paymentType): string
    {
        return TbkConstants::PAYMENT_TYPE_CODE[$paymentType] ?? $paymentType;
    }

    /**
     * Get the transaction status description from response status.
     *
     * @param string $status The code of the transaction status.
     * @return string The description of the corresponding transaction status.
     */
    public static function getStatus(string $status): string
    {
        return TbkConstants::STATUS_DESCRIPTION[$status] ?? $status;
    }

    /**
     * Get the formatted accounting date from response.
     *
     * @param string $accountingDate The accounting date in 'md' format.
     * @return string The accounting date in 'mm-dd' format.
     */
    public static function getAccountingDate(string $accountingDate): string
    {
        $date = DateTime::createFromFormat('md', $accountingDate);

        if (!$date) {
            return $accountingDate;
        }

        return $date->format('m-d');
    }

    /**
     * Converts a string of transaction UTC date to local date with time difference.
     *
     * @param string $date an date/time string in UTC.
     * @return string the string of local date with time difference.
     */
    public static function transactionDateToLocalDate(string $date): string
    {
        $utcDate = new DateTime($date, new DateTimeZone('UTC'));
        $utcDate->setTimeZone(new DateTimeZone(Configuration::get('PS_TIMEZONE')));
        return $utcDate->format('d-m-Y H:i:s P');
    }

    /**
     * Get the CLP formatted amount from an integer value.
     *
     * @param int $amount The integer amount to be formatted.
     * @return string The formatted amount as a string.
     */
    public static function getAmountFormatted(int $amount): string
    {
        return '$' . number_format($amount, 0, ',', '.');
    }

    /**
     * Get the common fields formatted for sale receipt.
     *
     * @param object $transactionResponse The transaction response.
     * @return array The formatted common fields.
     */
    private static function getCommonFieldsFormatted(object $transactionResponse): array
    {
        $utcDate = new DateTime($transactionResponse->transactionDate, new DateTimeZone('UTC'));
        $utcDate->setTimeZone(new DateTimeZone(Configuration::get('PS_TIMEZONE')));

        $buyOrder = $transactionResponse->buyOrder;
        $cardNumber = "**** **** **** {$transactionResponse->cardNumber}";
        $transactionDate = $utcDate->format('d-m-Y');
        $transactionTime = $utcDate->format('H:i:s');

        return [
            'buyOrder' => $buyOrder,
            'cardNumber' => $cardNumber,
            'transactionDate' => $transactionDate,
            'transactionTime' => $transactionTime
        ];
    }

    /**
     * Get the formatted response for Webpay transactions.
     *
     * @param object $transactionResponse The response object for Webpay transactions.
     * @return array The formatted response fields.
     */
    public static function getWebpayFormattedResponse(object $transactionResponse): array
    {
        $commonFields = self::getCommonFieldsFormatted($transactionResponse);

        $amount = self::getAmountFormatted($transactionResponse->amount);
        $paymentType = self::getPaymentType($transactionResponse->paymentTypeCode);
        $installmentType = self::getInstallmentType($transactionResponse->paymentTypeCode);
        $installmentAmount = self::getAmountFormatted($transactionResponse->installmentsAmount ?? 0);

        $webpayFields = [
            'amount' => $amount,
            'authorizationCode' => $transactionResponse->authorizationCode,
            'paymentType' => $paymentType,
            'installmentType' => $installmentType,
            'installmentNumber' => $transactionResponse->installmentsNumber,
            'installmentAmount' => $installmentAmount
        ];

        return array_merge($commonFields, $webpayFields);
    }

    public static function getWebpayMallFormattedResponse(object $transactionResponse): array
    {
        $commonFields = self::getCommonFieldsFormatted($transactionResponse);

        $details = $transactionResponse->details ?? [];

        $totalAmount = 0;
        $first = $details[0] ?? null;

        foreach ($details as $detail) {
            $totalAmount += $detail->amount ?? 0;
        }

        return array_merge($commonFields, [
            'amount' => self::getAmountFormatted($totalAmount),
            'authorizationCode' => $first->authorizationCode ?? '',
            'paymentType' => self::getPaymentType($first->paymentTypeCode ?? ''),
            'installmentType' => self::getInstallmentType($first->paymentTypeCode ?? ''),
            'installmentNumber' => $first->installmentsNumber ?? 0,
            'installmentAmount' => self::getAmountFormatted($first->installmentsAmount ?? 0),
        ]);
    }


    /**
     * Get the formatted response for Oneclick transactions.
     *
     * @param object $transactionResponse The response object for Oneclick transactions.
     * @return array The formatted response fields.
     */
    public static function getOneclickFormattedResponse(object $transactionResponse): array
    {
        $commonFields = self::getCommonFieldsFormatted($transactionResponse);
        $detail = $transactionResponse->details[0];

        $amount = self::getAmountFormatted($detail->amount);
        $paymentType = self::getPaymentType($detail->paymentTypeCode);
        $installmentType = self::getInstallmentType($detail->paymentTypeCode);
        $installmentAmount = self::getAmountFormatted($detail->installmentsAmount ?? 0);

        $oneclickFields = [
            'amount' => $amount,
            'authorizationCode' => $detail->authorizationCode,
            'paymentType' => $paymentType,
            'installmentType' => $installmentType,
            'installmentNumber' => $detail->installmentsNumber,
            'installmentAmount' => $installmentAmount
        ];

        return array_merge($commonFields, $oneclickFields);
    }

    /**
     * Get the common fields formatted for status response.
     *
     * @param object $statusResponse The status response.
     * @return array The formatted common fields for status response.
     */
    private static function getCommonFieldsStatusFormatted(object $statusResponse): array
    {
        $utcDate = new DateTime($statusResponse->transactionDate, new DateTimeZone('UTC'));
        $utcDate->setTimeZone(new DateTimeZone(Configuration::get('PS_TIMEZONE')));

        $buyOrder = $statusResponse->buyOrder;
        $cardNumber = "**** **** **** {$statusResponse->cardNumber}";
        $transactionDate = $utcDate->format('d-m-Y');
        $transactionTime = $utcDate->format('H:i:s');
        $accountingDate = self::getAccountingDate($statusResponse->accountingDate);

        return [
            'buyOrder' => $buyOrder,
            'cardNumber' => $cardNumber,
            'transactionDate' => $transactionDate,
            'transactionTime' => $transactionTime,
            'accountingDate' => $accountingDate
        ];
    }

    /**
     * Get the formatted response for Webpay status transactions.
     *
     * @param object $statusResponse The response object for Webpay status transactions.
     * @return array The formatted response fields.
     */
    public static function getWebpayStatusFormattedResponse(object $statusResponse): array
    {
        $commonFields = self::getCommonFieldsStatusFormatted($statusResponse);

        $status = self::getStatus($statusResponse->status);
        $amount = self::getAmountFormatted($statusResponse->amount);
        $paymentType = self::getPaymentType($statusResponse->paymentTypeCode);
        $installmentType = self::getInstallmentType($statusResponse->paymentTypeCode);
        $installmentNumber = $statusResponse->installmentsNumber;
        $installmentAmount = 'N/A';
        $balance = 'N/A';

        if ($installmentNumber > 0) {
            $installmentAmount = self::getAmountFormatted($statusResponse->installmentsAmount ?? 0);
        }

        if (isset($statusResponse->balance)) {
            $balance = self::getAmountFormatted($statusResponse->balance);
        }

        return [
            'vci' => $statusResponse->vci,
            'status' => $status,
            'responseCode' => $statusResponse->responseCode,
            'amount' => $amount,
            'authorizationCode' => $statusResponse->authorizationCode,
            'accountingDate' => $commonFields['accountingDate'],
            'paymentType' => $paymentType,
            'installmentType' => $installmentType,
            'installmentNumber' => $installmentNumber,
            'installmentAmount' => $installmentAmount,
            'sessionId' => $statusResponse->sessionId,
            'buyOrder' => $commonFields['buyOrder'],
            'cardNumber' => $commonFields['cardNumber'],
            'transactionDate' => $commonFields['transactionDate'],
            'transactionTime' => $commonFields['transactionTime'],
            'balance' => $balance
        ];
    }
    /**
     * Get the formatted response for Webpay status transactions.
     *
     * @param object $statusResponse The response object for Webpay status transactions.
     * @return array The formatted response fields.
     */
    public static function getWebpayMallStatusFormattedResponse(object $statusResponse): array
    {
        $commonFields = self::getCommonFieldsStatusFormatted($statusResponse);

        // Usamos la primera subtransacción como base
        $firstDetail = $statusResponse->details[0] ?? null;

        if (!$firstDetail) {
            return [
                'status' => 'No se encontraron subtransacciones',
                'amount' => '0',
            ];
        }

        // Sumar monto total
        $totalAmount = 0;
        foreach ($statusResponse->details as $detail) {
            $totalAmount += $detail->amount ?? 0;
        }

        $status = self::getStatus($firstDetail->status ?? 'desconocido');
        $amount = self::getAmountFormatted($totalAmount);
        $paymentType = self::getPaymentType($firstDetail->paymentTypeCode ?? '');
        $installmentType = self::getInstallmentType($firstDetail->paymentTypeCode ?? '');
        $installmentNumber = $firstDetail->installmentsNumber ?? 0;
        $installmentAmount = 'N/A';
        $balance = 'N/A';

        if ($installmentNumber > 0) {
            $installmentAmount = self::getAmountFormatted($firstDetail->installmentsAmount ?? 0);
        }

        return [
            'vci' => $statusResponse->vci ?? 'N/A',
            'status' => $status,
            'responseCode' => $firstDetail->responseCode ?? 'N/A',
            'amount' => $amount,
            'authorizationCode' => $firstDetail->authorizationCode ?? '',
            'accountingDate' => $commonFields['accountingDate'],
            'paymentType' => $paymentType,
            'installmentType' => $installmentType,
            'installmentNumber' => $installmentNumber,
            'installmentAmount' => $installmentAmount,
            'sessionId' => $statusResponse->sessionId ?? '',
            'buyOrder' => $commonFields['buyOrder'],
            'cardNumber' => $commonFields['cardNumber'],
            'transactionDate' => $commonFields['transactionDate'],
            'transactionTime' => $commonFields['transactionTime'],
            'balance' => $balance
        ];
    }

    /**
     * Get the formatted response for Oneclick status transactions.
     *
     * @param object $statusResponse The response object for Oneclick status transactions.
     * @return array The formatted response fields.
     */
    public static function getOneclickStatusFormattedResponse(object $statusResponse): array
    {
        $commonFields = self::getCommonFieldsStatusFormatted($statusResponse);
        $detail = $statusResponse->details[0];

        $status = self::getStatus($detail->status);
        $amount = self::getAmountFormatted($detail->amount);
        $paymentType = self::getPaymentType($detail->paymentTypeCode);
        $installmentType = self::getInstallmentType($detail->paymentTypeCode);
        $installmentNumber = $detail->installmentsNumber;
        $installmentAmount = 'N/A';
        $balance = 'N/A';

        if ($installmentNumber > 0) {
            $installmentAmount = self::getAmountFormatted($detail->installmentsAmount ?? 0);
        }

        if (isset($detail->balance)) {
            $balance = self::getAmountFormatted($detail->balance);
        }

        return [
            'status' => $status,
            'responseCode' => $detail->responseCode,
            'amount' => $amount,
            'authorizationCode' => $detail->authorizationCode,
            'accountingDate' => $commonFields['accountingDate'],
            'paymentType' => $paymentType,
            'installmentType' => $installmentType,
            'installmentNumber' => $installmentNumber,
            'installmentAmount' => $installmentAmount,
            'buyOrderMall' => $commonFields['buyOrder'],
            'buyOrderStore' => $detail->buyOrder,
            'cardNumber' => $commonFields['cardNumber'],
            'transactionDate' => $commonFields['transactionDate'],
            'transactionTime' => $commonFields['transactionTime'],
            'balance' => $balance
        ];
    }
}
