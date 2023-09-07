<?php

namespace Drupal\lecapi;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Swaps the controller for breadcrumbs subrequests.
 */
class EventListener implements EventSubscriberInterface {

  /**
   * Handle kernel request events.
   *
   * If there is a `computed_breadcrumbs` attribute on the current request,
   * pass the request to the breadcrumbs extractor.
   *
   * @param \Symfony\Component\HttpKernel\Event\RequestEvent $event
   *   The kernel event object.
   */
  public function onKernelRequest(RequestEvent $event) {
    $request = $event->getRequest();
    if ($request->attributes->has('computed_breadcrumbs')) {
      $request->attributes->set('_controller', '\Drupal\lecapi\Controller\ApiBreadcrumb::getBreadcrumbFromRequest');
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [KernelEvents::REQUEST => 'onKernelRequest'];
  }

}
