<?php
include_once("header.php");
require("fonctions.php");

if (isset($_GET['movie_id']) AND !empty($_GET['movie_id'])) {
    $movieId = $_GET['movie_id'] ?? 0;
} else {
    header("Location: popular.php");
    exit;
}

$film = getFilmById($movieId);
$filmEnglish = getFilmById($movieId, "en-US");
$actors = getActorsByMovieId($movieId);
$trailer = getMovieTrailer($movieId);

$synopsis = $film["overview"] === "" ? $filmEnglish["overview"] . " (Pas de synopsis en français)" : $film["overview"];
$genres = isset($film['genres']) ? $film['genres'] : [];
$budget = isset($film['budget']) && $film['budget'] > 0 ? number_format($film['budget'], 0, ',', ' ') . " $" : "Non disponible";
$revenue = isset($film['revenue']) && $film['revenue'] > 0 ? number_format($film['revenue'], 0, ',', ' ') . " $" : "Non disponible";
$runtime = isset($film['runtime']) && $film['runtime'] > 0 ? $film['runtime'] . " min" : "Non disponible";
$tagline = !empty($film['tagline']) ? $film['tagline'] : "";
$status = $film['status'] ?? "Non disponible";
$originalLanguage = strtoupper($film['original_language'] ?? "-");
$productionCountries = array_map(function ($country) {
    return $country['name'];
}, $film['production_countries'] ?? []);
?>

<div class="container py-5">
    <!-- En-tête avec affiche et infos principales -->
    <div class="row mb-5">
        <div class="col-md-4 mb-4">
            <img src="<?= 'https://image.tmdb.org/t/p/w780/' . $film['poster_path']; ?>"
                 class="img-fluid rounded shadow" alt="<?= $film['title']; ?>">
        </div>
        <div class="col-md-8">
            <h1 class="mb-3"><?= $film['title']; ?></h1>

            <?php if ($tagline): ?>
                <p class="lead text-muted"><em><?= htmlspecialchars($tagline); ?></em></p>
            <?php endif; ?>

            <?php if (!empty($film['original_title']) && $film['original_title'] !== $film['title']): ?>
                <p class="text-muted mb-3"><em><?= $film['original_title']; ?></em></p>
            <?php endif; ?>

            <!-- Genres -->
            <?php if (!empty($genres)): ?>
                <div class="mb-3">
                    <strong>Genres : </strong>
                    <?php foreach ($genres as $genre): ?>
                        <span class="badge bg-secondary me-2"><?= htmlspecialchars($genre['name']); ?></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Infos principales -->
            <div class="row text-muted mb-4">
                <div class="col-6 col-md-4">
                    <p><strong>Date de sortie :</strong><br><?= $film['release_date']; ?></p>
                </div>
                <div class="col-6 col-md-4">
                    <p><strong>Durée :</strong><br><?= $runtime; ?></p>
                </div>
                <div class="col-6 col-md-4">
                    <p><strong>Note :</strong><br><?= $film['vote_average']; ?>/10 (<?= $film['vote_count']; ?> votes)</p>
                </div>
                <div class="col-6 col-md-4">
                    <p><strong>Statut :</strong><br><?= htmlspecialchars($status); ?></p>
                </div>
                <div class="col-6 col-md-4">
                    <p><strong>Langue originale :</strong><br><?= htmlspecialchars($originalLanguage); ?></p>
                </div>
            </div>

            <!-- Popularité -->
            <div class="alert alert-info" role="alert">
                <strong>Popularité :</strong> <?= round($film['popularity'], 2); ?>
            </div>

            <!-- Bouton retour -->
            <button type="button" class="btn btn-secondary" onclick="history.back()">
                <i class="bi bi-arrow-left"></i> Retour
            </button>
        </div>
    </div>

    <!-- Bande-annonce -->
    <?php if ($trailer): ?>
        <div class="row mb-5">
            <div class="col-12">
                <h3 class="mb-3">Bande-annonce</h3>

                <div class="d-grid gap-2 mb-3">
                    <button type="button"
                            class="btn btn-danger btn-lg"
                            data-trailer-key="<?= htmlspecialchars($trailer['key']); ?>"
                            data-trailer-name="<?= htmlspecialchars($trailer['name']); ?>"
                            onclick="loadTrailer(this.dataset.trailerKey, this.dataset.trailerName)">
                        Voir la bande-annonce
                    </button>
                </div>

                <div id="trailer-container" class="ratio ratio-16x9 d-none">
                </div>

                <p class="mt-2 mb-0">
                    <a href="https://www.youtube.com/watch?v=<?= htmlspecialchars($trailer['key']); ?>"
                       target="_blank" rel="noopener noreferrer">
                        Ouvrir la bande-annonce en dehors du site si le lecteur intégré est bloqué
                    </a>
                </p>
            </div>
        </div>
    <?php endif; ?>

    <!-- Synopsis -->
    <div class="row mb-5">
        <div class="col-12">
            <h3 class="mb-3">Synopsis</h3>
            <p class="text-justify"><?= nl2br(htmlspecialchars($synopsis)); ?></p>
        </div>
        <?php if (!empty($productionCountries)): ?>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Pays de production</h5>
                        <p class="card-text"><?= htmlspecialchars(implode(', ', $productionCountries)); ?></p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Détails financiers -->
    <div class="row mb-5">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Budget</h5>
                    <p class="card-text"><?= $budget; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Revenus</h5>
                    <p class="card-text"><?= $revenue; ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Acteurs -->
    <div class="mb-5">
        <h3 class="mb-4">Acteurs</h3>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-4 g-4">
            <?php foreach ($actors as $actor): ?>
                <?php if ($actor['profile_path']): ?>
                    <div class="d-flex align-items-stretch">
                        <div class="card shadow-sm w-100">
                            <img src="<?= 'https://image.tmdb.org/t/p/w342/' . $actor['profile_path']; ?>"
                                 class="card-img-top" alt="<?= $actor['name']; ?>">
                            <div class="card-body lh-sm d-flex flex-column">
                                <p class="card-text">
                                    <strong><?= htmlspecialchars($actor['character']); ?></strong>
                                    <br>
                                    <small class="text-muted"><?= htmlspecialchars($actor['name']); ?></small>
                                    <?php if (isset($actor['known_for_department'])): ?>
                                        <br><small class="text-muted"><?= htmlspecialchars($actor['known_for_department']); ?></small>
                                    <?php endif; ?>
                                </p>
                                <button type="button" class="btn btn-primary btn-sm mt-auto"
                                        onclick="location.href='actor.php?actor_id=<?= $actor['id'] ?>'">
                                    Voir le profil
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script>
function loadTrailer(videoKey, videoName) {
    const container = document.getElementById('trailer-container');
    if (!container) return;

    container.classList.remove('d-none');
    container.innerHTML = `
        <iframe
            src="https://www.youtube-nocookie.com/embed/${videoKey}?autoplay=1&rel=0&modestbranding=1&playsinline=1"
            title="${videoName}"
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
            allowfullscreen>
        </iframe>
    `;
}
</script>

<?php require("footer.php"); ?>
