<?php

namespace CLAMP\Moodlegist\Package;

abstract class AbstractPackage
{

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var array
     */
    protected $versions = array();

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

    }

    /**
     * Ex: moodle-mod
     * @return string|null
     */
    abstract public function getComposerType();

    /**
     * Ex: https://moodle.org/plugins/download.php/10937/mod_attendance_moodle30_2016031501.zip
     * @return string URL
     */
    abstract public function getDownloadUrl($version);

    /**
     * Ex: https://moodle.org/plugins/TYPE_NAME
     * @return string URL
     */
    abstract public function getHomepageUrl();

    /**
     * Composer's internal id.
     *
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

    /**
     * @return string "moodle-plugin-db/TYPE_NAME"
     */
    abstract public function getPackageName();

    /**
     * Ex: moodle-plugin-db
     * @return string
     */
    abstract public function getVendorName();

    /**
     * @return array
     */
    public function getVersions()
    {
        return $this->versions;
    }
}
