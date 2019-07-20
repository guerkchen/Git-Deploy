<?php
require_once('config.php');

//read webhook contents, open logfile in append mode & get current time
$payload = file_get_contents('php://input');
$log     = fopen(LOGFILE, 'a');
$time    = time();

// log the time to differentiate log entries
date_default_timezone_set('UTC');
fputs($log, "\n" . date('d-m-Y (H:i:s T)', $time) . ' connecting from: ' . $_SERVER['REMOTE_ADDR'] . "\n");

//check for GitHub signature header
if (isset($_SERVER['HTTP_X_HUB_SIGNATURE'])) {
    $gitHeader = $_SERVER['HTTP_X_HUB_SIGNATURE'];
} else {
    respond($log, 'ERROR: X-Hub-Signature header not found', 403);
}

//split header into algorithm and hash
list($algo, $gitHash) = explode('=', $gitHeader, 2);

//generate hash from payload
$payloadHash = hash_hmac($algo, $payload, SECRET);

//compare hashes
if (!hash_equals($payloadHash, $gitHash)) {
    //hash doesn't match
    respond($log, 'ERROR: Hashes do not match', 403);
} 
else { //hash matches
    $json = json_decode($payload, true);

    //check for GitHub ping and respond
    if (isset($json['zen'])) {
        slack_message('ping', $json);
        echo 'Congratulations, Webhook set up successfully.';
        respond($log, 'SUCCESS: Github ping successful', 200);
    }

    //check if push matches desired branch
    if ($json['ref'] === BRANCH) {
        //check for valid git directory
        if (is_dir(DIR) && file_exists(DIR . '.git')) {
            //perform git pull
            chdir(DIR);
            $output = shell_exec(GIT . ' pull');
            fputs($log, 'Git: ' . $output);

            //delete files if DELETION array is not empty
            if (!empty(DELETION)) {
                foreach (DELETION as $file) {
                    //check that file exists
                    if (file_exists($file)) {
                        fputs($log, 'WARNING: ' . $file . ' was deleted' . "\n");
                        unlink($file);
                    }
                }
            }
        } else {
            respond($log, 'ERROR: Directory not found or is not a Git repository', 500);
        }
    } else {
        echo 'An alternative branch was updated to the one specified. No action was performed.';
        respond($log, 'WARNING: Incorrect branch', 200);
    }

    //Check output of git pull
    if (strncmp($output, 'Already', 7) === 0) {
        slack_message('warning', $json);
        echo 'The git pull was successful but no changes were made.';
        respond($log, 'SUCCESS: Git pull was successful but no changes were made', 200);
    } else {
        slack_message('success', $json);
        echo 'Congratulations, Git pull was successful.';
        respond($log, 'SUCCESS: Git pull was successful', 200);
    }
}

function respond(&$log, $text, $response_code) {
    fputs($log, $text . "\n");
    fclose($log);
    http_response_code($response_code);
    exit;
}

function slack_message($type, &$json) {
    if (!empty(SLACK_HOOK)) {
        switch ($type) {
            case 'success':
                $message = '{"attachments":[{"fallback":"The website deployment was successful.","color":"good","text":"The repository was successfully deployed to the server.","footer":"GitHub","footer_icon":"https://assets-cdn.github.com/images/modules/logos_page/GitHub-Mark.png","ts":"' . strtotime($json['head_commit']['timestamp']) . '","actions":[{"type":"button","text":"View Commit","url":"' . $json['head_commit']['url'] . '"},{"type":"button","text":"View Website","url":"https://danturn.co.uk"}]}]}';
                curl_message($message);
                break;
            case 'warning':
                $message = '{"attachments":[{"fallback":"The repository was deployed but no changes were made.","color":"warning","text":"The repository was deployed but no changes were made.","footer":"GitHub","footer_icon":"https://assets-cdn.github.com/images/modules/logos_page/GitHub-Mark.png","ts":"' . strtotime($json['head_commit']['timestamp']) . '","actions":[{"type":"button","text":"View Commit","url":"' . $json['head_commit']['url'] . '"},{"type":"button","text":"View Website","url":"https://danturn.co.uk"}]}]}';
                curl_message($message);
                break;
            case 'ping':
                $message = '{"attachments":[{"fallback":"The webhook was set up successfully.","color":"good","text":"The webhook was set up successfully.","footer":"GitHub","footer_icon":"https://assets-cdn.github.com/images/modules/logos_page/GitHub-Mark.png","actions":[{"type":"button","text":"View Repository","url":"' . $json['html_url'] . '"}]}]}';
                curl_message($message);
                break;
        }
    }
}

function curl_message(&$message) {
    $curl = curl_init(SLACK_HOOK);
    curl_setopt_array($curl, [
        CURLOPT_POST => 1,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_POSTFIELDS => $message
    ]);
    curl_exec($curl);
    curl_close($curl);
}