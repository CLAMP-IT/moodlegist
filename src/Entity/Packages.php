<?php

namespace App\Entity;

use App\Repository\PackagesRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PackagesRepository::class)]
class Packages
{
    #[ORM\Id, ORM\Column(type: Types::TEXT)]
    private ?string $type = null;

    #[ORM\Id, ORM\Column(type: Types::TEXT)]
    private ?string $name = null;

    #[ORM\Column]
    private ?int $newest_version = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $versions = null;

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function isEqual(Packages $package): bool
    {
        if($this->newest_version === $package->getNewestVersion() && 
            $this->versions === $package->getVersions()) {
                return true;
            } else {
                return false;
            }
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getNewestVersion(): ?int
    {
        return $this->newest_version;
    }

    public function setNewestVersion(int $newest_version): self
    {
        $this->newest_version = $newest_version;

        return $this;
    }

    public function getVersions(): ?string
    {
        return $this->versions;
    }

    public function setVersions(string $versions): self
    {
        $this->versions = $versions;

        return $this;
    }

    public function getVendorName()
    {
        return 'moodle-plugin-db';
    }

    public function getComposerType()
    {
        return 'moodle-'. $this->type;
    }

    public function getPackageName()
    {
        return $this->getVendorName() . '/' . $this->type . '_' . $this->name;
    }

    public function getHomepageUrl()
    {
        return "https://moodle.org/plugins/".$this->getName();
    }

    public function getDownloadUrl($version)
    {
        $versions = json_decode($this->versions, true);
        return $versions[$version]->downloadurl;
    }

    public function getPackages(&$uid = 1)
    {
        $packages = array();

        foreach (json_decode($this->versions, true) as $version) {
            $package = array();
            $package['name'] = $this->getPackageName();
            $package['version'] = $version['version'];
            $package['uid'] = $uid++;
            $package['dist'] = array(
                'type' => 'zip',
                'url' => $version['downloadurl']
            );
            $package['homepage'] = $this->getHomepageUrl();
            $package['require']['composer/installers'] = '^1.0 || ^2.0';
            $supportedmoodles = array();
            foreach ($version['supportedmoodles'] as $supportedmoodle) {
                $supportedmoodles[] = $supportedmoodle['release'] . '.*';
            }
            //$package['require'][Core::getPackageName()] = implode('||', $supportedmoodles);
            $package['type'] = $this->getComposerType();
            $package['extra']['installer-name'] = $this->getName();
            $packages[$this->getPackageName()][$package['version']] = $package;
        }

        return $packages;
    }
}
