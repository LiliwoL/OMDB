<?php

namespace App\Controller;

use App\Entity\Vote;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class OMDBController extends AbstractController
{
    // Page d'accueil
    /**
     * @Route("/", name="HomePage")
     */
    public function index()
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
        return $this->render('omdb/index.html.twig', 
            array(
                'query' => $query,
                'movies' => $json->Search   // On ne transmet que la propriété Search qui contient les films
            )
        );
    }



    // Une route basique nommée Recherche
    // Cette route analyse la requete et redirige 
    // vers l'action qui va afficher la liste avec un parametre
    /**
     * @Route("/recherche", name="Recherche")
     */
    public function recherche( Request $request )
    {
        // Analyser le contenu de l'objet Request
        dump ( $request->query->get( 'nom_du_film') );

        // Pour récupérer TOUS les paramètres GET
        //$request->query->all();


        // Redirection vers l'action du contrôleur qui va afficher 
        //la liste des films avec ce paramètre
        return $this->redirectToRoute( 
            'Liste des FILMS avec un paramètre',
            array(
                'parametre_attendu_par_la_route' => $request->query->get( 'nom_du_film' )
            )
        );
    }


    // Route qui affiche une liste de films
    // Pas de paramètre ici
    /**
     * @Route(
     *  "/liste_films",
     *  name="Liste des FILMS sans paramètre"
     * )
     */
    public function listeFilmsSansParametre()
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
        return $this->render('omdb/liste.html.twig', 
            array(
                'query' => $query,
                'movies' => $json->Search   // On ne transmet que la propriété Search qui contient les films
            )
        );
    }

    /**
     * @Route(
     *  "/liste_films_avec_parametre/{parametre_attendu_par_la_route}",
     *  name="Liste des FILMS avec un paramètre"
     * )
     */
    public function listeFilmsAvecParametre( $parametre_attendu_par_la_route )
    {
        $apiKey = '185a318e';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://www.omdbapi.com/?s=' . $parametre_attendu_par_la_route . '&apikey=' . $apiKey);
        curl_setopt($ch,  CURLOPT_RETURNTRANSFER, true);

        $resultat_curl = curl_exec($ch);

        // On transforme le résultat de cURL en un objet JSON utilisable
        $json = json_decode ( $resultat_curl );

        // Test du résultat
        if ( isset($json->Search ))
        {
            // Rendu
            return $this->render('omdb/liste.html.twig', 
                array(
                    'query' => $parametre_attendu_par_la_route,
                    'movies' => $json->Search   // On ne transmet que la propriété Search qui contient les films
                )
            );
        }else{
            // Rendu
            return $this->render('omdb/not_found.html.twig', 
                array(
                    'query' => $parametre_attendu_par_la_route
                )
            );
        }       
    }


    /**
     * Affichage d'une fiche détaillée d'un film
     * 
     * @Route(
     *  "/detail_film/{idFilm}",
     *  name="Fiche détaillée"
     * )
     */
    public function detailsFilm( $idFilm )
    {
        $apiKey = '185a318e';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://www.omdbapi.com/?i=' . $idFilm . '&apikey=' . $apiKey);
        curl_setopt($ch,  CURLOPT_RETURNTRANSFER, true);

        $resultat_curl = curl_exec($ch);

        // On transforme le résultat de cURL en un objet JSON utilisable
        $json = json_decode ( $resultat_curl );

        if ( isset ($json->Response) && $json->Response == "False" )
        {
            // Film non trouvé
            return $this->render('omdb/not_found.html.twig', 
                array(
                    'query' => $idFilm
                )
            );
        }else{

            // Récupération de la moyenne des notes
            $moyenne = $this->getDoctrine()
                        ->getRepository(Vote::class)    
                        ->findAverage($idFilm);

            // Rendu
            return $this->render('omdb/details.html.twig', 
                array(
                    'movie' => $json,  // On ne transmet que la propriété Search qui contient les films
                    'moyenne' => $moyenne
                )
            );
        }
        
    }


    /**
     * Fonction de partage de film
     * 
     * @Route(
     *  "/partage",
     *  name="Partage de film"
     * )
     *
     * @param Request $request
     * @return void
     */
    public function partageFilm ( Request $request, \Swift_Mailer $mailer )
    {
        // Analyser l'objet Request et TOUS ses paramètres
        dump ( $request->request->all() );

        // Id du film récupéré depuis l'objet Request
        $idFilm = $request->request->get( 'imdbID' );

        // Adresse du destinataire
        $emailDestinataire = $request->request->get( 'emailDestinataire' );



        // Appel à OMDB pour récupérer les infos du film (cf la route de la fiche détaillée)
        $apiKey = '185a318e';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://www.omdbapi.com/?i=' . $idFilm . '&apikey=' . $apiKey);
        curl_setopt($ch,  CURLOPT_RETURNTRANSFER, true);

        $resultat_curl = curl_exec($ch);

        // On transforme le résultat de cURL en un objet JSON utilisable
        $json = json_decode ( $resultat_curl );

        if ( isset ($json->Response) && $json->Response == "False" )
        {
            // Film non trouvé
            return $this->render('omdb/not_found.html.twig', 
                array(
                    'query' => $idFilm
                )
            );
        }else{
            // Si il y a une réponse on prépare le rendu de la vue
            // et on le STOCKE dans une variable $output
            $output = $this->render('mail/details.html.twig', 
                array(
                    'movie' => $json   // On transmet tout le contenu du JSON
                )
            );

            // Création d'un objet SwiftMessage
            $message = (new \Swift_Message('Un utilisateur veut vous partager le film: '))
                ->setFrom('barack-obama@whitehouse.us')
                ->setTo( $emailDestinataire ) // L'adresse du destinataire (cf. Request)
                ->setBody(
                    $output,
                    'text/html'
            );

            // Appel du facteur et envoi
            $mailer->send( $message );

            // Ajout d'un message de confirmation
            $this->addFlash(
                'success',
                'Message envoyé!'
            );

            // Redirection vers la fiche détaillée
            return $this->redirectToRoute( 
                'Fiche détaillée',
                array(
                    'idFilm' => $idFilm
                )
            );
        }
    }


    /**
     * Route pour ajouter un vote
     * 
     * @Route(
     *  "/nouveauvote/{imdbID}/{note}",
     *  name="Nouveau Vote"
     * )
     *
     */
    public function nouveauVote( $imdbID, $note )
    {
        // Créer une nouvelle entité de type VOTE
        // Il ne faut pas oublier le use à mettre en haut:
        // use App\Entity\Vote;
        $vote = new Vote();

        // J'affecte l'imdbID à partir du paramètre
        $vote->setImdbID( $imdbID );

        // J'affecte le vote à partir du paramètre
        $vote->setNote( $note );

        // Appel au Gestionnaire d'entités fourni par Doctrine
        $entityManager = $this->getDoctrine()->getManager();

        // On demande au Gestionnaire d'entités de PERSISTER les données en base
        $entityManager->persist($vote);

        // On demande au Gestionnaire d'entités d'exécuter les requêtes en attente
        $entityManager->flush();

        // On n'oublie pas la réponse à renvoyer!
        // Ajout d'un message de confirmation
        $this->addFlash(
            'success',
            'Vote enregistré!'
        );

        // Redirection vers la fiche détaillée
        return $this->redirectToRoute( 
            'Fiche détaillée',
            array(
                'idFilm' => $imdbID
            )
        );
    }


    /**
     * Route pour ajouter un vote via un formulaire
     * 
     * @Route(
     *  "/nouveauvote",
     *  name="Nouveau Vote par Formulaire"
     * )
     */
    public function nouveauVoteParFormulaire( Request $request )
    {
        // Récupération des paramètres dans la requête
        $imdbID = $request->request->get('imdbID');
        $note = $request->request->get('note');


        // Créer une nouvelle entité de type VOTE
        // Il ne faut pas oublier le use à mettre en haut:
        // use App\Entity\Vote;
        $vote = new Vote();

        // J'affecte l'imdbID à partir du paramètre
        $vote->setImdbID( $imdbID );

        // J'affecte le vote à partir du paramètre
        $vote->setNote( $note );

        // Appel au Gestionnaire d'entités fourni par Doctrine
        $entityManager = $this->getDoctrine()->getManager();

        // On demande au Gestionnaire d'entités de PERSISTER les données en base
        $entityManager->persist($vote);

        // On demande au Gestionnaire d'entités d'exécuter les requêtes en attente
        $entityManager->flush();

        // On n'oublie pas la réponse à renvoyer!
        // Ajout d'un message de confirmation
        $this->addFlash(
            'success',
            'Vote enregistré!'
        );

        // Redirection vers la fiche détaillée
        return $this->redirectToRoute( 
            'Fiche détaillée',
            array(
                'idFilm' => $imdbID
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
