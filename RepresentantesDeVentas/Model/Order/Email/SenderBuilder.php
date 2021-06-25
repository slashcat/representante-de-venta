<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Formax\RepresentantesDeVentas\Model\Order\Email;

use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Mail\Template\TransportBuilderByStore;
use Magento\Sales\Model\Order\Email\Container\IdentityInterface;
use Magento\Sales\Model\Order\Email\Container\Template;

class SenderBuilder extends \Magento\Sales\Model\Order\Email\SenderBuilder
{
    /**
     * @var Template
     */
    protected $templateContainer;

    /**
     * @var IdentityInterface
     */
    protected $identityContainer;

    /**
     * @var TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param Template $templateContainer
     * @param IdentityInterface $identityContainer
     * @param TransportBuilder $transportBuilder
     * @param TransportBuilderByStore $transportBuilderByStore
     */
    public function __construct(
        Template $templateContainer,
        IdentityInterface $identityContainer,
        TransportBuilder $transportBuilder,
        TransportBuilderByStore $transportBuilderByStore = null,
        \Magento\User\Api\Data\UserInterfaceFactory $userFactory,
        \Magento\Company\Model\CompanyManagement $companyManagement
    ) {
        parent::__construct($templateContainer, $identityContainer, $transportBuilder, $transportBuilderByStore);
        $this->userFactory = $userFactory;
        $this->companyManagement = $companyManagement;
    }
    /**
     * Prepare and send copy email message
     *
     * @return void
     */
    public function sendCopyToSellers($order)
    {
        $categories = $this->getCategories($order);
        $copyTo = $this->getSalesEmails($order,$categories);
        if (!empty($copyTo)) {
            foreach ($copyTo as $email) {
                $this->configureEmailTemplate();
                $this->transportBuilder->addTo($email);
                $transport = $this->transportBuilder->getTransport();
                $transport->sendMessage();
            }
        }
    }

    public function getCategories($order) {
        $items = $order->getAllItems();
        $categories = [];
        foreach ($items as $item) {
           $categories = array_merge ($categories,$item->getProduct()->getCategoryIds());
        }
        $categories = array_unique($categories);
        return $categories;
    }

    public function getSalesEmails($order,$categories) {
        $emails = [];
        $customerId = $order->getCustomerId();
        $company = $this->companyManagement->getByCustomerId($customerId);
        $sellers = unserialize($company->getSellers());
        if (is_array($sellers) && !empty($sellers)) {
            foreach ($sellers as $sellerId) {
                $seller = $this->userFactory->create()->load($sellerId);
                $categoriasAsociadas = $seller->getCategoriasAsociadas();
                if ($categoriasAsociadas) {
                    if (!empty(array_intersect($categories, explode(",",$categoriasAsociadas)))) {
                        $emails[] = $seller->getEmail();
                    }
                }
            }
        }
        return $emails;
    }

}
