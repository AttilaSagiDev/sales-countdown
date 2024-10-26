<?php
/**
 * Copyright (c) 2024 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\SalesCountdown\Model\Rule\Condition;

use Magento\Rule\Model\Condition\Product\AbstractProduct;
use Magento\Catalog\Model\Product as CatalogProduct;
use Magento\Framework\Model\AbstractModel;
use Magento\Store\Model\Store;

/**
 * @method string getAttribute() Returns attribute code
 */
class Product extends AbstractProduct
{
    /**
     * Validate product attribute value for condition
     *
     * @param CatalogProduct|AbstractModel $model
     * @return bool
     */
    public function validate(AbstractModel $model): bool
    {
        $attrCode = $this->getAttribute();
        if ('category_ids' == $attrCode) {
            return parent::validate($model);
        }

        $oldAttrValue = $model->getData($attrCode);
        if ($oldAttrValue === null) {
            if ($this->getOperator() === '<=>') {
                return true;
            }
            return false;
        }

        $this->_setAttributeValue($model);

        $result = $this->validateAttribute($model->getData($attrCode));
        $this->_restoreOldAttrValue($model, $oldAttrValue);

        return (bool)$result;
    }

    /**
     * Restore old attribute value
     *
     * @param AbstractModel $model
     * @param mixed $oldAttrValue
     * @return void
     */
    protected function _restoreOldAttrValue(AbstractModel $model, $oldAttrValue): void
    {
        $attrCode = $this->getAttribute();
        if ($oldAttrValue === null) {
            $model->unsetData($attrCode);
        } else {
            $model->setData($attrCode, $oldAttrValue);
        }
    }

    /**
     * Set attribute value
     *
     * @param CatalogProduct|AbstractModel $model
     * @return $this
     */
    protected function _setAttributeValue(AbstractModel $model): static
    {
        $storeId = $model->getStoreId();
        $defaultStoreId = Store::DEFAULT_STORE_ID;

        if (!isset($this->_entityAttributeValues[$model->getId()])) {
            return $this;
        }

        $productValues  = $this->_entityAttributeValues[$model->getId()];

        if (!isset($productValues[$storeId]) && !isset($productValues[$defaultStoreId])) {
            return $this;
        }

        $value = isset($productValues[$storeId]) ? $productValues[$storeId] : $productValues[$defaultStoreId];

        $value = $this->_prepareDatetimeValue($value, $model);
        $value = $this->_prepareMultiselectValue($value, $model);

        $model->setData($this->getAttribute(), $value);

        return $this;
    }

    /**
     * Prepare datetime attribute value
     *
     * @param mixed $value
     * @param CatalogProduct|AbstractModel $model
     * @return mixed
     */
    protected function _prepareDatetimeValue($value, AbstractModel $model): mixed
    {
        $attribute = $model->getResource()->getAttribute($this->getAttribute());
        if ($attribute && $attribute->getBackendType() == 'datetime') {
            if (!$value) {
                return null;
            }
            $this->setValue(strtotime($this->getValue()));
            $value = strtotime($value);
        }

        return $value;
    }

    /**
     * Prepare multiselect attribute value
     *
     * @param mixed $value
     * @param CatalogProduct|AbstractModel $model
     * @return mixed
     */
    protected function _prepareMultiselectValue($value, AbstractModel $model): mixed
    {
        $attribute = $model->getResource()->getAttribute($this->getAttribute());
        if ($attribute && $attribute->getFrontendInput() == 'multiselect') {
            $value = strlen($value) ? explode(',', $value) : [];
        }

        return $value;
    }
}
