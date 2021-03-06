<?php
/**
 * @category   Emarsys
 * @package    Emarsys_Emarsys
 * @copyright  Copyright (c) 2018 Emarsys. (http://www.emarsys.net/)
 */

namespace Emarsys\Emarsys\Block\Adminhtml\Mapping\Event\Renderer;

use Emarsys\Emarsys\Helper\Data as EmarsysHelper;
use Emarsys\Emarsys\Model\ResourceModel\Customer\Collection;
use Emarsys\Emarsys\Model\ResourceModel\Emarsysevents\CollectionFactory;
use Emarsys\Emarsys\Model\ResourceModel\Event;
use Emarsys\Emarsys\Model\ResourceModel\Sync;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Backend\Helper\Data;
use Magento\Backend\Model\Session;
use Magento\Framework\DataObject;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Emarsyseventmapping
 * @package Emarsys\Emarsys\Block\Adminhtml\Mapping\Event\Renderer
 */
class Emarsyseventmapping extends AbstractRenderer
{
    /**
     * @var Session
     */
    protected $session;

    /**
     * @var Collection
     */
    protected $collectionFactory;

    /**
     * @var Data
     */
    protected $backendHelper;

    /**
     * @var Sync
     */
    protected $syncResourceModel;

    /**
     * @var Event
     */
    protected $resourceModelEvent;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var CollectionFactory
     */
    protected $emarsysEventCollection;

    /**
     * @var EmarsysHelper
     */
    protected $emarsysHelper;

    /**
     * @var \Emarsys\Emarsys\Model\ResourceModel\Emarsysmagentoevents\CollectionFactory
     */
    protected $magentoEventCollection;

    /**
     * Emarsyseventmapping constructor.
     * @param Session $session
     * @param \Emarsys\Emarsys\Model\ResourceModel\Customer\CollectionFactory $collectionFactory
     * @param Data $backendHelper
     * @param Event $resourceModelEvent
     * @param StoreManagerInterface $storeManager
     * @param UrlInterface $urlInterface
     * @param EmarsysHelper $emarsysHelper
     * @param \Emarsys\Emarsys\Model\ResourceModel\Emarsysmagentoevents\CollectionFactory $magentoEventCollection
     * @param CollectionFactory $emarsysEventCollection
     */
    public function __construct(
        Session $session,
        \Emarsys\Emarsys\Model\ResourceModel\Customer\CollectionFactory $collectionFactory,
        Data $backendHelper,
        Event $resourceModelEvent,
        StoreManagerInterface $storeManager,
        UrlInterface $urlInterface,
        Data $emarsysHelper,
        \Emarsys\Emarsys\Model\ResourceModel\Emarsysmagentoevents\CollectionFactory $magentoEventCollection,
        CollectionFactory $emarsysEventCollection
    ) {
        $this->session = $session;
        $this->collectionFactory = $collectionFactory;
        $this->backendHelper = $backendHelper;
        $this->resourceModelEvent = $resourceModelEvent;
        $this->_storeManager = $storeManager;
        $this->emarsysEventCollection = $emarsysEventCollection;
        $this->emarsysHelper = $emarsysHelper;
        $this->_urlInterface = $urlInterface;
        $this->magentoEventCollection = $magentoEventCollection;
    }

    /**
     * @param DataObject $row
     * @return string
     */
    public function render(DataObject $row)
    {
        $id = $row->getId();
        $magentoEventName = $this->magentoEventCollection->create()
            ->addFieldToFilter('id', $id)
            ->getFirstItem()
            ->getData('magento_event');

        $emarsysEventname = trim(str_replace(" ", "_", strtolower($magentoEventName)));
        $session = $this->session;
        $storeId = $session->getStoreId();
        $gridSessionData = $session->getMappingGridData();
        $url = $this->_urlInterface->getUrl('*/*/changeValue');
        $emarsysEvents = $this->emarsysEventCollection->create()->addFieldToFilter('store_id', ['eq' => $storeId]);
        $dbEvents = $emarsysEvents->getAllIds();

        $readOnly = '';
        if ($this->emarsysHelper->isReadonlyMagentoEventId($id)) {
            $readOnly .= 'disabled = disabled ';
        }

        $html = '<select ' . $readOnly . 'name="directions" style="width:200px;" onchange="changeEmarsysValue(\'' . $url . '\', this.value, \'' . $id . '\', \'' . $id . '\')";>
			<option value="0">Please Select</option>';

        $gridSessionData[$id]['magento_event_id'] = $id;
        foreach ($emarsysEvents as $emarsysEvent) {
            $sel = '';

            if (($row->getEmarsysEventId() == $emarsysEvent->getId())
                || (($emarsysEventname == $emarsysEvent->getEmarsysEvent()) && ($row->getEmarsysEventId() == null))
                || (($emarsysEventname == $emarsysEvent->getEmarsysEvent()) && ($row->getEmarsysEventId() != null) && !in_array($row->getEmarsysEventId(), $dbEvents))
            ) {
                $sel .= 'selected = selected';
                $gridSessionData[$id]['emarsys_event_id'] = $emarsysEvent->getId();
                $gridSessionData[$id]['recommended'] = 1;
            }
            $html .= '<option ' . $sel . ' value="' . $emarsysEvent->getId() . '">' . $emarsysEvent->getEmarsysEvent() . '</option>';
        }
        $html .= '</select>';
        $session->setStoreId($storeId);
        $session->setMappingGridData($gridSessionData);
        return $html;
    }
}
