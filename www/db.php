<?php 
namespace db;
class Database
{
    private $conn;

    public function __construct()
    {
        $this->conn = mysqli_connect("database", "root", $_ENV['MYSQL_ROOT_PASSWORD'], 'actual_price');
        if ($this->conn->connect_error) {
            die("Помилка підключення до бази даних: " . $this->conn->connect_error);
        }
    }

    protected function checkAndInsertEmail($email)
    {
        $emailToCheck = $this->conn->real_escape_string($email);

        $checkQuery = "SELECT * FROM mail WHERE mail = '$emailToCheck'";
        $selectResult = $this->conn->query($checkQuery);
        if ($selectResult->num_rows == 0) {    
            $insertQuery = "INSERT INTO mail (mail,approve) VALUES ('$emailToCheck',0)";
            if (!$this->conn->query($insertQuery)) {
                die("Помилка при вставці даних: " . $this->conn->error);
            }
            return self::checkAndInsertEmail($email); 
        }else{
            return $selectResult->fetch_assoc();
        }
    }

    protected function checkAndInsertIdOrder($idOrder,$url,$prise,$currency)
    {
        $checkQuery = "SELECT * FROM `order` WHERE id_order = $idOrder";
        $selectResult = $this->conn->query($checkQuery);
        if ($selectResult->num_rows == 0) {
            
            $insertQuery = "INSERT INTO `order` (id_order,prise,currency,url) VALUES ('$idOrder','$prise','$currency','$url')";
            if (!$this->conn->query($insertQuery)) {
                die("Помилка при вставці даних: " . $this->conn->error);
            }
        }
    }
    protected function getAllIdOrder()
    {
        $checkQuery = "SELECT * FROM `order`";
        $selectResult = $this->conn->query($checkQuery);
        if ($selectResult->num_rows > 0) {
         $response = array();
         while($result = $selectResult->fetch_assoc()){
            array_push($response,$result);
         }
        return $response;
    	}
    }
    protected function updatePriseById($idOrder,$prise,$currency)
    {
        $checkQuery = "UPDATE `order` SET prise = '$prise' , currency = '$currency'  WHERE id_order = $idOrder";
        $selectResult = $this->conn->query($checkQuery);
        if (!$selectResult) {
        	die("Помилка при вставці даних: " . $this->conn->error);
    	}
    }
    protected function getMailByOrderSub($order_id)
    {
        $checkQuery = "SELECT mail.*, subscribe.* FROM `subscribe` INNER JOIN `mail` ON subscribe.mail_id = mail.id WHERE subscribe.order_id =  $order_id AND approve = 1";
        $selectResult = $this->conn->query($checkQuery);
        if ($selectResult->num_rows > 0) {
         $response = array();
         while($result = $selectResult->fetch_assoc()){
            array_push($response,$result);
         }
        return $response;
    	}
    }
    public function approveMail($mail)
    {
        $checkQuery = "SELECT * FROM `mail` WHERE mail = '$mail' AND approve = '0'";
        $selectResult = $this->conn->query($checkQuery);
        if ($selectResult->num_rows != 0) {           
            $insertQuery = "UPDATE  `mail` SET approve = '1' WHERE mail = '$mail'";
            if (!$this->conn->query($insertQuery)) {
                die("Помилка при вставці даних: " . $this->conn->error);
            }
            return true;
        }else{
            return false;
        }
    }
    protected function checkAndInsertSub($idMail,$idOrder)
    {
        $checkQuery = "SELECT * FROM `subscribe` WHERE order_id = $idOrder AND mail_id = $idMail";
        $selectResult = $this->conn->query($checkQuery);
        if ($selectResult->num_rows == 0) {           
            $insertQuery = "INSERT INTO `subscribe` (order_id,mail_id) VALUES ('$idOrder','$idMail')";
            if (!$this->conn->query($insertQuery)) {
                die("Помилка при вставці даних: " . $this->conn->error);
            }
            return true;
        }else{
        	return false;
        }
    }

    protected function closeConnection()
    {
        $this->conn->close();
    }
}

 ?>