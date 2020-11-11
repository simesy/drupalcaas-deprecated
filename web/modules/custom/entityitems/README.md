# Entity items

This module provides a site builder with a slightly opinionated way to standardize
repeating items in the content model.

## Background

If you use Paragraphs a lot (but this is not limited to Paragraphs) you will
frequently create a sub-paragraph paragraph to capture repeating sets of
fields. An example is an `accordion` paragraph and repeating `accordion_item`s.

The module author has issues with this...

- Every new repeating entity and its fields can create many tables that impacts debugging and general DX.
- Even if you have a great field naming/re-use strategy, it's hard to ensure people use it.
- Fixes to the content model are hard, and you usually just leave the debt.

## The principles

This module is built on the philosophy that many website builds could
do with a "field_collections" approach to "sub items" but that the solutions
available for this can create a lot of abstraction.

Say if you have "Cards" component, and multiple "Card items", what will those
sub items have? Subtitle, body, link. Maybe some media. In a component based
approach these fields are always the same.

The is module just provides a generic field with a bunch of standard elements
which you can switch on and off.

The result is neatness in:
- tables
- re-usable patterns
- accessing field values
- twig templating
- jsonapi meta
- tokens
- etc etc etc

It also creates a lot of fluidity later if you need get that annoying extra
requirement for a field in your component.

## Tips

This field doesn't and shouldn't solve every problem! If you find yourself
fighting this module, and the fields it provides don't cut it for you, then
build your own thing.

If you call your field `field_items`, and reuse it across all of your components
you may get significant DX benefits in the databasea, tokens and on your APIs.
All of your entities will have a repeating pattern of `$entity->field_items[0]->some_value`.

If you follow the above approach it make make it easy to retrospectively change
a component type using a set of SQL updates. (Some would argue that such updates
are better done through the entity APIs.)

Something that may seem icky... if you use this field, and you only enable
"subtitle" and "markup" (eg an FAQ item) you will find that the database tables
will have a lot of empty fields. Is this bad? Usually not - those empty fields
have minimal storage impact. However, it might have an implications for your project - YMMV.

## Fields

### subtitle

Title or subtitle field limited to 255 chars.

### summary

A plain string summary field limited to 4096 chars (which is a lot for a component
summary). You would use the markup field for anything more complex.

### markup

A full HTML body field.

### uri

This is just a URL, not a full link field. This is because components can often
use another field (or the whole component) as the clickable element.

### entity_id

This will cover those cases where your component needs to point to other
internal content. This doesn't support entities which have string IDs
(views, webforms, config, etc).

### media_id

This will usually cover those cases where your component needs a media or
image element, say for a card image.

### variant

Provides a mechanism for capturing variations of the component. For example,
a card has two style variants. (@todo) At the storage level you can define
the possible options, and then limit these options at the widget level if
needed.
