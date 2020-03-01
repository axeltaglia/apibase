<?php

namespace App\Controller;

use App\ApiRequest\ProductCreateRequest;
use App\ApiRequest\ProductUpdateRequest;
use App\EntityType\ProductsListType;
use App\EntityType\ProductType;
use App\Repository\ProductRepository;
use App\Services\ApiResponderService;
use App\Services\ProductService;
use Exception;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use FOS\RestBundle\Controller\Annotations as Rest;

/**
 * @Route("/api")
 */
class ProductController extends AbstractFOSRestController
{
    /**
     * @Rest\Get("/products", name="product_list")
     */
    public function list(Request $request, ProductRepository $productRepository, ApiResponderService $apiResponder, ProductsListType $productsListType): Response
    {
        try {
            $productsList = $productRepository
                ->select('product')
                ->setFilters($request)
                ->setOrdering($request)
                ->paginate($request);

            return $apiResponder->getResponse([
                'products' => $productsList->getResult()], 200, $productsList->getMetadata(), $productsListType);
        } catch (Exception $exception) {
            return $apiResponder->getResponse(null, $exception);
        }
    }

    /**
     * @Rest\Post("/products", name="product_create")
     */
    public function create(Request $request, ProductService $productService, ProductRepository $productRepository, ProductCreateRequest $productCreateRequest, ProductType $productType, ApiResponderService $apiResponder)
    {
        try {
            $product = $productService->create($productCreateRequest->submitData($request));
            $product = $productsList = $productRepository
                ->select('product')
                ->where("product.id", $product->getId())
                ->getOneResult();

            return $apiResponder->getResponse($product, 201, null, $productType);
        } catch (Exception $exception) {
            return $apiResponder->getResponse(null, $exception);
        }
    }

    /**
     * @Rest\Get("/products/{id}", name="product_read")
     */
    public function read($id, ProductRepository $productRepository, ApiResponderService $apiResponder, ProductType $productType)
    {
        try {
            $product = $productsList = $productRepository
                ->select('product')
                ->where("product.id", $id)
                ->getOneResult();

            return $apiResponder->getResponse($product, 200, null, $productType);
        } catch (Exception $exception) {
            return $apiResponder->getResponse(null, $exception);
        }
    }

    /**
     * @Rest\Put("/products/{id}", name="product_update")
     */
    public function update($id, Request $request, ProductService $productService, ProductRepository $productRepository, ProductUpdateRequest $productUpdateRequest, ProductType $productType, ApiResponderService $apiResponder)
    {
        try {
            $productService->find($id);
            $productService->update($productUpdateRequest->submitData($request));
            $product = $productsList = $productRepository
                ->select('product')
                ->where("product.id", $id)
                ->getOneResult();

            return $apiResponder->getResponse($product, 200, null, $productType);
        } catch (Exception $exception) {
            return $apiResponder->getResponse(null, $exception);
        }
    }

    /**
     * @Rest\Delete("/products/{id}", name="product_delete")
     */
    public function delete($id, ProductService $productService, ApiResponderService $apiResponder)
    {
        try {
            $productService->find($id);
            $productService->delete();
            return $apiResponder->getResponse(null, 204);
        } catch (Exception $exception) {
            return $apiResponder->getResponse(null, $exception);
        }
    }
}


