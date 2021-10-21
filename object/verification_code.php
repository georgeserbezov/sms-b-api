<?php
    class VerificationCode{
        private $conn;
        private $table_name = "verification_code";
    
        private $code;
        private $userId;

        public function __construct($db)
        {
            $this->conn = $db;
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }

        function setCode ($code) {
            $this->code = $code;
        }

        function setUserId ($userId) {
            $this->userId = $userId;
        }

        function getCode($code) {
            $query = "SELECT * FROM verification_code WHERE code = (?)";
            $stmt = $this->conn->prepare($query);

            try {
                $stmt->execute([$code]);
                return $stmt;
            } catch(PDOExecption $e) {
                $this->conn->rollback();
                echo "Error!: " . $e->getMessage() . "</br>";
            }
        }

        function create()
        {   
            try {
                $query = "INSERT INTO verification_code (code, user_id) VALUES (?,?)";
                $stmt = $this->conn->prepare($query);
              
                $this->code=htmlspecialchars(strip_tags($this->code));
                $this->userId=htmlspecialchars(strip_tags($this->userId));

                $res = $stmt->execute([$this->code, $this->userId]);

                return $res;
            } catch(PDOExecption $e) {
                $this->conn->rollback();
                echo "Error!: " . $e->getMessage() . "</br>";
            }
        }
    }
?>