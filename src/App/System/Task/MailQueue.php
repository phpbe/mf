<?php

namespace Be\Mf\App\System\Task;

use Be\Mf\Be;
use Be\Mf\Task\TaskException;

/**
 * @BeTask("发邮件队列", schedule="* * * * *")
 */
class MailQueue extends \Be\Mf\Task\TaskInterval
{

    public function execute()
    {
        $t0 = microtime(1);

        $db = Be::getDb();
        while (true) {
            $sql = 'SELECT * FROM system_mail_queue WHERE sent = 0 AND times<10 ORDER BY create_time ASC, times ASC';
            $queues = $db->getObjects($sql);
            if (count($queues) > 0) {
                foreach ($queues as $queue) {

                    try {
                        $mail = Be::newService('System.Mail');

                        if (is_string($queue->to)) {
                            $mail->to($queue->to);
                        } else {
                            if (is_array($queue->to)) {
                                $email = null;
                                $name = '';
                                if (isset($queue->to['email'])) {
                                    $email = $queue->to['email'];
                                }

                                if (isset($queue->to['name'])) {
                                    $name = $queue->to['name'];
                                }

                                if (!$email) {
                                    throw new TaskException('收件人邮箱缺失！');
                                }

                                $mail->to($email, $name);
                            }
                        }

                        if (isset($queue->cc)) {
                            if (is_string($queue->cc)) {
                                $mail->cc($queue->cc);
                            } else {
                                if (is_array($queue->cc)) {
                                    $email = null;
                                    $name = '';
                                    if (isset($queue->cc['email'])) {
                                        $email = $queue->cc['email'];
                                    }

                                    if (isset($queue->cc['name'])) {
                                        $name = $queue->cc['name'];
                                    }

                                    if ($email) {
                                        $mail->cc($email, $name);
                                    }
                                }
                            }
                        }

                        if (isset($queue->bcc)) {
                            if (is_string($queue->bcc)) {
                                $mail->bcc($queue->bcc);
                            } else {
                                if (is_array($queue->bcc)) {
                                    $email = null;
                                    $name = '';
                                    if (isset($queue->bcc['email'])) {
                                        $email = $queue->bcc['email'];
                                    }

                                    if (isset($queue->bcc['name'])) {
                                        $name = $queue->bcc['name'];
                                    }

                                    if ($email) {
                                        $mail->bcc($email, $name);
                                    }
                                }
                            }
                        }

                        $mail->subject($queue->subject ?? '');
                        $mail->body($queue->body ?? '');
                        $mail->send();

                        $sql = 'UPDATE system_mail_queue SET sent = 1, sent_time = ? WHERE id = ?';
                        $db->query($sql, [date('Y-m-d H:i:s'), $queue->id]);

                    } catch (\Throwable $t) {
                        $sql = 'UPDATE system_mail_queue SET times = times + 1, message = ? WHERE id = ?';
                        $db->query($sql, [$t->getMessage(), $queue->id]);
                    }
                }
            } else {
                sleep(10);
            }

            $t1 = microtime(1);
            if ($t1 - $t0 > 50) {
                break;
            }
        }

    }
}
