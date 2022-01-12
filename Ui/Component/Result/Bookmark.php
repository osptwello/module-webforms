<?php
namespace VladimirPopov\WebForms\Ui\Component\Result;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Api\BookmarkManagementInterface;
use Magento\Ui\Api\BookmarkRepositoryInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Ui\Component\AbstractComponent;

class Bookmark extends AbstractComponent
{
    const NAME = 'bookmark';

    /**
     * @var BookmarkRepositoryInterface
     */
    protected $bookmarkRepository;

    /**
     * @var FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @var BookmarkManagementInterface
     */
    protected $bookmarkManagement;


    protected $request;

    /**
     * @param ContextInterface $context
     * @param BookmarkRepositoryInterface $bookmarkRepository
     * @param BookmarkManagementInterface $bookmarkManagement
     * @param RequestInterface $request
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        BookmarkRepositoryInterface $bookmarkRepository,
        BookmarkManagementInterface $bookmarkManagement,
        RequestInterface $request,
        array $components = [],
        array $data = []
    ) {
        $data['config']['storageConfig']['namespace'] = 'result_grid'.$request->getParam('webform_id');
        parent::__construct($context, $components, $data);
        $this->request = $request;
        $this->bookmarkManagement = $bookmarkManagement;
    }

    /**
     * Get component name
     *
     * @return string
     */
    public function getComponentName()
    {
        return static::NAME;
    }


    /**
     * Register component
     *
     * @return void
     */
    public function prepare()
    {

        $namespace = 'result_grid'.$this->request->getParam('webform_id');
        $config = [];
        if (!empty($namespace)) {
            $bookmarks = $this->bookmarkManagement->loadByNamespace($namespace);
            /** @var \Magento\Ui\Api\Data\BookmarkInterface $bookmark */
            foreach ($bookmarks->getItems() as $bookmark) {
                if ($bookmark->isCurrent()) {
                    $config['activeIndex'] = $bookmark->getIdentifier();
                }

                $config = array_merge_recursive($config, $bookmark->getConfig());
            }
        }

        $this->setData('config', array_replace_recursive($config, $this->getConfiguration($this)));

        parent::prepare();

        $jsConfig = $this->getConfiguration($this);
        $this->getContext()->addComponentDefinition($this->getComponentName(), $jsConfig);
    }
}