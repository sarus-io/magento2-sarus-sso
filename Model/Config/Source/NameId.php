<?php
/**
 * Copyright © Sarus, LLC. All rights reserved.
 */

namespace Sarus\SsoIdp\Model\Config\Source;

class NameId implements \Magento\Framework\Data\OptionSourceInterface
{
    const EMAIL = 'email';
    const CUSTOMER_ID = 'id';

    const FORMAT_EMAIL = 'urn:oasis:names:tc:SAML:1.1:nameid-format:emailAddress';
    const FORMAT_ENTITY = 'urn:oasis:names:tc:SAML:2.0:nameid-format:entity';

    /**
     * @var array
     */
    private $formats = [
        self::EMAIL => self::FORMAT_EMAIL,
        self::CUSTOMER_ID => self::FORMAT_ENTITY
    ];

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::EMAIL, 'label' => __('Customer Email')],
            ['value' => self::CUSTOMER_ID, 'label' => __('Customer ID')],
        ];
    }

    /**
     * @param string $typeName
     * @return string|null
     */
    public function getFormat($typeName)
    {
        return isset($this->formats[$typeName]) ? $this->formats[$typeName] : null;
    }
}
