<?php
declare(strict_types=1);

namespace Flowpack\SiteKickstarter\Infrastructure\ImageHandler;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Package\PackageManager;
use Neos\Flow\Package\FlowPackageInterface;
use Neos\Flow\ResourceManagement\ResourceManager;
use Neos\Imagine\ImagineFactory;
use Neos\Utility\Files;

class ImageHandlerService
{

    /**
     * @Flow\InjectConfiguration(path="supportedImageHandlers")
     * @var string[]
     */
    protected $supportedImageHandlers;

    /**
     * @Flow\InjectConfiguration(path="requiredImageFormats")
     * @var string[]
     */
    protected $requiredImageFormats;

    /**
     * @Flow\Inject
     * @var PackageManager
     */
    protected $packageManager;

    /**
     * @Flow\Inject
     * @var ResourceManager
     */
    protected $resourceManager;

    /**
     * @Flow\Inject
     * @var ImagineFactory
     */
    protected $imagineFactory;

    /**
     * @return array
     */
    public function getAvailableImageHandlers(): array
    {
        $foundImageHandlers = [];
        foreach ($this->supportedImageHandlers as $extensionName) {
            if (\extension_loaded($extensionName)) {
                $unsupportedFormats = $this->findUnsupportedImageFormats($extensionName);
                if (\count($unsupportedFormats) === 0) {
                    $foundImageHandlers[] = $extensionName;
                }
            }
        }
        return $foundImageHandlers;
    }

    /**
     * @param string $driver
     * @return array Not supported image formats
     */
    protected function findUnsupportedImageFormats(string $driver): array
    {
        $this->imagineFactory->injectSettings(['driver' => ucfirst($driver)]);
        $imagine = $this->imagineFactory->create();
        $unsupportedFormats = [];

        foreach ($this->requiredImageFormats as $imageFormat) {
            /** @var FlowPackageInterface $neosPackage */
            $neosPackage = $this->packageManager->getPackage('Neos.Neos');
            $imagePath = Files::concatenatePaths([$neosPackage->getResourcesPath(), 'Private/Installer/TestImages/Test.' . $imageFormat]);

            try {
                $imagine->open($imagePath);
            } /** @noinspection BadExceptionsProcessingInspection */ catch (\Exception $exception) {
                $unsupportedFormats[] = sprintf('"%s"', $imageFormat);
            }
        }

        return $unsupportedFormats;
    }
}
