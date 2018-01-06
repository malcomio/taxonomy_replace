<?php

namespace Drupal\taxonomy_replace\Form;

use Drupal\Core\Entity\ContentEntityConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

class TaxonomyReplaceForm extends ContentEntityConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'taxonomy_replace_term_replace_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $term = $this->getEntity();
    $old_tid = $term->id();
    $vid = $term->getVocabularyId();
    
    
    $form['old_term'] = [
      '#title' => $this->t('Current taxonomy term'),
      '#type' => 'textfield',
      '#value' => $term->label() . " ($old_tid)",
      '#disabled' => TRUE,
    ];

    $form['old_tid'] = [
      '#type' => 'value',
      '#value' => $old_tid,
    ];

    $nodes = $this->get_nids_by_tid($old_tid);
    $node_list = [];
    foreach ($nodes as $row) {
      $node = \Drupal::entityTypeManager()->getStorage('node')->load($row->nid);
      $node_list[] = $node->toLink();
    }

    $node_list_output = array(
      '#theme' => 'item_list',
      '#items' => $node_list,
      '#title' => $this->t('Nodes that will be updated'),
    );
    
    $form['nodes'] = [
      '#markup' => render($node_list_output),
    ];
      

    $form['new_tid'] = [
      '#title' => $this->t('Taxonomy term to use instead'),
      '#type' => 'entity_autocomplete',
      '#target_type' => 'taxonomy_term',
      // Limit the selection to the same vocabulary.
      '#selection_settings' => [
        'target_bundles' => [
          $vid => $vid,
        ],
      ],
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => 'Submit',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    kint($form_state);

    // TODO: Get all references to the current term.
    $nodes = $this->get_nids_by_tid($this->entity->id());

    $old_tid = $form_state->getValue('old_tid');
    $new_tid = $form_state->getValue('new_tid');
    
    // TODO: Replace with new term.
    foreach ($nodes as $row) {
      $node = \Drupal::entityTypeManager()->getStorage('node')->load($row->nid);
      
      // 
    }

    // TODO: Delete current term.
    /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
    $entity = $this->getEntity();

    // TODO: Log the replacement.
    
    // TODO: Redirect somewhere sensible.
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to replace and delete the @entity-type %label?', [
      '@entity-type' => $this->getEntity()
        ->getEntityType()
        ->getLowercaseLabel(),
      '%label' => $this->getEntity()->label(),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('entity.taxonomy_vocabulary.collection');
  }

  public function getRedirectUrl() {

  }

  protected function get_nids_by_tid($term_id) {
    $query = \Drupal::database()->select('taxonomy_index', 'ti');
    $query->fields('ti', ['nid', 'tid']);
    $query->condition('ti.tid', $term_id);
    $result = $query->execute()->fetchAll();
    
    return $result;
  }
}
