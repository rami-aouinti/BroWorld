<?php

namespace App\Ecommerce\Transport\Controller;

use App\Ecommerce\Domain\Entity\Orders;
use App\Ecommerce\Domain\Entity\OrdersDetails;
use App\Ecommerce\Domain\Repository\ProductsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/ecommerce/commandes', name: 'ecommerce_app_orders_', options: ['sitemap' => true])]
class OrdersController extends AbstractController
{
    #[Route('/ajout', name: 'add')]
    public function add(
        SessionInterface $session,
        ProductsRepository $productsRepository,
        EntityManagerInterface $em
    ): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $panier = $session->get('panier', []);

        if($panier === []){
            $this->addFlash('message', 'Votre panier est vide');
            return $this->redirectToRoute('ecommerce_main');
        }
        $order = new Orders();
        $order->setUsers($this->getUser());
        $order->setReference(uniqid());
        foreach($panier as $item => $quantity){
            $orderDetails = new OrdersDetails();
            $product = $productsRepository->find($item);
            $price = $product->getPrice();
            $orderDetails->setProducts($product);
            $orderDetails->setPrice($price);
            $orderDetails->setQuantity($quantity);

            $order->addOrdersDetail($orderDetails);
        }
        $em->persist($order);
        $em->flush();
        $session->remove('panier');
        $this->addFlash('message', 'Commande créée avec succès');
        return $this->redirectToRoute('ecommerce_main');
    }
}
