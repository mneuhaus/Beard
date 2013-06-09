# What is Give?

You probably already keep some kind of project templates for various usecases
so you don't have to start all over every time. When you use one of those you
probably have to rename and change various places every time fit the new project. Name of the Project, filenames, and similar stuff. Give offers you a
way to make this a bit easier to reproduce quickly. You only need to add
a give.json to your project template with what needs to be changed and commit
that project template to GitHub. You can rename files, replace strings in files, ask for variables and use them in files and filenames and execute any
command to do a "bower install" or something else.

## Download to local directory
```
curl -s https://raw.github.com/mneuhaus/Give-Repository/master/give-0.2.0.phar > give.phar
chmod +x give.phar
```

## Move to global location

### OSX
```
mv give.phar /usr/local/bin/give
```

## Updating

```
give update
```

## Usage

This command takes clones the Repository "https://github.com/famelo/Bootstrap"
into a directoy "MyProject". After cloning all changes to the files are made
that are specified in the give.json in that directoy.

```
give famelo/Bootstrap MyProject
```

## Configuration
Take a look at this repository of an give.json example: https://github.com/mneuhaus/Give-Test