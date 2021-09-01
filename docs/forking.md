# Forking caas

Since this is not an upstream product you can use it by forking it. That means
you clone the repository, modify some parts of the code to suit your project
and push the code to your repository. This is how many agencies start new projects: from
an existing project that contains most of the desired customisations for Drupal.

You could script the changes described here but this is partly a training project
so all the changes are explained.

## Lando and Platform.sh

If you are not using these tools you can leave the configuration in place. The
configuration won't interfere with your other work.

*Change the drush aliases*

In ./drush/drush.yml point to the local lando URL.

In ./drush/sites/self.site.yml we explicitly point to production and other
key environments as needed. Change the URLs as required.

