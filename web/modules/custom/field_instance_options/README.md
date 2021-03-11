# Field instance options

This is a proof of concept module that moves the list field options to the
field instance.

## Why

When you add a list field, you can define the list options using the format
`key|Label`. However, since this is done on the field storage, it means every
instance of the field gets the same list.

Developers have a trick to override this using [callback_allowed_values_function](http://api.drupal.org/api/search/8/callback_allowed_values_function)
and returning a different option list for each bundle. When doing this, more
care should be taken to make sure that changing these options doesn't orphan
values in the database. This is considered an advanced technique.

This module exposes the options in the UI so that they are stored with each
field instance. It doesn't make the technique any less advanced, it just provides
a neater way to store and edit configuration.

## Setup

Set up the field storage with `allowed_values_function` set to  `_field_instance_options_allowed_values`

```
# field.field.TYPE.BUNDLE.FIELD_NAME.yml
...
settings:
  allowed_values: {  }
  allowed_values_function: _field_instance_options_allowed_values
```

Once this is done, you can edit the field settings and set your option in
the same way you would set them in the storage settings.

## Limitations

Use at your own risk.

* There are no defaults settable in the storage settings.
* In theory you can duplicate options on each instance but it's not tested.
* Only string lists are supported (`list_string` field type).
* There are no tests or performance measures.
* There are no protections against site builders doing weird things.
