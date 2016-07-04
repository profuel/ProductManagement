<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductManagement\Communication\Form\DataProvider;

use Generated\Shared\Transfer\ProductAbstractTransfer;
use Spryker\Zed\Category\Persistence\CategoryQueryContainerInterface;
use Spryker\Zed\ProductManagement\Business\ProductManagementFacadeInterface;
use Spryker\Zed\ProductManagement\Communication\Form\ProductFormAdd;
use Spryker\Zed\ProductManagement\Communication\Form\ProductFormAttributeMetadata;
use Spryker\Zed\ProductManagement\Communication\Form\ProductFormAttributeValues;
use Spryker\Zed\ProductManagement\Communication\Form\ProductFormPrice;
use Spryker\Zed\ProductManagement\Communication\Form\ProductFormSeo;
use Spryker\Zed\ProductManagement\Dependency\Facade\ProductManagementToLocaleInterface;
use Spryker\Zed\ProductManagement\Dependency\Facade\ProductManagementToPriceInterface;
use Spryker\Zed\Product\Business\ProductFacadeInterface;
use Spryker\Zed\Product\Persistence\ProductQueryContainerInterface;

class AbstractProductFormDataProvider
{

    const LOCALE_NAME = 'locale_name';

    /**
     * @var \Spryker\Zed\Category\Persistence\CategoryQueryContainerInterface
     */
    protected $categoryQueryContainer;

    /**
     * @var \Spryker\Zed\Product\Persistence\ProductQueryContainerInterface
     */
    protected $productQueryContainer;

    /**
     * @var \Spryker\Zed\ProductManagement\Dependency\Facade\ProductManagementToLocaleInterface
     */
    protected $localeFacade;

    /**
     * @var \Generated\Shared\Transfer\LocaleTransfer
     */
    protected $locale;

    /**
     * @var \Spryker\Zed\Product\Business\ProductFacadeInterface
     */
    protected $productFacade;

    /**
     * @var \Spryker\Zed\ProductManagement\Dependency\Facade\ProductManagementToPriceInterface
     */
    protected $priceFacade;

    /**
     * @var \Spryker\Zed\ProductManagement\Business\ProductManagementFacadeInterface
     */
    protected $productManagementFacade;

    /**
     * @var \Generated\Shared\Transfer\ProductManagementAttributeMetadataTransfer[]
     */
    protected $attributeMetadataTransferCollection = [];

    /**
     * @var \Generated\Shared\Transfer\ProductManagementAttributeTransfer[]
     */
    protected $attributeTransferCollection = [];

    /**
     * @var array
     */
    protected $taxCollection = [];


    public function __construct(
        CategoryQueryContainerInterface $categoryQueryContainer,
        ProductQueryContainerInterface $productQueryContainer,
        ProductManagementToPriceInterface $priceFacade,
        ProductFacadeInterface $productFacade,
        ProductManagementFacadeInterface $productManagementFacade,
        ProductManagementToLocaleInterface $localeFacade,
        array $attributeMetadataCollection,
        array $attributeCollection,
        array $taxCollection
    ) {
        $this->categoryQueryContainer = $categoryQueryContainer;
        $this->productQueryContainer = $productQueryContainer;
        $this->localeFacade = $localeFacade;
        $this->priceFacade = $priceFacade;
        $this->productFacade = $productFacade;
        $this->productManagementFacade = $productManagementFacade;
        $this->locale = $localeFacade->getCurrentLocale();
        $this->attributeMetadataTransferCollection = $attributeMetadataCollection;
        $this->attributeTransferCollection = $attributeCollection;
        $this->taxCollection = $taxCollection;
    }

