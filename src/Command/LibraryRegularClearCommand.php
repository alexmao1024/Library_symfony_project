<?php

namespace App\Command;

use App\Entity\NormalUser;
use App\Entity\Subscribe;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'library:regular-clear',
    description: 'This command can clean up those people whose overdue subscribed records.',
)]
class LibraryRegularClearCommand extends Command
{

    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'NormalUser\'s id, manual delete someone\'s records' )
            ->addOption('automatic', null, InputOption::VALUE_NONE, 'This option can automatic detect overdue subscribed records and delete them.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $arg1 = $input->getArgument('arg1');

        if ($arg1) {
            $clearUser = $this->entityManager->getRepository(NormalUser::class)->find($arg1);
            $this->entityManager->remove($clearUser->getSubscribe());
            $this->entityManager->remove($clearUser->getMessages());
            $this->entityManager->flush();
            $io->note(sprintf('You passed an userId: %s', $arg1));
        }

        if ($input->getOption('automatic')) {
            $nowDate = DateTime::createFromFormat('Y-m-d H:i:s', date('Y-m-d H:i:s'));
            $subscribes = $this->entityManager->getRepository(Subscribe::class)->findBy(['status' => 'sent']);
            foreach ( $subscribes as $subscribe)
            {
                $interval = (int)$nowDate->diff($subscribe->getSentAt())->format('%a');
                if ($interval >= 2)
                {
                    $this->entityManager->remove($subscribe);
                    $this->entityManager->remove($subscribe->getNormalUser()->getMessages());
                    $this->entityManager->flush();
                }
            }
            $io->note('You automatic deleted!');
        }

        $io->success('Successfully!');

        return Command::SUCCESS;
    }
}
