<?php
/**
 * Copyright © Sarus, LLC. All rights reserved.
 */

namespace Sarus\SsoIdp\Model\Logout;

class RequestValidator
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
     * @param \LightSaml\Model\Protocol\LogoutRequest $logoutRequest
     * @param \Magento\Customer\Model\Customer|null $customer
     * @return bool
     */
    public function validate($logoutRequest, $customer = null)
    {
        $this->validateIssuer($logoutRequest);
        $this->validateIfLogoutConfigured();

        $this->validateNotOnOrAfter($logoutRequest);

        $this->validateSignature($logoutRequest);

        $this->validateDestination($logoutRequest);
        $this->validateNameIdFormat($logoutRequest);
        $this->validateNameId($logoutRequest, $customer);

        return true;
    }

    /**
     * @param \LightSaml\Model\Protocol\LogoutRequest $logoutRequest
     * @return bool
     * @throws \InvalidArgumentException
     */
    private function validateIssuer($logoutRequest)
    {
        if (null == $logoutRequest->getIssuer()) {
            throw new \InvalidArgumentException('Issuer is not specified.');
        }

        if ($logoutRequest->getIssuer()->getValue() !== $this->configSp->getEntityId()) {
            throw new \InvalidArgumentException(sprintf('SP %s unknown issuer.', $logoutRequest->getIssuer()->getValue()));
        }
        return true;
    }

    /**
     * @return void
     * @throws \InvalidArgumentException
     */
    private function validateIfLogoutConfigured()
    {
        if (!$this->configSp->getSingleLogoutUrl()) {
            throw new \InvalidArgumentException('The SP is not configured for logout.');
        }
    }

    /**
     * @param \LightSaml\Model\Protocol\LogoutRequest $logoutRequest
     * @return bool
     * @throws \InvalidArgumentException
     */
    private function validateNotOnOrAfter($logoutRequest)
    {
        if (!$logoutRequest->getNotOnOrAfterTimestamp()) {
            return true;
        }

        $nowTime = time();
        if ($logoutRequest->getNotOnOrAfterTimestamp() + $this->configIdp->getAllowedSecondsSkew() <= $nowTime) {
            $nowTimeString = \LightSaml\Helper::time2string($nowTime);
            throw new \InvalidArgumentException(
                sprintf('NotOnOrAfter: %s, now %s.', $logoutRequest->getNotOnOrAfterString(), $nowTimeString)
            );
        }

        return true;
    }

    /**
     * @param \LightSaml\Model\Protocol\LogoutRequest $logoutRequest
     * @return bool
     * @throws \InvalidArgumentException
     */
    private function validateSignature($logoutRequest)
    {
        /** @var \LightSaml\Model\XmlDSig\SignatureXmlReader $signatureReader */
        $signatureReader = $logoutRequest->getSignature();
        if ($this->configIdp->isWantLogoutRequestSigned() && !$signatureReader) {
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
     * @param \LightSaml\Model\Protocol\LogoutRequest $logoutRequest
     * @return bool
     * @throws \InvalidArgumentException
     */
    private function validateDestination($logoutRequest)
    {
        if (!$logoutRequest->getDestination()) {
            throw new \InvalidArgumentException('Destination url is not specified.');
        }

        if ($this->configIdp->getLogoutUrl() != $logoutRequest->getDestination()) {
            throw new \InvalidArgumentException(
                sprintf('Destination url is %s, expected %s.', $logoutRequest->getDestination(), $this->configIdp->getLogoutUrl())
            );
        }

        return true;
    }

    /**
     * @param \LightSaml\Model\Protocol\LogoutRequest $logoutRequest
     * @return bool
     * @throws \InvalidArgumentException
     */
    private function validateNameIdFormat($logoutRequest)
    {
        if (!$logoutRequest->getNameID() || !$logoutRequest->getNameID()->getFormat()) {
            throw new \InvalidArgumentException('Name ID format is not specified.');
        }

        if ($logoutRequest->getNameID()->getFormat() !== $this->configSp->getNameIdFormat()) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Name ID format is %s, expected %s.',
                    $logoutRequest->getNameID()->getFormat(),
                    $this->configSp->getNameIdFormat()
                )
            );
        }

        return true;
    }

    /**
     * @param \LightSaml\Model\Protocol\LogoutRequest $logoutRequest
     * @param \Magento\Customer\Model\Customer|null $customer
     * @return bool
     * @throws \InvalidArgumentException
     */
    private function validateNameId($logoutRequest, $customer = null)
    {
        if (!$logoutRequest->getNameID() || !$logoutRequest->getNameID()->getValue()) {
            throw new \InvalidArgumentException('Name ID is not specified.');
        }

        if ($customer
            && $customer->getDataUsingMethod($this->configSp->getNameId()) != $logoutRequest->getNameID()->getValue()
        ) {
            throw new \InvalidArgumentException('Wrong Name ID value.');
        }

        return true;
    }
}
