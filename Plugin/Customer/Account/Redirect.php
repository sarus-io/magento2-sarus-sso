<?php
/**
 * Copyright © Sarus, LLC. All rights reserved.
 */

namespace Sarus\SsoIdp\Plugin\Customer\Account;

class Redirect
{
    /**
     * @var \Sarus\SsoIdp\Model\Config\IdentityProvider
     */
    private $configIdp;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @param \Sarus\SsoIdp\Model\Config\IdentityProvider $configIdp
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        \Sarus\SsoIdp\Model\Config\IdentityProvider $configIdp,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->configIdp = $configIdp;
        $this->customerSession = $customerSession;
    }

    /**
     * @param \Magento\Customer\Model\Account\Redirect $subject
     * @param null|string $result
     * @return null|string
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetRedirectCookie($subject, $result)
    {
        if (!$this->configIdp->isEnabled()
            || strpos((string)$this->customerSession->getBeforeAuthUrl(), $this->configIdp->getSingleSingOnUrl()) !== 0
        ) {
            return $result;
        }

        return null;
    }
}
