<?php
include_once("header.php");
include_once("fonctions.php");

if (isset($_GET['actor_id']) AND !empty($_GET['actor_id'])) {
    $actorId = $_GET['actor_id'] ?? 0;
} else {
    header("Location: popular.php");
    exit;
}

$actor = getActorById($actorId);
$actorEnglish = getActorById($actorId, "en-US");
$films = getFilmsByActorId($actorId);

$biography = $actor["biography"] === "" ? $actorEnglish["biography"] . " (Pas de biographie en français)" : $actor["biography"];

// Calcul de l'âge
$age = "Inconnu";
if (isset($actor['birthday']) && !empty($actor['birthday'])) {
    $birthDate = new DateTime($actor['birthday']);
    $today = new DateTime();
    $age = $today->diff($birthDate)->y . " ans";
}

// Vérifier si le profil existe
$hasProfile = isset($actor['profile_path']) && !empty($actor['profile_path']);
?>

<div class="container py-5">
    <!-- En-tête avec photo et infos principales -->
    <div class="row mb-5">
        <div class="col-md-4 mb-4">
            <?php if ($hasProfile): ?>
                <img src="<?= 'https://image.tmdb.org/t/p/w342/' . $actor['profile_path']; ?>"
                     class="img-fluid rounded shadow" alt="<?= $actor['name']; ?>">
            <?php else: ?>
                <div class="bg-secondary text-white text-center p-5 rounded">
                    <i class="bi bi-person-fill" style="font-size: 4rem;"></i>
                    <p class="mt-2">Photo non disponible</p>
                </div>
            <?php endif; ?>
        </div>
        <div class="col-md-8">
            <h1 class="mb-3"><?= $actor['name']; ?></h1>

            <!-- Infos personnelles -->
            <div class="row text-muted mb-4">
                <div class="col-6 col-md-6">
                    <p><strong>Âge :</strong><br><?= $age; ?></p>
                </div>
                <div class="col-6 col-md-6">
                    <p><strong>Date de naissance :</strong><br><?= $actor['birthday'] ?? "Inconnue"; ?></p>
                </div>
            </div>

            <div class="row text-muted mb-4">
                <div class="col-12">
                    <p><strong>Lieu de naissance :</strong><br><?= $actor['place_of_birth'] ?? "Inconnu"; ?></p>
                </div>
            </div>

            <!-- Popularité -->
            <div class="alert alert-info" role="alert">
                <strong>Popularité :</strong> <?= round($actor['popularity'], 2); ?>
            </div>

            <!-- Bouton retour -->
            <button type="button" class="btn btn-secondary" onclick="history.back()">
                <i class="bi bi-arrow-left"></i> Retour
            </button>
        </div>
    </div>

    <!-- Biographie -->
    <?php if (!empty($biography)): ?>
        <div class="row mb-5">
            <div class="col-12">
                <h3 class="mb-3">Biographie</h3>
                <p class="text-justify"><?= nl2br(htmlspecialchars($biography)); ?></p>
            </div>
        </div>
    <?php endif; ?>

    <!-- Films de l'acteur -->
    <div class="mb-5">
        <h3 class="mb-4">Films (<?= count($films); ?>)</h3>
        <?php if (!empty($films)): ?>
            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-4 g-4">
                <?php foreach ($films as $film): ?>
                    <?php if (isset($film['poster_path']) && $film['poster_path']): ?>
                        <div class="d-flex align-items-stretch">
                            <div class="card shadow-sm w-100">
                                <img src="<?= 'https://image.tmdb.org/t/p/w342/' . $film['poster_path']; ?>"
                                     class="card-img-top" alt="<?= $film['title']; ?>">
                                <div class="card-body lh-sm d-flex flex-column">
                                    <h5 class="card-title small"><?= htmlspecialchars($film['title']); ?></h5>
                                    <p class="card-text text-muted">
                                        <small>
                                            <strong>Sortie :</strong> <?= $film['release_date'] ?? "Inconnue"; ?>
                                        </small>
                                    </p>
                                    <?php if (isset($film['vote_average']) && $film['vote_average'] > 0): ?>
                                        <p class="card-text text-muted">
                                            <small><strong>Note :</strong> <?= $film['vote_average']; ?>/10</small>
                                        </p>
                                    <?php endif; ?>
                                    <button type="button" class="btn btn-primary btn-sm mt-auto"
                                            onclick="location.href='movie.php?movie_id=<?= $film['id'] ?>'">
                                        Voir le film
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info">Aucun film trouvé pour cet acteur.</div>
        <?php endif; ?>
    </div>
</div>

<?php require("footer.php"); ?>
