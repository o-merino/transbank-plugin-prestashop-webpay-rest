<?php

namespace PrestaShop\Module\WebpayPlus\Grid;

use PrestaShop\PrestaShop\Core\Grid\Definition\Factory\AbstractFilterableGridDefinitionFactory;
use PrestaShop\PrestaShop\Core\Grid\Column\ColumnCollection;
use PrestaShop\PrestaShop\Core\Grid\Column\Type\DataColumn;
use PrestaShop\PrestaShop\Core\Grid\Column\Type\Common\DateTimeColumn;
use PrestaShop\PrestaShop\Core\Grid\Filter\Filter;
use PrestaShop\PrestaShop\Core\Grid\Filter\FilterCollection;
use PrestaShop\PrestaShop\Core\Grid\Filter\FilterCollectionInterface;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use PrestaShopBundle\Form\Admin\Type\DateRangeType;
use PrestaShopBundle\Form\Admin\Type\SearchAndResetType;
use PrestaShop\PrestaShop\Core\Grid\Column\Type\Common\ActionColumn;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use PrestaShop\PrestaShop\Core\Grid\Column\Type\ColorColumn;
use PrestaShop\Module\WebpayPlus\Grid\TransactionsStatusChoiceProvider;
use PrestaShop\PrestaShop\Core\Form\FormChoiceProviderInterface;


final class TransactionsGridDefinitionFactory extends AbstractFilterableGridDefinitionFactory
{
    const GRID_ID = 'webpay_transactions';

    /**
     * @var FormChoiceProviderInterface
     */

    protected function getId()
    {
        return self::GRID_ID;
    }

    protected function getName()
    {
        return 'Transacciones';
    }

    protected function getColumns()
    {
        return (new ColumnCollection())
            ->add((new DataColumn('cart_id'))
                    ->setName('Id del carrito')
                    ->setOptions([
                        'field' => 'cart_id',
                    ])
            )
            ->add((new DataColumn('order_id'))
                    ->setName('Id del pedido')
                    ->setOptions([
                        'field' => 'order_id',
                    ])
            )
            ->add((new DataColumn('response_code'))
                    ->setName('Código respuesta')
                    ->setOptions([
                        'field' => 'response_code',
                    ])
            )
            ->add((new DataColumn('vci'))
                    ->setName('VCI')
                    ->setOptions([
                        'field' => 'vci',
                    ])
            )
            ->add((new DataColumn('amount'))
                    ->setName('Monto')
                    ->setOptions([
                        'field' => 'amount',
                    ])
            )
            ->add((new DataColumn('iso_code'))
                    ->setName('Moneda')
                    ->setOptions([
                        'field' => 'iso_code',
                    ])
            )->add((new DataColumn('card_number'))
                    ->setName('Nº. Tarjeta.')
                    ->setOptions([
                        'field' => 'card_number',
                    ])
            )->add((new DataColumn('token'))
                    ->setName('Token')
                    ->setOptions([
                        'field' => 'token',
                    ])
            )
            ->add((new ColorColumn('status'))
                ->setName($this->trans('Status', [], 'Admin.Global'))
                ->setOptions([
                    'field' => 'status',
                    'color_field' => 'status_color',
                ]))
            ->add((new DataColumn('environment'))
                ->setName('Ambiente')
                ->setOptions(['field' => 'environment']))
            ->add((new DataColumn('product'))
                ->setName('Producto')
                ->setOptions(['field' => 'product']))
            ->add((new DateTimeColumn('created_at'))
                    ->setName('Fecha de Trans.')
                    ->setOptions([
                        'field' => 'created_at',
                    ])
            )->add((new ActionColumn('actions'))
                ->setName('Acciones'));
    }

    protected function getFilters(): FilterCollectionInterface
    {
        return (new FilterCollection())->add(
            (new Filter('cart_id', TextType::class))
                ->setAssociatedColumn('cart_id')
                ->setTypeOptions([
                    'required' => false
                ])
        )->add(
            (new Filter('order_id', TextType::class))
                ->setAssociatedColumn('order_id')
                ->setTypeOptions([
                    'required' => false
                ])
        )->add(
            (new Filter('response_code', TextType::class))
                ->setAssociatedColumn('response_code')
                ->setTypeOptions([
                    'required' => false
                ])
        )->add(
            (new Filter('vci', TextType::class))
                ->setAssociatedColumn('vci')
                ->setTypeOptions([
                    'required' => false
                ])
        )->add(
            (new Filter('amount', NumberType::class))
                ->setAssociatedColumn('amount')
                ->setTypeOptions([
                    'required' => false
                ])
        )
            ->add(
                (new Filter('iso_code', TextType::class))
                    ->setAssociatedColumn('iso_code')
                    ->setTypeOptions([
                        'required' => false
                    ])
            )
            ->add(
                (new Filter('card_number', TextType::class))
                    ->setAssociatedColumn('card_number')
                    ->setTypeOptions([
                        'required' => false
                    ])
            )->add(
                (new Filter('token', TextType::class))
                    ->setAssociatedColumn('token')
                    ->setTypeOptions([
                        'required' => false
                    ])
            )->add(
                (new Filter('status', ChoiceType::class))
                    ->setAssociatedColumn('status')
                    ->setTypeOptions([
                        'choices' => (new TransactionsStatusChoiceProvider())->getChoices(),
                        'required' => false
                    ])
            )->add(
                (new Filter('product', TextType::class))
                    ->setAssociatedColumn('product')
                    ->setTypeOptions([
                        'required' => false
                    ])
            )->add(
                (new Filter('environment', TextType::class))
                    ->setAssociatedColumn('environment')
                    ->setTypeOptions([
                        'required' => false
                    ])
            )->add(
                (new Filter('created_at', DateRangeType::class))
                    ->setAssociatedColumn('created_at')
                    ->setTypeOptions([
                        'required' => false
                    ])
            )->add(
                (new Filter('actions', SearchAndResetType::class))
                    ->setAssociatedColumn('actions')
                    ->setTypeOptions([
                        'reset_route' => 'admin_common_reset_search_by_filter_id',
                        'reset_route_params' => [
                            'filterId' => self::GRID_ID,
                        ],
                        'redirect_route' => 'ps_controller_webpay_transaction_list',
                    ])
            );
    }
}
