# Backend dev

This is a collection of tips for backed development. It assumes you're using Platform.sh
and Lando which are set up out of the box. There are some fairly simple ones for training
purposes that would already be familiar to Platform.sh or Lando users.

*General assumptions*

* You are using lando and platform.sh
* You have setup a @prod site alias in ./drush/sites/self.site.yml
* Your prod site is actually running
* You are on OSX (but in many cases it won't matter)

## Suggested aliases

These are optional, but you'll see a lot of commands are `lando` or `platform`
commands. So you could add these to your `.bash_profile` and customise to
taste.

```
alias p=platform
alias l=lando
alias pd="platform drush"
alias ld="lando drush"
```

## Syncing

You can sync the database from production to local. You can modify the commmand in the
tooling section of .lando.yml for your project.

```
lando sync-prod
```

## Login to local / prod

```
lando drush @prod uli
```

