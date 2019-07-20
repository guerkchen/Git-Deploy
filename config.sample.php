<?php
define('SECRET', 'sTrOnG_sEcReT');                                     // The secret token to add as a GitHub secret
define('DIR', '/var/www/example.com/');                                // The path to your repository; this must begin with a forward slash (/)
define('BRANCH', 'refs/heads/master');                                 // The branch route
define('LOGFILE', 'git-deploy.log');                                   // The name of the file you want to log to
define('GIT', '/usr/bin/git');                                         // The path to the git executable
define('DELETION', array('README.md', '.gitattributes'));              // The array of files to be deleted from repository on the server
define('SLACK_HOOK', 'https://hooks.slack.com/services/SLACK_KEY');    // The URL for Slack integration