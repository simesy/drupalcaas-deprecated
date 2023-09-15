
# Drupal Content-as-a-Service Demo

A Drupal site build demonstrated in video tutorials, presented by Free Sauce.

This is a demonstration project where everything added to this repository has been
described in a video. You can build it from scratch yourself, over on the
[Freesauce playlist](https://www.youtube.com/playlist?list=PLxbpGX8IrZNqRyzN5F98h1NCpJXjwDO3g).

## Local development

Two ways are available to run locally: Lando and DDEV. They both use
port 80/443 so you would only run one at any time.

(There was also a bonus video where I looked at adding native docker compose based
on Docker4Drupal @see [video](https://youtu.be/wihnEBTKGQc) and [tag v0.1.6](https://github.com/simesy/drupalcaas/tree/v0.1.6)).

### Lando

* https://docs.lando.dev/getting-started/installation.html
* https://drupalcaas.lndo.site
* https://youtu.be/nVldMlh1AUg

```
lando start
lando drush si minimal
lando drush uli
```

### DDEV

* https://ddev.readthedocs.io/en/stable/
* https://drupalcaas.ddev.site
* https://youtu.be/aqEhYOWaxZc

```
ddev start
ddev drush si minimal
ddev drush uli
```
