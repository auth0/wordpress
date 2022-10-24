<?php

declare(strict_types=1);

namespace Auth0\WordPress;

final class Database
{
    public const CONST_TABLE_OPTIONS = 'options';
    public const CONST_TABLE_ACCOUNTS = 'accounts';
    public const CONST_TABLE_SYNC = 'sync';
    public const CONST_TABLE_LOG = 'log';

    public function createTable(string $table)
    {
        if ($table === self::CONST_TABLE_ACCOUNTS) {
            return $this->createTableAccounts();
        }

        if ($table === self::CONST_TABLE_SYNC) {
            return $this->createTableSync();
        }
    }

    public function insertRow(
        string $table,
        array $data,
        array $formats
    ): int|bool {
        try {
            return $this->getWpdb()->insert($table, $data, $formats);
        } catch (\Throwable $th) {
            return false;
        }
    }

    public function selectRow(
        string $select,
        string $from,
        string $query,
        ...$args
    ): array|object|null {
        $query = $this->getWpdb()->prepare($query, ...$args);
        return $this->getWpdb()->get_row(sprintf('SELECT %s FROM %s ', $select, $from) . $query);
    }

    public function deleteRow(
        string $table,
        array $where,
        array $format
    ): int|bool {
        return $this->getWpdb()->delete($table, $where, $format);
    }

    public function selectResults(
        string $select,
        string $from,
        string $query,
        ...$args
    ): array|object|null {
        $query = $this->getWpdb()->prepare($query, ...$args);
        return $this->getWpdb()->get_results(sprintf('SELECT %s FROM %s ', $select, $from) . $query);
    }

    public function selectDistinctResults(
        string $select,
        string $from,
        string $query,
        ...$args
    ): array|object|null {
        $query = $this->getWpdb()->prepare($query, ...$args);
        return $this->getWpdb()->get_results(sprintf('SELECT DISTINCT %s FROM %s ', $select, $from) . $query);
    }

    public function getTableName(
        string $table
    ): string {
        return $this->getWpdb()->prefix . 'auth0_' . $table;
    }

    private function getWpdb(): object
    {
        global $wpdb;
        return $wpdb;
    }

    private function createTableAccounts()
    {
        $charset = $this->getWpdb()->get_charset_collate();
        $table = $this->getTableName(self::CONST_TABLE_ACCOUNTS);

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        return \maybe_create_table(
            $table,
            sprintf('CREATE TABLE %s (
            id BIGINT NOT NULL AUTO_INCREMENT,
            site TINYINT NOT NULL,
            blog BIGINT NOT NULL,
            user BIGINT NOT NULL,
            auth0 TEXT NOT NULL,
            PRIMARY KEY (id)
        )' . $charset . ';', $table)
        );
    }

    private function createTableSync()
    {
        $charset = $this->getWpdb()->get_charset_collate();
        $table = $this->getTableName(self::CONST_TABLE_SYNC);

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        return \maybe_create_table(
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
        )' . $charset . ';', $table)
        );
    }
}
