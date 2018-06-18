<?php
/**
 * Copyright © Sarus, LLC. All rights reserved.
 */

namespace Sarus\SsoIdp\Model;

use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use LightSaml\Error\LightSamlException;

class MessageTransporter
{
    /**
     * @param \LightSaml\Model\Protocol\SamlMessage $message
     * @param string $bindingType
     * @return void
     */
    public function send($message, $bindingType)
    {
        $messageContext = new \LightSaml\Context\Profile\MessageContext();
        $messageContext->setMessage($message);

        $bindingFactory = new \LightSaml\Binding\BindingFactory();
        $binding = $bindingFactory->create($bindingType);

        $httpResponse = $binding->send($messageContext);
        $httpResponse->send();
    }

    /**
     * @return \LightSaml\Context\Profile\MessageContext
     * @throws \RuntimeException
     */
    public function buildMessageContextFromRequest()
    {
        $request = SymfonyRequest::createFromGlobals();

        try {
            $bindingFactory = new \LightSaml\Binding\BindingFactory();
            $binding = $bindingFactory->getBindingByRequest($request);

            $messageContext = new \LightSaml\Context\Profile\MessageContext();
            $binding->receive($request, $messageContext);
        } catch (LightSamlException $e) {
            throw new \RuntimeException('Missing SAMLRequest or SAMLResponse parameter');
        }

        return $messageContext;
    }
}
