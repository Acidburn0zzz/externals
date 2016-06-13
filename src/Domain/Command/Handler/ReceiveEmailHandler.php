<?php
declare(strict_types = 1);

namespace Externals\Domain\Command\Handler;

use Doctrine\DBAL\Connection;
use Externals\Domain\Command\ReceiveEmail;
use Externals\Domain\Email\EmailContentParser;
use Externals\Domain\Email\EmailRepository;
use Externals\Domain\Email\EmailSubjectParser;
use Externals\Domain\Thread\ThreadRepository;

/**
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class ReceiveEmailHandler
{
    /**
     * @var Connection
     */
    private $db;

    /**
     * @var ThreadRepository
     */
    private $threadRepository;

    /**
     * @var EmailRepository
     */
    private $emailRepository;

    /**
     * @var EmailSubjectParser
     */
    private $subjectParser;

    /**
     * @var EmailContentParser
     */
    private $contentParser;

    public function __construct(
        Connection $db,
        ThreadRepository $threadRepository,
        EmailRepository $emailRepository,
        EmailSubjectParser $subjectParser,
        EmailContentParser $contentParser
    ) {
        $this->db = $db;
        $this->threadRepository = $threadRepository;
        $this->emailRepository = $emailRepository;
        $this->subjectParser = $subjectParser;
        $this->contentParser = $contentParser;
    }

    public function __invoke(ReceiveEmail $receiveEmail)
    {
        $email = $receiveEmail->getEmail();

        // Check if we have already received the email
        if ($this->emailRepository->contains((string) $email->getId())) {
            return;
        }

        $threadSubject = $this->subjectParser->sanitize($email->getSubject());
        $content = $this->contentParser->parse($email->getTextContent());

        $threadId = $this->threadRepository->findBySubject($threadSubject);
        if (!$threadId) {
            // New thread
            $threadId = $this->threadRepository->create($threadSubject);
        }

        $this->db->insert('emails', [
            'id' => $email->getId(),
            'subject' => $email->getSubject(),
            'content' => $content,
            'originalContent' => $email->getTextContent(),
            'threadId' => $threadId,
            'date' => $email->getDate(),
        ], [
            'string',
            'text',
            'text',
            'text',
            'integer',
            'datetime',
        ]);
    }
}
