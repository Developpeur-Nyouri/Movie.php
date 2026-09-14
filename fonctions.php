<?php

// require_once("get-proxy.php");// au lycée pour faire des requêtes https vous avons besoin d'indiquer le proxy

$key = "9e43f45f94705cc8e1d5a0400d19a7b7";

function tmdbRequest($url)
{
    $proxyUrl = getenv('HTTPS_PROXY') ?: getenv('HTTP_PROXY');
    $proxy = parse_url($proxyUrl ?: '');

    $context = stream_context_create([
        'http' => [
            'proxy' => 'tcp://' . ($proxy['host'] ?? '127.0.0.1') . ':' . ($proxy['port'] ?? 8080),
            'request_fulluri' => true,
        ],
        'ssl' => [
            'verify_peer' => true,
            'verify_peer_name' => true,
            'cafile' => '/etc/ssl/certs/ca-certificates.crt',
        ],
    ]);

    return file_get_contents($url, false, $context);
}

//fonction qui retourne dans un tableau asociatif les 20 films les plus populaires 
function popularMovies()
{
    global $key;
    $url = "https://api.themoviedb.org/3/movie/popular?api_key=$key&language=fr-FR";
    $response = tmdbRequest($url);
    //$response = file_get_contents("https://api.themoviedb.org/3/movie/popular?api_key=$key&language=fr-FR");

    $result = json_decode($response, true);
    return $result['results'];
}

function topMovies()
{
    global $key;
    $url = "https://api.themoviedb.org/3/movie/top_rated?api_key=$key&language=fr-FR";
    $response = tmdbRequest($url);
    //$response = file_get_contents("https://api.themoviedb.org/3/movie/top_rated?api_key=$key&language=fr-FR");

    $result = json_decode($response, true);
    return $result['results'];
}

function Genre($genreId)
{
    global $key;
    $url = "https://api.themoviedb.org/3/discover/movie?api_key=$key&language=fr-FR&with_genres=$genreId";
    $response = tmdbRequest($url);
    //$response = file_get_contents("https://api.themoviedb.org/3/discover/movie?api_key=$key&language=fr-FR&with_genres=$genreId");
    $result = json_decode($response, true);
    return $result['results'];
}

function getNameGenreById($genreId)
{
    global $key;
    $url = "https://api.themoviedb.org/3/genre/movie/list?api_key=$key&language=fr-FR";
    $response = tmdbRequest($url);
    //$response = file_get_contents("https://api.themoviedb.org/3/genre/movie/list?api_key=$key&language=fr-FR");
    $result = json_decode($response, true);
    foreach ($result['genres'] as $genre) {
        if ($genre['id'] == $genreId) {
            return $genre['name'];
        }
    }
    return "Inconnu";
}

function getFilmById($movieId, $language = "fr-FR")
{
    global $key;
    $url = "https://api.themoviedb.org/3/movie/$movieId?api_key=$key&language=$language";
    $response = tmdbRequest($url);
    //$response = file_get_contents("https://api.themoviedb.org/3/movie/$movieId?api_key=$key&language=fr-FR");
    $result = json_decode($response, true);
    return $result;
}

function getActorsByMovieId($movieId){
    global $key;
    $url = "https://api.themoviedb.org/3/movie/$movieId/credits?api_key=$key&language=fr-FR";
    $response = tmdbRequest($url);
    //$response = file_get_contents("https://api.themoviedb.org/3/movie/$movieId/credits?api_key=$key&language=fr-FR");
    $result = json_decode($response, true);
    return $result['cast'];
}

function getMovieTrailer($movieId)
{
    global $key;

    foreach (['en-US', 'fr-FR'] as $language) {
        $url = "https://api.themoviedb.org/3/movie/$movieId/videos?api_key=$key&language=$language";
        $result = json_decode(tmdbRequest($url), true);

        $videos = array_filter($result['results'] ?? [], function ($video) {
            return $video['site'] === 'YouTube' && $video['type'] === 'Trailer';
        });

        usort($videos, function ($first, $second) {
            return (int) ($second['official'] ?? false) <=> (int) ($first['official'] ?? false);
        });

        if (!empty($videos)) {
            return reset($videos);
        }
    }

    return null;
}

function getActorById($actorId, $language = "fr-FR"){
    global $key;
    $url = "https://api.themoviedb.org/3/person/$actorId?api_key=$key&language=$language";
    $response = tmdbRequest($url);
    //$response = file_get_contents("https://api.themoviedb.org/3/person/$actorId?api_key=$key&language=fr-FR");
    $result = json_decode($response, true);
    return $result;
}

function getFilmsByActorId($actorId){
    global $key;
    $url = "https://api.themoviedb.org/3/person/$actorId/movie_credits?api_key=$key&language=fr-FR";
    $response = tmdbRequest($url);
    //$response = file_get_contents("https://api.themoviedb.org/3/person/$actorId/movie_credits?api_key=$key&language=fr-FR");
    $result = json_decode($response, true);
    return $result['cast'];
}


?>