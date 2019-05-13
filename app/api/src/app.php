<?php

namespace Task;

/**
 * Definition of App Class.
 */
class App {

  private $method;
  private $model;
  private $errors = [];

  /**
   * Construct function.
   */
  public function __construct(Model $model) {
    $this->model = $model;
    $this->method = $this->getMethod();
  }

  /**
   * Returns the request method.
   *
   * @return any
   *   Request Method or False.
   */
  private function getMethod() {
    if (isset($_SERVER)) {
      return $_SERVER['REQUEST_METHOD'];
    }

    return FALSE;
  }

  /**
   * Returns the correct response based on the request method.
   *
   * @param array $params
   *   Array of errors.
   */
  public function run(array $params = []) {
    if ($this->validate($params)) {
      switch ($this->method) {
        case 'GET':
          $action = 'get';
          break;

        case 'POST':
          $action = 'create';
          break;

        case 'PUT':
        case 'PATCH':
          $action = 'update';
          break;

        case 'DELETE':
          $action = 'delete';
          break;
      }

      if ($action) {
        $response = $this->model->execute($action, $params);
      }
      else {
        $response = ['errors' => ['invalid request']];
      }
    }
    else {
      $response = ['errors' => $this->getErrors()];
    }

    print json_encode($response);
  }

  /**
   * Return errors found in the valid function.
   *
   * @return array
   *   Array of errors.
   */
  private function getErrors() {
    return $this->errors;
  }

  /**
   * Validate the request.
   *
   * @param array $params
   *   Body Params.
   *
   * @return bool
   *   Validate response.
   */
  private function validate(array $params = []) {
    $valid = TRUE;
    switch ($this->method) {
      case 'PUT':
      case 'PATCH':
      case 'DELETE':
        $valid = isset($params['id']);
        if (!$valid) {
          $this->errors[] = "The field id is required.";
        }
        break;

      case 'POST':
        $fields = ['date_start', 'date_end', 'price'];
        foreach ($fields as $field) {
          $check = isset($params[$field]) && !empty($params[$field]);
          if ($check) {
            $this->errors[] = "The field $field is invalid";
          }

          $valid &= $check;
        }
        break;
    }

    return $valid;
  }

}
