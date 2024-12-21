<?php
// src/Service/CommandeService.php
// src/Service/CommandeService.php

namespace App\Service;

use App\Entity\Commande;
use App\Entity\CommandeArticle;
use App\Repository\CommandeArticleRepository;
use App\Repository\ArticleRepository;

class CommandeService
{
    private CommandeArticleRepository $commandeArticleRepository;
    private ArticleRepository $articleRepository;

    public function __construct(CommandeArticleRepository $commandeArticleRepository, ArticleRepository $articleRepository)
    {
        $this->commandeArticleRepository = $commandeArticleRepository;
        $this->articleRepository = $articleRepository;
    }

    public function ajouterArticle(Commande $commande, int $articleId, int $quantite, float $prix): CommandeArticle
    {
        $article = $this->articleRepository->find($articleId);

        // Vérifier si la quantité demandée est disponible
        if ($article && $article->getStock() >= $quantite) {
            // Créer une nouvelle commandeArticle
            $commandeArticle = new CommandeArticle();
            $commandeArticle->setArticle($article);
            $commandeArticle->setQuantite($quantite);
            $commandeArticle->setPrix($prix);
            $commandeArticle->setCommande($commande);

            // Sauvegarder dans la base de données
            $this->commandeArticleRepository->save($commandeArticle, true);

            // Mettre à jour le stock de l'article
            $article->setStock($article->getStock() - $quantite);
            $this->articleRepository->save($article, true);

            return $commandeArticle;
        }

        throw new \Exception("Quantité insuffisante pour cet article.");
    }

    public function supprimerArticle(CommandeArticle $commandeArticle): void
    {
        // Rétablir le stock de l'article
        $article = $commandeArticle->getArticle();
        $article->setStock($article->getStock() + $commandeArticle->getQuantite());

        // Supprimer l'article de la commande
        $this->commandeArticleRepository->remove($commandeArticle, true);
    }

    public function calculerTotal(Commande $commande): float
    {
        $total = 0;

        // Calculer le total des articles dans la commande
        foreach ($commande->getCommandeArticles() as $commandeArticle) {
            $total += $commandeArticle->getQuantite() * $commandeArticle->getPrix();
        }

        return $total;
    }
}
