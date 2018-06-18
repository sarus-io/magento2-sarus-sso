<?php
/**
 * Copyright © Sarus, LLC. All rights reserved.
 */

namespace Sarus\SsoIdp\Model\Config\Source;

use RobRichards\XMLSecLibs\XMLSecurityKey;

class SignatureAlgorithm implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => XMLSecurityKey::RSA_SHA1, 'label' => XMLSecurityKey::RSA_SHA1],
            ['value' => XMLSecurityKey::DSA_SHA1, 'label' => XMLSecurityKey::DSA_SHA1],
            ['value' => XMLSecurityKey::RSA_SHA256, 'label' => XMLSecurityKey::RSA_SHA256],
            ['value' => XMLSecurityKey::RSA_SHA384, 'label' => XMLSecurityKey::RSA_SHA384],
            ['value' => XMLSecurityKey::RSA_SHA512, 'label' => XMLSecurityKey::RSA_SHA512],
        ];
    }
}

