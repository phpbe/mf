<?php
namespace Be\App\System\Service;

use Be\System\Be;
use Be\System\Exception\ServiceException;
use PHPMailer\PHPMailer\PHPMailer;

class Mail extends \Be\System\Service
{
    private $mailer = null;

    // 构造函数
    public function __construct()
    {
        $this->mailer = new PHPMailer(true);
        $this->mailer->SetLanguage('zh_cn');

        $config = Be::getConfig('System.Mail');
        if ($config->fromMail) $this->mailer->From = $config->fromMail;
        if ($config->fromName) $this->mailer->FromName = $config->fromName;

        $this->mailer->IsHTML(true);

        if ($config->charset) $this->mailer->CharSet = $config->charset;
        if ($config->encoding) $this->mailer->Encoding = $config->encoding;


        if ($config->smtp == 1) {
            $this->mailer->IsSMTP();
            $this->mailer->Host = $config->smtpHost; // smtp 主机地址
            $this->mailer->Port = $config->smtpPort; // smtp 主机端口
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = $config->smtpUser; // smtp 用户名
            $this->mailer->Password = $config->smtpPass; // smtp 用户密码
            $this->mailer->Timeout = $config->smtpTimeout; // smtp 超时时间 秒

            if ($config->smtp_secure != '0') $this->mailer->SMTPSecure = $config->smtpSecure; // smtp 加密 'ssl' 或 'tls'
        }
    }

    // 析构函数
    public function __destruct()
    {
        $this->mailer = null;
    }


    public function from($fromMail, $fromName = '')
    {
        $this->mailer->SetFrom($fromMail, $fromName);
    }


    public function replyTo($replyToMail, $replyToName = '')
    {
        $this->mailer->AddReplyTo($replyToMail, $replyToName);
    }


    // 添加收件人
    public function to($email, $name = '')
    {
        if (!$this->mailer->AddAddress($email, $name)) {
            throw new \Exception($this->mailer->ErrorInfo);
        }
    }


    // 添加收件人
    public function cc($email, $name = '')
    {
        if (!$this->mailer->AddCC($email, $name)) {
            throw new ServiceException($this->mailer->ErrorInfo);
        }
    }


    // 添加收件人
    public function bcc($email, $name = '')
    {
        if (!$this->mailer->AddBCC($email, $name)) {
            throw new ServiceException($this->mailer->ErrorInfo);
        }
    }


    public function attachment($path)
    {
        if (!$this->mailer->AddAttachment($path)) {
            throw new ServiceException($this->mailer->ErrorInfo);
        }
    }

    public function subject($subject = '')
    {
        $this->mailer->Subject = $subject;
    }

    public function body($body = '')
    {
        $this->mailer->Body = $body;
    }

    // 设置不支持 html 的客户端显示的主体内容
    public function altBody($altNody = '')
    {
        $this->mailer->AltBody = $altNody;
    }

    // 占位符格式化
    public function format($text, $data)
    {
        if (is_array($data)) {
            foreach ($data as $key => $val) {
                $text = str_replace('{' . $key . '}', $val, $text);
            }
        } else {
            $text = str_replace('{0}', $data, $text);
        }

        return $text;
    }

    public function send()
    {
        if (!$this->mailer->Send()) {
            throw new ServiceException($this->mailer->ErrorInfo);
        }
    }

    /**
     * 放到队列中发送
     * @TODO
     */
    public function queueSend() {

    }

    /**
     * 指定发送时间
     * @param $timestamp，要发送的时间
     * @TODO
     */
    public function scheduleSend($timestamp) {

    }

    public function verify($email)
    {
        return $this->mailer->ValidateAddress($email);
        //return preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$/i", $email);
    }
}
