<?php
namespace Api\P1Dwii\Controller;
class controller {
    protected $db;

    public function __construct($db) {
        $this->db = $db;
    }
    
    // Retorna todos os produtos
    public function getProdutos() {
        $stmt = $this->db->query("SELECT * FROM produtos");
        $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($produtos);
    }
    
    // Retorna os logs
    public function getLogs() {
        $stmt = $this->db->query("SELECT * FROM logs");
        $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($logs);
    }
    

    // Retorna um único produto
    public function getProduto($id) {
        $stmt = $this->db->prepare("SELECT * FROM produtos WHERE id = ?");
        $stmt->execute([$id]);
        $produto = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($produto) {
            echo json_encode($produto);
        } else {
            http_response_code(404);
            echo json_encode(["message" => "Produto não encontrado"]);
        }
    }

    // Cria o log e designa a ação
    private function logAcao($acao, $produtoId, $user) {
        $stmt = $this->db->prepare("INSERT INTO logs (acao, produto_id, userInsert) VALUES (?, ?, ?)");
        $stmt->execute([$acao, $produtoId, $user]);
    }

    // Cria um novo produto
    public function createProduto() {
        $data = json_decode(file_get_contents("php://input"), true);

        if (!$this->validarProduto($data)) {
            http_response_code(400);
            echo json_encode(["message" => "Dados invalidos! O nome do produto precisa ter mais de tres caracteres, o valor dele precisa ser positivo, e o estoque inteiro."]);
            return;
        }

        $stmt = $this->db->prepare("INSERT INTO produtos (nome, descricao, preco, estoque, userInsert) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$data['nome'], $data['descricao'], $data['preco'], $data['estoque'], $data['userInsert']]);

        // Registro de log
        $produtoId = $this->db->lastInsertId();
        $this->logAcao('Criado', $produtoId, $data['userInsert']);

        echo json_encode(["message" => "Produto criado com sucesso"]);
    }

    // Atualiza um produto
    public function updateProduto($id) {
        $data = json_decode(file_get_contents("php://input"), true);

        if (!$this->validarProduto($data)) {
            http_response_code(400);
            echo json_encode(["message" => "Dados inválidos"]);
            return;
        }

        $stmt = $this->db->prepare("UPDATE produtos SET nome = ?, descricao = ?, preco = ?, estoque = ? WHERE id = ?");
        $stmt->execute([$data['nome'], $data['descricao'], $data['preco'], $data['estoque'], $id]);
        
        // Registro de log
        $this->logAcao('Atualizado', $id, $data['userInsert']);

        echo json_encode(["message" => "Produto atualizado com sucesso"]);
    }

    // Deleta um produto
    public function deleteProduto($id) {
        // Obter o userInsert do produto antes de deletá-lo
        $stmt = $this->db->prepare("SELECT userInsert FROM produtos WHERE id = ?");
        $stmt->execute([$id]);
        $produto = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if ($produto) {
            // Registro de log
            $this->logAcao('Deletado', $id, $produto['userInsert']); 
    
            // Deletar o produto
            $stmt = $this->db->prepare("DELETE FROM produtos WHERE id = ?");
            $stmt->execute([$id]);
    
            echo json_encode(["message" => "Produto deletado com sucesso"]);
        } else {
            echo json_encode(["message" => "Produto nao encontrado"]);
        }
    }

    // Função para validar os dados do produto
    private function validarProduto($data) {
        return isset($data['nome'], $data['preco'], $data['estoque']) && strlen($data['nome']) >= 3 && $data['preco'] >= 0 && $data['estoque'] >= 0;
    }

    public function clearLogs() {
        $stmt = $this->db->prepare("DELETE FROM logs");
        $stmt->execute();
        echo json_encode(["message" => "Logs deletados com sucesso"]);
    }
}
