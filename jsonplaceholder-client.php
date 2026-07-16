<?php

declare(strict_types=1);

require './vendor/autoload.php';

use AndyDefer\PhpClient\Abstracts\Graph;
use AndyDefer\PhpClient\Abstracts\Request;
use AndyDefer\PhpClient\Abstracts\Response;
use AndyDefer\PhpClient\Abstracts\Struct;
use AndyDefer\PhpClient\Clients\ClientService;
use AndyDefer\PhpClient\Enums\ContentType;
use AndyDefer\PhpClient\Enums\HttpMethod;
use AndyDefer\PhpClient\ValueObjects\RequestBodyVO;
use AndyDefer\PhpClient\ValueObjects\UrlQueryVO;
use AndyDefer\PhpClient\ValueObjects\UrlVO;

// ============================================================
// 1. ENUM - Centralisation des URLs
// ============================================================

/**
 * Enum des endpoints JSONPlaceholder.
 */
enum PlaceholderEndpoint: string
{
    case POSTS = 'https://jsonplaceholder.typicode.com/posts';
    case COMMENTS = 'https://jsonplaceholder.typicode.com/comments';

    public function getUrl(): UrlVO
    {
        return new UrlVO($this->value);
    }

    public function withId(int $id): UrlVO
    {
        $baseUrl = $this->getUrl();
        $path = parse_url($this->value, PHP_URL_PATH).'/'.$id;

        return $baseUrl->withPath($path);
    }

    public function withQuery(array $params): UrlVO
    {
        $baseUrl = $this->getUrl();
        $query = http_build_query($params);

        return $baseUrl->withQuery(new UrlQueryVO($query));
    }
}

// ============================================================
// 2. GRAPH - Portion de structure
// ============================================================

/**
 * Représente un post JSONPlaceholder.
 */
final class PostGraph extends Graph
{
    public function __construct(
        public readonly int $userId,
        public readonly int $id,
        public readonly string $title,
        public readonly string $body,
    ) {}
}

/**
 * Représente un commentaire JSONPlaceholder.
 */
final class CommentGraph extends Graph
{
    public function __construct(
        public readonly int $postId,
        public readonly int $id,
        public readonly string $name,
        public readonly string $email,
        public readonly string $body,
    ) {}
}

// ============================================================
// 3. STRUCT - Structure complète de réponse
// ============================================================

/**
 * Structure pour la liste des posts.
 */
final class PostListStruct extends Struct
{
    /**
     * @param  PostGraph[]  $posts
     */
    public function __construct(
        public readonly array $posts,
    ) {}
}

/**
 * Structure pour la liste des commentaires.
 */
final class CommentListStruct extends Struct
{
    /**
     * @param  CommentGraph[]  $comments
     */
    public function __construct(
        public readonly array $comments,
    ) {}
}

/**
 * Structure pour un commentaire unique.
 */
final class CommentStruct extends Struct
{
    public function __construct(
        public readonly int $postId,
        public readonly int $id,
        public readonly string $name,
        public readonly string $email,
        public readonly string $body,
    ) {}
}

/**
 * Structure pour la création d'un post.
 */
final class CreatePostStruct extends Struct
{
    public function __construct(
        public readonly string $title,
        public readonly string $body,
        public readonly int $userId,
    ) {}
}

/**
 * Structure pour la réponse de création.
 */
final class CreatedPostStruct extends Struct
{
    public function __construct(
        public readonly int $id,
        public readonly string $title,
        public readonly string $body,
        public readonly int $user_id,
    ) {}
}

// ============================================================
// 4. REQUESTS
// ============================================================

/**
 * Requête GET /posts - Récupère tous les posts.
 */
final class GetPostsRequest extends Request
{
    protected function setMethod(): HttpMethod
    {
        return HttpMethod::GET;
    }

    protected function setUrl(): UrlVO
    {
        return PlaceholderEndpoint::POSTS->getUrl();
    }

    protected function setBody(): RequestBodyVO
    {
        return new RequestBodyVO(
            new class extends Struct {},
            ContentType::JSON
        );
    }
}

/**
 * Requête GET /comments - Récupère tous les commentaires.
 */
final class GetCommentsRequest extends Request
{
    private ?int $postId = null;

    public function setPostId(int $postId): self
    {
        $this->postId = $postId;

        return $this;
    }

    protected function setMethod(): HttpMethod
    {
        return HttpMethod::GET;
    }

    protected function setUrl(): UrlVO
    {
        if ($this->postId !== null) {
            return PlaceholderEndpoint::COMMENTS->withQuery(['postId' => $this->postId]);
        }

        return PlaceholderEndpoint::COMMENTS->getUrl();
    }

    protected function setBody(): RequestBodyVO
    {
        return new RequestBodyVO(
            new class extends Struct {},
            ContentType::JSON
        );
    }
}

/**
 * Requête GET /comments/{id} - Récupère un commentaire spécifique.
 */
