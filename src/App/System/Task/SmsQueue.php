<?php

namespace Be\Mf\App\System\Task;

/**
 * @BeTask("发短信队列", schedule="* * * * *")
 */
class SmsQueue extends \Be\Mf\Task\TaskInterval
{

}
