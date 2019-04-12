<?php
/**
 * @author    Aaron Scherer <aequasi@gmail.com>
 * @date      2013
 * @license   http://www.apache.org/licenses/LICENSE-2.0.html Apache License, Version 2.0
 */

namespace Aequasi\Bundle\MemcachedBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * GetCommand
 *
 * Grabs the given key out of cache
 */
class GetCommand extends ContainerAwareCommand
{

    /**
     *
     */
    protected function configure()
    {
        $this->setName('memcached:get')
            ->setDescription('Get a key\'s value from memcached')
            ->addArgument('cluster', InputArgument::REQUIRED, 'What cluster do you want to use')
            ->addArgument('key', InputArgument::REQUIRED, 'What key do you want to get');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $key = $input->getArgument('key');
        $cluster = $input->getArgument('cluster');

        try {
            $memcached = $this->getContainer()->get('memcached.' . $cluster);
            $value = $memcached->get($key);
            if ($memcached->hasError()) {
                $output->writeln(sprintf('<error>%s</error>', $memcached->getError()));
            } else {
                $output->writeln(sprintf('<info>Key: %s</info>', $key));
                $output->writeln(sprintf('<info>Value: %s</info>', $value));
            }
        } catch (ServiceNotFoundException $e) {
            $output->writeln("<error>cluster '{$cluster}' is not found</error>");
        }
        $output->writeln("\n");
    }
}
