<?php

namespace Drupal\calendar_table\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Create form.
 */
class CalendarTable extends FormBase {

  /**
   * Table header.
   *
   * @var string[]
   */
  protected $header;

  /**
   * Inactive table cells.
   *
   * @var string[]
   */
  protected $inactiveCells;

  /**
   * Active table cells.
   *
   * @var string[]
   */
  protected $activeCells;

  /**
   * Number of constructed tables.
   *
   * @var int
   */
  protected $table = 1;

  /**
   * Number of constructed rows.
   *
   * @var int
   */
  protected $rows = 1;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'calendar_table';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['#prefix'] = '<div id = "form-wrapper">';
    $form['#suffix'] = '</div>';
    $form['#attached'] = ['library' => ['calendar_table/global']];
    // Add buttons.
    $form['addRow'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add Year'),
      '#submit' => ['::addRows'],
      '#limit_validation_errors' => [],
      '#ajax' => [
        'callback' => '::submitAjaxForm',
        'event' => 'click',
        'wrapper' => 'form-wrapper',
        'progress' => [
          'type' => 'none',
        ],
      ],
    ];
    $form['addTable'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add Table'),
      '#submit' => ['::addTable'],
      '#limit_validation_errors' => [],
      '#ajax' => [
        'callback' => '::submitAjaxForm',
        'event' => 'click',
        'wrapper' => 'form-wrapper',
        'progress' => [
          'type' => 'none',
        ],
      ],
    ];
    $this->buildTable($this->table, $form, $form_state);
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
      '#ajax' => [
        'callback' => '::submitAjaxForm',
        'event' => 'click',
        'wrapper' => 'form-wrapper',
      ],
    ];
    return $form;
  }

  /**
   * Table header.
   */
  public function addHeader() {
    $this->header = [
      'year' => $this->t('Year'),
      'jan' => $this->t('Jan'),
      'feb' => $this->t('Feb'),
      'mar' => $this->t('Mar'),
      'q1' => $this->t('Q1'),
      'apr' => $this->t('Apr'),
      'may' => $this->t('May'),
      'jun' => $this->t('Jun'),
      'q2' => $this->t('Q2'),
      'jul' => $this->t('Jul'),
      'aug' => $this->t('Aug'),
      'sep' => $this->t('Sep'),
      'q3' => $this->t('Q3'),
      'oct' => $this->t('Oct'),
      'nov' => $this->t('Nov'),
      'dec' => $this->t('Dec'),
      'q4' => $this->t('Q4'),
      'ytd' => $this->t('YTD'),
    ];
  }

  /**
   * Inactive cells of table.
   */
  protected function inactiveCells() {
    $this->inactiveCells = [
      'q1' => $this->t("Q1"),
      'q2' => $this->t("Q2"),
      'q3' => $this->t("Q3"),
      'q4' => $this->t("Q4"),
      'ytd' => $this->t("YTD"),
    ];
  }

  /**
   * Active cells of table.
   */
  protected function activeCells() {
    $this->activeCells = [
      'jan' => $this->t('Jan'),
      'feb' => $this->t('Feb'),
      'mar' => $this->t('Mar'),
      'apr' => $this->t('Apr'),
      'may' => $this->t('May'),
      'jun' => $this->t('Jun'),
      'jul' => $this->t('Jul'),
      'aug' => $this->t('Aug'),
      'sep' => $this->t('Sep'),
      'oct' => $this->t('Oct'),
      'nov' => $this->t('Nov'),
      'dec' => $this->t('Dec'),
    ];
  }

  /**
   * Build tables.
   */
  public function buildTable(int $table, array &$form, FormStateInterface $form_state) {
    $this->addHeader();
    for ($t = 1; $t <= $table; $t++) {
      $tableId = $t;
      $form[$tableId] = [
        '#type' => 'table',
        '#header' => $this->header,
      ];
      $this->buildRow($tableId, $this->rows, $form, $form_state);
    }
  }

  /**
   * Build rows.
   */
  public function buildRow(string $tableId, int $rows, array &$form, FormStateInterface $form_state) {
    $this->inactiveCells();
    for ($r = $rows; $r > 0; $r--) {
      $rowId = $r;
      foreach ($this->header as $id => $value) {
        $cellId = $id;
        $form[$tableId][$rowId][$cellId] = [
          '#type' => 'number',
          '#step' => '0.01',
        ];
        // Default value for inactive cells.
        if (array_key_exists($id, $this->inactiveCells)) {
          $value = $form_state->getValue($tableId . '][' . $r . '][' . $id, 0);
          $form[$tableId][$rowId][$cellId]['#default_value'] = round($value, 2);
          $form[$tableId][$rowId][$cellId]['#disabled'] = TRUE;
        }
        // Default value for year.
        elseif ($id == 'year') {
          $year = date('Y', strtotime('-' . ($r - 1) . 'year'));
          $form[$tableId][$rowId][$cellId]['#default_value'] = $year;
          $form[$tableId][$rowId][$cellId]['#disabled'] = TRUE;
        }
      }
    }
  }

  /**
   * Function adding a new row.
   */
  public function addRows(array $form, FormStateInterface $form_state): array {
    // Increase by 1 the number of rows.
    $this->rows++;
    // Rebuild form with 1 extra row.
    $form_state->setRebuild();
    return $form;
  }

  /**
   * Function adding a new table.
   */
  public function addTable(array $form, FormStateInterface $form_state): array {
    // Increase by 1 the number of tables.
    $this->table++;
    // Create new tables.
    $form_state->setRebuild();
    return $form;
  }

  /**
   * Refreshing the page.
   */
  public function submitAjaxForm(array $form, FormStateInterface $form_state): array {
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  }

}
