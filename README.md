# DESAFIO BACKEND

## Configuração do Ambiente

### Requisitos
- Instalar o _PHP >= 8.0_ e [extensões](https://www.php.net/manual/pt_BR/extensions.php) (**não esquecer de instalar as seguintes extensões: _pdo_, _pdo_sqlite_ e _sqlite3_**);
- Instalar o [_SQLite_](https://www.sqlite.org/index.html);
- Instalar o [_Composer_](https://getcomposer.org/).

### Instalação
- Instalar dependências pelo _composer_ com `composer install` na raiz do projeto;
- Servir a pasta _public_ do projeto através de algum servidor.
  (_Sugestão [PHP Built in Server](https://www.php.net/manual/en/features.commandline.webserver.)_. Exemplo para servir a pasta public: `php -S localhost:8000 -t public`)

## Sobre o Projeto

- O cliente XPTO Ltda. contratou seu serviço para realizar alguns ajustes em seu sistema de cadastro de produtos;
- O sistema permite o cadastro, edição e remoção de _produtos_ e _categorias de produtos_ para uma _empresa_;
- Para que sejam possíveis os cadastros, alterações e remoções é necessário um usuário administrador;
- O sistema possui categorias padrão que pertencem a todas as empresas, bem como categorias personalizadas dedicadas a uma dada empresa. As categorias padrão são: (`clothing`, `phone`, `computer` e `house`) e **devem** aparecer para todas as _empresas_;
- O sistema tem um relatório de dados dedicado ao cliente.

## Sobre a API
As rotas estão divididas em:
  -  _CRUD_ de _categorias_;
  - _CRUD_ de _produtos_;
  - Rota de busca de um _relatório_ que retorna um _html_.

E podem ser acessadas através do uso do Insomnia, Postman ou qualquer ferramenta de sua preferência.

**Atenção**, é bem importante que se adicione o _header_ `admin_user_id` com o id do usuário desejado ao acessar as rotas para simular o uso de um usuário no sistema.

A documentação da API se encontra na pasta `docs/api-docs.pdf`
  - A documentação assume que a url base é `localhost:8000` mas você pode usar qualquer outra url ao configurar o servidor;
  - O _header_ `admin_user_id` na documentação está indicado com valor `1` mas pode ser usado o id de qualquer outro usuário caso deseje (_pesquisando no banco de dados é possível ver os outros id's de usuários_).
  
Caso opte por usar o [Insomnia](https://insomnia.rest/) o arquivo para importação se encontra em `docs/insomnia-api.json`.
Caso opte por usar o [Postman](https://www.postman.com/) o arquivo para importação se encontra em `docs/postman-api.json`.

## Sobre o Banco de Dados
- O banco de dados é um _sqlite_ simples e já vem com dados preenchidos por padrão no projeto;
- O banco tem um arquivo de backup em `db/db-backup.sqlite` com o estado inicial do projeto caso precise ser "resetado".

## Demandas
Abaixo, as solicitações do cliente:

### Categorias
- [x] A categoria está vindo errada na listagem de produtos para alguns casos
  (_exemplo: produto `blue trouser` está vindo na categoria `phone` e deveria ser `clothing`_);
- [x] Alguns produtos estão vindo com a categoria `null` ao serem pesquisados individualmente (_exemplo: produto `iphone 8`_);
- [x] Cadastrei o produto `king size bed` em mais de uma categoria, mas ele aparece **apenas** na categoria `furniture` na busca individual do produto.

### Filtros e Ordenamento
Para a listagem de produtos:
- [x] Gostaria de poder filtrar os produtos ativos e inativos;
- [x] Gostaria de poder filtrar os produtos por categoria;
- [x] Gostaria de poder ordenar os produtos por data de cadastro.

### Relatório
- [x] O relatório não está mostrando a coluna de logs corretamente, se possível, gostaria de trazer no seguinte formato:
  (Nome do usuário, Tipo de alteração e Data),
  (Nome do usuário, Tipo de alteração e Data),
  (Nome do usuário, Tipo de alteração e Data)
  Exemplo:
  (John Doe, Criação, 01/12/2023 12:50:30),
  (Jane Doe, Atualização, 11/12/2023 13:51:40),
  (Joe Doe, Remoção, 21/12/2023 14:52:50)

### Logs
- [x] Gostaria de saber qual usuário mudou o preço do produto `iphone 8` por último.

### Extra
- [x] Aqui fica um desafio extra **opcional**: _criar um ambiente com_ Docker _para a api_.

**Seu trabalho é atender às 7 demandas solicitadas pelo cliente.**

Caso julgue necessário, podem ser adicionadas ou modificadas as rotas da api. Caso altere, por favor, explique o porquê e indique as alterações nesse `README`.

Sinta-se a vontade para refatorar o que achar pertinente, considerando questões como arquitetura, padrões de código, padrões restful, _segurança_ e quaisquer outras boas práticas. Levaremos em conta essas mudanças.

Boa sorte! :)

## Suas Respostas, Duvidas e Observações

### Ambiente

Este projeto foi desenvolvido utilizando a versão `8.0-apache` do PHP, além de extensões necessárias, e a execução da API é facilitada através do Docker. Foram criados dois arquivos, Dockerfile e docker-compose.yml, para a construção e execução do container, respectivamente.

## Configuração do Ambiente

1. **Clone este repositório:**

    ```bash
    git clone https://github.com/Henrique-Navarro/TesteBackend-ContatoSeguro.git
    ```

2. **Execute o Docker Compose no diretório raiz do projeto para iniciar os serviços:**

    ```bash
    cd ./TesteBackend-ContatoSeguro
    docker-compose up -d
    ```

    2.1 Em caso de erro na instalação das dependências do projeto, instale-as manualmente:
    
    - Acesse o container: 
        ```bash
        docker exec -it <id_do_container> bash
        ```
    - Dentro do container, execute:
        ```bash
        composer install
        ```
    - Saia do container:
        ```bash
        exit
        ```

    Para verificar se o container está em execução, utilize o comando:
    
    ```bash
    docker ps
    ```
    
    - Certifique-se de incluir o diretório `db` contendo os arquivos do banco de dados `SQLite`.
      
3. **Aplicação em execução em:**

    [http://localhost:8000](http://localhost:8000)

4. **Para encerrar o ambiente Docker:**

    ```bash
    docker-compose down
    ```


### Funcionalidades

As categorias padrões do sistema (`clothing`, `phone`, `computer` e `house`), bem como as categorias dedicadas a uma dada empresa estão aparecendo corretamente para as empresas, para isso, foram modificadas as queries das funções `getAll`, `getOne`, `updateOne` e `deleteOne` da classe CategoryController:

```
$query = "
            SELECT *
            FROM category c
            WHERE (c.company_id = {$this->getCompanyFromAdminUser($adminUserId)}
            OR c.company_id IS NULL)
        ";
```

Categorias com o atributo `company_id` com o valor `NULL`, foram consideradas as categorias padrões e são visíveis para todas as companias;

A query da função `getProductCategory` também foi modificada para que funcionasse corretamente:

```
$query = "
            SELECT c.*
            FROM category c
            INNER JOIN product_category pc
                ON pc.cat_id = c.id
            WHERE pc.product_id = {$productId}
        ";
```


### Demandas

#### Categorias

* O problema da categoria estar sendo retornada incorretamente foi ajustada, ajustando a query SQL da função getAll da classe ProductService:

```
$query = "
            SELECT p.*, c.title as category
            FROM product p
            INNER JOIN product_category pc ON pc.product_id = p.id
            INNER JOIN category c ON c.id = pc.cat_id
            WHERE p.company_id = {$adminUserId}
        ";
```

* O problema da categoria retornando como nula ao pesquisar um produto individualmente foi resolvida, modificando a query SQL da função getProductCategory, juntamente com a query da função getOne, citadas acima


* A propriedade `category` de `Product` foi alterada para ser um array, permitindo que os produtos possam ter mais de uma categoria. Agora, ao pesquisar todas as categorias de um produto, os resultados são exibidos corretamente. 

Na classe `Product`:

```
public $category = [];
```

#### Filtros e Ordenamento

* Modificação na função getAll(), que agora aceita três parâmetros: `activeOnly` (booleano), `orderByDate` (booleano) e `categoryId` (inteiro). Essa melhoria possibilita a filtragem de produtos ativos/inativos, por categoria, e a ordenação por data de cadastro. Para cada parâmetro fornecido, é realizada uma verificação para garantir que seu valor seja válido. Em caso afirmativo, a query é ajustada para incorporar a opção correspondente, garantindo assim a eficácia da funcionalidade solicitada.

```
$query = "SELECT p.*, c.title as category FROM product p INNER JOIN product_category pc ON pc.product_id = p.id INNER JOIN category c ON c.id = pc.cat_id WHERE p.company_id = {$adminUserId}";

if ($activeOnly) 
    $query .= " AND p.active = 1";

if ($categoryId !== null)
    $query .= " AND c.id = {$categoryId}";

if ($orderByDate) 
    $query .= " ORDER BY p.created_at DESC";
```

Exemplo de uso: 

```localhost:8000/products?activeOnly=false&orderByDate=false&categoryId=4```

#### Relatório

A função `generate`, encarregada de criar um relatório, retorna o HTML de uma tabela seguindo o padrão abaixo, demandado pelo cliente:

```
<table style='font-size: 10px;'>
    <tr>
        <td>nome do produto: (adminUserId, action, date), </td>
    </tr>
</table>
```

Não foi possível retornar o nome do administrador conforme solicitado pelo cliente, sendo disponibilizado apenas o ID. A função aceita os mesmos parâmetros de ordenamento e filtragem (`activeOnly`, `orderByDate`, `categoryId`), além de um parâmetro adicional `productId` que facilita a busca, permitindo identificar qual usuário realizou as alterações em um determinado produto.

Exemplo de uso: 
```localhost:8000/report?orderByDate=true&activeOnly=true&categoryId=4```

Exemplo de uso:
```localhost:8000/report?orderByDate=true&activeOnly=true&productId=4```

### Modificações

#### Tratamento de Erros

* Implementação de blocos try-catch em *todas* as funções das classes do pacote `Controller` para assegurar um tratamento robusto de erros.

Um exemplo do tratamento de erros na função `insertOne` da classe `ProductController`:

```
public function insertOne(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try
        {
            $body = $request->getParsedBody();
            $adminUserId = $this->service->validateAdminUserId($request->getHeader('admin_user_id')[0]);
    
            if ($this->service->insertOne($body, $adminUserId)) {
                return $response->withStatus(200);
            } else {
                return $response->withStatus(404);
            }
        }
        catch(\Exception $e)
        {
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
            return $response->withStatus(400);
        }
    }
```

* Adição de verificações minuciosas nas respectivas classes do pacote `Service`, resultando no lançamento de exceções específicas e no retorno de status apropriados para cada situação

* Criação de funções dedicadas ao tratamento de erros em algumas classes do pacote `Service`, como exemplificado na classe `CategoryService`:

```
private function validateCategoryBody($body)
    {
        $requiredAttributes = ['title', 'active'];
    
        foreach ($requiredAttributes as $attribute) {
            if (!isset($body[$attribute])) {
                throw new \InvalidArgumentException("Missing required attribute: {$attribute}");
            }
        }
    
        if (!is_string($body['title']) || !is_bool($body['active'])) {
            throw new \InvalidArgumentException("Invalid attribute types in the body");
        }
    }

    private function validateCategoryId($id)
    {
        if (!ctype_digit(strval($id)) || $id <= 0) {
            throw new \InvalidArgumentException("Invalid category ID");
        }
    }

    public function validateAdminUserId($adminUserId){
        if (!ctype_digit(strval($adminUserId)) || $adminUserId <= 0) {
            throw new \InvalidArgumentException("Invalid admin user ID");
        }
        return $adminUserId;
    }
```

#### Padrões RESTful

* Criação da classe `ReportService` para colaborar com ReportController, promovendo uma estrutura mais organizada e uma definição nítida das responsabilidades de cada classe.

* Migração das queries SQL pertinentes da classe `CompanyController` para a classe `CompanyService`. A criação dos métodos `getAll` e `getOne` proporcionou uma organização aprimorada e uma delineação mais clara das funções de cada classe.

Exemplo da função `getAll` na classe `CompanyService`:

```
public function getAll()
    {
        $stm = $this->pdo->prepare("
            SELECT *    
            FROM company
        ");

        if (!$stm->execute())
            throw new \RuntimeException("Failed to retrieve companies.");

        return $stm;
    }
```
