<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Modules\Access\Entity\Role;
use App\Modules\Admin\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Bazowa klasa testów integracyjnych API.
 * Każdy test jest owijany w transakcję przez DAMA\DoctrineTestBundle
 * i automatycznie wycofywany — baza zostaje czysta między testami.
 */
abstract class ApiTestCase extends WebTestCase
{
    protected KernelBrowser $client;
    protected EntityManagerInterface $em;

    #[\Override]
    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->em = static::getContainer()->get(EntityManagerInterface::class);
    }

    // ── Helpers: tworzenie danych testowych ──────────────────────────────────

    protected function createRole(string $name, ?string $description = null): Role
    {
        $role = (new Role())->setName($name)->setDescription($description);
        $this->em->persist($role);
        $this->em->flush();

        return $role;
    }

    protected function createUser(
        string $email,
        string $password,
        Role $role,
        string $firstName = 'Test',
        string $lastName = 'User',
        bool $isActive = true,
    ): User {
        $hasher = static::getContainer()->get(UserPasswordHasherInterface::class);

        $user = (new User())
            ->setEmail($email)
            ->setFirstName($firstName)
            ->setLastName($lastName)
            ->setRole($role)
            ->setIsActive($isActive);

        $user->setPassword($hasher->hashPassword($user, $password));

        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }

    /**
     * Loguje użytkownika i zwraca JWT token.
     */
    protected function getToken(string $email, string $password): string
    {
        $this->client->request(
            'POST',
            '/api/auth/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['email' => $email, 'password' => $password], \JSON_THROW_ON_ERROR),
        );

        $data = json_decode((string) $this->client->getResponse()->getContent(), true);
        self::assertArrayHasKey('token', $data, 'Logowanie nie zwróciło tokenu JWT.');

        return $data['token'];
    }

    // ── Helpers: żądania HTTP ────────────────────────────────────────────────

    /**
     * @param (bool|int|string)[]|null $body
     *
     * @psalm-param array{name?: string, description?: 'Nowy opis'|'Opis nowej roli'|'X', email?: string, password?: string, firstName?: 'Nowe'|'Nowy'|'X', lastName?: 'ImieNazwisko'|'Uzytkownik'|'Y', roleId?: int, isActive?: bool, phone?: '+48 500 600 700', nip?: '1234567890', city?: 'Kraków', country?: 'Polska', status?: 'inactive'|'prospect'}|null $body
     */
    protected function jsonRequest(
        string $method,
        string $uri,
        ?array $body = null,
        ?string $token = null,
    ): array {
        $headers = ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'];
        if ($token !== null) {
            $headers['HTTP_AUTHORIZATION'] = 'Bearer ' . $token;
        }

        $this->client->request(
            $method,
            $uri,
            [],
            [],
            $headers,
            $body !== null ? json_encode($body, \JSON_THROW_ON_ERROR) : null,
        );

        return json_decode((string) $this->client->getResponse()->getContent(), true) ?? [];
    }

    protected function assertStatusCode(int $expected): void
    {
        self::assertResponseStatusCodeSame($expected);
    }
}
