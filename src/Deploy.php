<?php

namespace wh1te_w0lf\webhook_deploy;
use wh1te_w0lf\webhook_deploy\base\Component;
use wh1te_w0lf\webhook_deploy\base\Log;

class Deploy extends Component {

    /** @var base\Log $_log */
    protected $_log;

    /** @var base\Notificator $_log */
    protected $_notification;

    /** @var ErrorHandler $_errorHandler */
    protected $_errorHandler;

    /** @var string $_secret */
    protected $_secret = '';

    protected $_repository = '';
    protected $_branch = '';
    protected $_serverBranch = '';
    protected $_repositoryPath = '';

    protected $_beforeUpdateHook = '';
    protected $_afterUpdateHook = '';

    public function run($get, $post, $server) {
        $this->_log->log(Log::lTrace, "Start deploy");
        if (!empty($this->_secret) && !empty($get['secret']) && ($get['secret'] !== $this->_secret))  {
            $this->_log->log(Log::lWarning, "Incorrect or empty secret key!");
            return null;
        }

        $wasServerChanges = $this->_isServerChangesExists();

        if ($wasServerChanges) {
            $this->_applyServerChanges();
        }

        if ($this->_analyzeHook($post, $server)) {
            $this->_beforeDevChanges();
            // $this->_applyDevChanges();
            $this->_afterDevChanges();
        }

        $this->_log->log(Log::lTrace, "Finish deploy");
        $this->_notification->notificate("Deploy success" . ($wasServerChanges ? " <b>There was server changes</b>" : ''));
    }

    protected function _isServerChangesExists() {
        $this->_log->log(Log::lTrace, "isServerChangesExists");
        $cmd = "cd {$this->_repositoryPath} && GIT_WORK_TREE='{$this->_repositoryPath}' git branch";
        exec($cmd, $output, $return);
        if ($return) {
            throw new \Exception("isServerChangesExists: git branch return {$return}");
        }

        $branch = trim(trim(implode('', $output), '*'));
        $this->_log->log(Log::lTrace, "Current branch is: {$branch}");

        if ($branch != $this->_serverBranch) {
            throw new \Exception("Incorrect branch on server {$branch}");
        }


        $cmd = "cd {$this->_repositoryPath} && GIT_WORK_TREE='{$this->_repositoryPath}' git status";
        exec($cmd, $output, $return);
        if ($return) {
            throw new \Exception("isServerChangesExists: git status return {$return}");
        }

        foreach ($output as $line) {
            if (strstr(strtolower($line), strtolower('Changes not staged for commit')) || strstr(strtolower($line), 'modified:')) {
                $this->_log->log(Log::lTrace, "Server changes exists");
                return true;
            }
        }
        $this->_log->log(Log::lTrace, "There are no modified files");
        return false;
    }

    protected function _applyServerChanges() {
        $this->_log->log(Log::lTrace, "applyServerChanges");
        $cmd = <<<EOL
cd {$this->_repositoryPath} && 
GIT_WORK_TREE='{$this->_repositoryPath}' git add -u &&
GIT_WORK_TREE='{$this->_repositoryPath}' git commit -m "Server changes" &&
GIT_WORK_TREE='{$this->_repositoryPath}' git push origin {$this->_serverBranch}
EOL;
        exec($cmd, $output, $return);

        if ($return) {
            throw new \Exception("applyServerChanges: git add return {$return}");
        }
    }

    protected function _analyzeHook($post, $server) {
        $this->_log->log(Log::lTrace, "analyzeHook");

        $headers = [
            'HTTP_X_EVENT_KEY',
            'HTTP_USER_AGENT',
            'REMOTE_ADDR'
        ];

        foreach ($headers as $header) {
            if (!isset($server[$header])) {
                $this->_log->log(Log::lWarning, "No header {$header}");
                return false;
            }
        }

        if (isset($post['payload'])) {
            $payload = $post['payload'];
        } else { // new method
            $payload = json_decode(file_get_contents('php://input'), true);
        }

        if (empty($payload)) {
            $this->_log->log(Log::lWarning, "Empty payload");
            return false;
        }

        if (!isset($payload['repository']['full_name'], $payload['push']['changes'])) {
            $this->_log->log(Log::lWarning, "Invalid payload data was received");
            return false;
        }

        if (strtolower($payload['repository']['full_name']) != strtolower($this->_repository)) {
            $this->_log->log(Log::lWarning, "Incorrect repository name");
            return false;
        }

        $this->_log->log(Log::lTrace, "Repository: {$payload['repository']['full_name']}. Hook OK");
        return true;
    }

    protected function _beforeDevChanges() {
        if (empty($this->_beforeUpdateHook)) {
            return;
        }
        $cmd = <<<EOL
cd {$this->_repositoryPath} && 
{$this->_beforeUpdateHook}
EOL;
        exec($cmd, $output, $return);

        if ($return) {
            throw new \Exception("beforeDevChanges return {$return}");
        }
    }

    protected function _applyDevChanges() {
        $this->_log->log(Log::lTrace, "applyServerChanges");
        $cmd = <<<EOL
cd {$this->_repositoryPath} && 
GIT_WORK_TREE='{$this->_repositoryPath}' git checkout {$this->_branch} &&
GIT_WORK_TREE='{$this->_repositoryPath}' git pull origin {$this->_branch} &&
GIT_WORK_TREE='{$this->_repositoryPath}' git checkout {$this->_serverBranch} &&
GIT_WORK_TREE='{$this->_repositoryPath}' git merge {$this->_branch}
EOL;
        exec($cmd, $output, $return);

        if ($return) {
            throw new \Exception("applyServerChanges: git return {$return}");
        }
    }

    protected function _afterDevChanges() {
        if (empty($this->_afterUpdateHook)) {
            return;
        }
        $cmd = <<<EOL
cd {$this->_repositoryPath} && 
{$this->_afterUpdateHook}
EOL;
        exec($cmd, $output, $return);
        if ($return) {
            throw new \Exception("afterDevChanges return {$return}");
        }
    }

}