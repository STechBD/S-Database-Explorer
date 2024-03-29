<?php


/**
 * Project: S Database Explorer (SDE)
 * Description: S Database Explorer (SDE) is a simple and lightweight MySQL database explorer library based on PHP and PDO.
 * Version: 3.0.1
 * Version Code: 4
 * Since: 1.0.0
 * Author: Md. Ashraful Alam Shemul
 * Email: ceo@stechbd.net
 * Website: https://www.stechbd.net/project/SDE/
 * Developer: S Technologies
 * Homepage: https://www.stechbd.net
 * Contact: product@stechbd.net
 * Created: August 14, 2015
 * Modified: August 15, 2023
 */


namespace STechBD;

use PDO;
use PDOException;
use PDOStatement;
use JsonException;


/**
 * The main class of S Database Explorer.
 *
 * @since 1.0.0
 */
class SDE
{
	private PDO $connection;
	private string $prefix = '';

	/**
	 * The construction method to load PDO.
	 *
	 * @param string $name
	 * @param string $username
	 * @param string $password
	 * @param string $host
	 * @param false|string $prefix
	 *
	 * @since 1.0.0
	 */
	public function __construct(string $name, string $username = 'root', string $password = '', string $host = 'localhost', false|string $prefix = false)
	{
		try {
			$this->connection = new PDO("mysql:host=$host;dbname=$name;", $username, $password);
			$this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

			if ($prefix) {
				$this->prefix = $prefix . '_';
			}
		} catch (PDOException $e) {
			throw new PDOException($e->getMessage());
		}
	}

	/**
	 * Method to insert a row in a Database Table.
	 *
	 * @param string $table
	 * @param string $column
	 * @param string $values
	 * @param array $parameters
	 *
	 * @return false|PDOStatement
	 * @throws PDOException
	 * @since 1.0.0
	 */
	public function insert(string $table, string $column, string $values, array $parameters = []): false|PDOStatement
	{
		try {
			$statement = 'INSERT INTO `' . $this->prefix . $table . '` (' . $column . ') VALUES (' . $values . ')';

			$this->executeStatement($statement, $parameters);

			return $this->last();
		} catch (PDOException $e) {
			throw new PDOException($e->getMessage());
		}
	}

	/**
	 * Method to select row(s) from a Database Table.
	 *
	 * @param string $column
	 * @param string $table
	 * @param bool|string $condition
	 * @param bool $limit
	 * @param string|bool $order
	 * @param false|string $offset
	 * @param array $parameters
	 *
	 * @return array|false
	 * @since 1.0.0
	 */
	public function select(string $column, string $table, false|string $condition = false, false|string $limit = false, false|string $order = false, false|string $offset = false, array $parameters = []): false|array
	{
		try {
			$statement = 'SELECT ' . $column . ' FROM `' . $this->prefix . $table . '`';

			if ($condition) {
				$statement .= ' WHERE ' . $condition;
			}

			if ($limit) {
				$statement .= ' LIMIT ' . $limit;
			}

			if ($order) {
				$statement .= ' ORDER BY ' . $condition;
			}

			if ($offset) {
				$statement .= ' OFFSET ' . $offset;
			}

			return $this->executeStatement($statement, $parameters)->fetchAll();
		} catch (PDOException $e) {
			throw new PDOException($e->getMessage());
		}
	}

	/**
	 * Method to update a row in a Database Table.
	 *
	 * @param string $table
	 * @param string $set
	 * @param string $condition
	 * @param array $parameters
	 *
	 * @return void
	 * @throws PDOException
	 * @since 1.0.0
	 */
	public function update(string $table, string $set, string $condition, array $parameters = []): void
	{
		try {
			$statement = 'UPDATE ' . $this->prefix . $table . ' SET ' . $set . ' WHERE ' . $condition;

			$this->executeStatement($statement, $parameters);
		} catch (PDOException $e) {
			throw new PDOException($e->getMessage());
		}
	}

	/**
	 * Method to remove a row from a Database Table.
	 *
	 * @param string $table
	 * @param string $condition
	 * @param array $parameters
	 *
	 * @return false|PDOStatement
	 * @throws PDOException
	 * @since 1.0.0
	 */
	public function remove(string $table, string $condition, array $parameters = []): false|PDOStatement
	{
		try {
			$statement = 'DELETE FROM ' . $this->prefix . $table . ' WHERE ' . $condition;

			return $this->executeStatement($statement, $parameters);
		} catch (PDOException $e) {
			throw new PDOException($e->getMessage());
		}
	}

	/**
	 * Method to run a custom query.
	 *
	 * @param string $statement
	 * @param array $parameters
	 *
	 * @return false|PDOStatement
	 * @throws PDOException
	 * @since 3.0.0
	 */
	public function run(string $statement, array $parameters = []): false|PDOStatement
	{
		try {
			return $this->executeStatement($statement, $parameters);
		} catch (PDOException $e) {
			throw new PDOException($e->getMessage());
		}
	}

	/**
	 * Method to collect JSON output from a query.
	 *
	 * @param array $array
	 *
	 * @return string|false
	 * @throws JsonException
	 * @since 3.0.0
	 */
	public function json(array $array): false|string
	{
		return json_encode($array, JSON_THROW_ON_ERROR);
	}

	/**
	 * Method to get the last inserted ID.
	 *
	 * @return string
	 * @since 3.0.0
	 */
	public function last(): string
	{
		return $this->connection->lastInsertId();
	}

	/**
	 * Method to get the number of row(s).
	 * @param string $table
	 * @param string $condition
	 * @param array $parameters
	 *
	 * @return int
	 * @throws PDOException
	 * @since 3.0.0
	 */
	public function count(string $table, string $condition = '', array $parameters = []): int
	{
		try {
			$statement = 'SELECT COUNT(*) FROM ' . $this->prefix . $table;

			if ($condition) {
				$statement .= ' WHERE ' . $condition;
			}

			$stmt = $this->connection->prepare($statement);
			$stmt->execute($parameters);

			return $stmt->fetchColumn();
		} catch (PDOException $e) {
			throw new PDOException($e->getMessage());
		}
	}

	/**
	 * Method to get the summation of a column.
	 *
	 * @param string $table
	 * @param string $column
	 * @param string $condition
	 * @param array $parameters
	 *
	 * @return int
	 * @throws PDOException
	 * @since 3.0.0
	 */
	public function sum(string $table, string $column, string $condition = '', array $parameters = []): int
	{
		try {
			$statement = 'SELECT SUM(' . $column . ') FROM ' . $this->prefix . $table;

			if ($condition) {
				$statement .= ' WHERE ' . $condition;
			}

			$stmt = $this->connection->prepare($statement);
			$stmt->execute($parameters);

			return $stmt->fetchColumn();
		} catch (PDOException $e) {
			throw new PDOException($e->getMessage());
		}
	}

	/**
	 * Method to execute a statement. This method is used internally.
	 *
	 * @param string $statement
	 * @param array $parameters
	 *
	 * @return false|PDOStatement
	 * @throws PDOException
	 * @since 3.0.0
	 */
	private function executeStatement(string $statement, array $parameters = []): false|PDOStatement
	{
		try {
			$stmt = $this->connection->prepare($statement);
			$stmt->execute($parameters);

			return $stmt;
		} catch (PDOException $e) {
			throw new PDOException($e->getMessage());
		}
	}
}
