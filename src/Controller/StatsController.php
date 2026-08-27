<?php

namespace App\Controller;

use App\Repository\TransactionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/stats')]
class StatsController extends AbstractController
{
    #[Route('/', name: 'app_stats_index', methods: ['GET'])]
    public function index(TransactionRepository $transactionRepository): Response
    {
        $transactions = $transactionRepository->findBy(['user' => $this->getUser()]);

        $byYear = [];
        foreach ($transactions as $transaction) {
            $year = $transaction->getDate()->format('Y');
            if (!isset($byYear[$year])) {
                $byYear[$year] = ['income' => 0, 'expense' => 0];
            }
            if ($transaction->getType() === 'income') {
                $byYear[$year]['income'] += $transaction->getAmount();
            } else {
                $byYear[$year]['expense'] += $transaction->getAmount();
            }
        }
        krsort($byYear);

        $byCategory = [];
        foreach ($transactions as $transaction) {
            $catName = $transaction->getCategory() ? $transaction->getCategory()->getName() : 'Sans catégorie';
            if (!isset($byCategory[$catName])) {
                $byCategory[$catName] = 0;
            }
            $signedAmount = $transaction->getType() === 'income' ? $transaction->getAmount() : -$transaction->getAmount();
            $byCategory[$catName] += $signedAmount;
        }

        return $this->render('stats/index.html.twig', [
            'byYear' => $byYear,
            'byCategory' => $byCategory,
        ]);
    }
}
