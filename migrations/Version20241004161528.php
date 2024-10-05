<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;


final class Version20241004161528 extends AbstractMigration
{

    public function up(Schema $schema): void
    {
       
        $this->addSql('CREATE TABLE `user` (
            id INT AUTO_INCREMENT NOT NULL, 
            email VARCHAR(180) NOT NULL, 
            roles JSON NOT NULL, 
            password VARCHAR(255) NOT NULL, 
            created_at DATETIME DEFAULT NULL, 
            UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), 
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    
       
       
        $this->addSql('CREATE TABLE car (
            id INT AUTO_INCREMENT NOT NULL, 
            brand VARCHAR(255) NOT NULL, 
            model VARCHAR(255) NOT NULL, 
            available TINYINT(1) NOT NULL, 
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    
        
        $this->addSql('CREATE TABLE reservation (
            id INT AUTO_INCREMENT NOT NULL, 
            user_id INT DEFAULT NULL, 
            car_id INT DEFAULT NULL, 
            start_date DATETIME NOT NULL, 
            end_date DATETIME NOT NULL, 
            INDEX IDX_42C84955A76ED395 (user_id), 
            INDEX IDX_42C84955C3C6F69F (car_id), 
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    
     
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C84955A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C84955C3C6F69F FOREIGN KEY (car_id) REFERENCES car (id)');
    }
    

}
