<?php
declare(strict_types = 1);

namespace Externals\Application\Database;

use Doctrine\DBAL\Schema\Schema;

/**
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class SchemaDefinition
{
    public function define(Schema $schema)
    {
        // Emails tables
        $emailsTable = $schema->createTable('emails');
        $emailsTable->addColumn('id', 'string');
        $emailsTable->addColumn('number', 'integer', ['unsigned' => true]);
        $emailsTable->addColumn('subject', 'text');
        $emailsTable->addColumn('threadId', 'string', ['notnull' => false]);
        $emailsTable->addColumn('date', 'datetime');
        $emailsTable->addColumn('content', 'text');
        $emailsTable->addColumn('source', 'text');
        $emailsTable->addColumn('fromEmail', 'string', ['notnull' => false]);
        $emailsTable->addColumn('fromName', 'string', ['notnull' => false]);
        $emailsTable->addColumn('inReplyTo', 'string', ['notnull' => false]);
        $emailsTable->setPrimaryKey(['id']);
        $emailsTable->addUniqueIndex(['number']);
        $emailsTable->addIndex(['threadId']);
        $emailsTable->addIndex(['inReplyTo']);

        // Threads table
        // @deprecated Kept for keeping the old URLs
        $threadsTable = $schema->createTable('threads');
        $threadsTable->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
        $threadsTable->addColumn('subject', 'text');
        $threadsTable->setPrimaryKey(['id']);

        // Users table
        $usersTable = $schema->createTable('users');
        $usersTable->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
        $usersTable->addColumn('githubId', 'string');
        $usersTable->addColumn('name', 'string');
        $usersTable->setPrimaryKey(['id']);
        $usersTable->addIndex(['githubId']);

        // Thread reading status table
        // @deprecated To delete
        $userThreadsReadTable = $schema->createTable('user_threads_read');
        $userThreadsReadTable->addColumn('userId', 'integer', ['unsigned' => true]);
        $userThreadsReadTable->addColumn('threadId', 'integer', ['unsigned' => true]);
        $userThreadsReadTable->addColumn('emailsRead', 'integer');
        $userThreadsReadTable->setPrimaryKey(['userId', 'threadId']);

        // Email reading status table
        $userEmailsReadTable = $schema->createTable('user_emails_read');
        $userEmailsReadTable->addColumn('userId', 'integer', ['unsigned' => true]);
        $userEmailsReadTable->addColumn('emailId', 'string');
        $userEmailsReadTable->setPrimaryKey(['userId', 'emailId']);
    }
}
