<?php
// src/Command/HashPasswordsCommand.php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:hash-passwords',
    description: 'Hashes existing plain text passwords in the database',
)]
class HashPasswordsCommand extends Command
{
    private UserRepository $userRepository;
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ) {
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
        parent::__construct();
    }

    // Optional: Add an argument to hash a specific user by username
    protected function configure(): void
    {
        $this
            ->addArgument('username', InputArgument::OPTIONAL, 'Username of a specific user to hash')
        ;
    }


    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $username = $input->getArgument('username');

        if ($username) {
            // Hash password for a specific user
            $user = $this->userRepository->findOneBy(['username' => $username]);

            if (!$user) {
                $io->error(sprintf('User "%s" not found.', $username));
                return Command::FAILURE;
            }

            // !!! WARNING: This assumes the current password in the DB is the plain text one you want to hash !!!
            // For safety, you might want to prompt for the plain text password if you're unsure.
            $plainPassword = $user->getPassword(); // THIS IS RISKY IF PASSWORDS ARE ALREADY HASHED!

             // Safest approach: Hash specific known plain text passwords
            $plainPassword = $io->ask(sprintf('Enter the plain text password for user "%s":', $username));

            if (!$plainPassword) {
                 $io->error('Password cannot be empty.');
                 return Command::FAILURE;
            }


            try {
                 $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
                 $user->setPassword($hashedPassword);
                 $this->entityManager->flush();
                 $io->success(sprintf('Password hashed successfully for user "%s".', $username));
                 return Command::SUCCESS;
            } catch (\Exception $e) {
                 $io->error('An error occurred while hashing the password: ' . $e->getMessage());
                 return Command::FAILURE;
            }


        } else {
             // !!! WARNING: This will attempt to re-hash ALL user passwords.
             // Only run this if you are certain ALL current passwords are plain text!
             $io->warning('Hashing ALL user passwords. This assumes they are currently in plain text.');

             if (!$io->confirm('Are you sure you want to hash all passwords? This is irreversible.', false)) {
                 return Command::SUCCESS;
             }

             $users = $this->userRepository->findAll();
             $count = 0;

             foreach ($users as $user) {
                 // THIS IS EXTREMELY RISKY IF SOME PASSWORDS ARE ALREADY HASHED!
                 // The safer approach is to hash known plain text passwords one by one.
                 $plainPassword = null;
                 // You would need a way to know the plain text password for each user here.
                 // Example: based on username, use a hardcoded temporary password map (NOT RECOMMENDED).
                 // Or, prompt for each user's plain text password (impractical for many users).

                 // Recommended: Manually update key users with known plain text passwords
                 // Find user by username/email and set their password explicitly here
                 // e.g. if ($user->getUsername() === 'admin') { $plainPassword = 'admin1234'; }
                 // e.g. if ($user->getUsername() === 'doctor') { $plainPassword = 'doctor123'; }
                 // e.g. if ($user->getUsername() === 'patient') { $plainPassword = 'patient123'; }

                 if ($user->getUsername() === 'admin') {
                     $plainPassword = 'admin1234'; // Replace with the actual plain text password
                 } elseif ($user->getUsername() === 'doctor') {
                      $plainPassword = 'doctor123'; // Replace with the actual plain text password
                 } elseif ($user->getUsername() === 'patient') {
                     $plainPassword = 'patient123'; // Replace with the actual plain text password
                 } elseif ($user->getUsername() === 'doctor2') {
                     $plainPassword = 'therapy456'; // Replace with the actual plain text password
                 } elseif ($user->getUsername() === 'patient2') {
                     $plainPassword = 'patient456'; // Replace with the actual plain text password
                 }
                 // Add conditions for any other users with plain text passwords you need to hash

                 if ($plainPassword !== null) {
                      $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
                      $user->setPassword($hashedPassword);
                      $count++;
                 } else {
                     // Skip users whose plain text password is not known or already hashed
                      $io->note(sprintf('Skipping user "%s" (plain password not known or already processed).', $user->getUsername()));
                 }

             } // End foreach

             $this->entityManager->flush();
             $io->success(sprintf('Hashed passwords for %d users.', $count));
             return Command::SUCCESS;

        } // End else (hash all users)
    }
}