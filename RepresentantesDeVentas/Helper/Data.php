<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Formax\RepresentantesDeVentas\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Customer\Model\Session $session,
        \Magento\Company\Api\CompanyManagementInterface $companyManagement,
        \Magento\User\Api\Data\UserInterfaceFactory $userFactory
    ) {
        parent::__construct($context);
        $this->session = $session;
        $this->companyManagement = $companyManagement;
        $this->userFactory = $userFactory;
    }

    /**
     * @return array|null
     */
    public function getRepresentantesDeVentas() {
        $result = null;
        $sellers = $this->getSellers();
        if ($sellers) {
            foreach ($sellers as $seller) {
                $result[] = [
                    'email' => $seller->getEmail(),
                    'nombre' => $seller->getName(),
                    'telefono' => $seller->getTelefono()
                ];
            }
        }
        return $result;
    }

    /**
     * @return array|null
     */
    public function getSellers() {
        $sellers = null;
        $customer = $this->session->getCustomer();
        $customerId = $customer->getId();
        if ($customerId) {
            $company = $this->companyManagement->getByCustomerId($customerId);
            if ($company) {
                $sellersIds = unserialize($company->getSellers());
                if (is_array($sellersIds) && !empty($sellersIds)) {
                    foreach ($sellersIds as $sellerId) {
                        $sellers[] = $this->userFactory->create()->load($sellerId);
                    }
                }
            }
        }
        return $sellers;
    }
}
