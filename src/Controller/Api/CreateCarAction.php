<?php

namespace App\Controller\Api;

use App\Entity\Car;
use App\Repository\CarRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Doctrine\ORM\EntityManagerInterface;

final class CreateCarAction extends AbstractController
{
    public function __construct(
        private readonly CarRepository $carRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    #[Route('/api/cars', name: 'create_car', methods: ['POST'])]
   
    public function createCar(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $brand = $data['brand'] ?? null;
        $model = $data['model'] ?? null;
        $available = $data['available'] ?? true;

        if (empty($brand) || empty($model)) {
            return new JsonResponse(['error' => 'Brand and model are required.'], 400);
        }

        try {
            $car = Car::create($brand,$model,$available);
            $this->carRepository->save($car);

            return new JsonResponse([
                'id' => $car->getId(),
                'brand' => $car->getBrand(),
                'model' => $car->getModel(),
            ], 201);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Unable to create car: ' . $e->getMessage()], 400);
        }
    }

    #[Route('/api/cars/{id}', name: 'update_car', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN')]
    public function updateCar(int $id, Request $request): JsonResponse
    {
        $car = $this->carRepository->find($id);
        if (!$car) {
            return new JsonResponse(['error' => 'Car not found'], 404);
        }

        $data = json_decode($request->getContent(), true);
        $brand = $data['brand'] ?? $car->getBrand();
        $model = $data['model'] ?? $car->getModel();
        $available = $data['available'] ?? $car->isAvailable();

        try {
            $car->setBrand($brand);
            $car->setModel($model);
            $car->setAvailable($available);

            $this->entityManager->flush();

            return new JsonResponse([
                'id' => $car->getId(),
                'brand' => $car->getBrand(),
                'model' => $car->getModel(),
            ], 200);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Unable to update car: ' . $e->getMessage()], 400);
        }
    }

    #[Route('/api/cars/{id}', name: 'delete_car', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')] 
    public function deleteCar(int $id): JsonResponse
    {
        $car = $this->carRepository->find($id);
        if (!$car) {
            return new JsonResponse(['error' => 'Car not found'], 404);
        }

        try {
            $this->entityManager->remove($car);
            $this->entityManager->flush();

            return new JsonResponse(['message' => 'Car deleted successfully'], 200);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Unable to delete car: ' . $e->getMessage()], 400);
        }
    }
}
