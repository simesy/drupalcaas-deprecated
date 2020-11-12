# Lil Engine Content API

This (will soon be) a live project which Lil Engine use to run content for various headless
sites. It's a public project for a few reasons:

* We can! There is nothing sensitive in the code.
* We think the IA is good practice for paragraphs/components.
* We can use this for demos and blogs.
* We use it as a model for Platform.sh and Lando setup.
* There are not enough public Drupal code bases.

If you want you can clone this site and modify it to your taste, whether or not you are
building a headless site. Intentionally, the custom code is kept to a minimum. There
is no profile. It's just Drupal and a bunch of (hopefully you'll agree) lean config.

## Key features

Components / paragraphs are designed to be intuitive and reusable. If we can use
these for multiple sites then you are likely to find them practical too.

We have a `content` through which you can attached components. If you create
new content type just add this field and select which components are allow. By
using this method it means that attaching components to any entity type is
just a matter of creating this field.

So there is a `component` custom block type, and that has a `content` field too.

We have a special `item` paragraph that is re-used whenever we need a re-usable
set of fields (eg on an accordion or cards component). It has a different form
display for wherever it's being used.

We don't use `field_` on this project because we are really interested in cleanliness
of our endpoints.

Editor access to content is controlled via a Sites taxonomy and Workbench Access module.

There is a very thin theme wrapper around Gin. This is so we can control some twig for
content previews and paragraph previews. If you are making a non-headless Drupal
site you can check out the theme for some nice twig naming tips.

## Feature requests

This project is fork-and-go. There is nothing to upstream here. If you decided you
want to use this as a boilerplate site for new projects then you might pop in some
feature requests but you might also just create a fork. Be mindful that we are not
trying to abstract things like the Platform.sh settings - there is heaps that you
will want to customise if you copy this project.
