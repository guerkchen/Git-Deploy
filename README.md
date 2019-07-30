# Git Deploy

A PHP script to automatically pull from a GitHub repository  when it is updated. 

You can configure which branch to pull from,  files to be deleted after this pull (e.g. .gitignore, LICENSE, etc.) and integrate with Slack to know when the pull has been successful. 

## Getting Started

### Prerequisites

Generate an SSH key and add it to your account so that `git pull` can be run on private repos and without a password.

Check out the [GitHub documentation](https://help.github.com/articles/generating-ssh-keys/) for detailed instructions.

### Setup

1. Copy this repo into a publically accessible directory on your server (e.g. www, public_html, etc.)

2. Rename `config.sample.php` to `config.php` and update each variable to the desired value. For example: 

    ```PHP
    define('SECRET', 'sTrOnG_sEcReT');
    define('DIR', '/var/www/example.com/');
    define('BRANCH', 'refs/heads/master');
    define('LOGFILE', 'git-deploy.log');
    define('GIT', '/usr/bin/git');
    define('DELETION', array('README.md', '.gitattributes'));
    define('SLACK_HOOK', 'https://hooks.slack.com/services/SLACK_KEY';
    ```

    The `SLACK_HOOK` and `DELETION` variables can be empty if not required.

3. Adjust the permissions for the directory so that it is accessible by the webserver user (e.g. www, www-data, apache, etc.)

    1. Open the termial and navigate to the directory containing  the repository on the server.
    2. Run `sudo chown -R yourusername:webserverusername git-deploy` to change the group. 
    3. Run `sudo chmod -R g+s git-deploy` to ensure that permissions are inherited by all files and directories.
    4. Run `sudo chmod -R 775 git-deploy` to set read and write permissions.

### External Services

#### GitHub

You need to configure GitHub to notify your endpoint when the repository is updated. 

In your repository, navigate to Settings &rarr; Webhooks &rarr; Add webhook, and use the following settings:

* *Payload URL*: https://www.yoursite.com/location-of/deploy.php
* *Content type*: application/json
* *Secret*: The value of `SECRET` in `config.php`
* *Which events would you like to trigger this webhook?*: :radio_button: Just the push event
* *Active*: :ballot_box_with_check: Selected

Click 'Add webhook' to save your settings, and allow the script to start working. 

#### Slack

You need to configure a Slack app so that the script can post messages to your workspace.

1. [Sign in](https://slack.com/signin) to your Slack workspace using a web browser.

2. At the top right, navigate to :gear: &rarr; Add an app, then type "Incoming Webhook" into the text field.

3. Click on the Incoming Webhook app and create a new configurarion.

4. Select the channel you wish the script to post to when the repository is updated, then click Add Incoming Webhooks Integration. 

5. Copy the Webhook URL and set it as the value of `SLACK_HOOK` in `config.php`

The script will now post the status of each pull to the Slack channel. You can customise the integration's settings such as appearence and channel as you see fit.  


## Usage

Once set up, the script will work automatically as the repository is updated. You are able to update the configuration as you wish.

To check the status of the script you can:
* View the output of the log at the `LOGFILE` location  provided within `config.php`
* Head to your repository on GitHub. Navigate to Settings &rarr; Webhooks &rarr; Edit, and then view the recent deliveries section. 
* View the message that have been posted by the integration on your Slack channel.

## Author

* Daniel Turner - [turnerdaniel](https://www.github.com/turnerdaniel)

## Acknowledgements

* [Vicente Guerra](https://www.github.com/vicenteguerra) - For their original work on the [git deploy](https://github.com/vicenteguerra/git-deploy) script which this project is based on.