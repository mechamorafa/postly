<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailHandler {
    private $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function receberEmail() {
        $mail = new PHPMailer(true);

        try {
            // Configurações do servidor de e-mail
            $mail->isPOP3();
            $mail->Host = 'seu_servidor_pop3';
            $mail->Port = 110; // Porta POP3
            $mail->Username = 'seu_email@example.com';
            $mail->Password = 'sua_senha';
            $mail->setFrom('seu_email@example.com', 'Seu Nome');

            // Receber o e-mail
            $mail->connect();
            $mail->login();
            $mail->select('INBOX');

            // Pegar o último e-mail recebido
            $emailNumber = $mail->searchMailbox('ALL');
            $email = $mail->getMail($emailNumber[0]);

            // Inserir o conteúdo do e-mail no banco de dados
            if ($email) {
                $conteudoEmail = $email->textPlain; // Ou $email->textHtml para HTML

                $stmt = $this->db->prepare("INSERT INTO emails (conteudo) VALUES (:conteudo)");
                $stmt->bindParam(':conteudo', $conteudoEmail);
                $stmt->execute();
            }

            // Desconectar do servidor de e-mail
            $mail->disconnect();
        } catch (Exception $e) {
            echo 'Houve um erro: ' . $mail->ErrorInfo;
        }
    }
}

// Exemplo de utilização
try {
    // Conexão com o banco de dados MySQL via PDO
    $db = new PDO('mysql:host=seu_host;dbname=seu_banco_de_dados', 'seu_usuario', 'sua_senha');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Criar uma instância da classe EmailHandler
    $emailHandler = new EmailHandler($db);

    // Receber e inserir o e-mail no banco de dados
    $emailHandler->receberEmail();
} catch (PDOException $e) {
    echo 'Erro ao conectar com o banco de dados: ' . $e->getMessage();
}
?>
