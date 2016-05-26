<?php

namespace Outlandish\Wpackagist\Service;

use Silex\Provider\DoctrineServiceProvider as BaseDoctrineServiceProvider;
use Silex\Application;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Event\ConnectionEventArgs;

class DoctrineServiceProvider extends BaseDoctrineServiceProvider
{
    public function register(Application $app)
    {
        parent::register($app);

        $provider = $this;

        $app['db.event_manager'] = $app->share($app->extend('db.event_manager', function ($manager, $app) use ($provider) {
            $manager->addEventListener('postConnect', $provider);

            return $manager;
        }));
    }

    public function postConnect(ConnectionEventArgs $args)
    {
        $this->migrate($args->getConnection());
    }

    protected function migrate(Connection $conn)
    {
        $updated_to = $current = $this->getSchemaVersion($conn);
        $version = $current + 1;
        $method = "migrateTo$version";

        while (method_exists($this, $method)) {
            $this->$method($conn);
            $updated_to = $version;
            $method = "migrateTo".++$version;
        }

        if ($updated_to > $current) {
            $this->setSchemaVersion($conn, $updated_to);
        }
    }

    protected function getSchemaVersion(Connection $conn)
    {
        if ($conn->query("SELECT tbl_name FROM sqlite_master WHERE tbl_name = 'schema_version'")->fetchColumn()) {
            return $conn->query('SELECT version FROM schema_version')->fetchColumn();
        } else {
            return 0;
        }
    }

    protected function setSchemaVersion(Connection $conn, $version)
    {
        $version = (int) $version;

        $conn->exec("DELETE FROM schema_version");
        $conn->exec("INSERT INTO schema_version (version) VALUES ($version)");
    }

    protected function migrateTo1(Connection $conn)
    {
        $conn->exec('
            CREATE TABLE IF NOT EXISTS packages (
                type TEXT,
                name TEXT,
                frankenstyle_name TEXT,
                newest_version INT,
                versions TEXT,

                PRIMARY KEY (type, name, frankenstyle_name)
            );

            CREATE TABLE IF NOT EXISTS schema_version (
                version INT,

                PRIMARY KEY (version)
            );
        ');
    }
}
