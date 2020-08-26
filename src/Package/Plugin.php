<?php

namespace CLAMP\Moodlegist\Package;

class Plugin extends AbstractPackage
{

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
        return $this->versions[$version]->downloadurl;
    }

    public function getPackages(&$uid = 1)
    {
        $packages = array();

        foreach ($this->versions as $version) {
            $package = array();
            $package['name'] = $this->getPackageName();
            $package['version'] = $version['version'];
            $package['uid'] = $uid++;
            $package['dist'] = array(
                'type' => 'zip',
                'url' => $version['downloadurl']
            );
            $package['homepage'] = $this->getHomepageUrl();
            $package['require']['composer/installers'] = '~1.0';
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
