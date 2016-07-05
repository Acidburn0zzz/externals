<?php
declare(strict_types = 1);

namespace Externals\Email;

use Doctrine\DBAL\Connection;

/**
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class EmailRepository
{
    /**
     * @var Connection
     */
    private $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    public function contains(string $emailId) : bool
    {
        return $this->db->fetchColumn('SELECT COUNT(id) FROM emails WHERE id = ?', [$emailId]) > 0;
    }

    /**
     * Returns a threaded view of the emails.
     *
     * @return ThreadItem[]
     */
    public function getThreadView(int $threadId) : array
    {
        $query = 'SELECT emails.*, IF(read_status.userId, 1, 0) as was_read
                  FROM emails
                  LEFT JOIN user_emails_read read_status ON read_status.emailId = emails.id
                  WHERE threadId = ?
                  ORDER BY date ASC';
        $emails = $this->db->fetchAll($query, [$threadId]);
        /** @var Email[] $emails */
        $emails = array_map([$this, 'createEmail'], $emails);

        // Index by ID
        /** @var ThreadItem[] $indexedThreadItem */
        $indexedThreadItem = [];
        foreach ($emails as $email) {
            $id = $email->getImapId() ?? $email->getId();
            $indexedThreadItem[$id] = new ThreadItem($email);
        }

        // Link each email to the one it replies to
        $rootItems = [];
        foreach ($indexedThreadItem as $item) {
            $replyId = $item->getEmail()->getInReplyTo();
            if ($replyId && isset($indexedThreadItem[$replyId])) {
                $indexedThreadItem[$replyId]->addReply($item);
            } else {
                $rootItems[] = $item;
            }
        }

        return $rootItems;
    }

    /**
     * Returns the number of emails in a thread.
     */
    public function getThreadCount(int $threadId) : int
    {
        return (int) $this->db->fetchColumn('SELECT COUNT(id) FROM emails WHERE threadId = ?', [$threadId]);
    }

    /**
     * @return Email[]
     */
    public function findAll() : array
    {
        return array_map([$this, 'createEmail'], $this->db->fetchAll('SELECT * FROM emails'));
    }

    public function add(Email $email)
    {
        $this->db->insert('emails', [
            'id' => $email->getId(),
            'subject' => $email->getSubject(),
            'content' => $email->getContent(),
            'originalContent' => $email->getOriginalContent(),
            'threadId' => $email->getThreadId(),
            'date' => $email->getDate(),
            'fromEmail' => $email->getFrom()->getEmail(),
            'fromName' => $email->getFrom()->getName(),
            'imapId' => $email->getImapId(),
            'inReplyTo' => $email->getInReplyTo(),
        ], [
            'string',
            'text',
            'text',
            'text',
            'integer',
            'datetime',
            'string',
            'string',
            'string',
            'string',
        ]);
    }

    public function updateContent(Email $email)
    {
        $this->db->update('emails', [
            'content' => $email->getContent(),
        ], ['id' => $email->getId()], ['text']);
    }

    private function createEmail(array $row) : Email
    {
        $date = $row['date'];
        if (is_string($date)) {
            $date = new \DateTimeImmutable($date);
        }

        $email = new Email(
            $row['id'],
            $row['subject'],
            $row['content'],
            $row['originalContent'],
            (int) $row['threadId'],
            $date,
            new EmailAddress($row['fromEmail'], $row['fromName']),
            $row['imapId'],
            $row['inReplyTo']
        );

        if (array_key_exists('was_read', $row) && $row['was_read']) {
            $email->markAsRead();
        }

        return $email;
    }
}
