<?php
declare(strict_types=1);

namespace Doit\CustomShipping\Model\Carrier;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Rate\ResultFactory;
use Psr\Log\LoggerInterface;
use Doit\CustomShipping\Model\Rate\Calculator;

class Standard extends AbstractCarrier implements CarrierInterface
{
    protected $_code = 'doitshipping';

    private const METHOD_CODE = 'standard';

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ErrorFactory $rateErrorFactory,
        LoggerInterface $logger,
        private ResultFactory $rateResultFactory,
        private MethodFactory $rateMethodFactory,
        private Calculator $calculator,
        array $data = []
    ) {
        parent::__construct(
            $scopeConfig,
            $rateErrorFactory,
            $logger,
            $data
        );
    }

    public function collectRates(RateRequest $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        $configuredPrice = $this->getConfigData('price');

        // Validate here too: configuration can be changed outside Admin.
        if (!is_numeric($configuredPrice)
            || !is_finite((float) $configuredPrice)
            || (float) $configuredPrice < 0
        ) {
            $this->_logger->warning(
                'Doit shipping: invalid configured price; no rate returned.'
            );

            return false;
        }

        $price = $this->calculator->calculateStandard(
            (float) $configuredPrice,
            (float) $request->getPackageQty());

        $method = $this->rateMethodFactory->create();

        $method->setCarrier($this->_code);
        $method->setCarrierTitle(
            (string) $this->getConfigData('title')
        );

        $method->setMethod(self::METHOD_CODE);
        $method->setMethodTitle(
            (string) $this->getConfigData('name')
        );

        $method->setPrice($price);

        // Exercise assumption: internal delivery cost is not yet tracked.
        $method->setCost(0.0);

        $result = $this->rateResultFactory->create();
        $result->append($method); // Keep the working Standard option.

        $configuredSurcharge = $this->getConfigData('express_surcharge');

        if (!is_numeric($configuredSurcharge)
            || !is_finite((float) $configuredSurcharge)
            || (float) $configuredSurcharge < 0
        ) {
            $this->_logger->warning(
                'Doit shipping: invalid surcharge; Express omitted.'
            );

            // Standard remains available.
            return $result;
        }

        $expressMethod = $this->rateMethodFactory->create();

        $expressMethod->setCarrier($this->_code);
        $expressMethod->setCarrierTitle(
            (string) $this->getConfigData('title')
        );

        $expressMethod->setMethod('express');
        $expressMethod->setMethodTitle('Express Delivery');

        $expressMethod->setPrice(
            $this->calculator->calculateExpress(
                $price,
                (float) $configuredSurcharge)
        );
        $expressMethod->setCost(0.0);

        $result->append($expressMethod);

        return $result;

    }

    public function getAllowedMethods(): array
    {
        return [
            self::METHOD_CODE => (string) $this->getConfigData('name'),
            'express' => 'Express Delivery',
        ];
    }
}
