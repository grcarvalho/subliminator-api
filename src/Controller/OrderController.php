<?php

namespace App\Controller;

use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class OrderController extends AbstractController
{
    #[Route('/api/orders', name: 'order_list', methods: ['GET'])]
    public function list(EntityManagerInterface $entityManager, PaginatorInterface $paginator, Request $request): Response
    {
        // Query para obter todas as ordens
        $query = $entityManager->getRepository(Order::class)->createQueryBuilder('o')
            ->orderBy('o.id', 'ASC')
            ->getQuery();

        // Paginação
        $pagination = $paginator->paginate(
            $query, // Query de dados
            $request->query->getInt('page', 1), // Número da página
            10 // Número de itens por página
        );

        // Construir a resposta JSON
        $orders = [];
        foreach ($pagination->getItems() as $order) {
            $orders[] = [
                'id' => $order->getId(),
                'date' => $order->getDate()->format('Y-m-d H:i:s'),
                'customer' => $order->getCustomer(),
                'address1' => $order->getAddress1(),
                'city' => $order->getCity(),
                'postcode' => $order->getPostcode(),
                'country' => $order->getCountry(),
                'amount' => $order->getAmount(),
                'status' => $order->getStatus(),
                'deleted' => $order->getDeleted(),
                'last_modified' => $order->getLastModified()->format('Y-m-d H:i:s')
            ];
        }

        return $this->json([
            'orders' => $orders,
            'pagination' => [
                'total_items' => $pagination->getTotalItemCount(),
                'current_page' => $pagination->getCurrentPageNumber(),
                'items_per_page' => $pagination->getItemNumberPerPage(),
                'total_pages' => ceil($pagination->getTotalItemCount() / 10)
            ]
        ]);
    }

    /**
     * @Route("/api/orders/{id}", name="order_get", methods={"GET"})
     */
    #[Route('/api/orders/{id}', name: 'order_get', methods: ['GET'])]
    public function getOrder(int $id, EntityManagerInterface $entityManager, PaginatorInterface $paginator, Request $request): JsonResponse
    {
        // $order = $entityManager->getRepository(Order::class)->find($id);

        // if ($order === null) {
        //     return $this->json(['message' => 'Order not found'], Response::HTTP_NOT_FOUND);
        // }

        // //return $this->json($order);
        // return $this->json($order);

        $query = $entityManager->getRepository(Order::class)->createQueryBuilder('o')
            ->orderBy('o.id', 'ASC')
            ->where('o.id = :id')
            ->setParameter('id', $id)
            ->getQuery();

        // Paginação
        $pagination = $paginator->paginate(
            $query, // Query de dados
            $request->query->getInt('page', 1), // Número da página
            10 // Número de itens por página
        );

        // Construir a resposta JSON
        $orders = [];
        foreach ($pagination->getItems() as $order) {
            return $this->json([
                'id' => $order->getId(),
                'date' => $order->getDate()->format('Y-m-d H:i:s'),
                'customer' => $order->getCustomer(),
                'address1' => $order->getAddress1(),
                'city' => $order->getCity(),
                'postcode' => $order->getPostcode(),
                'country' => $order->getCountry(),
                'amount' => $order->getAmount(),
                'status' => $order->getStatus(),
                'deleted' => $order->getDeleted(),
                'last_modified' => $order->getLastModified()->format('Y-m-d H:i:s')
            ]);
        }
    }

    /**
     * @Route("/api/orders/{id}", name="order_update", methods={"PUT"})
     */
    #[Route('/api/orders/{id}', name: 'order_update', methods: ['PUT'])]
    public function updateOrder(int $id, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $order = $entityManager->getRepository(Order::class)->find($id);

        if (!$order) {
            return $this->json(['message' => 'Order not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['customer'])) {
            $order->setCustomer($data['customer']);
        }
        if (isset($data['address1'])) {
            $order->setAddress1($data['address1']);
        }
        if (isset($data['city'])) {
            $order->setCity($data['city']);
        }
        if (isset($data['postcode'])) {
            $order->setPostcode($data['postcode']);
        }
        if (isset($data['country'])) {
            $order->setCountry($data['country']);
        }
        if (isset($data['amount'])) {
            $order->setAmount($data['amount']);
        }
        if (isset($data['status'])) {
            $order->setStatus($data['status']);
        }

        $order->setLastModified(new \DateTime());

        $entityManager->persist($order);
        $entityManager->flush();

        return $this->json($order);
    }
}
