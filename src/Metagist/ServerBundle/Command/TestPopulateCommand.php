<?php
/**
 * TestPopulateCommand.php
 * 
 * @package metagist-server
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
namespace Metagist\ServerBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Faker\Factory;

/**
 * Creates fake users and ratings.
 * 
 * @package metagist-server
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class TestPopulateCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('mg:populate')
            ->setDescription('Creates a lot of fake users, ratings, comments.');
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $factory = Factory::create();
        
        $em = $this->getContainer()->get('doctrine')->getManager();
        
        /* @var $packageRepo \Metagist\ServerBundle\Entity\PackageRepository */
        $packageRepo = $em->getRepository('MetagistServerBundle:Package');
        $packages    = $packageRepo->findAll();
        
        $users = array();
        for ($i=0; $i < 10; $i++) {
            $user = new \Metagist\ServerBundle\Entity\User($factory->userName, 'ROLE_USER', 'http://lorempixel.com/100/100/?'. uniqid());
            $em->persist($user);
            $users[] = $user;
            $output->writeln('added fake user ' . $user->getUsername());
        }
        $em->flush();
        
        foreach ($packages as $package) {
            foreach ($users as $user) {
                $rating = new \Metagist\ServerBundle\Entity\Rating();
                $rating->setPackage($package);
                $rating->setUser($user);
                $rating->setRating($factory->randomNumber(1, 5));
                $rating->setTitle($factory->sentence());
                $rating->setComment($factory->text());
                $em->persist($rating);
            }
        }
        $em->flush();
        
    }
}
