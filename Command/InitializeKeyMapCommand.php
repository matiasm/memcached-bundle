<?php
/**
 * @author    Aaron Scherer <aequasi@gmail.com>
 * @date      2013
 * @license   http://www.apache.org/licenses/LICENSE-2.0.html Apache License, Version 2.0
 */

namespace Aequasi\Bundle\MemcachedBundle\Command;

use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * ClearCommand
 *
 * Flushed the given memcached cluster
 */
class InitializeKeyMapCommand extends ContainerAwareCommand
{

    /**
     *
     */
    protected function configure()
    {
        $this
            ->setName('memcached:initialize:keymap')
            ->setDescription('Initialize the Memcached Mysql Key Map')
            ->addArgument('cluster', InputArgument::REQUIRED, 'What cluster do you want to use');
    }


    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $cluster = $input->getArgument('cluster');
        try {
            $memcached = $this->getContainer()->get('memcached.' . $cluster);

            /** @var Connection $connection */
            $connection = $memcached->getKeyMapConnection();

            $sql = <<<SQL
CREATE TABLE `memcached_key_map` (
`id` BIGINT(32) UNSIGNED NOT NULL AUTO_INCREMENT,
`cache_key` VARCHAR(255) NOT NULL,
`category` VARCHAR(255),
`description` VARCHAR(2048),
`memory_size` BIGINT(32) UNSIGNED,
`lifeTime` INT(11) UNSIGNED,
`expiration` DATETIME,
`insert_date` DATETIME NOT NULL,
PRIMARY KEY (`id`),
INDEX (`cache_key`),
INDEX (`expiration`),
INDEX (`insert_date`)
) ENGINE=INNODB;
SQL;

            $output->writeln("Attempting to create `memcached_key_map` table");
            $connection->executeQuery("DROP TABLE IF EXISTS `memcached_key_map`;");
            $connection->executeQuery($sql);

        } catch (ServiceNotFoundException $e) {
            $output->writeln("<error>cluster '{$cluster}' is not found</error>");
        }
        $output->writeln("\n");
    }
}
