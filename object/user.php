<?php
    class User{
        private $conn;
        private $table_name = "user";

        private $email;
        private $phone;
        private $password;

        public function __construct($db)
        {
            $this->conn = $db;
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }

        function setEmail ($email) {
            $this->email = $email;
        }

        function setPhone ($phone) {
            $this->phone = $phone;
        }

        function setPassword ($password) {
            $this->password = $password;
        }

        function setVerified ($id) {
            $sql = "UPDATE user SET verified=1 WHERE id=?";
            $stmt= $this->conn->prepare($sql);
            $stmt->execute([$id]);
        }

        function getEmail($id)
        {
            try {
                $stmt = $this->conn->prepare("SELECT email FROM user WHERE id=? LIMIT 1"); 
                $stmt->execute([$id]); 
                $row = $stmt->fetch();
    
                return $row;
            } catch(PDOExecption $e) {
                $this->conn->rollback();
                echo "Error!: " . $e->getMessage() . "</br>";
            }
        }

        function getPhone($id)
        {
            try {
                $stmt = $this->conn->prepare("SELECT phone FROM user WHERE id=? LIMIT 1");
                $stmt->execute([$id]); 
                $row = $stmt->fetch();
                return $row;
            } catch(PDOExecption $e) {
                $this->conn->rollback();
                echo "Error!: " . $e->getMessage() . "</br>";
            }
        }

        function create()
        {
            try {
                $query = "INSERT INTO user (email, phone, password, verified) VALUES (?,?,?,?)";
                $stmt = $this->conn->prepare($query);
              
                $this->email=htmlspecialchars(strip_tags($this->email));
                $this->phoone=htmlspecialchars(strip_tags($this->phone));
                $this->password=htmlspecialchars(strip_tags($this->password));

                $res = $stmt->execute([
                    $this->email, 
                    $this->phone, 
                    $this->password, 
                    0
                ]); 

                $id = $this->conn->lastInsertId();
        
                return $id;
            } catch(PDOExecption $e) {
                $this->conn->rollback();
                echo "Error!: " . $e->getMessage() . "</br>";
            }
        }
    }
?>