    /**
     * @param int|null $idProductAbstract |null
     *
     * @return mixed
     */
    public function getOptions($idProductAbstract = null)
    {
        $attributes = $this->getAttributesForAbstractProduct($idProductAbstract);

        $formOptions[ProductFormAdd::ATTRIBUTE_METADATA] = [
            ProductFormAttributeMetadata::OPTION_LABELS => $this->convertSelectedAttributeMetadataToFormValues($attributes),
            ProductFormAttributeMetadata::OPTION_VALUES => $this->convertSelectedAttributeValuesToFormValues($attributes),
        ];

/*        $formOptions[ProductFormAdd::ATTRIBUTE_VALUES] = [
            ProductFormAttributeValues::OPTION_LABELS => $this->convertAttributeMetadataToOptionValues($attributes),
            ProductFormAttributeValues::OPTION_VALUES => $this->convertSelectedAttributeValuesToFormValues($attributes),
        ];*/

        $formOptions[ProductFormAdd::TAX_SET] = $this->taxCollection;
        $formOptions[ProductFormAdd::ID_LOCALE] = $this->localeFacade->getCurrentLocale()->getIdLocale();

        return $formOptions;
    }

    /**
     * @return array
     */
    protected function getDefaultFormFields()
    {
        return [
            ProductFormAdd::FIELD_SKU => null,
            ProductFormAdd::GENERAL => $this->getLocalizedAttributesDefaultFields(),
            ProductFormAdd::ATTRIBUTE_METADATA => $this->getAttributeMetadataDefaultFields(),
            ProductFormAdd::ATTRIBUTE_VALUES => $this->getAttributeValuesDefaultFields(),
            ProductFormAdd::TAX_SET => $this->getPriceAndStockDefaultFields(),
            ProductFormAdd::SEO => $this->getSeoDefaultFields(),
            ProductFormAdd::PRICE_AND_STOCK => [
                ProductFormPrice::FIELD_PRICE => 0,
                ProductFormPrice::FIELD_TAX_RATE => 0,
                ProductFormPrice::FIELD_STOCK => 0
            ]
        ];
    }

    /**
     * @param int $idProductAbstract
     *
     * @return array
     */
    public function getAttributesForAbstractProduct($idProductAbstract = null)
    {
        if ($idProductAbstract === null) {
            return [];
        }

        return $this->productManagementFacade
            ->getProductAttributesByAbstractProductId($idProductAbstract);
    }

    /**
     * @param \Generated\Shared\Transfer\ProductAbstractTransfer $productAbstractTransfer
     *
     * @return array
     */
    public function getLocalizedAbstractAttributes(ProductAbstractTransfer $productAbstractTransfer)
    {
        $localizedAttributes = [];
        foreach ($productAbstractTransfer->getLocalizedAttributes() as $attribute) {
            $localizedAttributes[$attribute->getLocale()->getLocaleName()] = $attribute->toArray();
        }

        return $localizedAttributes;
    }

    /**
     * @return array
     */
    public function getLocalizedAttributesDefaultFields()
    {
        $availableLocales = $this->localeFacade->getAvailableLocales();

        $fields = [];
        foreach ($availableLocales as $id => $code) {
            $fields[$code] = [
                ProductFormAdd::FIELD_NAME => null,
                ProductFormAdd::FIELD_DESCRIPTION => null,
            ];
        }

        return $fields;
    }

    /**
     * @return array
     */
    public function getSeoDefaultFields()
    {
        $availableLocales = $this->localeFacade->getAvailableLocales();

        $fields = [];
        foreach ($availableLocales as $id => $code) {
            $fields[$code] = [
                ProductFormSeo::FIELD_META_TITLE => null,
                ProductFormSeo::FIELD_META_KEYWORDS => null,
                ProductFormSeo::FIELD_META_DESCRIPTION => null,
            ];
        }

        return $fields;
    }

    /**
     * @return array
     */
    public function getAttributeValuesDefaultFields()
    {
        $attributes = [];
        /* @var ProductManagementAttributeTransfer $attributeTransfer */
        foreach ($this->attributeTransferCollection as $type => $attributeTransfer) {
            $attributes[$type]['value'] = [];
        }

        return $attributes;
    }

    /**
     * @return array
     */
    public function getAttributeMetadataDefaultFields()
    {
        $attributes = [];
        /* @var ProductManagementAttributeTransfer $attributeTransfer */
        foreach ($this->attributeTransferCollection as $type => $attributeTransfer) {
            $attributes[$type]['value'] = false;
        }

        return $attributes;
    }

