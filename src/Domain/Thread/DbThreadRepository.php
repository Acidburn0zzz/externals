<?php
declare(strict_types = 1);

namespace Externals\Domain\Thread;

use Doctrine\DBAL\Connection;

/**
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class DbThreadRepository implements ThreadRepository
{
    /**
     * @var Connection
     */
    private $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    public function findBySubject(string $subject)
    {
        $threadId = $this->db->fetchColumn('SELECT id FROM threads WHERE subject = ?', [$subject]);

        return (int) $threadId;
    }

    public function create(string $subject) : int
    {
        $this->db->insert('threads', [
            'subject' => $subject,
        ]);

        return (int) $this->db->lastInsertId();
    }
}
