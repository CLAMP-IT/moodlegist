<?php

namespace App\Twig\Runtime;

use Twig\Extension\RuntimeExtensionInterface;

class FormatVersionRuntime implements RuntimeExtensionInterface
{
    public function __construct()
    {
        // Inject dependencies if needed
    }

    public function doSomething($versions, $search)
    {
        $versions = json_decode($versions, true);
        $supportedversions = array();
        if ($search != 'any') {
            foreach ($versions as $key => $version) {
                $supportedmoodles = array();
                foreach ($version['supportedmoodles'] as $supportedmoodle) {
                    $supportedmoodles[] = $supportedmoodle['release'];
                }
                if (in_array($search, $supportedmoodles)) {
                    $supportedversions[$key] = $version;
                }
            }
        } else {
            $supportedversions = $versions;
        }

        $versionnumbers = array();
        foreach ($supportedversions as $key => $row) {
            $versionnumbers[$key] = $row['version'];
        }

        array_multisort($versionnumbers, SORT_ASC, $supportedversions);
        return $supportedversions;
    }
}
