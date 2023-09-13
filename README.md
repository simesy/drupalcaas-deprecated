# Drupal Content As A Service (CAAS)

This is a Free Sauce demonstration project build in progress. You can build it from scratch
over on the [Freesauce playlist](https://www.youtube.com/playlist?list=PLxbpGX8IrZNqRyzN5F98h1NCpJXjwDO3g).

## Local development

Multiple ways to run it locally but they are very minimal examples. They all use
port 80/443 so you would only run one at a time.

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
