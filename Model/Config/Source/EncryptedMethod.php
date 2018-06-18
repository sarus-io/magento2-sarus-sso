<?php
/**
 * Copyright © Sarus, LLC. All rights reserved.
 */

namespace Sarus\SsoIdp\Model\Config\Source;

use RobRichards\XMLSecLibs\XMLSecurityKey;

class EncryptedMethod implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => XMLSecurityKey::TRIPLEDES_CBC, 'label' => XMLSecurityKey::TRIPLEDES_CBC],
            ['value' => XMLSecurityKey::AES128_CBC, 'label' => XMLSecurityKey::AES128_CBC],
            ['value' => XMLSecurityKey::AES192_CBC, 'label' => XMLSecurityKey::AES192_CBC],
            ['value' => XMLSecurityKey::AES256_CBC, 'label' => XMLSecurityKey::AES256_CBC],
            ['value' => XMLSecurityKey::RSA_1_5, 'label' => XMLSecurityKey::RSA_1_5],
            ['value' => XMLSecurityKey::RSA_SHA1, 'label' => XMLSecurityKey::RSA_SHA1],
            ['value' => XMLSecurityKey::RSA_SHA256, 'label' => XMLSecurityKey::RSA_SHA256],
            ['value' => XMLSecurityKey::RSA_SHA384, 'label' => XMLSecurityKey::RSA_SHA384],
            ['value' => XMLSecurityKey::RSA_SHA512, 'label' => XMLSecurityKey::RSA_SHA512],
            ['value' => XMLSecurityKey::RSA_OAEP_MGF1P, 'label' => XMLSecurityKey::RSA_OAEP_MGF1P],
        ];
    }
}
