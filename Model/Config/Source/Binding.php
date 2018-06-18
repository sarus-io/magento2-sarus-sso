<?php
/**
 * Copyright © Sarus, LLC. All rights reserved.
 */

namespace Sarus\SsoIdp\Model\Config\Source;

use LightSaml\SamlConstants;

class Binding implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => SamlConstants::BINDING_SAML2_HTTP_REDIRECT, 'label' => __('Redirect')],
            ['value' => SamlConstants::BINDING_SAML2_HTTP_POST, 'label' => __('Post')],
        ];
    }
}
