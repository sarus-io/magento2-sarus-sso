<?php
/**
 * Copyright © Sarus, LLC. All rights reserved.
 */

namespace Sarus\SsoIdp\Model\Config\Source;

use RobRichards\XMLSecLibs\XMLSecurityDSig;

class DigestAlgorithm implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => XMLSecurityDSig::SHA1, 'label' => XMLSecurityDSig::SHA1],
            ['value' => XMLSecurityDSig::SHA256, 'label' => XMLSecurityDSig::SHA256],
            ['value' => XMLSecurityDSig::SHA384, 'label' => XMLSecurityDSig::SHA384],
            ['value' => XMLSecurityDSig::SHA512, 'label' => XMLSecurityDSig::SHA512],
            ['value' => XMLSecurityDSig::RIPEMD160, 'label' => XMLSecurityDSig::RIPEMD160],
        ];
    }
}
