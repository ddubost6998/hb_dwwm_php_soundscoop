<?php
require_once 'layout/header.php';
require_once 'classes/DbConnection.php';

try {
    $pdo = new DbConnection();
} catch (PDOException $e) {
    echo("Erreur de connexion à la base de données : " . $e->getMessage());
}

$articlesByPage = 5;
$totalArticles  = $pdo->query("SELECT COUNT(*) FROM article")->fetchColumn();
$totalPages     = ceil($totalArticles / $articlesByPage);
$page           = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset         = ($page - 1) * $articlesByPage;

$stmt = $pdo->prepare("SELECT article.*, categorie.name_categorie
                    FROM article
                    INNER JOIN article_categorie ON article.id_article = article_categorie.article_id
                    INNER JOIN categorie ON article_categorie.categorie_id = categorie.id_categorie
                    ORDER BY article.issue_date DESC
                    LIMIT :offset, :articlesByPage");
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->bindParam(':articlesByPage', $articlesByPage, PDO::PARAM_INT);
$stmt->execute();

$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($articles) { ?>
    <main class="prose mx-auto my-32 dark:text-white dark:border-gray-600 dark:focus:border-purple-500">
        
        <h1 class="text-center dark:text-white dark:border-gray-600 dark:focus:border-purple-500">Tous nos articles</h1>

        <?php foreach ($articles as $article) { ?>
            <div class="mt-16 mb-8 p-5 items-center bg-purple-300 rounded-lg shadow-lg hover:bg-purple-200 dark:border-gray-700 dark:bg-gray-800 dark:hover-bg-gray-700">
                <img class="rounded-lg mt-0 w-auto" src="<?php echo $article['url_img']; ?>" alt="Image de <?php echo $article['title']; ?>"/>
                <h5 class="mb-1 text-2xl font-bold tracking-tight text-gray-900 dark:text-white"><?php echo $article['title']; ?></h5>
                <p>Publier le : <?php echo date('d/m/Y', strtotime($article['issue_date']))?></p>
                <p class="mb-3 font-normal text-gray-700 dark:text-gray-400">
                    <?php
                    $content = $article['content'];
                    if (strlen($content) > 45) {
                        $content = substr($content, 0, 45) . '...';
                    }
                    echo $content;
                    ?>
                </p>
                <p class="bg-purple-100 p-2 w-32"><?php echo $article['name_categorie'];?></p>
                <a href="article.php?id_article=<?php echo $article['id_article']; ?>" class="w-auto flex items-center px-3 py-2 text-center text-sm font-medium text-white bg-purple-700 rounded-lg hover:bg-purple-800 focus:ring-4 focus:outline-none focus:ring-purple-300 dark:bg-purple-400 dark:hover:bg-purple-600 dark:focus:ring-purple-800">En lire plus
                    <svg class="w-3.5 h-3.5 ml-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 10">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 5h12m0 0L9 1m4 4L9 9"/>
                    </svg>
                </a>
            </div>
        <?php } ?>

        <div class="pagination text-center">
            <?php if ($totalPages > 1): ?>
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo ($page - 1); ?>" class="prev">Précédent</a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?page=<?php echo $i; ?>" class="<?php echo ($i === $page) ? 'active' : ''; ?>"><?php echo $i; ?></a>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?php echo ($page + 1); ?>" class="next">Suivant</a>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        
    </main>
    <?php } else {
        echo "Erreur lors de l'exécution de la requête : " . $pdo->errorInfo()[2];
    }

require_once 'layout/footer.php';
