<?php

    require_once ("models/User.php");
    require_once ("models/Message.php");

    class UserDAO implements UserDAOInterface
    {
        private $conn;
        private $url;
        private $message;

        public function __construct(PDO $conn, $url){
            $this->conn = $conn;
            $this->url = $url; 
            $this->message = new Message($url);
        }
        public function buildUser($data){
            $user = new User();
            $user->id = $data["id"];
            $user->name = $data["name"];
            $user->lastname = $data["lastname"];
            $user->email = $data["email"];
            $user->password = $data["password"];
            $user->image = $data["image"];
            $user->bio = $data["bio"];
            $user->token = $data["token"];

            return $user;
        }
        public function create(User $user, $authUser = false){
            $statement = $this->conn->prepare("INSERT INTO users(name,lastname,email, password, token) 
            VALUES(:name,:lastname,:email, :password, :token)");

            $statement->bindParam(":name",$user->name);
            $statement->bindParam(":lastname",$user->lastname);
            $statement->bindParam(":email",$user->email);
            $statement->bindParam(":password",$user->password);
            $statement->bindParam(":token",$user->token);

            $statement->execute();

            // Autenticar usuário, caso auth seja true~

            if($authUser){
                $this->setTokenToSession($user->token);
            }

        }
        public function update(User $user, $redirect = true){

            $statement = $this->conn->prepare("UPDATE users SET name = :name, lastname = :lastname, email = :email, image = :image, bio = :bio, token = :token WHERE id = :id ");

            $statement->bindParam(":name", $user->name);
            $statement->bindParam(":lastname", $user->lastname);
            $statement->bindParam(":email", $user->email);
            $statement->bindParam(":image", $user->image);
            $statement->bindParam(":bio", $user->bio);
            $statement->bindParam(":token", $user->token);
            $statement->bindParam(":id", $user->id);

            $statement->execute();

            if($redirect){
                // Redireciona para o perfil do usuário
                $this->message->setMessage("Dados atualizados com sucesso!","success","editprofile.php");
            }

        }
        public function verifyToken($protected = true){

            if(!empty($_SESSION["token"])){
                
                // Pega o token da session
                $token = $_SESSION["token"]; 

                $user = $this->findByToken($token);

                if($user){
                    return $user;
                }else if($protected){
                    // Redireciona usuário não autenticado
                    $this->message->setMessage("Faça a autenticação para acessar essa página!","error","index.php");
                }

            }else if($protected){
                // Redireciona usuário não autenticado
                $this->message->setMessage("Faça a autenticação para acessar essa página!","error","index.php");
            }

        }
        public function setTokenToSession($token, $redirect = true){

            // Salvar token na session
            $_SESSION["token"] = $token;

            if($redirect){
                // Redireciona para o perfil do usuário
                $this->message->setMessage("Seja bem-vindo!","success","editprofile.php");
            }

        }
        public function authenticateUser($email, $password){

            $user = $this->findByEmail($email);
            if($user){

                // Checar se as senhas batem
                if(password_verify($password, $user->password)){

                    // Gerar um token e inserir na session

                    $token = $user->generateToken();

                    $this->setTokenToSession($token, false);

                    // Atualizar token no usuário

                    $user->token = $token;

                    $this->update($user, false);

                    return true;

                }else{  
                    return false;
                }

            }else{
                return false;
            }

        }
        public function findByEmail($email){

            if($email != ""){
                $statement = $this->conn->prepare("SELECT * FROM users WHERE email = :email");
                $statement->bindParam(":email",$email);
                $statement->execute();

                if($statement->rowCount() > 0){

                    $data = $statement->fetch();
                    $user =  $this->buildUser($data);

                    return $user;  
                    

                }else{
                    return false;
                }
            }else{  
                return false;
            }

        }
        public function findById($id){ 
            if($id != ""){
                $statement = $this->conn->prepare("SELECT * FROM users WHERE id = :id");
                $statement->bindParam(":id",$id);
                $statement->execute();

                if($statement->rowCount() > 0){

                    $data = $statement->fetch();
                    $user =  $this->buildUser($data);

                    return $user;  
                    

                }else{
                    return false;
                }
            }else{  
                return false;
            }
        }
        public function findByToken($token){

            if($token != ""){
                $statement = $this->conn->prepare("SELECT * FROM users WHERE token = :token");
                $statement->bindParam(":token",$token);
                $statement->execute();

                if($statement->rowCount() > 0){

                    $data = $statement->fetch();
                    $user =  $this->buildUser($data);

                    return $user;  
                    

                }else{
                    return false;
                }
            }else{  
                return false;
            }

        }

        public function destroyToken(){

            // Remove o token da session
            $_SESSION["token"] = "";

            // Redirecionar e apresentar a mensagem de sucesso
            $this->message->setMessage("Você fez o logout com sucesso!", "success","index.php");

        }
        public function changePassword(User $user){

            $statement = $this->conn->prepare("UPDATE users SET password = :password WHERE id = :id");
            $statement->bindParam(":password", $user->password);
            $statement->bindParam(":id", $user->id);
            $statement->execute();

            // Redirecionar e apresentar a mensagem de sucesso
            $this->message->setMessage("Senha alterada com sucesso!", "success","editprofile.php");
        }
    }