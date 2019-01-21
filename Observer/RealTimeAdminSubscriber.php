<?php
/**
 * @category   Emarsys
 * @package    Emarsys_Emarsys
 * @copyright  Copyright (c) 2018 Emarsys. (http://www.emarsys.net/)
 */

namespace Emarsys\Emarsys\Observer;

use Emarsys\{
    Emarsys\Helper\Data,
    Emarsys\Model\Api\Subscriber
};
use Magento\{
    Framework\App\Request\Http,
    Framework\Event\Observer,
    Store\Model\StoreManagerInterface,
    Framework\Event\ObserverInterface
};

/**
 * Class RealTimeAdminSubscriber
 * @package Emarsys\Emarsys\Observer
 */
class RealTimeAdminSubscriber implements ObserverInterface
{
    /**
     * @var Subscriber
     */
    protected $subscriberModel;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * @var Http
     */
    protected $request;

    /**
     * RealTimeAdminSubscriber constructor.
     * @param Subscriber $subscriberModel
     * @param StoreManagerInterface $storeManager
     * @param Data $dataHelper
     * @param Http $request
     */
    public function __construct(
        Subscriber $subscriberModel,
        StoreManagerInterface $storeManager,
        Data $dataHelper,
        Http $request
    ) {
        $this->subscriberModel = $subscriberModel;
        $this->storeManager = $storeManager;
        $this->dataHelper = $dataHelper;
        $this->request = $request;
    }

    /**
     * @param Observer $observer
     * @throws \Exception
     */
    public function execute(Observer $observer)
    {
        $subscriberId = $observer->getEvent()->getSubscriber()->getId();
        $storeId = $observer->getEvent()->getSubscriber()->getStoreId();

        /** @var \Magento\Store\Model\Store $store */
        $store = $this->storeManager->getStore($storeId);
        if (!$this->dataHelper->isEmarsysEnabled($store->getWebsiteId())
            || !$store->getConfig(EmarsysHelperData::XPATH_EMARSYS_ENABLE_CONTACT_FEED)
        ) {
            return;
        }

        $realtimeStatus = $store->getConfig(Data::XPATH_EMARSYS_REALTIME_SYNC);
        if ($realtimeStatus == 1) {
            $frontendFlag = '';
            $this->subscriberModel->syncSubscriber($subscriberId, $storeId, $frontendFlag);
        } else {
            $this->dataHelper->syncFail($subscriberId, $store->getWebsiteId(), $storeId, 0, 2);
        }
    }
}