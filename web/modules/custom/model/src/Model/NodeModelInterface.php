<?php

namespace Drupal\model\Model;

use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 */
interface NodeModelInterface {

  public function formAlter(array &$form, FormStateInterface $form_state): void;

}
