<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Formax\RepresentantesDeVentas\Model\Order\Email\Sender;

use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\SenderBuilder;

class OrderSender extends \Magento\Sales\Model\Order\Email\Sender\OrderSender
{
    /**
     * Sends order email to the customer.
     *
     * Email will be sent immediately in two cases:
     *
     * - if asynchronous email sending is disabled in global settings
     * - if $forceSyncMode parameter is set to TRUE
     *
     * Otherwise, email will be sent later during running of
     * corresponding cron job.
     *
     * @param Order $order
     * @param bool $forceSyncMode
     * @return bool
     */
    public function send(Order $order, $forceSyncMode = false)
    {
        $order->setSendEmail($this->identityContainer->isEnabled());

        if (!$this->globalConfig->getValue('sales_email/general/async_sending') || $forceSyncMode) {
            if ($this->checkAndSend($order)) {
                $order->setEmailSent(true);
                $this->orderResource->saveAttribute($order, ['send_email', 'email_sent']);
                return true;
            }
        } else {
            $order->setEmailSent(null);
            $this->orderResource->saveAttribute($order, 'email_sent');
        }

        $this->orderResource->saveAttribute($order, 'send_email');

        return false;
    }

    /**
     * Send order email if it is enabled in configuration.
     *
     * @param Order $order
     * @return bool
     */
    public function checkAndSend(Order $order)
    {
        $this->identityContainer->setStore($order->getStore());
        if (!$this->identityContainer->isEnabled()) {
            return false;
        }
        $this->prepareTemplate($order);

        /** @var SenderBuilder $sender */
        $sender = $this->getSender();

        try {
            $sender->send();
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            return false;
        }
        if ($this->identityContainer->getCopyMethod() == 'copy') {
            try {
                $sender->sendCopyToSellers($order);
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
            }
        }
        return true;
    }
}
