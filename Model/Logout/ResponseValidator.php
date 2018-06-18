<?php
/**
 * Copyright © Sarus, LLC. All rights reserved.
 */

namespace Sarus\SsoIdp\Model\Logout;

class ResponseValidator
{
    /**
     * @var \Sarus\SsoIdp\Model\Config\IdentityProvider
     */
    private $configIdp;

    /**
     * @var \Sarus\SsoIdp\Model\Config\ServiceProvider
     */
    private $configSp;

    /**
     * @var \Sarus\SsoIdp\Helper\SpCredentials
     */
    private $spCredentials;

    /**
     * @param \Sarus\SsoIdp\Model\Config\IdentityProvider $configIdp
     * @param \Sarus\SsoIdp\Model\Config\ServiceProvider $configSp
     * @param \Sarus\SsoIdp\Helper\SpCredentials $spCredentials
     */
    public function __construct(
        \Sarus\SsoIdp\Model\Config\IdentityProvider $configIdp,
        \Sarus\SsoIdp\Model\Config\ServiceProvider $configSp,
        \Sarus\SsoIdp\Helper\SpCredentials $spCredentials
    ) {
        $this->configIdp = $configIdp;
        $this->configSp = $configSp;
        $this->spCredentials = $spCredentials;
    }

    /**
     * @param \LightSaml\Model\Protocol\LogoutResponse $logoutResponse
     * @param string|null $requestId
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function validate($logoutResponse, $requestId = null)
    {
        $this->validateIssuer($logoutResponse);

        $this->validateSignature($logoutResponse);

        $this->validateRequestId($logoutResponse, $requestId);
        $this->validateDestination($logoutResponse);

        $this->validateStatus($logoutResponse);

        return true;
    }

    /**
     * @param \LightSaml\Model\Protocol\LogoutResponse $logoutResponse
     * @return bool
     * @throws \InvalidArgumentException
     */
    private function validateIssuer($logoutResponse)
    {
        if (null == $logoutResponse->getIssuer()) {
            throw new \InvalidArgumentException('Issuer is not specified.');
        }

        if ($logoutResponse->getIssuer()->getValue() != $this->configSp->getEntityId()) {
            throw new \InvalidArgumentException(sprintf('SP %s unknown issuer.', $logoutResponse->getIssuer()->getValue()));
        }
        return true;
    }

    /**
     * @param \LightSaml\Model\Protocol\LogoutResponse $logoutResponse
     * @return bool
     * @throws \InvalidArgumentException
     */
    private function validateSignature($logoutResponse)
    {
        /** @var \LightSaml\Model\XmlDSig\SignatureXmlReader $signatureReader */
        $signatureReader = $logoutResponse->getSignature();
        if ($this->configIdp->isWantLogoutResponseSigned() && !$signatureReader) {
            throw new \InvalidArgumentException('No signature, but is required.');
        }

        if (!$signatureReader) {
            return true;
        }

        $isSignatureValid = $signatureReader->validate($this->spCredentials->getPublicKey());
        if (!$isSignatureValid) {
            throw new \InvalidArgumentException('Signature is not validated.');
        }
        return true;
    }

    /**
     * @param \LightSaml\Model\Protocol\LogoutResponse $logoutResponse
     * @param string|null $requestId
     * @return bool
     * @throws \InvalidArgumentException
     */
    private function validateRequestId($logoutResponse, $requestId = null)
    {
        if (!$requestId) {
            throw new \InvalidArgumentException('Logout request was not sent.');
        }

        if ($logoutResponse->getInResponseTo() != $requestId) {
            throw new \InvalidArgumentException(
                sprintf(
                    'The InResponseTo of the Logout Response: %s, does not match the ID of the Logout request sent by the SP: %s',
                    $logoutResponse->getInResponseTo(),
                    $requestId
                )
            );
        }
        return true;
    }

    /**
     * @param \LightSaml\Model\Protocol\LogoutResponse $logoutResponse
     * @return bool
     * @throws \InvalidArgumentException
     */
    private function validateDestination($logoutResponse)
    {
        if (!$logoutResponse->getDestination()) {
            throw new \InvalidArgumentException('Destination url is not specified.');
        }

        if ($this->configIdp->getLogoutUrl() != $logoutResponse->getDestination()) {
            throw new \InvalidArgumentException(
                sprintf(
                    'The Destination of the Logout Response %s, does not match with current page %s.',
                    $logoutResponse->getDestination(),
                    $this->configIdp->getLogoutUrl()
                )
            );
        }

        return true;
    }

    /**
     * @param \LightSaml\Model\Protocol\LogoutResponse $logoutResponse
     * @return bool
     * @throws \InvalidArgumentException
     */
    private function validateStatus($logoutResponse)
    {
        if (!$logoutResponse->getStatus()) {
            throw new \InvalidArgumentException('Response status is not specified.');
        }

        if (!$logoutResponse->getStatus()->isSuccess()) {
            throw new \InvalidArgumentException($this->getErrorStatusMsg($logoutResponse->getStatus()));
        }

        return true;
    }

    /**
     * @param \LightSaml\Model\Protocol\Status $status
     * @return string
     */
    private function getErrorStatusMsg($status)
    {
        $explodedCode = explode(':', $status->getStatusCode());
        $printableCode = array_pop($explodedCode);

        $statusExceptionMsg = 'The status code of the Response was not Success, was ' . $printableCode;
        if ($status->getStatusMessage()) {
            $statusExceptionMsg .= ' -> ' . $status->getStatusMessage();
        }
        return $statusExceptionMsg;
    }
}
