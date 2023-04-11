<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Packages;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Filesystem\Filesystem;

#[AsCommand(
    name: 'build',
    description: 'Build package.json from DB',
)]
class BuildCommand extends Command
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;

        parent::__construct();
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

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->note("Building packages");

        $fs = new Filesystem();
        $basePath = 'public/p.new/';
        $fs->mkdir($basePath.'moodle');
        $fs->mkdir($basePath.'moodle-plugin-db');

        // Get all the packages.
        $em = $this->entityManager;
        $packages = $em->getRepository(Packages::class)->findAll();

        // Composer requires this.
        $uid = 1;

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
        if ($fs->exists('public/p')) {
            $fs->rename('public/p', 'public/p.old');
        }
        $fs->rename($basePath, 'public/p/');
        file_put_contents('public/packages.json', $content);
        $fs->remove('public/p.old');

        $io->success('Wrote packages.json file');

        return Command::SUCCESS;
    }
}
