<?php

namespace CLAMP\Moodlegist\Package;

class Core extends AbstractPackage
{
    public function getComposerType()
    {
        return 'metapackage';
    }

    // TODO
    public function getDownloadUrl($version)
    {
        return null;
    }

    public function getHomepageUrl()
    {
        return 'https://download.moodle.org/';
    }

    public function getPackageName()
    {
        return 'moodle/moodle';
    }

    public function getPackages(&$uid = 1)
    {
        $packages = array();
        foreach ($this->versions as $version) {
            $package = array();
            $package['name'] = $this->getPackageName();
            $package['version'] = $version;
            $package['uid'] = $uid++;
            $package['type'] = $this->getComposerType();
            $packages[$this->getPackageName()][$package['version']] = $package;
        }
        return $packages;
    }

    public function getVendorName()
    {
        return 'moodle';
    }
}
