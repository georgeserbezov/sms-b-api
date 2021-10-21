<?php
    class Attempts{
        private $conn;
        private $table_name = "attempt";
    
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
            $query = "SELECT * FROM attempt WHERE code = (?)";
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
                $query = "INSERT INTO attempt (code, user_id) VALUES (?,?)";
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

        function check($uid) 
        {
            $query = "
                SELECT * FROM attempt 
                WHERE user_id = (?)
                AND attempt_at BETWEEN DATE_ADD(NOW(), INTERVAL -1 MINUTE) AND NOW()
                ORDER BY attempt_at ASC
            ";
            $stmt = $this->conn->prepare($query);

            try {
                $stmt->execute([$uid]);
                return $stmt;
            } catch(PDOExecption $e) {
                $this->conn->rollback();
                echo "Error!: " . $e->getMessage() . "</br>";
            }
        }

        function oneMinutePassed($uid)
        {
            $query = "
                SELECT * FROM attempt 
                WHERE user_id = (?)
                ORDER BY attempt_at ASC
            ";
        }
    }
?>