    /**
     * @return array
     */
    public function getPriceAndStockDefaultFields()
    {
        return $this->convertToFormValues($this->taxCollection);
    }

    /**
     * @param array $data
     * @param array $values
     * @param bool $defaultValue
     *
     * @return array
     */
    protected function convertToFormValues(array $data, array $values = [], $defaultValue = true)
    {
        $attributes = [];
        foreach ($data as $type => $valueSet) {
            $attributes[$type]['value'] = $defaultValue;
            if (isset($values[$type])) {
                $attributes[$type]['value'] = $values[$type];
            }
        }

        return $attributes;
    }

    /**
     * @param array $productAttributes
     *
     * @return array
     */
    protected function convertSelectedAttributeValuesToFormValues(array $productAttributes)
    {
        $values = [];
        foreach ($this->attributeMetadataTransferCollection as $type => $transfer) {
            $isDefined = !array_key_exists($type, $this->attributeTransferCollection);
            $isProductSpecificAttribute = !array_key_exists($type, $productAttributes);
            $isMulti = isset($productAttributes[$type]) && is_array($productAttributes[$type]);
            $value = isset($productAttributes[$type]) ? $productAttributes[$type] : null;

            $values[$type] = [
                'value' => [$value],
                'product_specific' => $isProductSpecificAttribute,
                'custom' => $isDefined,
                'label' => $this->getLocalizedAttributeMetadataKey($type),
                'multi' => $isMulti
            ];

            //append product custom attributes
            foreach ($productAttributes as $key => $value) {
                $isMulti = isset($value) && is_array($value);
                if (!array_key_exists($key, $values)) {
                    $values[$key] = [
                        'value' => [$value],
                        'product_specific' => true,
                        'custom' => true,
                        'label' => $this->getLocalizedAttributeMetadataKey($key),
                        'multi' => $isMulti
                    ];
                }
            }
        }

        return $values;
    }

    /**
     * @param array $productAttributes
     *
     * @return array
     */
    protected function convertSelectedAttributeMetadataToFormValues(array $productAttributes)
    {
        $values = [];
        foreach ($this->attributeMetadataTransferCollection as $type => $transfer) {
            $isDefined = !array_key_exists($type, $this->attributeTransferCollection);
            $isProductSpecificAttribute = !array_key_exists($type, $productAttributes);
            $isMulti = isset($productAttributes[$type]) && is_array($productAttributes[$type]);
            $value = isset($productAttributes[$type]) ? $productAttributes[$type] : false;

            if ($isMulti && $value) {
                $value = !empty($value);
            }

            $values[$type] = [
                'value' => $value,
                'product_specific' => $isProductSpecificAttribute,
                'custom' => $isDefined,
                'label' => $this->getLocalizedAttributeMetadataKey($type),
                'multi' => $isMulti
            ];
        }

        //append product custom attributes
        foreach ($productAttributes as $key => $value) {
            $isMulti = isset($value) && is_array($value);
            if (!array_key_exists($key, $values)) {
                $values[$key] = [
                    'value' => true,
                    'product_specific' => true,
                    'custom' => true,
                    'label' => $this->getLocalizedAttributeMetadataKey($key),
                    'multi' => $isMulti
                ];
            }
        }

        return $values;
    }

    protected function getLocalizedAttributeMetadataKey($keyToLocalize)
    {
        if (!isset($this->attributeMetadataTransferCollection[$keyToLocalize])) {
            return $keyToLocalize;
        }

        if (!isset($this->attributeTransferCollection[$keyToLocalize])) {
            return $keyToLocalize;
        }

        $attributeTransfer = $this->attributeTransferCollection[$keyToLocalize];
        foreach ($attributeTransfer->getLocalizedAttributes() as $localizedAttribute) {
            if ((int)$localizedAttribute->getFkLocale() === (int)$this->localeFacade->getCurrentLocale()->getIdLocale()) {
                return $localizedAttribute->getName();
            }
        }

        return $keyToLocalize;
    }

}
