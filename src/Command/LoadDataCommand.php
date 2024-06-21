<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;

#[AsCommand(
    name: 'LoadData',
    description: 'Command to import the orders list into the database',
)]
class LoadDataCommand extends Command
{
    protected static $defaultName = 'app:load-data';
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();

        $this->entityManager = $entityManager;
    }

    protected function configure()
    {
        $this
            ->setDescription('Load data from a JSON file into the database')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Caminho para o arquivo JSON
        $jsonFilePath = __DIR__ . '/../../json-data/orders.json';
        if (!file_exists($jsonFilePath)) {
            $io->error('JSON file not found!');
            return Command::FAILURE;
        }

        // Ler o conteúdo do arquivo JSON
        $jsonData = file_get_contents($jsonFilePath);
        $orders = json_decode($jsonData, true);

        if ($orders === null) {
            $io->error('Invalid JSON data!');
            return Command::FAILURE;
        }

        foreach ($orders as $orderData) {
            $order = new Order();
            $order->setId($orderData['id']);
            $order->setDate(new \DateTime($orderData['date']));
            $order->setCustomer($orderData['customer']);
            $order->setAddress1($orderData['address1']);
            $order->setCity($orderData['city']);
            $order->setPostcode($orderData['postcode']);
            $order->setCountry($orderData['country']);
            $order->setAmount($orderData['amount']);
            $order->setStatus($orderData['status']);
            $order->setDeleted($orderData['deleted']);
            $order->setLastModified(new \DateTime($orderData['last_modified']));

            $this->entityManager->persist($order);
        }

        $this->entityManager->flush();

        $io->success('Data successfully loaded into the database.');

        return Command::SUCCESS;
    }
}
