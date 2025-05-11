<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;
use Symfony\Component\Filesystem\Filesystem;

#[AsCommand(
    name: 'app:generate:entities',
    description: 'Generate entities and repositories from database schema'
)]
class GenerateEntitiesCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setDescription('Generate entities and repositories from database schema')
            ->setHelp('This command analyzes the database schema and generates Doctrine entities and repositories.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Generating Entities and Repositories from Database');

        $filesystem = new Filesystem();
        $entityDir = 'src/Entity';
        $repositoryDir = 'src/Repository';
        $mappingDir = 'config/doctrine';

        // Ensure directories exist
        $filesystem->mkdir([$entityDir, $repositoryDir, $mappingDir]);

        // Step 1: Import database schema to XML mappings
        $io->section('Importing database schema to XML mappings');
        $tables = ['users', 'consultations', 'articles', 'category'];
        $process = new Process([
            'php', 'bin/console', 'doctrine:mapping:import',
            '--force',
            '--path=config/doctrine',
            'App\\Entity',
            'xml',
            '--filter=' . implode(',', $tables)
        ]);
        $process->run();

        if (!$process->isSuccessful()) {
            $io->error('Failed to import database schema: ' . $process->getErrorOutput());
            return Command::FAILURE;
        }
        $io->success('Database schema imported to XML mappings.');

        // Step 2: Convert XML mappings to annotations
        $io->section('Converting XML mappings to entity annotations');
        foreach ($tables as $table) {
            $process = new Process([
                'php', 'bin/console', 'doctrine:mapping:convert',
                '--from-database',
                '--force',
                'annotation',
                'src/Entity',
                '--filter=' . $table
            ]);
            $process->run();

            if (!$process->isSuccessful()) {
                $io->error('Failed to convert mapping for ' . $table . ': ' . $process->getErrorOutput());
                return Command::FAILURE;
            }
        }
        $io->success('XML mappings converted to entity annotations.');

        // Step 3: Generate repositories
        $io->section('Generating repositories');
        foreach ($tables as $table) {
            $entityClass = 'App\\Entity\\' . ucfirst($table);
            $process = new Process([
                'php', 'bin/console', 'make:entity',
                '--regenerate',
                '--overwrite',
                $entityClass
            ]);
            $process->run();

            if (!$process->isSuccessful()) {
                $io->error('Failed to generate repository for ' . $table . ': ' . $process->getErrorOutput());
                return Command::FAILURE;
            }
        }
        $io->success('Repositories generated successfully.');

        // Clean up XML mappings (optional)
        $filesystem->remove($mappingDir);

        $io->success('Entities and repositories generated successfully in src/Entity and src/Repository.');
        return Command::SUCCESS;
    }
}