final class GetCommentRequest extends Request
{
    private int $id = 0;

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    protected function setMethod(): HttpMethod
    {
        return HttpMethod::GET;
    }

    protected function setUrl(): UrlVO
    {
        return PlaceholderEndpoint::COMMENTS->withId($this->id);
    }

    protected function setBody(): RequestBodyVO
    {
        return new RequestBodyVO(
            new class extends Struct {},
            ContentType::JSON
        );
    }
}

/**
 * Requête POST /posts - Crée un nouveau post.
 */
final class CreatePostRequest extends Request
{
    private string $title = '';

    private string $content = '';

    private int $userId = 0;

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function setUserId(int $userId): self
    {
        $this->userId = $userId;

        return $this;
    }

    protected function setMethod(): HttpMethod
    {
        return HttpMethod::POST;
    }

    protected function setUrl(): UrlVO
    {
        return PlaceholderEndpoint::POSTS->getUrl();
    }

    protected function setBody(): RequestBodyVO
    {
        $struct = new CreatePostStruct(
            title: $this->title,
            body: $this->content,
            userId: $this->userId
        );

        return new RequestBodyVO($struct, ContentType::JSON);
    }
}

// ============================================================
// 5. RESPONSES
// ============================================================

/**
 * Réponse pour la liste des posts.
 */
final class PostListResponse extends Response
{
    /**
     * @return PostGraph[]
     */
    public function getPosts(): array
    {
        $data = $this->getBody()->format();
        $posts = [];
        foreach ($data as $item) {
            $posts[] = PostGraph::from($item);
        }

        return $posts;
    }

    /**
     * @return PostGraph[]
     */
    public function getPostsByUserId(int $userId): array
    {
        return array_filter(
            $this->getPosts(),
            fn (PostGraph $post): bool => $post->userId === $userId
        );
    }

    public static function getStructClass(): string
    {
        return PostListStruct::class;
    }
}

/**
 * Réponse pour la liste des commentaires.
 */
final class CommentListResponse extends Response
{
    /**
     * @return CommentGraph[]
     */
    public function getComments(): array
    {
        $data = $this->getBody()->format();
        $comments = [];
        foreach ($data as $item) {
            $comments[] = CommentGraph::from($item);
        }

        return $comments;
    }

    /**
     * @return CommentGraph[]
     */
    public function getCommentsByPostId(int $postId): array
    {
        return array_filter(
            $this->getComments(),
            fn (CommentGraph $comment): bool => $comment->postId === $postId
        );
    }

    public static function getStructClass(): string
    {
        return CommentListStruct::class;
    }
}

/**
 * Réponse pour un commentaire unique.
 */
final class CommentResponse extends Response
{
    public function getComment(): CommentGraph
    {
        $data = $this->getBody()->format();

        return CommentGraph::from($data);
    }

    public static function getStructClass(): string
    {
        return CommentStruct::class;
    }
}

/**
 * Réponse pour la création d'un post.
 */
final class CreatePostResponse extends Response
{
    public function getCreatedPost(): CreatedPostStruct
    {
        $data = $this->getBody()->format();

        if (is_object($data)) {
            $data = (array) $data;
        }

        return new CreatedPostStruct(
            id: (int) ($data['id'] ?? 0),
            title: (string) ($data['title'] ?? ''),
            body: (string) ($data['body'] ?? ''),
            user_id: (int) ($data['user_id'] ?? 0)
        );
    }

    public function getId(): int
    {
        return $this->getCreatedPost()->id;
    }

    public function getTitle(): string
    {
        return $this->getCreatedPost()->title;
    }

    public function getContent(): string
    {
        return $this->getCreatedPost()->body;
    }

    public function getUserId(): int
    {
        return $this->getCreatedPost()->user_id;
    }

    public static function getStructClass(): string
    {
        return CreatedPostStruct::class;
    }
}

// ============================================================
// 6. CLIENT
// ============================================================

class JsonPlaceholderClient
{
    private ClientService $client;

    public function __construct()
    {
        $this->client = new ClientService;
    }

    public function getPosts(): array
    {
        $request = new GetPostsRequest;

        $response = $this->client->get(
            PlaceholderEndpoint::POSTS->getUrl()->getValue(),
            $request,
            PostListResponse::class
        );

        if ($response->isError()) {
            throw new RuntimeException(
                'Erreur lors de la récupération des posts: '.$response->getStatusCode()->getPhrase()
            );
        }

        return $response->getPosts();
    }

    public function getPostsByUser(int $userId): array
    {
        $posts = $this->getPosts();

        return array_filter(
            $posts,
            fn (PostGraph $post): bool => $post->userId === $userId
        );
    }

    public function getComments(?int $postId = null): array
    {
        $request = new GetCommentsRequest;
        if ($postId !== null) {
            $request->setPostId($postId);
        }

        $response = $this->client->get(
            PlaceholderEndpoint::COMMENTS->getUrl()->getValue(),
            $request,
            CommentListResponse::class
        );

        if ($response->isError()) {
            throw new RuntimeException(
                'Erreur lors de la récupération des commentaires: '.$response->getStatusCode()->getPhrase()
            );
        }

        return $response->getComments();
    }

