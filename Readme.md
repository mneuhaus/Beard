# What is Beard?

## Download to local directory
```
curl -s https://github.com/mneuhaus/Beard/releases/download/0.11.2/beard-0.11.2.phar > beard.phar
chmod +x beard.phar
```

## Move to global location

### OSX
```
mv beard.phar /usr/local/bin/beard
```

## Commands

### Patch

You can create an beard.json in your main directory of your project
where you can specify various patches and gerrit changes that you
need to pull in for your project. This makes it easier to distribute
development instances to other developers, that depend on pending
gerrit changes or other patches.

**beard.json**
```
{
    "defaults": {
        "gerrit_api_endpoint": "https://review.typo3.org/",
        "gerrit_git": "git.typo3.org"
    },
    "changes": [
        {
            "name": "[WIP][FEATURE] Improve resolving of view",
            "type": "gerrit",
            "path": "Packages/Framework/TYPO3.Flow",
            "change_id": "16392",

            // you can override the defaults here for each change
        	// "gerrit_api_endpoint": "https://review.typo3.org/",
        	// "gerrit_git": "git.typo3.org"
        },
        {
            "name": "Some little change to composer.json",
            "type": "diff",
            "path": "Packages/Framework/TYPO3.FLOW3",
            "file": "Patches/TYPO3.Flow3.diff"
        }
    ]
}
```

**command**
```
beard patch
```

### Reset repositories

This command resets all repositories beneath the main directory to it's remote state.
This will remove any non-pushed changes from the local repositories, so be careful ;)

```
beard reset
```

### Status

The status command helps you to get an overview over all repositories beneath this directory.

```
beard status
```

## Updating

```
beard update
```

## Similarity to Famelo.Gerrit

If you know Famelo.Gerrit, the TYPO3.Flow package you might think this is already covered by that,
which is true. Actually this tool is an evolution of Famelo.Gerrit, which i choose to make standalone.
Main pro's to make it standalone are:
- it works even with completely broken TYPO3.Flow project/installations
- you can use it for almost any kind of project

And just one more thing. "beard patch" will look and process your old "gerrit.json" for now as well :)

## Experimental DB Dump && Restore

(This functionality is quite new and experimental, don't come crying if you're data
is messed up ;)

You can dump your current database into a folder specified by the database settings
in the beard.json file like this:

```json
{
    "database": {
        "dbname": "mydb",
        "user": "myuser",
        "password": "mypassword",
        "host": "myhost",
        "baseDir": "typo3conf",
        "filename": "Template.sql",
        "skip": "cache_.*,cf_.*,sys_log,tx_devlog,tx_extensionmanager_domain_model_extension"
    }
}
```

then you can dump your database with:

```
beard db:dump
```

and restore it with:

```
beard db:restore
```

### DB Hooks

additionally bot the dump and restore command support and "--addHook" option
to set up a git "pre-commit" hook for dumping and adding the the database and
a "post-merge" hook for restoring from the dump in the git commit.
