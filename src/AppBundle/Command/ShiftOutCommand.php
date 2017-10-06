<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ShiftOutCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('app:shiftout-warning')
            ->setDescription('Checks for unactive users and send a warning')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
       /* $userRepo = $this->getContainer()
                         ->get('app.user_activity.repository');

        $mailer = $this->getContainer()
                       ->get('app.mailer');*/

        $orgs = $this->getContainer()
                     ->get('app.organization.read_model.repository')
                     ->findAll();

        foreach ($orgs as $org) {

            dump($org->getSettings());



         /*   $usersToBeWarned = $userRepo->findUserWithLowActivity(
                $org->getId(),
                $org->getSettings()->get('shiftout_min_credits'),
                $org->getSettings()->get('shiftout_min_item'),
                $org->getSettings()->get('shiftout_days')
            );

            foreach ($usersToBeWarned as $user) {
                $mailer->sendWarning($user);
            }*/
        }

    }
}