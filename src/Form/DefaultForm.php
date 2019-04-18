<?php

namespace Drupal\entitynode\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\NodeType;

/**
 * Class DefaultForm.
 */
class DefaultForm extends FormBase {


  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'entityNodeForm';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
      $query_1 = \Drupal::entityQuery('node');
      $query_1->condition('type', 'landing_page');
      $nids = $query_1->execute();
      $nodes = \Drupal\node\Entity\Node::loadMultiple($nids);

      $connection = \Drupal::database();
      $query = $connection->query("SELECT * FROM {menu_link_content_data} WHERE link__uri LIKE 'entity:node/%'");
      $result = $query->fetchAll();
      $rows = array();

      foreach($nodes as $node){
          $previd = $node->get('field_previous_id')->value;
          $nodeid = $node->get('nid')->value;
          $name = $node->get('title')->value;
          foreach ( $result as $res) {
              $node_loc = preg_split('{/}', $res->link__uri);
              //foreach($nodes as $node){
              if($node_loc[1]==$previd //&& $node_loc[1] != $nodeid
                  && $node->get('langcode')->value==$res->langcode
              ){
                  $new_text = $node_loc[0]."/".$nodeid;
                  $rows[] = array(
                      $res->title,
                      $res->link__uri,
                      //$nodes->get('nid')->value,
                      $previd,
                      $new_text,
                      $name,
                  );
              }
              //}

          }

      }
      //echo $result;

      // generate a table of mappings to render
      $form['mapping'] = [
          '#type' => 'table',
          '#header' => [$this->t('Title'), $this->t('Before'), $this->t('Match'), $this->t('New'),$this->t('Name')],
          '#rows' => $rows,
      ];

      $form['submit'] = [
          '#type' => 'submit',
          '#value' => $this->t('Update Body'),
      ];


      return $form;
  }

  public function viewMappings(array &$form, FormStateInterface &$form_state) {
    $form_state->setRebuild();
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state)
  {
      $query_1 = \Drupal::entityQuery('node');
      $query_1->condition('type', 'landing_page');
      $nids = $query_1->execute();
      $nodes = \Drupal\node\Entity\Node::loadMultiple($nids);

      $connection = \Drupal::database();
      $query = $connection->query("SELECT * FROM {menu_link_content_data} WHERE link__uri LIKE 'entity:node/%'");
      $result = $query->fetchAll();
      $rows = array();

      foreach($nodes as $node){
          $previd = $node->get('field_previous_id')->value;
          $nodeid = $node->get('nid')->value;
          $name = $node->get('title')->value;
          foreach ( $result as $res) {
              $node_loc = preg_split('{/}', $res->link__uri);
              //foreach($nodes as $node){
              if($node_loc[1]==$previd //&& $node_loc[1] != $nodeid
                  && $node->get('langcode')->value==$res->langcode
              ){
                  $new_text = $node_loc[0]."/".$nodeid;
                  $num_updated = $connection->update('menu_link_content_data')
                      ->fields([
                          'link__uri' => $new_text,
                      ])
                      ->condition('link__uri', $res->link__uri, '=')
                      ->execute();
              }
              //}

          }

      }
  }
}
