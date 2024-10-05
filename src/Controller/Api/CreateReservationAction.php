<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Service\ReservationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

 final class CreateReservationAction extends AbstractController
{

    public function __construct(private ReservationService $reservationService)
    {
    
    }

    #[Route('/api/reservation', name: 'create_reservation', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $user = $this->getUser();
        
        $response = $this->reservationService->createReservation($data,$user);

        return new JsonResponse($response['data'], $response['status']);
    }

    #[Route('/api/reservation/{id}', name: 'update_reservation', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $user =$this->getUser();

        $response = $this->reservationService->updateReservation($id, $data,$user);

        return new JsonResponse($response['data'], $response['status']);
    }

    #[Route('/api/reservation/{id}', name: 'delete_reservation', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $user = $this->getUser();
        $response = $this->reservationService->deleteReservation($id, $user);
        return new JsonResponse($response['data'], $response['status']);
    }

    #[Route('/api/user/{id}/reservation', name: 'get_user_reservations', methods: ['GET'])]
    public function getUserReservations(int $id): JsonResponse
    {   
        $user =  $this->getUser();
        $response = $this->reservationService->getUserReservations($id, $user);

        return new JsonResponse($response['data'], $response['status']);
    }
}
