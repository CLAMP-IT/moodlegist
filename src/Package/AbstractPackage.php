<?php

namespace Outlandish\Wpackagist\Package;

use Composer\Package\Version\VersionParser;

class AbstractPackage
{
    /**
     *
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $frankenstyle_name;

    /**
     * @var \DateTime
     */
    protected $last_committed;

    /**
     * @var \DateTime
     */
    protected $last_fetched;

    /**
     * @var array
     */
    protected $versions = array();

    /**
     * @var string
     */
    protected $plugin_db_url = 'https://download.moodle.org/api/1.3/pluglist.php';

    public function __construct(array $data = array())
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }

        if (is_string($this->versions)) {
            $this->versions = json_decode($this->versions, true);
        }
        if (!$this->versions) {
            $this->versions = array();
        }

        if (is_string($this->last_committed)) {
            $this->last_committed = new \DateTime($this->last_committed);
        }

        if (is_string($this->last_fetched)) {
            $this->last_fetched = new \DateTime($this->last_fetched);
        }
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string package shortname
     */
    public function getName()
    {
        return $this->name;
    }

    public function getFrankenstyleName()
    {
        return $this->frankenstyle_name;
    }

    /**
     * Ex: wpackagist
     * @return string
     */
    public function getVendorName()
    {
        return 'moodle-plugin-db';
    }

    public function getPluginDbUrl()
    {
        return $this->plugin_db_url;
    }

    /**
     * Ex: wordpress-plugin
     * @return string|null
     */
    public function getComposerType()
    {
        return 'moodle-'. $this->type;
    }

    /**
     * @return string "wpackagist-TYPE/PACKAGE"
     */
    public function getPackageName()
    {
        return $this->getVendorName().'/'.$this->getFrankenstyleName();
    }

    /**
     * Ex: https://wordpress.org/extend/themes/THEME/
     * @return string URL
     */
    public function getHomepageUrl()
    {
        return "https://moodle.org/plugins/".$this->getName();
    }

    /**
     * Ex: https://downloads.wordpress.org/plugin/plugin.1.0.zip
     * @return string URL
     */
    public function getDownloadUrl($version)
    {
        return $this->versions[$version]->downloadurl;
    }

    /**
     * @return \DateTime|null
     */
    public function getLastCommited()
    {
        return $this->last_committed;
    }

    /**
     * @return \DateTime|null
     */
    public function getLastFetched()
    {
        return $this->last_fetched;
    }

    /**
     * @return array
     */
    public function getVersions()
    {
        return $this->versions;
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
            $package['type'] = $this->getComposerType();
            $package['extra']['installer-name'] = $this->getName();
            $packages[$this->getPackageName()][$package['version']] = $package;
        }

        return $packages;
    }

    /**
     * @param $version
     * @param  int                       $uid
     * @return array
     * @throws \UnexpectedValueException
     */
    public function getPackageVersion($version, &$uid = 1)
    {
        $tag = $this->versions[$version];

        $package = array(
            'name'               => $this->getPackageName(),
            'version'            => $version,
    //        'version_normalized' => $normalizedVersion,
            'uid'                => $uid++,
        );

        if ($url = $this->getDownloadUrl($version)) {
            $package['dist'] = array(
                'type' => 'zip',
                'url'  => $url,
            );
        }

        if ($url = $this->getHomepageUrl()) {
            $package['homepage'] = $url;
        }

        if ($type = $this->getComposerType()) {
            $package['require']['composer/installers'] = '~1.0';
            $package['type'] = $type;
        }

        return $package;
    }
}
