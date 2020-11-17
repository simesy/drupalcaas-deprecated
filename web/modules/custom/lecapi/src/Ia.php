<?php

namespace Drupal\lecapi;

/**
 * A class to centralise content model information.
 */
class Ia {

  /**
   * Re-usable heading field name.
   */
  const FIELD_HEADING = '_heading';

  /**
   * Re-usable markup field name.
   */
  const FIELD_MARKUP = '_markup';

  /**
   * Re-usable media field name.
   */
  const FIELD_MEDIA = '_media';

  /**
   * Re-usable link field name.
   */
  const FIELD_LINK = '_link';

  /**
   * Entry point for repeating content on all entity types.
   */
  const FIELD_CONTENT = 'content';

  /**
   * Entry point for repeating item content on paragraph types.
   */
  const FIELD_ITEMS = 'items';

  /**
   * Controls paragraph types.
   */
  const FIELD_VARIANT = 'variant';

  /**
   * CTA paragraph.
   */
  const PG_CTA = 'cta';

  /**
   * Markup paragraph.
   */
  const PG_MARKUP = 'markup';

  /**
   * FAQ paragraph.
   */
  const PG_FAQ = 'list';

  /**
   * Sequence paragraph.
   */
  const PG_SEQUENCE = 'sequence';

  /**
   * Item paragraph.
   */
  const PG_ITEM = 'item';

}
