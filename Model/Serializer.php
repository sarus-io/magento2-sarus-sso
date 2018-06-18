<?php
/**
 * Copyright © Sarus, LLC. All rights reserved.
 */

namespace Sarus\SsoIdp\Model;

use \LightSaml\Model\SamlElementInterface;

class Serializer
{
    /**
     * @param \LightSaml\Model\SamlElementInterface $samlElement
     * @return string
     */
    public function toXml(SamlElementInterface $samlElement)
    {
        $serializationContext = new \LightSaml\Model\Context\SerializationContext();
        $samlElement->serialize($serializationContext->getDocument(), $serializationContext);
        return $serializationContext->getDocument()->saveXML();
    }
}
