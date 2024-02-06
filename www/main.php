<?php
namespace Main;

require_once 'db.php';
use db\Database as Database;

class Construction extends Database
{
    private static function getIdOrder($url)
    {
        $legasiPage =  self::getPage($url);
        $pattern = '/er34gjf0">ID: <!-- -->(\d+)<\/span>/';
        $idOrder = self::regular($legasiPage,$pattern);
        return $idOrder;
    }
    private static function getPriseByOrder($idOrder)
    {
        $apiUrl = "https://ua.production.delivery.olx.tools/payment/ad/" . $idOrder . "/buyer/?lang=uk-UA";
        $jsonPrise =  self::getPage($apiUrl);
        $array = self::jsonPars($jsonPrise);
        $price = $array['product']['price'];
        $currency = $array['product']['currency'];
        return [$price,$currency];
    }

    public static function errorMessage($message)
    {
        echo $message;
    }

    private static function getPage($url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $response = curl_exec($ch);
        if ($response === false) {
            self::errorMessage(curl_error($ch));
        } else {
            return $response;
        }
    }

    private static function regular($html_body,$pattern)
    {           
        if (preg_match($pattern, $html_body, $matches)) {
            $foundDigits = $matches[1];
            return "$foundDigits";
        } else {
            self::errorMessage("empty id");
        }
    }
    private static function sendApprove($mail)
    {           
            $message = 'Підтвердіть свій емейл для підписки на оновлення цін - <a href="http://' . $_SERVER["SERVER_NAME"] . '/approve.php?mail=' .$mail. '">Підтвердити</a>';
            self::sendMail($mail,$message);
    }
    public function approveMail($mail)
    {
        parent::approveMail($mail);
    }

    public function dataProcessing($json)
    {           
        $request = self::jsonPars($json);
        if(self::validateEmail($request['mail']) && self::validateUrl($request['url'])){
            $idOrder = self::getIdOrder($request['url']); 
            $prise = self::getPriseByOrder($idOrder);
            parent::checkAndInsertIdOrder($idOrder,$request['url'],$prise[0],$prise[1]);
            $idMail = parent::checkAndInsertEmail($request['mail']);
                if($idMail['approve'] == 0){
                    self::sendApprove($idMail['mail']);
                    parent::checkAndInsertSub($idMail['id'],$idOrder);
                }else{
                    self::errorMessage('you already subscribed');
                }
        }
    }

    public static function jsonPars($string)
    {           
       return json_decode($string,true);
    }   
    static function gMessage($prise,$currency,$url)
    {           
        $result = 'Нова ціна! '. substr_replace($prise,'.', strlen($prise) - 2, 0) . ' ' . $currency . " на товар - <a href='" . $url ."'>Товар</a>";
       return $result;
    } 
    public static function sendMail($mail = 'cmetanactborogom2281337@gmail.com',$message = 'HELLOW')
    { 
        $subject = "Actual price";
        $headers = "From: nnikitenko254@gmail.com";
        $mailSuccess = mail($mail, $subject, $message, $headers);
        var_dump([$mail,$subject, $message,$headers]);
        if ($mailSuccess) {
        } else {
            self::errorMessage('error mail send');
        }
    }
    public function validateEmail($email)
    {
        $pattern = '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/';
        if (preg_match($pattern, $email)) {
            return true;
        } else {
            self::errorMessage('Bad email');
        }
    }
    public function validateURL($url)
    {
        $pattern = '/^(https?:\/\/)?([a-zA-Z0-9.-]+\.[a-zA-Z]{2,})(\/[^\s]*)?$/';
        if (preg_match($pattern, $url)) {
            return true;
        } else {
            self::errorMessage('Bad url');
        }
    }
    public function updatePrise()
    {
        $array_order = parent::getAllIdOrder();
        foreach ($array_order as $key => $value) {
            $prise = self::getPriseByOrder($value['id_order']);
            if($prise[0] != $value['prise']){
                parent::updatePriseById($value['id_order'],$prise[0],$prise[1]);
                $mail = parent::getMailByOrderSub($value['id_order']);
                if(!empty($mail)){
                    foreach ($mail as $mail_key => $mail_value) {
                        self::sendMail($mail_value['mail'],self::gMessage($prise[0],$prise[1],$value['url']));
                    }
                }
            }            
        }
    }
}

