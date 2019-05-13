<?php

namespace Task;

/**
 * Definition of Model Class.
 */
class Model {

  private $connection;

  /**
   * Construct function.
   */
  public function __construct(array $config) {
    $this->connection = new \PDO("mysql:host={$config['host']};dbname={$config['db']}", $config['user'], $config['passowrd']);
  }

  /**
   * Execute one of the CRUD functions.
   */
  public function execute($action, array $params = []) {
    if ($this->connection) {
      return $this->$action($params);
    }
    else {
      return ['error' => "Can not connect to database"];
    }
  }

  /**
   * Helper to run queries.
   */
  public function runQuery($query, $params = [], $return = FALSE) {
    $stmt = $this->connection->prepare($query);

    foreach ($params as $key => $value) {
      $stmt->bindValue($key, $value);
    }

    $data = $stmt->execute();

    if ($return) {
      $records = [];
      while ($row = $stmt->fetch()) {
        $records[] = [
          'date_start' => $row['date_start'],
          'date_end' => $row['date_end'],
          'price' => $row['price'],
          'id' => $row['id'],
        ];
      }

      return $records;
    }
  }

  /**
   * Return all records of the database.
   */
  private function get() {
    return $this->runQuery("SELECT * FROM records ORDER BY date_start", [], TRUE);
  }

  /**
   * Do all the correct validations to add a new record in the database.
   */
  private function create($params = []) {

    /* Delete any record inside the new one.
     * Input: New record from 2019-01-01 to 2019-01-30
     * Action: Delete all records inside that range Ex: [2019-01-02, 2019-01-10]
     */

    $sql = "DELETE FROM records WHERE  date_start >= :date_start AND date_end <= :date_end";
    $this->runQuery($sql, [
      ':date_start' => $params['date_start'],
      ':date_end' => $params['date_end'],
    ]);

    $date_start = strtotime($params['date_start']);
    $low_limit = $date_start - 86400;
    $low_date = date('Y-m-d', $low_limit);
    $date_end = strtotime($params['date_end']);
    $high_limit = $date_end + 86400;
    $high_date = date('Y-m-d', $high_limit);

    /* Query all entires that could be crossing intervals.
     * We also query some other entry that could be close to the new one
     * and have the same price value.
     */

    $query = "
      SELECT * 
      FROM records 
      WHERE (date_start <= :date_start AND :date_start <= date_end )
      OR (date_start <= :date_end AND :date_end <= date_end )
      OR (price = :price)
      ORDER BY date_start
    ";

    $formatted_params = [
      ':date_start' => $params['date_start'],
      ':date_end' => $params['date_end'],
      ':price' => $params['price'],
    ];

    $records = $this->runQuery($query, $formatted_params, TRUE);

    $insert = TRUE;
    $previous = FALSE;

    foreach ($records as $row) {
      $start = strtotime($row['date_start']);
      $end = strtotime($row['date_end']);

      /* Update the end date of an entry that overlaps on the left.
       * In case the price is the same instead of adding a new one
       * we change the end date to the end date of the  new entry.
       */

      if ($start < $low_limit && $end >= $low_limit) {
        if ($params['price'] == $row['price']) {
          $end = $date_end;
          $insert = FALSE;
          $previous = $row;
        }
        else {
          $end = $low_limit;
        }
      }

      /* Update the start date of an entry that overlaps on the right.
       * In case the price is the same instead of adding a new one
       * we change the start date to the start date of the  new entry.
       */

      if ($start <= $high_limit && $end > $high_limit) {
        if ($params['price'] == $row['price']) {
          $start = $date_start;
          $insert = FALSE;
        }
        else {
          /* There is a case when the new entry overlaps only on a samll range
           * of the previous entry in that case we create a new one.
           * Input: Previous entry [1 - 23] - New Entry [2 - 12]
           * Result: [1 - 1] [2 - 12] [ 13 - 23]
           */

          if ($start <= $low_limit) {
            $low = date('Y-m-d', $start);
            $high = date('Y-m-d', $low_limit);

            $this->runQuery("INSERT INTO records(date_start,date_end,price) VALUES(:date_start,:date_end,:price)", [
              ':date_start' => $low,
              ':date_end' => $high,
              ':price' => $row['price'],
            ]);
          }
          $start = $high_limit;
        }
      }

      $start = date('Y-m-d', $start);
      $end = date('Y-m-d', $end);

      if ($previous && ($previous['price'] == $row['price']) && ($previous['id'] != $row['id'])) {
        /* There is a case when the previous entry, the new entry,
         * and a following entry theses three has the same price value
         * in that case we merge the entries in one
         * Input: Current enties [1 - 5] [10 - 15], New Entry [5 - 10]
         * Result: [1 - 15]
         */
        $this->runQuery("UPDATE records SET  date_end = :date_end WHERE id = :id", [
          ':date_end' => $end,
          ':id' => $previous['id'],
        ]);

        $this->runQuery("DELETE FROM records WHERE id = :id", [':id' => $row['id']]);
        $previous = FALSE;
      }
      elseif ($start != $row['date_start'] || $end != $row['date_end']) {
        $this->runQuery("UPDATE records SET date_start = :date_start, date_end = :date_end WHERE id = :id", [
          ':date_start' => $start,
          ':date_end' => $end,
          ':id' => $row['id'],
        ]);
      }
    }

    if ($insert) {
      $this->runQuery("INSERT INTO records(date_start,date_end,price) VALUES(:date_start,:date_end,:price)", $formatted_params);
    }

    return ['create' => TRUE];
  }

  /**
   * Update a record by deleting the previouso and running a new create.
   */
  private function update($params = []) {
    $this->delete($params);
    $this->create($params);
    return ['update' => TRUE];
  }

  /**
   * Delete one or all records.
   */
  private function delete($params = []) {
    if ($params['id'] == 'all') {
      $sql = "TRUNCATE records";
      $sql_params = [];
    }
    elseif (is_numeric($params['id'])) {
      $sql = "DELETE FROM records WHERE id = :id";
      $sql_params[':id'] = $params['id'];
    }
    else {
      return ['error' => 'Record not found.'];
    }

    $this->runQuery($sql, $sql_params);
    return ['delete' => TRUE];
  }

}
