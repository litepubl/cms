<?php
namespace litepubl\Composer;

use Composer\IO\IOInterface;
use Composer\Installer\LibraryInstaller;
use Composer\Package\PackageInterface;
use Composer\Repository\InstalledRepositoryInterface;

class Installer extends \Composer\Installers\Installer
{
    /**
* 
     * {@inheritDoc}
     */
    const VENDOR = 'litepubl';

    public function getInstallPath(PackageInterface $package)
    {
        $type = $package->getType();
        $frameworkType = $this->findFrameworkType($type);
        $installer = new LitepublInstaller($package, $this->composer, $this->io);
        return $installer->getInstallPath($package, $frameworkType);
    }

    /**
* 
     * Finds a supported framework type if it exists and returns it
     *
     *
     * @param  string $type
     * @return string
     */
    protected function findFrameworkType($type)
    {
        if (static::VENDOR === substr($type, 0, strlen(static::VENDOR))) {
            return static::VENDOR;
        }

        return false;
    }

    /**
* 
     * Get the second part of the regular expression to check for support of a
     * package type
     *
     *
     * @param  string $frameworkType
     * @return string
     */
    protected function getLocationPattern($frameworkType)
    {
            $framework = new LitepublInstaller(null, $this->composer, $this->io);
            $locations = array_keys($framework->getLocations());
        return '(' . implode('|', $locations) . ')';
    }
}
