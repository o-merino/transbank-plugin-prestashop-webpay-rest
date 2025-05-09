<?php

declare(strict_types=1);

namespace PrestaShop\Module\WebpayPlus\Form;

use PrestaShop\PrestaShop\Core\Configuration\DataConfigurationInterface;
use PrestaShop\PrestaShop\Core\ConfigurationInterface;

final class WebpayPlusDataConfiguration implements DataConfigurationInterface
{
    public const WEBPAY_STOREID = 'WEBPAY_STOREID';
    public const WEBPAY_API_KEY_SECRET = 'WEBPAY_API_KEY_SECRET';
    public const WEBPAY_ENVIRONMENT = 'WEBPAY_ENVIRONMENT';
    public const WEBPAY_DEFAULT_ORDER_STATE_ID_AFTER_PAYMENT = 'WEBPAY_DEFAULT_ORDER_STATE_ID_AFTER_PAYMENT';
    public const WEBPAY_ACTIVE = 'WEBPAY_ACTIVE';

    /**
     * @var ConfigurationInterface
     */
    private $configuration;

    /**
     * @param ConfigurationInterface $configuration
     */
    public function __construct(ConfigurationInterface $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfiguration(): array
    {
        $webpayActive = $this->configuration->get(static::WEBPAY_ACTIVE);
        $webpayEnviroment = $this->configuration->get(static::WEBPAY_ENVIRONMENT);
        $webpayCommerceCode = $this->configuration->get(static::WEBPAY_STOREID);
        $webpayApikey = $this->configuration->get(static::WEBPAY_API_KEY_SECRET);
        $webpayDefaultOrderStateIdAfterPayment = $this->configuration->get(static::WEBPAY_DEFAULT_ORDER_STATE_ID_AFTER_PAYMENT);
        return [
            'form_webpay_active' => $webpayActive,
            'form_webpay_environment' => $webpayEnviroment,
            'form_webpay_commerce_code' => $webpayCommerceCode,
            'form_webpay_api_key' => $webpayApikey,
            'form_webpay_order_after_payment' => $webpayDefaultOrderStateIdAfterPayment,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function updateConfiguration(array $configuration): array
    {
        $this->configuration->set(static::WEBPAY_ACTIVE, $configuration['form_webpay_active']);
        $this->configuration->set(static::WEBPAY_STOREID, $configuration['form_webpay_commerce_code']);

        if (!empty($configuration['form_webpay_api_key'])) {
            $this->configuration->set(static::WEBPAY_API_KEY_SECRET, $configuration['form_webpay_api_key']);
        }

        $this->configuration->set(static::WEBPAY_ENVIRONMENT, $configuration['form_webpay_environment']);
        $this->configuration->set(static::WEBPAY_DEFAULT_ORDER_STATE_ID_AFTER_PAYMENT, $configuration['form_webpay_order_after_payment']);

        return [];
    }

    /**
     * Ensure the parameters passed are valid.
     * This function can be used to validate updateConfiguration(array $configuration) data input.
     *
     * @param array $configuration
     *
     * @return bool Returns true if no exception are thrown
     */
    public function validateConfiguration(array $configuration): bool
    {
        return true;
    }
}
