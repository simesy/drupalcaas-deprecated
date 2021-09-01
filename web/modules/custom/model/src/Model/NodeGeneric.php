<?php

namespace Drupal\model\Model;

use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;

class NodeGeneric extends Node implements NodeModelInterface {

  public function formAlter(array &$form, FormStateInterface $form_state): void {}

}
