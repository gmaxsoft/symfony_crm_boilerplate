<?php

declare(strict_types=1);

namespace App\Shared\Command;

use App\Modules\Access\Entity\Role;
use App\Modules\Access\Repository\RoleRepository;
use App\Modules\Admin\Entity\User;
use App\Modules\Admin\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:db:seed',
    description: 'Wypełnia bazę danych danymi startowymi (role + konto administratora).',
)]
final class DatabaseSeedCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface      $em,
        private readonly RoleRepository              $roleRepository,
        private readonly UserRepository              $userRepository,
        private readonly UserPasswordHasherInterface $hasher,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                'fresh',
                null,
                InputOption::VALUE_NONE,
                'Wyczyść istniejące dane przed seedowaniem (UWAGA: usuwa wszystkich użytkowników i role).',
            )
            ->addOption(
                'admin-email',
                null,
                InputOption::VALUE_OPTIONAL,
                'Adres e-mail konta administratora.',
                'admin@venom.pl',
            )
            ->addOption(
                'admin-password',
                null,
                InputOption::VALUE_OPTIONAL,
                'Hasło konta administratora.',
                'Admin123!',
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('VENOM CRM — Database Seeder');

        if ($input->getOption('fresh')) {
            $io->warning('Opcja --fresh: czyszczenie istniejących danych…');
            $this->em->createQuery('DELETE FROM App\Modules\Admin\Entity\User')->execute();
            $this->em->createQuery('DELETE FROM App\Modules\Access\Entity\Role')->execute();
            $io->text('Dane wyczyszczone.');
        }

        // ── 1. Role ───────────────────────────────────────────────────
        $io->section('1. Role systemowe');

        $rolesData = [
            [
                'name'        => Role::ADMINISTRATOR,
                'description' => 'Pełny dostęp do wszystkich funkcji systemu CRM.',
            ],
            [
                'name'        => Role::EMPLOYEE_ADMIN,
                'description' => 'Dostęp do zarządzania kontrahentami i raportów.',
            ],
            [
                'name'        => Role::SALESPERSON,
                'description' => 'Dostęp do własnych kontrahentów i szans sprzedaży.',
            ],
        ];

        $roleMap = [];
        foreach ($rolesData as $data) {
            $existing = $this->roleRepository->findByName($data['name']);

            if ($existing) {
                $roleMap[$data['name']] = $existing;
                $io->text(sprintf('  ✓ Rola "%s" już istnieje — pomijam.', $data['name']));
                continue;
            }

            $role = (new Role())
                ->setName($data['name'])
                ->setDescription($data['description']);

            $this->em->persist($role);
            $this->em->flush();

            $roleMap[$data['name']] = $role;
            $io->text(sprintf('  + Utworzono rolę "%s".', $data['name']));
        }

        // ── 2. Konto administratora ───────────────────────────────────
        $io->section('2. Konto administratora');

        $adminEmail    = $input->getOption('admin-email');
        $adminPassword = $input->getOption('admin-password');

        $existing = $this->userRepository->findByEmail($adminEmail);

        if ($existing) {
            $io->text(sprintf('  ✓ Użytkownik "%s" już istnieje — pomijam.', $adminEmail));
        } else {
            /** @var Role $adminRole */
            $adminRole = $roleMap[Role::ADMINISTRATOR];

            $admin = (new User())
                ->setEmail($adminEmail)
                ->setFirstName('Admin')
                ->setLastName('System')
                ->setRole($adminRole)
                ->setIsActive(true);

            $admin->setPassword($this->hasher->hashPassword($admin, $adminPassword));

            $this->em->persist($admin);
            $this->em->flush();

            $io->text(sprintf('  + Utworzono konto administratora: %s', $adminEmail));
        }

        // ── 3. Podsumowanie ───────────────────────────────────────────
        $io->success('Seed zakończony pomyślnie!');

        $io->table(
            ['Parametr', 'Wartość'],
            [
                ['URL logowania (frontend)', 'http://localhost:5173/login'],
                ['E-mail administratora',    $adminEmail],
                ['Hasło administratora',     $adminPassword],
                ['Role w systemie',          implode(', ', array_keys($roleMap))],
            ]
        );

        $io->note([
            'Zmień hasło administratora po pierwszym logowaniu.',
            'Aby ponownie wygenerować dane, użyj flagi: --fresh',
        ]);

        return Command::SUCCESS;
    }
}
