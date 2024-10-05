<?php

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ReservationFuncTest extends WebTestCase
{
    private $client;
    private $token;

    public function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
        $this->registerAndLogin(); 
    }

    public function registerAndLogin(): void
    {
        
        $userData = [
            'email' => 'test16@example.com',
            'password' => 'securepassword'
        ];
        $this->client->request(
            'POST',
            '/public/api/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($userData)
        );

       
        $this->assertEquals(201, $this->client->getResponse()->getStatusCode(), 'User registration failed.');

       
        $this->client->request(
            'POST',
            '/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($userData)
        );

        $loginResponse = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('token', $loginResponse, 'No token found in login response.');

       
        $this->token = $loginResponse['token'];
        $this->client->setServerParameter('HTTP_Authorization', sprintf('Bearer %s', $this->token));
    }

    public function testReservationProcess(): void
    {
       
        $carData = [
            'brand' => 'Tesla',
            'model' => 'Model S',
        ];
        $this->client->request(
            'POST',
            '/api/cars',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($carData)
            
        );

      
        $this->assertEquals(201, $this->client->getResponse()->getStatusCode(), 'Car creation failed.');

        $carResponse = json_decode($this->client->getResponse()->getContent(), true);
        $carId = $carResponse['id'];

      
        $reservationData = [
            'car_id' => $carId,
            'startDate' => '2024-10-10 09:00:00',
            'endDate' => '2024-10-12 18:00:00'
        ];
        $this->client->request(
            'POST',
            '/api/reservation',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($reservationData)
        );
        $this->assertEquals(201, $this->client->getResponse()->getStatusCode(), 'Reservation creation failed.');
      

        $reservationResponse = json_decode($this->client->getResponse()->getContent(), true);
        $reservationId = $reservationResponse['id'];


        
        $this->client->request('GET', '/api/user/' . $reservationResponse['user'] . '/reservation');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), 'Fetching reservations failed.');

        $reservations = json_decode($this->client->getResponse()->getContent(), true);
       
        $this->assertCount(1, $reservations, 'User should have one reservation.');
        $this->assertEquals($reservationId, $reservations[0]['id'], 'Fetched reservation ID does not match.');
       
   
        $updatedReservationData = [
            'car_id' => $carId,
            'startDate' => '2024-10-15 09:00:00',
            'endDate' => '2024-10-17 18:00:00'
        ];
        $this->client->request(
            'PUT',
            '/api/reservation/' . $reservationId,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($updatedReservationData)
        );
       
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), 'Reservation update failed.');

      
        $this->client->request(
            'DELETE',
            '/api/reservations/' . $reservationId,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json']
        );
        $this->assertEquals(204, $this->client->getResponse()->getStatusCode(), 'Reservation deletion failed.');
    }
}
