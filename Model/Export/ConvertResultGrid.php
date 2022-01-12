<?php


namespace VladimirPopov\WebForms\Model\Export;


use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem;
use Magento\Framework\Oauth\Exception;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Ui\Model\Export\MetadataProvider;
use VladimirPopov\WebForms\Model\Result;
use VladimirPopov\WebForms\Model\ResultRepository;

class ConvertResultGrid
{
    /**
     * @var DirectoryList
     */
    protected $directory;

    /**
     * @var MetadataProvider
     */
    protected $metadataProvider;

    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var ResultRepository
     */
    protected $resultRepository;

    /**
     * @param Filesystem $filesystem
     * @param Filter $filter
     * @param MetadataProvider $metadataProvider
     * @param ResultRepository $resultRepository
     * @throws FileSystemException
     */
    public function __construct(
        Filesystem $filesystem,
        Filter $filter,
        MetadataProvider $metadataProvider,
        ResultRepository $resultRepository
    ) {
        $this->filter = $filter;
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->metadataProvider = $metadataProvider;
        $this->resultRepository = $resultRepository;
    }

    /**
     * Get Customer name by result id
     * (Fix for customer column)
     *
     * @param $id string|int
     * @return string
     */
    protected function getCustomerName($id) {
        try {

            /** @var Result $result */
            $result = $this->resultRepository->getById($id);
            return $result->getCustomerName();
        } catch (\Exception $exception) {
            return '';
        }
    }

    /**
     * Replace newline characters with spaces
     * @param $arr array
     * @return array
     */
    protected function replaceNewlineCharacters($arr) {
        for($i = 0; $i < count($arr); ++$i) {
            if (is_string($arr[$i])) {
                $arr[$i] = str_replace(array("\r", "\n"), ' ', $arr[$i]);
            }
        }
        return $arr;
    }

}