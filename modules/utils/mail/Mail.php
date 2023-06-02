<?php
class Mail
{

    public static function sendMail($subject, $content, $addresses = [], $attachs = [], $type = 'guohan'): bool
    {/*{{{*/
        $mailConfig = Config::get("mail.$type") ?? [];
        if (empty($mailConfig)) {
            DBC::throwEx('[Mail Exception]wrong config type:' . $type);
        }
        if (empty($addresses)) {
            DBC::throwEx('[Mail Exception]empty address');
        }
        $mail = new PHPMailer();
        $mail->SMTPDebug = 0;
        $mail->isSMTP();
        $mail->SMTPAuth = true;
        $mail->Host = $mailConfig['host'];
        $mail->SMTPSecure = 'ssl';
        $mail->Port = $mailConfig['port'];
        $mail->CharSet = 'GBK';
        $mail->FromName = 'gh';
        $mail->Username = $mailConfig['userName'];
        $mail->Password = $mailConfig['passWord'];
        $mail->From = $mailConfig['from'];
        $mail->isHTML(true);
        if (!is_array($addresses)) {
            $addresses = [$addresses];
        }
        foreach ($addresses as $address) {
            $mail->addAddress($address);
        }
        $mail->Subject = $subject;
        $mail->Body = $content;
        if (!is_array($attachs)) {
            $attachs = [$attachs];
        }
        foreach ($attachs as $attach) {
            if (!is_file($attach)) {
                DBC::throwEx('[Mail Exception]wrong path:' . $attach);
                continue;
            }
            $mail->addAttachment($attach);
        }
        return $mail->send();
    }/*}}}*/

}
