<?php

namespace App\Command;

use App\Entity\Email;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'pager:fetch-emails',
    description: 'Fetches emails from mail server',
)]
class FetchEmailsCommand extends Command
{
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $entityManager;

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    /**
     * @throws \DateMalformedStringException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $imapHost = $_ENV['IMAP_HOST'];
        $username = $_ENV['IMAP_USERNAME'];
        $password = $_ENV['IMAP_PASSWORD'];

        $io = new SymfonyStyle($input, $output);

        $mailbox = imap_open($imapHost, $username, $password);

        if (!$mailbox) {
            $io->error('Ошибка подключения: ' . imap_last_error());

            return Command::FAILURE;
        }

        $emails = imap_search($mailbox, 'FROM "' . $_ENV['EMAIL_FROM'] . '"');

        if (!$emails) {
            $io->info('Нет свежих сообщений от ' . $_ENV['EMAIL_FROM']);

            return Command::INVALID;
        }

        $progressBar = $io->createProgressBar(count($emails));

        foreach ($emails as $emailId) {
            $header = imap_headerinfo($mailbox, $emailId);
            $body = imap_fetchbody($mailbox, $emailId, 1);
            $body = mb_convert_encoding($body, "utf-8", "windows-1251");
            $idFound = preg_match('/\d{3,7}/', $header->subject, $matches);

            $emailEntity = new Email();
            $emailEntity->setSubject($header->subject);
            $emailEntity->setSender($header->fromaddress);
            $emailEntity->setBody($body);
            $emailEntity->setReceivedAt(new DateTimeImmutable($header->date, new \DateTimeZone('Asia/Yakutsk')));

            if ($idFound) {
                $senderId = $matches[0] ?? null;
                $emailEntity->setSenderId($senderId);
            }

            $this->entityManager->persist($emailEntity);
            $progressBar->advance();

            if ($_ENV['IMAP_DELETE']) {
                imap_delete($mailbox, $emailId);
            }
        }

        $this->entityManager->flush();
        if ($_ENV['IMAP_DELETE']) {
            imap_expunge($mailbox);
        }

        imap_close($mailbox);
        $progressBar->finish();
        $io->success('Сообщения успешно загружены!');

        return Command::SUCCESS;
    }
}

