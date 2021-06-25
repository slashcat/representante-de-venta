<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Formax\RepresentantesDeVentas\Model\Order\Email;

use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\SenderBuilder;

class Sender extends \Magento\Sales\Model\Order\Email\Sender
{
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
