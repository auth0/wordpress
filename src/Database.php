<?php

declare(strict_types=1);

namespace Auth0\WordPress;

use Throwable;

final class Database
{
    /**
     * @var string
     */
    public const CONST_TABLE_ACCOUNTS = 'accounts';

    /**
     * @var string
     */
    public const CONST_TABLE_LOG = 'log';

    /**
     * @var string
     */
    public const CONST_TABLE_OPTIONS = 'options';

    /**
     * @var string
     */
    public const CONST_TABLE_SYNC = 'sync';

    public function createTable(string $table)
    {
        if (self::CONST_TABLE_ACCOUNTS === $table) {
            return $this->createTableAccounts();
        }

        if (self::CONST_TABLE_SYNC === $table) {
            return $this->createTableSync();
        }
    }

    public function deleteRow(
        string $table,
        array $where,
        array $format,
    ): int | bool {
        return $this->getWpdb()->delete($table, $where, $format);
    }

    public function getTableName(
        string $table,
    ): string {
        return $this->getWpdb()->prefix . 'auth0_' . $table;
    }

    public function insertRow(
        string $table,
        array $data,
        array $formats,
    ): int | bool {
        try {
            return $this->getWpdb()->insert($table, $data, $formats);
        } catch (Throwable) {
            return false;
        }
    }

    public function selectDistinctResults(
        string $select,
        string $from,
        string $query,
        array $args = [],
    ): array | object | null {
        $query = $this->getWpdb()->prepare($query, ...$args);

        return $this->getWpdb()->get_results(sprintf('SELECT DISTINCT %s FROM %s ', $select, $from) . $query);
    }

    public function selectResults(
        string $select,
        string $from,
        string $query,
        array $args = [],
    ): array | object | null {
        $query = $this->getWpdb()->prepare($query, ...$args);

        return $this->getWpdb()->get_results(sprintf('SELECT %s FROM %s ', $select, $from) . $query);
    }

    public function selectRow(
        string $select,
        string $from,
        string $query,
        array $args = [],
    ): array | object | null {
        $query = $this->getWpdb()->prepare($query, ...$args);

        return $this->getWpdb()->get_row(sprintf('SELECT %s FROM %s ', $select, $from) . $query);
    }

    private function createTableAccounts()
    {
        $charset = $this->getWpdb()->get_charset_collate();
        $table = $this->getTableName(self::CONST_TABLE_ACCOUNTS);

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        return maybe_create_table(
            $table,
            sprintf('CREATE TABLE %s (
            id BIGINT NOT NULL AUTO_INCREMENT,
            site TINYINT NOT NULL,
            blog BIGINT NOT NULL,
            user BIGINT NOT NULL,
            auth0 TEXT NOT NULL,
            PRIMARY KEY (id)
        )' . $charset . ';', $table),
        );
    }

    private function createTableSync()
    {
        $charset = $this->getWpdb()->get_charset_collate();
        $table = $this->getTableName(self::CONST_TABLE_SYNC);

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        return maybe_create_table(
            $table,
            sprintf('CREATE TABLE %s (
            id BIGINT NOT NULL AUTO_INCREMENT,
            site TINYINT NOT NULL,
            blog BIGINT NOT NULL,
            created INT(11) NOT NULL,
            payload TEXT NOT NULL,
            hashsum VARCHAR(64) NOT NULL UNIQUE,
            locked INT(1) NOT NULL,
            PRIMARY KEY (id)
        )' . $charset . ';', $table),
        );
    }

    private function getWpdb(): object
    {
        global $wpdb;

        return $wpdb;
    }
}
