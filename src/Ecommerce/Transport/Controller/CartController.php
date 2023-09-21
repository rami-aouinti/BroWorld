<?php
namespace App\Ecommerce\Transport\Controller;

use App\Ecommerce\Domain\Entity\Products;
use App\Ecommerce\Domain\Repository\ProductsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/ecommerce/cart', name: 'ecommerce_cart_')]
class CartController extends AbstractController
{
    /**
     * @param SessionInterface $session
     * @param ProductsRepository $productsRepository
     * @return Response
     */
    #[Route('/', name: 'index')]
    public function index(
        SessionInterface $session,
        ProductsRepository $productsRepository
    ): Response
    {
        $panier = $session->get('panier', []);

        // On initialise des variables
        $data = [];
        $total = 0;

        foreach($panier as $id => $quantity){
            $product = $productsRepository->find($id);

            $data[] = [
                'product' => $product,
                'quantity' => $quantity
            ];
            $total += $product->getPrice() * $quantity;
        }

        return $this->render('ecommerce/cart/index.html.twig', compact('data', 'total'));
    }


    /**
     * @param Products $product
     * @param SessionInterface $session
     * @return RedirectResponse
     */
    #[Route(path: '/add/{id}', name: 'add')]
    public function add(Products $product, SessionInterface $session): RedirectResponse
    {
        //On récupère l'id du produit
        $id = $product->getId();

        // On récupère le panier existant
        $panier = $session->get('panier', []);

        // On ajoute le produit dans le panier s'il n'y est pas encore
        // Sinon on incrémente sa quantité
        if(empty($panier[$id])){
            $panier[$id] = 1;
        }else{
            $panier[$id]++;
        }

        $session->set('panier', $panier);

        //On redirige vers la page du panier
        return $this->redirectToRoute('ecommerce_cart_index');
    }

    /**
     * @param Products $product
     * @param SessionInterface $session
     * @return RedirectResponse
     */
    #[Route(path: '/remove/{id}', name: 'remove')]
    public function remove(Products $product, SessionInterface $session): RedirectResponse
    {
        //On récupère l'id du produit
        $id = $product->getId();

        // On récupère le panier existant
        $panier = $session->get('panier', []);

        // On retire le produit du panier s'il n'y a qu'1 exemplaire
        // Sinon on décrémente sa quantité
        if(!empty($panier[$id])){
            if($panier[$id] > 1){
                $panier[$id]--;
            }else{
                unset($panier[$id]);
            }
        }

        $session->set('panier', $panier);

        //On redirige vers la page du panier
        return $this->redirectToRoute('ecommerce_cart_index');
    }

    /**
     * @param Products $product
     * @param SessionInterface $session
     * @return RedirectResponse
     */
    #[Route(path: '/delete/{id}', name: 'delete')]
    public function delete(Products $product, SessionInterface $session): RedirectResponse
    {
        //On récupère l'id du produit
        $id = $product->getId();

        // On récupère le panier existant
        $panier = $session->get('panier', []);

        if(!empty($panier[$id])){
            unset($panier[$id]);
        }

        $session->set('panier', $panier);

        //On redirige vers la page du panier
        return $this->redirectToRoute('ecommerce_cart_index');
    }

    /**
     * @param SessionInterface $session
     * @return RedirectResponse
     */
    #[Route(path: '/empty', name: 'empty')]
    public function empty(SessionInterface $session): RedirectResponse
    {
        $session->remove('panier');

        return $this->redirectToRoute('ecommerce_cart_index');
    }
}
