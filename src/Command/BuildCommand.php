<?php

namespace CLAMP\Moodlegist\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\Helper;
use CLAMP\Moodlegist\Package\Plugin;
use CLAMP\Moodlegist\Package\Core;

class BuildCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('build')
            ->setDescription('Build package.json from DB');
    }

    /**
     * Return a string to split packages in more-or-less even groups
     * of their last modification. Minimizes groups modifications.
     *
     * @return string
     */
    protected function getComposerProviderGroup($package)
    {
        return $package->getComposerType();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("Building packages");

        $fs = new Filesystem();

        $basePath = 'web/p.new/';
        $fs->mkdir($basePath.'moodle');
        $fs->mkdir($basePath.'moodle-plugin-db');

        /**
         * @var \PDO $db
         */
        $db = $this->getApplication()->getSilexApplication()['db'];

        $packages = $db->query('
            SELECT * FROM packages
            WHERE versions IS NOT NULL
            ORDER BY name
        ')->fetchAll(\PDO::FETCH_CLASS, 'CLAMP\Moodlegist\Package\Plugin');

        $uid = 1; // don't know what this does but composer requires it

        $corepackage = array(
            'versions' => array('2.2', '2.3', '2.4', '2.5', '2.6', '2.7', '2.8', '2.9', '3.0', '3.1', '3.2', '3.3', '3.4', '3.5', '3.6', '3.7', '3.8', '3.9', '3.10', '3.11')
        );
        $packages[] = new \CLAMP\Moodlegist\Package\Core($corepackage);

        $providers = array();

        foreach ($packages as $package) {
            $packageName = $package->getPackageName();
            $packagesData = $package->getPackages($uid);

            foreach ($packagesData as $packageName => $packageData) {
                $content = json_encode(array('packages' => array($packageName => $packageData)));
                $sha256 = hash('sha256', $content);
                file_put_contents("$basePath$packageName\$$sha256.json", $content);
                $providers[$this->getComposerProviderGroup($package)][$packageName] = array(
                    'sha256' => $sha256,
                );
            }
        }

        $table = new Table($output);
        $table->setHeaders(array('provider', 'packages', 'size'));

        $providerIncludes = array();
        foreach ($providers as $providerGroup => $providers) {
            $content = json_encode(array('providers' => $providers));
            $sha256 = hash('sha256', $content);
            file_put_contents("{$basePath}providers-$providerGroup\$$sha256.json", $content);

            $providerIncludes["p/providers-$providerGroup\$%hash%.json"] = array(
                'sha256' => $sha256,
            );

            $table->addRow(array(
                $providerGroup,
                count($providers),
                Helper::formatMemory(filesize("{$basePath}providers-$providerGroup\$$sha256.json")),
            ));
        }

        $table->render();

        $content = json_encode(array(
            'packages' => array(),
            'providers-url' => '/p/%package%$%hash%.json',
            'provider-includes' => $providerIncludes,
        ));

        // switch old and new files
        if ($fs->exists('web/p')) {
            $fs->rename('web/p', 'web/p.old');
        }
        $fs->rename($basePath, 'web/p/');
        file_put_contents('web/packages.json', $content);

        // this doesn't work
        // $fs->remove('web/p.old');

        exec('rm -rf web/p.old', $return, $code);

        $output->writeln("Wrote packages.json file");
    }
}
