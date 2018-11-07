<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

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


    /**
     * @Route(
     *  "/film_avec_parametre_et_avec_formulaire/{query}",
     *  name="Film avec paramètre et avec formulaire",
     *  defaults={
     *      "query" = "cité"
     *  }
     * )
     */
    public function affichageFilmAvecParametreEtAvecFormulaire( $query )
    {
        $apiKey = '185a318e';
        //$query = "running";


        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://www.omdbapi.com/?s=' . $query . '&apikey=' . $apiKey);
        curl_setopt($ch,  CURLOPT_RETURNTRANSFER, true);

        $resultat_curl = curl_exec($ch);

        // On transforme le résultat de cURL en un objet JSON utilisable
        $json = json_decode ( $resultat_curl );

        // Création d'un formulaire simple
        $form = $this->createFormBuilder()
            ->add('query', TextType::class)
            ->add('save', SubmitType::class, array('label' => 'Rechercher'))
            ->getForm();


        // Rendu
        return $this->render('omdb/formulaire.html.twig', 
            array(
                'query' => $query,
                'movies' => $json->Search,   // On ne transmet que la propriété Search qui contient les films
                'form' => $form->createView()
            )
        );
    }


    /**
     * @Route(
     *  "/film_avec_parametre_et_avec_formulaire/",
     *  name="Film avec paramètre et avec formulaire en POST"
     * )
     */
    public function affichageFilmAvecParametreEtAvecFormulairePOST( Request $request )
    {
        $apiKey = '185a318e';

        if ( $request->request->get('query') )
        {
            $query = $request->request->get('query');
        }else{
            $query = "rien";
        }

    

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://www.omdbapi.com/?s=' . $query . '&apikey=' . $apiKey);
        curl_setopt($ch,  CURLOPT_RETURNTRANSFER, true);

        $resultat_curl = curl_exec($ch);

        // On transforme le résultat de cURL en un objet JSON utilisable
        $json = json_decode ( $resultat_curl );



        // Rendu
        return $this->render('omdb/formulaire.html.twig', 
            array(
                'query' => $query,
                'movies' => $json->Search,   // On ne transmet que la propriété Search qui contient les films
                'form' => $form->createView()
            )
        );
    }
}
