<?php
// src/Controller/CommandeController.php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Client;
use App\Entity\Commande;
use App\Entity\CommandeArticle;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CommandeController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/commande/nouvelle', name: 'commande_nouvelle', methods: ['GET'])]
    public function nouvelle()
    {
        $articles = $this->entityManager->getRepository(Article::class)->findAll();
        $orderItems = [];

        return $this->render('commande/new.html.twig', [
            'articles' => $articles,
            'orderItems' => $orderItems,
        ]);
    }
    
    #[Route('/commande/nouvelle', name: 'order_create', methods: ['POST'])]
    public function create(Request $request)
    {
        // Récupérer les informations du client depuis la requête
        $clientId = $request->request->get('client');
        $telephone = $request->request->get('telephone');
        $adresse = $request->request->get('adresse');
        
        // Vérifier si clientId est valide (non vide et numérique)
        if (empty($clientId) || !is_numeric($clientId)) {
            return new Response('ID client invalide', 400);
        }
        
        // Trouver le client par son ID
        $client = $this->entityManager->getRepository(Client::class)->find($clientId);
        if (!$client) {
            return new Response('Client non trouvé', 404);
        }
        
        // Créer une nouvelle commande
        $commande = new Commande();
        $commande->setClient($client);
        $commande->setDateCommande(new \DateTime());
        
        // Récupérer les articles de la commande depuis la requête
        $articles = $request->request->get('articles'); // Supposons que 'articles' est un tableau des IDs des articles
        
        if (empty($articles)) {
            return new Response('Aucun article sélectionné', 400);
        }
    
        // Initialiser le total à zéro
        $total = 0;
        
        // Ajouter les articles à la commande et calculer le total
        foreach ($articles as $articleId) {
            $article = $this->entityManager->getRepository(Article::class)->find($articleId);
            if ($article) {
                // Ajouter l'article à la commande
                $commande->addArticle($article); // Vous devez avoir une méthode addArticle dans Commande
                $total += $article->getPrix(); // Ajouter le prix de l'article au total
            } else {
                // Si un article n'est pas trouvé, retourner une réponse d'erreur
                return new Response('Article avec ID ' . $articleId . ' non trouvé', 404);
            }
        }
        
        // Définir le total de la commande
        $commande->setTotal($total);
        
        // Ajouter l'adresse et le téléphone à la commande si nécessaire
        $commande->setAdresse($adresse);
        $commande->setTelephone($telephone);
        
        // Persister la commande
        $this->entityManager->persist($commande);
        
        // Finaliser la persistance
        $this->entityManager->flush();
        
        // Ajouter un flash message pour informer l'utilisateur
        $this->addFlash('success', 'Commande créée avec succès');
        
        // Rediriger vers la page de détail de la commande ou vers une autre page
        return $this->redirectToRoute('order_list');
    }
    

    
    
    

    #[Route('/commande/success', name: 'order_store')]
    public function success()
    {
        return new Response('Commande créée avec succès !');
    }
}
