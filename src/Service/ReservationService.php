<?php

namespace App\Service;

use App\Entity\Car;
use App\Entity\Reservation;
use App\Entity\User;
use App\Repository\CarRepository;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class ReservationService
{
    private CarRepository $carRepository;
    private ReservationRepository $reservationRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(
        CarRepository $carRepository,
        ReservationRepository $reservationRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->carRepository = $carRepository;
        $this->reservationRepository = $reservationRepository;
        $this->entityManager = $entityManager;
    }

    public function createReservation(array $data, User $user): array
    {
        $car = $this->carRepository->find($data['car_id']);
        if (!$car || !$car->isAvailable()) {
            return ['data' => ['error' => 'Car is not available'], 'status' => 400];
        }

        $startDate = new \DateTime($data['startDate']);
        $endDate = new \DateTime($data['endDate']);


        if ($startDate >= $endDate) {
            return ['data' => ['error' => 'End date must be after start date'], 'status' => 400];
        }

        if ($this->reservationRepository->findConflictingReservations($car, $startDate, $endDate)) {
            return ['data' => ['error' => 'Car is already reserved for these dates'], 'status' => 400];
        }
        if (!$user instanceof User) {
            return new JsonResponse(['error' => 'User not authenticated'], 401);
        }

        $reservation = Reservation::create($user, $car, $startDate, $endDate);

        $this->reservationRepository->save($reservation);

        return ['data' => ['id' => $reservation->getId(), 'message' => 'Reservation created','user'=>$user->getId()], 'status' => 201];
    }

    public function updateReservation(int $id, array $data, User $user): array
    {
        $reservation = $this->reservationRepository->find($id);
        if (!$reservation || $reservation->getUser() !== $user) {
            return ['data' => ['error' => 'Unauthorized or not found'], 'status' => 403];
        }

        $startDate = new \DateTime($data['startDate']);
        $endDate = new \DateTime($data['endDate']);

        if ($startDate >= $endDate) {
            return ['data' => ['error' => 'End date must be after start date'], 'status' => 400];
        }

        $reservation->setStartDate($startDate);
        $reservation->setEndDate($endDate);

        $this->entityManager->flush();

        return ['data' => ['message' => 'Reservation updated'], 'status' => 200];
    }

    public function deleteReservation(int $id, User $user): array
    {
        $reservation = $this->reservationRepository->find($id);
        if (!$reservation || $reservation->getUser() !== $user) {
            return ['data' => ['error' => 'Unauthorized or not found'], 'status' => 403];
        }

        $this->entityManager->remove($reservation);
        $this->entityManager->flush();

        return ['data' => ['message' => 'Reservation deleted'], 'status' => 200];
    }

    public function getUserReservations(int $userId, User $user): array
    {
        if ($user->getId() !== $userId) {
            return ['data' => ['error' => 'Unauthorized access'], 'status' => 403];
        }

        $reservations = $this->reservationRepository->findBy(['user' => $user]);

        $reservationsData = [];
        foreach ($reservations as $reservation) {
            $reservationsData[] = [
                'id' => $reservation->getId(),
                'car' => [
                    'id' => $reservation->getCar()->getId(),
                    'brand' => $reservation->getCar()->getBrand(),
                    'model' => $reservation->getCar()->getModel(),
                ],
                'start_date' => $reservation->getStartDate()->format('Y-m-d H:i:s'),
                'end_date' => $reservation->getEndDate()->format('Y-m-d H:i:s'),
            ];
        }

        return ['data' => $reservationsData, 'status' => 200];
    }
}
