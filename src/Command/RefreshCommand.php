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

#[AsCommand(
    name: 'refresh',
    description: 'Refresh list of plugins from Moodle plugins database',
)]
class RefreshCommand extends Command
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;

        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $em = $this->entityManager;
        $io = new SymfonyStyle($input, $output);
        $url = 'https://download.moodle.org/api/1.3/pluglist.php';
        $io->note("Retrieving plugin list from Moodle.org");
        $json = file_get_contents($url);
        $plugin_list = json_decode($json);

        $io->note("Updating database");
        $newCount = 0;
        $updateCount =  0;
        $repo = $em->getRepository(Packages::class);
        foreach($plugin_list->plugins as $plugin) {
            if (empty($plugin->component)) {
                continue;
            }
            list($type, $name) = explode('_', $plugin->component, 2);
            $newest_version = (int)end($plugin->versions)->version;
            $versions = json_encode($plugin->versions);

            $package = new Packages();
            $package->setType($type);
            $package->setName($name);
            $package->setNewestVersion($newest_version);
            $package->setVersions($versions);

            $result = $repo->findBy(['type' => $type, 'name' => $name]);
            if (!$result) {
                // New item.
                $em->persist($package);
                $newCount++;
            } else {
                if($result[0]->isEqual($package)) {
                    continue;
                } else {
                    $result[0]->setNewestVersion($newest_version);
                    $result[0]->setVersions($versions);
                    $em->persist($result[0]);
                    $updateCount++;
                }
            }
        }
        $em->flush();
        $io->success(sprintf('Found %d new and %d updated plugins', $newCount, $updateCount));

        return Command::SUCCESS;
    }
}
