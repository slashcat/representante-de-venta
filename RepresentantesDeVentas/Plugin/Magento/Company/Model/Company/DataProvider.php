<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Formax\RepresentantesDeVentas\Plugin\Magento\Company\Model\Company;

class DataProvider
{

    /**
     * @param \Magento\Company\Model\Company\DataProvider $subject
     * @param $result
     * @return mixed
     */
    public function afterGetGeneralData(
        \Magento\Company\Model\Company\DataProvider $subject,
        $result,
        \Magento\Company\Api\Data\CompanyInterface $company
    ) {
        if ($company->getData('sellers')) {
            $result['sellers'] = unserialize($company->getData('sellers'));
        }
        return $result;
    }
}

