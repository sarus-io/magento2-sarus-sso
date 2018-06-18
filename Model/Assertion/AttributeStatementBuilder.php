<?php
/**
 * Copyright © Sarus, LLC. All rights reserved.
 */

namespace Sarus\SsoIdp\Model\Assertion;

class AttributeStatementBuilder
{
    const ATTR_EMAIL = 'email';
    const ATTR_FIRST_NAME = 'first_name';
    const ATTR_MIDDLE_NAME = 'middle_name';
    const ATTR_LAST_NAME = 'last_name';
    const ATTR_ADDRESS1 = 'address1';
    const ATTR_ADDRESS2 = 'address2';
    const ATTR_CITY_LOCALITY = 'city_locality';
    const ATTR_STATE_REGION = 'state_region';
    const ATTR_POSTAL_CODE = 'postal_code';
    const ATTR_COUNTRY = 'country';

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var array
     */
    private $customerAttributes = [
        self::ATTR_EMAIL => 'email',
        self::ATTR_FIRST_NAME => 'firstname',
        self::ATTR_MIDDLE_NAME => 'middlename',
        self::ATTR_LAST_NAME => 'lastname',
    ];

    /**
     * @var array
     */
    private $addressAttributes = [
        self::ATTR_ADDRESS1 => 'street1',
        self::ATTR_ADDRESS2 => 'street2',
        self::ATTR_CITY_LOCALITY => 'city',
        self::ATTR_STATE_REGION => 'region',
        self::ATTR_POSTAL_CODE => 'postcode',
        self::ATTR_COUNTRY => 'country_id',
    ];

    /**
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->customerSession = $customerSession;
    }

    /**
     * @return \LightSaml\Model\Assertion\AttributeStatement
     */
    public function build()
    {
        $attributeData = $this->fetchCustomerData();

        $attributeStatement = new \LightSaml\Model\Assertion\AttributeStatement();
        foreach ($attributeData as $name => $value) {
            $attributeStatement->addAttribute($this->buildAttribute($name, $value));
        }
        return $attributeStatement;
    }

    /**
     * @param string $name
     * @param string $value
     * @return \LightSaml\Model\Assertion\Attribute
     */
    private function buildAttribute($name, $value)
    {
        $attribute = new \LightSaml\Model\Assertion\Attribute();
        $attribute->setName($name);
        $attribute->addAttributeValue($value);
        $attribute->setNameFormat('urn:oasis:names:tc:SAML:2.0:attrname-format:basic');
        return $attribute;
    }

    /**
     * @return array
     */
    private function fetchCustomerData()
    {
        $attributeData = [];

        $customer = $this->customerSession->getCustomer();
        foreach ($this->customerAttributes as $remoteAttr => $localAttr) {
            $attributeData[$remoteAttr] = $customer->getDataUsingMethod($localAttr);
        }

        $billingAddress = $customer->getPrimaryBillingAddress();
        if ($billingAddress) {
            foreach ($this->addressAttributes as $remoteAttr => $localAttr) {
                $attributeData[$remoteAttr] = $billingAddress->getDataUsingMethod($localAttr);
            }
        }

        return $attributeData;
    }
}
