<?php

namespace wh1te_w0lf\webhook_deploy\base;

abstract class Notificator extends Component {
    abstract public function notificate($message);
}