    public function getCommentsByPost(int $postId): array
    {
        return $this->getComments($postId);
    }

    public function getComment(int $id): CommentGraph
    {
        $request = new GetCommentRequest;
        $request->setId($id);

        $response = $this->client->get(
            PlaceholderEndpoint::COMMENTS->withId($id)->getValue(),
            $request,
            CommentResponse::class
        );

        if ($response->isError()) {
            throw new RuntimeException(
                'Erreur lors de la récupération du commentaire: '.$response->getStatusCode()->getPhrase()
            );
        }

        return $response->getComment();
    }

    public function createPost(string $title, string $content, int $userId): CreatedPostStruct
    {
        $request = new CreatePostRequest;
        $request
            ->setTitle($title)
            ->setContent($content)
            ->setUserId($userId);

        $request->getHeaders()
            ->setContentType(ContentType::JSON)
            ->setAccept(ContentType::JSON);

        $response = $this->client->post(
            PlaceholderEndpoint::POSTS->getUrl()->getValue(),
            $request,
            CreatePostResponse::class
        );

        if ($response->isError()) {
            throw new RuntimeException(
                'Erreur lors de la création du post: '.$response->getStatusCode()->getPhrase()
            );
        }

        return $response->getCreatedPost();
    }
}

// ============================================================
// 7. UTILISATION
// ============================================================

echo "=== JSONPlaceholder Client - Exemple d'utilisation ===\n\n";

$client = new JsonPlaceholderClient;

// Exemple 1 : Récupérer tous les posts
echo "1. Récupération de tous les posts:\n";
try {
    $posts = $client->getPosts();
    echo '   Nombre de posts: '.count($posts)."\n";
    echo "   Premier post:\n";
    echo '   - ID: '.$posts[0]->id."\n";
    echo '   - Titre: '.substr($posts[0]->title, 0, 50)."...\n";
    echo '   - User ID: '.$posts[0]->userId."\n\n";
} catch (Exception $e) {
    echo '   Erreur: '.$e->getMessage()."\n\n";
}

// Exemple 2 : Récupérer les posts d'un utilisateur spécifique
echo "2. Récupération des posts de l'utilisateur 1:\n";
try {
    $userPosts = $client->getPostsByUser(1);
    echo '   Nombre de posts: '.count($userPosts)."\n";
    echo "   Premier post:\n";
    echo '   - ID: '.$userPosts[0]->id."\n";
    echo '   - Titre: '.substr($userPosts[0]->title, 0, 50)."...\n\n";
} catch (Exception $e) {
    echo '   Erreur: '.$e->getMessage()."\n\n";
}

// Exemple 3 : Récupérer tous les commentaires
echo "3. Récupération de tous les commentaires:\n";
try {
    $comments = $client->getComments();
    echo '   Nombre de commentaires: '.count($comments)."\n";
    echo "   Premier commentaire:\n";
    echo '   - ID: '.$comments[0]->id."\n";
    echo '   - Nom: '.$comments[0]->name."\n";
    echo '   - Post ID: '.$comments[0]->postId."\n\n";
} catch (Exception $e) {
    echo '   Erreur: '.$e->getMessage()."\n\n";
}

// Exemple 4 : Récupérer les commentaires d'un post spécifique
echo "4. Récupération des commentaires du post 1:\n";
try {
    $postComments = $client->getCommentsByPost(1);
    echo '   Nombre de commentaires: '.count($postComments)."\n";
    echo "   Premier commentaire:\n";
    echo '   - ID: '.$postComments[0]->id."\n";
    echo '   - Email: '.$postComments[0]->email."\n\n";
} catch (Exception $e) {
    echo '   Erreur: '.$e->getMessage()."\n\n";
}

// Exemple 5 : Récupérer un commentaire spécifique
echo "5. Récupération du commentaire 1:\n";
try {
    $comment = $client->getComment(1);
    echo '   ID: '.$comment->id."\n";
    echo '   Nom: '.$comment->name."\n";
    echo '   Email: '.$comment->email."\n";
    echo '   Body: '.substr($comment->body, 0, 80)."...\n\n";
} catch (Exception $e) {
    echo '   Erreur: '.$e->getMessage()."\n\n";
}

// Exemple 6 : Créer un nouveau post
echo "6. Création d'un nouveau post:\n";
try {
    $created = $client->createPost(
        title: 'Mon nouveau post',
        content: 'Ceci est le contenu de mon nouveau post créé via l\'API JSONPlaceholder.',
        userId: 1
    );

    echo "   Post créé !\n";
    echo '   ID: '.$created->id."\n";
    echo '   Titre: '.$created->title."\n";
    echo '   Contenu: '.substr($created->body, 0, 50)."...\n";
    echo '   User ID: '.$created->user_id."\n\n";

} catch (Exception $e) {
    echo '   Erreur: '.$e->getMessage()."\n\n";
}
