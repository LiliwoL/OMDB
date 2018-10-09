<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class OMDBController extends AbstractController
{

    // Une route basique nommée query
    /**
     * @Route("/query", name="Query")
     */
    public function query()
    {
        // On fait appel à la méthode render pour renvoyer un contenu HTML
        // depuis un fichier TWIG à qui on passe un tableau de variables
        return $this->render('omdb/index.html.twig', 
            [
                'controller_name' => 'OMDBController',
                'variable' => "coucou",
                'liste' => array(
                    'contenu 1' => 'valeur 1',
                    'contenu 2' => 'valeur 2'
                )
            ]
        );
    }


    /**
     * @Route("/film", name="Film")
     */
    public function affichageFilm()
    {
        $apiKey = '185a318e';
        $query = "running";


        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://www.omdbapi.com/?s=' . $query . '&apikey=' . $apiKey);
        curl_setopt($ch,  CURLOPT_RETURNTRANSFER, true);

        $resultat_curl = curl_exec($ch);

        // On transforme le résultat de cURL en un objet JSON utilisable
        $json = json_decode ( $resultat_curl );

        // Rendu
        return $this->render('omdb/film.html.twig', 
            array(
                'query' => $query,
                'movies' => $json->Search   // On ne transmet que la propriété Search qui contient les films
            )
        );
    }

    /**
     * @Route(
     *  "/film_avec_parametre/{query}",
     *  name="Film avec paramètre"
     * )
     */
    public function affichageFilmAvecParametre( $query )
    {
        $apiKey = '185a318e';
        //$query = "running";


        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://www.omdbapi.com/?s=' . $query . '&apikey=' . $apiKey);
        curl_setopt($ch,  CURLOPT_RETURNTRANSFER, true);

        $resultat_curl = curl_exec($ch);

        // On transforme le résultat de cURL en un objet JSON utilisable
        $json = json_decode ( $resultat_curl );

        // Rendu
        return $this->render('omdb/film.html.twig', 
            array(
                'query' => $query,
                'movies' => $json->Search   // On ne transmet que la propriété Search qui contient les films
            )
        );
    }
}
