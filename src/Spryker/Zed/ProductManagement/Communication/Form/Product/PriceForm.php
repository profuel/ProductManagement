<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductManagement\Communication\Form\Product;

use Spryker\Zed\Gui\Communication\Form\Type\Select2ComboBoxType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class PriceForm extends AbstractType
{

    const FIELD_PRICE = 'price';
    const FIELD_TAX_RATE = 'tax_rate';
    const FIELD_STOCK = 'stock';

    /**
     * @var string
     */
    protected $validationGroup;

    /**
     * @var array
     */
    protected $taxCollection;

    /**
     * @param array $taxCollection
     * @param string $validationGroup
     */
    public function __construct(array $taxCollection, $validationGroup)
    {
        $this->taxCollection = $taxCollection;
        $this->validationGroup = $validationGroup;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'PriceForm';
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolverInterface $resolver
     *
     * @return void
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);

        $resolver->setDefaults([
            'required' => false,
            'cascade_validation' => true,
        ]);
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     *
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this
            ->addPriceField($builder, $options)
            ->addTaxRateField($builder, $options)
            ->addStockField($builder, $options);
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     *
     * @return $this
     */
    protected function addPriceField(FormBuilderInterface $builder, array $options)
    {
        $builder->add(self::FIELD_PRICE, 'text', [
            'label' => 'Price',
            'required' => true,
            'constraints' => [
                new NotBlank(),
            ],
        ]);

        return $this;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     *
     * @return $this
     */
    protected function addTaxRateField(FormBuilderInterface $builder, array $options)
    {
        $builder->add(self::FIELD_TAX_RATE, new Select2ComboBoxType(), [
            'label' => 'Tax Set',
            'required' => true,
            'choices' => $this->taxCollection,
            'placeholder' => '-',
            'constraints' => [
                new NotBlank(),
            ],
        ]);

        return $this;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     *
     * @return $this
     */
    protected function addStockField(FormBuilderInterface $builder, array $options)
    {
        $builder->add(self::FIELD_STOCK, 'text', [
            'label' => 'Stock',
            'required' => true,
            'constraints' => [
                new NotBlank(),
            ],
        ]);

        return $this;
    }

}