# Lil Engine Content API

This (will soon be) a live project which Lil Engine use to run content for various headless
sites. It's a public project for a few reasons:

* We can! There is nothing sensitive in the code.
* We think the IA is good practice for paragraphs/components.
* We can use this for demos and blogs.
* We use it as a model for Platform.sh and Lando setup.
* There are not enough public Drupal code bases.

If you want you can clone this site and modify it to your taste whether or not you are
building a headless site. Intentionally, the custom code is kept to a minimum. There
is no profile. It's just Drupal and a bunch of (hopefully you'll agree) lean config.

## Key features

### Components

Components / paragraphs are designed to be semantic, intuitive and reusable. If we
can use this IA for multiple websites then you are likely to find them practical
for any small to medium build.

After a lot of projects we have settled on some default primary components which cover
most situations.

- faq - Accordions, FAQs
- sequence - Timelines, Steppers
- cards - Card style components
- cta - Callouts and CTAs
- markup - A HTML block
- view - An embedded view
- block - An embedded content block

### IA Cleanliness

There are a few factors that have a huge impact on database and IA cleanliness.
This impacts the database tables, twig naming, tokens, filters, indexed fields,
and API endpoints.

We have a `content` field through which you can attached components. If you create
a new node bundle just add this field and select which components are allowed (and
update the form display to match the other instances of the field).

Following the same pattern, there is a `component` custom block type, and that has
a `content` field too. By using this method it means that attaching components to
any entity type is just a matter of creating this field and you'll inherit a lot
of re-usability in theme, tokens, filters, and so on.

We don't use `field_` prefix on this project because we are really interested in
the cleanliness of so many things like the

We have a special `item` paragraph that is re-used whenever we need a re-usable
set of fields (eg on an accordion or cards component). It comes with a different form
display for wherever it's being used. (An item inside a Cards can have a different
form to an item inside an Accordion.)

Because Paragraphs (and item paragraphs) are so re-used it has a massive impact
on all areas of editor and developer usability and re-usability.

### Workflow

User access to editing content is controlled via a Sites taxonomy and Workbench
Access module.

### Custom code

There is a very thin theme wrapper around Gin. This is so we can control some twig for
content previews and paragraph previews. If you are making a non-headless Drupal
site you can check out the theme for some nice twig naming tips.

## Feature requests

This project is fork-and-go. There is nothing to upstream here. If you decided you
want to use this as a boilerplate site for new projects then you might pop in some
feature requests but you might also just create a fork. Be mindful that we are not
trying to abstract things like the Platform.sh settings - there is heaps that you
will want to customise if you copy this project.
