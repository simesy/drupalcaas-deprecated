<?php

namespace Drupal\lecapi\Controller;

use Drupal\Component\Render\MarkupInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Routing\RouteMatch;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Controller for breadcrumb api.
 */
class ApiBreadcrumb extends ControllerBase {

  /**
   * @var \Symfony\Component\HttpKernel\KernelInterface
   */
  protected $httpKernel;

  /**
   * The breadcrumb manager.
   *
   * @var \Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface
   */
  protected $breadcrumbManager;

  /**
   * The alias manager.
   *
   * @var \Drupal\path_alias\AliasManagerInterface
   */
  private $pathAliasManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->httpKernel = $container->get('http_kernel.basic');
    $instance->breadcrumbManager = $container->get('breadcrumb');
    $instance->pathAliasManager = $container->get('path_alias.manager');
    return $instance;
  }

  /**
   * Controller for route /lecapi/api/breadcrumb?path=abc.
   */
  public function index(Request $request) {
    $path = $request->query->get('path');
    if (empty($path)) {
      throw new NotFoundHttpException('Unable to work with empty path. Please send a ?path query string parameter with your request.');
    }
    // Get path alias if have.
    $path = $this->pathAliasManager->getAliasByPath($path);
    $sub_request = Request::create(
      $path,
      'GET',
      $request->query->all(),
      $request->cookies->all(),
      $request->files->all(),
      $request->server->all(),
    );
    if ($session = $request->getSession()) {
      $sub_request->setSession($session);
    }
    // Set attribute for alter controller later.
    $sub_request->attributes->set('computed_breadcrumbs', TRUE);
    // Make sub request then return breadcrumb json response.
    return $this->httpKernel->handle($sub_request, HttpKernelInterface::SUB_REQUEST);
  }

  /**
   * Return breadcrumb json response from a request.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Return a json response.
   */
  public function getBreadcrumbFromRequest(Request $request) {
    $route_match = RouteMatch::createFromRequest($request);
    $breadcrumb = $this->breadcrumbManager->build($route_match);
    $return = [];
    foreach ($breadcrumb->getLinks() as $key => $link) {
      $text = $link->getText();
      $url = $link->getUrl()->toString();
      $return[$key] = [
        'text' => ($text instanceof MarkupInterface) ? $text->__toString() : $text,
        'url' => $url,
      ];
    }
    return new JsonResponse($return);
  }

}
