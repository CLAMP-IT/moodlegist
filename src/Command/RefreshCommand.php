<?php

namespace CLAMP\Moodlegist\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RefreshCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('refresh')
            ->setDescription('Refresh list of plugins from Moodle plugins database');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        /**
         * @var \PDO $db
         */
        $db = $this->getApplication()->getSilexApplication()['db'];

        $updateStmt = $db->prepare('UPDATE packages SET newest_version = :newest_version, versions = :versions WHERE type = :type AND name = :name AND frankenstyle_name = :frankenstyle_name');
        $insertStmt = $db->prepare('INSERT INTO packages (type, name, frankenstyle_name, newest_version, versions) VALUES (:type, :name, :frankenstyle_name, :newest_version, :versions)');

        $url = 'https://download.moodle.org/api/1.3/pluglist.php';
        $json = file_get_contents($url);

        $plugin_list = json_decode($json);
        $output->writeln("Updating database");

        $db->beginTransaction();
        $newCount = 0;
        $updateCount =  0;
        foreach ($plugin_list->plugins as $plugin) {
            if (empty($plugin->component)) {
                continue;
            }
            list($type, $name) = explode('_', $plugin->component, 2);

            /*if (!array_key_exists($type, $types)) {
                continue;
            }*/
            $newest_version = end($plugin->versions)->version;
            $params = array(':type' => $type, ':name' => (string) $name, ':frankenstyle_name' => (string) $plugin->component, ':newest_version' => (int) $newest_version, ':versions' => json_encode($plugin->versions));

            $updateStmt->execute($params);
            if ($updateStmt->rowCount() == 0) {
                $insertStmt->execute($params);
                $newCount++;
            } else {
                $updateCount++;
            }
        }
        $db->commit();

        $output->writeln("Found $newCount new and $updateCount updated plugins");
    }
}
