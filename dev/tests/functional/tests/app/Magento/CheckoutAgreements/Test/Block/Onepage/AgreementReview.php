<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CheckoutAgreements\Test\Block\Onepage;

use Magento\Checkout\Test\Block\Onepage\Review;
use Magento\CheckoutAgreements\Test\Fixture\CheckoutAgreement;
use Magento\Mtf\Client\Locator;

/**
 * Class AgreementReview
 * One page checkout order review block
 */
class AgreementReview extends Review
{
    /**
     * Notification agreements locator
     *
     * @var string
     */
    protected $notification = 'div.mage-error';

    /**
     * Agreement locator
     *
     * @var string
     */
    protected $agreement = './/div[contains(@id, "checkout-review-submit")]//label[.="%s"]';

    /**
     * Agreement checkbox locator
     *
     * @var string
     */
    protected $agreementCheckbox = './/input[contains(@id, "agreement")]';

    /**
     * Get notification massage
     *
     * @return string
     */
    public function getNotificationMassage()
    {
        return $this->_rootElement->find($this->notification)->getText();
    }

    /**
     * Set agreement
     *
     * @param string $value
     * @return void
     */
    public function setAgreement($value)
    {
        $this->_rootElement->find($this->agreementCheckbox, Locator::SELECTOR_XPATH, 'checkbox')->setValue($value);
    }

    /**
     * Check agreement
     *
     * @param CheckoutAgreement $agreement
     * @return bool
     */
    public function checkAgreement(CheckoutAgreement $agreement)
    {
        return $this->_rootElement
            ->find(sprintf($this->agreement, $agreement->getCheckboxText()), Locator::SELECTOR_XPATH)->isVisible();
    }
}
