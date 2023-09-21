<?php

namespace App\Admin\Transport\Controller\Ecommerce;

use App\Ecommerce\Application\Service\PictureService;
use App\Ecommerce\Domain\Entity\Images;
use App\Ecommerce\Domain\Entity\Products;
use App\Ecommerce\Domain\Repository\ProductsRepository;
use App\Ecommerce\Transport\Form\ProductsFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin/ecommerce/produits', name: 'ecommerce_admin_products_')]
class ProductsController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(ProductsRepository $productsRepository): Response
    {
        $produits = $productsRepository->findAll();
        return $this->render('ecommerce/admin/products/index.html.twig', compact('produits'));
    }

    #[Route('/add', name: 'add', methods: ['GET', 'POST'])]
    public function add(
        Request $request,
        EntityManagerInterface $em,
        SluggerInterface $slugger,
        PictureService $pictureService
    ): Response
    {
        $product = new Products();
        $productForm = $this->createForm(ProductsFormType::class, $product);
        $productForm->handleRequest($request);
        if($productForm->isSubmitted() && $productForm->isValid()){
            // On récupère les images
            $images = $productForm->get('images')->getData();

            foreach($images as $image){
                $folder = 'products';
                $fichier = $pictureService->add($image, $folder, 300, 300);
                $img = new Images();
                $img->setName($fichier);
                $product->addImage($img);
            }
            $slug = $slugger->slug($product->getName());
            $product->setSlug($slug);
            $em->persist($product);
            $em->flush();
            $this->addFlash('success', 'Produit ajouté avec succès');

            return $this->redirectToRoute('ecommerce_admin_products_index');
        }

        return $this->render('ecommerce/admin/products/add.html.twig', compact('productForm'));
    }

    #[Route('/edition/{id}', name: 'edit')]
    public function edit(
        Products $product,
        Request $request,
        EntityManagerInterface $em,
        SluggerInterface $slugger,
        PictureService $pictureService
    ): Response
    {
        $productForm = $this->createForm(ProductsFormType::class, $product);
        $productForm->handleRequest($request);
        if($productForm->isSubmitted() && $productForm->isValid()){
            $images = $productForm->get('images')->getData();
            foreach($images as $image){
                $folder = 'products';
                $fichier = $pictureService->add($image, $folder, 300, 300);

                $img = new Images();
                $img->setName($fichier);
                $product->addImage($img);
            }
            $slug = $slugger->slug($product->getName());
            $product->setSlug($slug);
            $em->persist($product);
            $em->flush();
            $this->addFlash('success', 'Produit modifié avec succès');

            return $this->redirectToRoute('ecommerce/admin_products_index');
        }


        return $this->render('ecommerce/admin/products/edit.html.twig',[
            'productForm' => $productForm->createView(),
            'product' => $product
        ]);
    }

    #[Route('/suppression/{id}', name: 'delete')]
    public function delete(Products $product): Response
    {
        // On vérifie si l'utilisateur peut supprimer avec le Voter
        $this->denyAccessUnlessGranted('PRODUCT_DELETE', $product);

        return $this->render('ecommerce/admin/products/index.html.twig');
    }

    #[Route('/suppression/image/{id}', name: 'delete_image', methods: ['DELETE'])]
    public function deleteImage(Images $image, Request $request, EntityManagerInterface $em, PictureService $pictureService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if($this->isCsrfTokenValid('delete' . $image->getId(), $data['_token'])){
            $nom = $image->getName();

            if($pictureService->delete($nom, 'products', 300, 300)){
                $em->remove($image);
                $em->flush();

                return new JsonResponse(['success' => true], 200);
            }
            // La suppression a échoué
            return new JsonResponse(['error' => 'Erreur de suppression'], 400);
        }

        return new JsonResponse(['error' => 'Token invalide'], 400);
    }

}
